<?php //strict

namespace PayPal\Helper;

use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;

use PayPal\Services\SessionStorageService;

/**
 * Class PaymentHelper
 * @package PayPal\Helper
 */
class PaymentHelper
{
    const PAYMENTKEY_PAYPAL = 'PAYPAL';
    const PAYMENTKEY_PAYPALEXPRESS = 'PAYPALEXPRESS';
    const PAYMENTKEY_PAYPALPLUS = 'PAYPALPLUS';
    const PAYMENTKEY_PAYPALINSTALLMENT = 'PAYPALINSTALLMENT';

    const MODE_PAYPAL = 'paypal';
    const MODE_PAYPALEXPRESS = 'paypalexpress';
    const MODE_PAYPAL_PLUS = 'plus';
    const MODE_PAYPAL_INSTALLMENT = 'installment';
    const MODE_PAYPAL_NOTIFICATION = 'notification';

    /**
     * @var PaymentMethodRepositoryContract
     */
    private $paymentMethodRepository;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var SessionStorageService
     */
    private $sessionService;

    /**
     * @var PaymentOrderRelationRepositoryContract
     */
    private $paymentOrderRelationRepo;

    /**
     * @var PaymentRepositoryContract
     */
    private $paymentRepository;

    /**
     * @var OrderRepositoryContract
     */
    private $orderRepo;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var array
     */
    private $statusMap = array();

    /**
     * PaymentHelper constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     * @param PaymentRepositoryContract $paymentRepo
     * @param PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo
     * @param ConfigRepository $config
     * @param SessionStorageService $sessionService
     * @param OrderRepositoryContract $orderRepo
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                                PaymentRepositoryContract $paymentRepo,
                                PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo,
                                ConfigRepository $config,
                                SessionStorageService $sessionService,
                                OrderRepositoryContract $orderRepo)
    {
        $this->config                                   = $config;
        $this->sessionService                           = $sessionService;
        $this->paymentMethodRepository                  = $paymentMethodRepository;
        $this->paymentOrderRelationRepo                 = $paymentOrderRelationRepo;
        $this->paymentRepository                        = $paymentRepo;
        $this->orderRepo                                = $orderRepo;
        $this->statusMap                                = array();
    }

    public function getPayPalMopIdByPaymentKey($paymentKey)
    {
        if(strlen($paymentKey))
        {
            // List all payment methods for the given plugin
            $paymentMethods = $this->paymentMethodRepository->allForPlugin('plentyPayPal');

            if( !is_null($paymentMethods) )
            {
                foreach($paymentMethods as $paymentMethod)
                {
                    if($paymentMethod->paymentKey == $paymentKey)
                    {
                        return $paymentMethod->id;
                    }
                }
            }
        }

        return 'no_paymentmethod_found';
    }

    /**
     * Get the REST return URLs for the given mode
     *
     * @param string $mode
     * @return array(success => $url, cancel => $url)
     */
    public function getRestReturnUrls($mode)
    {
        $domain = $this->getDomain();

        $urls = [];

        switch($mode)
        {
            case self::MODE_PAYPAL_PLUS:
            case self::MODE_PAYPAL:
                $urls['success'] = $domain.'/payment/payPal/checkoutSuccess/'.$mode;
                $urls['cancel'] = $domain.'/payment/payPal/checkoutCancel/'.$mode;
                break;
            case self::MODE_PAYPAL_INSTALLMENT:
                $urls['success'] = $domain.'/payment/payPalInstallment/prepareInstallment';
                $urls['cancel'] = $domain.'/payment/payPal/checkoutCancel/'.$mode;
                break;
            case self::MODE_PAYPALEXPRESS:
                $urls['success'] = $domain.'/payment/payPal/expressCheckoutSuccess';
                $urls['cancel'] = $domain.'/payment/payPal/expressCheckoutCancel';
                break;
            case self::MODE_PAYPAL_NOTIFICATION:
                $urls[self::MODE_PAYPAL_NOTIFICATION] = $domain.'/payment/payPal/notification';
                break;
        }

        return $urls;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        /** @var \Plenty\Modules\Helper\Services\WebstoreHelper $webstoreHelper */
        $webstoreHelper = pluginApp(\Plenty\Modules\Helper\Services\WebstoreHelper::class);

        /** @var \Plenty\Modules\System\Models\WebstoreConfiguration $webstoreConfig */
        $webstoreConfig = $webstoreHelper->getCurrentWebstoreConfiguration();

        $domain = $webstoreConfig->domainSsl;
        if($domain == 'http://dbmaster.plenty-showcase.de' || $domain == 'http://dbmaster-beta7.plentymarkets.eu' || $domain == 'http://dbmaster-stable7.plentymarkets.eu')
        {
            $domain = 'http://master.plentymarkets.com';
        }

        return $domain;
    }

    /**
     * Create a payment in plentymarkets from the paypal execution response data
     *
     * @param array $paypalPaymentData
     * @param array $paymentData
     * @return Payment
     */
    public function createPlentyPayment(array $paypalPaymentData, $paymentData = [])
    {
        /** @var Payment $payment */
        $payment = pluginApp( \Plenty\Modules\Payment\Models\Payment::class );

        $payment->mopId             = (int)$this->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPAL);
        $payment->transactionType   = Payment::TRANSACTION_TYPE_BOOKED_POSTING;
        $payment->status            = $this->mapStatus((STRING)$paypalPaymentData['state']);
        $payment->currency          = $paypalPaymentData['transactions'][0]['amount']['currency']?$paypalPaymentData['transactions'][0]['amount']['currency']:'EUR';
        $payment->amount            = $paypalPaymentData['transactions'][0]['amount']['total'];
        $payment->receivedAt        = $paypalPaymentData['create_time'];

        if(!empty($paymentData['type']))
        {
            $payment->type = $paymentData['type'];
        }

        if(!empty($paymentData['parentId']))
        {
            $payment->parentId = $paymentData['parentId'];
        }

        if(!empty($paymentData['unaccountable']))
        {
            $payment->unaccountable = $paymentData['unaccountable'];
        }

        $paymentProperty = [];

        /**
         * Add payment property with type booking text
         */
        $paymentProperty[] = $this->getPaymentProperty(PaymentProperty::TYPE_BOOKING_TEXT, 'TransactionID: '.(string)$paypalPaymentData['transactions'][0]['related_resources'][0]['sale']['id']);

        /**
         * Add payment property with type transactionId
         */
        $paymentProperty[] = $this->getPaymentProperty(PaymentProperty::TYPE_TRANSACTION_ID, $paypalPaymentData['transactions'][0]['related_resources'][0]['sale']['id']);

        /**
         * Add payment property with type origin
         */
        $paymentProperty[] = $this->getPaymentProperty(PaymentProperty::TYPE_ORIGIN, Payment::ORIGIN_PLUGIN);

        /**
         * Add payment property with type account of the receiver
         */
        $paymentProperty[] = $this->getPaymentProperty(PaymentProperty::TYPE_ACCOUNT_OF_RECEIVER, $paypalPaymentData['id']);

        if(!empty($paypalPaymentData[SessionStorageService::PAYPAL_INSTALLMENT_COSTS])
        && is_array($paypalPaymentData[SessionStorageService::PAYPAL_INSTALLMENT_COSTS]))
        {
            $creditFinancing = $paypalPaymentData[SessionStorageService::PAYPAL_INSTALLMENT_COSTS];

            $paymentText = [];
            $paymentText['financingCosts'] = $creditFinancing['total_interest']['value'];
            $paymentText['totalCostsIncludeFinancing'] = $creditFinancing['total_cost']['value'];
            $paymentText['currency'] = $creditFinancing['total_cost']['currency'];

            /**
             * Add payment property with type payment text
             */
            $paymentProperty[] = $this->getPaymentProperty(PaymentProperty::TYPE_PAYMENT_TEXT, json_encode($paymentText));
        }

        if(!empty($paypalPaymentData['payment_instruction']) && is_array($paypalPaymentData['payment_instruction']))
        {
            if(is_array($paypalPaymentData['payment_instruction']['recipient_banking_instruction']))
            {
                $paymentText = [];
                $paymentText['bankName'] = $paypalPaymentData['payment_instruction']['recipient_banking_instruction']['bank_name'];
                $paymentText['accountHolder'] = $paypalPaymentData['payment_instruction']['recipient_banking_instruction']['account_holder_name'];
                $paymentText['iban'] = $paypalPaymentData['payment_instruction']['recipient_banking_instruction']['international_bank_account_number'];
                $paymentText['bic'] = $paypalPaymentData['payment_instruction']['recipient_banking_instruction']['bank_identifier_code'];
                $paymentText['referenceNumber'] = $paypalPaymentData['payment_instruction']['reference_number'];
                $paymentText['paymentDue'] = $paypalPaymentData['payment_instruction']['payment_due_date'];

                /**
                 * Add payment property with type payment text
                 */
                $paymentProperty[] = $this->getPaymentProperty(PaymentProperty::TYPE_PAYMENT_TEXT, json_encode($paymentText));
            }
        }

        /**
         * TODO Add the following properties
         *
         * IBAN BIC empfänger
         *
         * // Gebühr
         *  $paypalPaymentData['transactions'][0]['related_resources'][0]['sale']['transaction_fee']['value']
         *  $paypalPaymentData['transactions'][0]['related_resources'][0]['sale']['transaction_fee']['currency']
         *
         * // Invoice number
         *  $paypalPaymentData['transactions'][0]['invoice_number']
         */

        $payment->properties = $paymentProperty;
        $payment->regenerateHash = true;

        $payment = $this->paymentRepository->createPayment($payment);

        return $payment;
    }

    /**
     * Returns a PaymentProperty with the given params
     *
     * @param Payment $payment
     * @param array $data
     * @return PaymentProperty
     */
    private function getPaymentProperty($typeId, $value)
    {
        /** @var PaymentProperty $paymentProperty */
        $paymentProperty = pluginApp( \Plenty\Modules\Payment\Models\PaymentProperty::class );

        $paymentProperty->typeId = $typeId;
        $paymentProperty->value = $value;

        return $paymentProperty;
    }

    /**
     * @param $saleId
     * @param $state
     */
    public function updatePayment($saleId, $state)
    {
        /** @var array $payments */
        $payments = $this->paymentRepository->getPaymentsByPropertyTypeAndValue(PaymentProperty::TYPE_TRANSACTION_ID, $saleId);

        // update the payment
        if(!empty($payments))
        {
            $state = $this->mapStatus((STRING)$state);

            /** @var Payment $payment */
            foreach($payments as $payment)
            {
                if($payment->status != $state)
                {
                    $payment->status = $state;
                    $payment->updateOrderPaymentStatus = true;

                    if($state == Payment::STATUS_APPROVED || $state == Payment::STATUS_CAPTURED)
                    {
                        $payment->unaccountable = 0;
                    }

                    $payment->regenerateHash = true;
                    $this->paymentRepository->updatePayment($payment);
                }
            }
        }
        // create a new payment
        else
        {
            /** @var \PayPal\Services\PaymentService $paymentService */
            $paymentService = pluginApp(\PayPal\Services\PaymentService::class);

            $sale = $paymentService->getSaleDetails($saleId);

            if(empty($sale['error']))
            {
                $this->createPlentyPayment($sale);
            }
        }
    }

    /**
     * Assign the payment to an order in plentymarkets
     *
     * @param Payment $payment
     * @param int $orderId
     */
    public function assignPlentyPaymentToPlentyOrder(Payment $payment, int $orderId)
    {
        // Get the order by the given order ID
        $order = $this->orderRepo->findOrderById($orderId);

        // Check whether the order truly exists in plentymarkets
        if(!is_null($order) && $order instanceof Order)
        {
            // Assign the given payment to the given order
            $this->paymentOrderRelationRepo->createOrderRelation($payment, $order);
        }
    }



    // TODO: assignPlentyPaymentToPlentyContact



    /**
     * Map the PayPal payment status to the plentymarkets payment status
     *
     * @param string $status
     * @return int
     *
     */
    public function mapStatus(string $status)
    {
        if(!is_array($this->statusMap) || count($this->statusMap) <= 0)
        {
            $statusConstants = $this->paymentRepository->getStatusConstants();

            if(!is_null($statusConstants) && is_array($statusConstants))
            {
                $this->statusMap['created']               = $statusConstants['captured'];
                $this->statusMap['approved']              = $statusConstants['approved'];
                $this->statusMap['failed']                = $statusConstants['refused'];
                $this->statusMap['partially_completed']   = $statusConstants['partially_captured'];
                $this->statusMap['completed']             = $statusConstants['captured'];
                $this->statusMap['in_progress']           = $statusConstants['awaiting_approval'];
                $this->statusMap['pending']               = $statusConstants['awaiting_approval'];
                $this->statusMap['refunded']              = $statusConstants['refunded'];
                $this->statusMap['denied']                = $statusConstants['refused'];
            }
        }

        return strlen($status)?(int)$this->statusMap[$status]:2;
    }

    /**
     * @param Payment $payment
     * @param int $propertyType
     * @return null|string
     */
    public function getPaymentPropertyValue($payment, $propertyType)
    {
        $properties = $payment->properties;

        if(($properties->count() > 0) || (is_array($properties ) && count($properties ) > 0))
        {
            /** @var PaymentProperty $property */
            foreach($properties as $property)
            {
                if($property instanceof PaymentProperty)
                {
                    if($property->typeId == $propertyType)
                    {
                        return $property->value;
                    }
                }
            }
        }

        return null;
    }

}

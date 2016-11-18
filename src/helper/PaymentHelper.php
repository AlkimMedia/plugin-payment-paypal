<?php //strict

namespace PayPal\Helper;

use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;

use PayPal\Services\SessionStorageService;

/**
 * Class PaymentHelper
 * @package PayPal\Helper
 */
class PaymentHelper
{
    const MODE_PAYPAL = 'paypal';
    const MODE_PAYPALEXPRESS = 'paypalexpress';

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

    /**
     * Get the ID of the PayPal payment method
     *
     * @return mixed
     */
    public function getPayPalMopId()
    {
        // List all payment methods for the given plugin
        $paymentMethods = $this->paymentMethodRepository->allForPlugin('plentyPayPal');

        if( !is_null($paymentMethods) )
        {
            foreach($paymentMethods as $paymentMethod)
            {
                if($paymentMethod->paymentKey == 'PAYPAL')
                {
                    return $paymentMethod->id;
                }
            }
        }

        return 'no_paymentmethod_found';
    }

    /**
     * Get the ID of the PayPal Express payment method
     *
     * @return mixed
     */
    public function getPayPalExpressMopId()
    {
        // List all payment methods for the given plugin
        $paymentMethods = $this->paymentMethodRepository->allForPlugin('plentyPayPal');

        if( !is_null($paymentMethods) )
        {
            foreach($paymentMethods as $paymentMethod)
            {
                if($paymentMethod->paymentKey == 'PAYPALEXPRESS')
                {
                    return $paymentMethod->id;
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
        /** @var \Plenty\Modules\Helper\Services\WebstoreHelper $webstoreHelper */
        $webstoreHelper = pluginApp(\Plenty\Modules\Helper\Services\WebstoreHelper::class);

        /** @var \Plenty\Modules\System\Models\WebstoreConfiguration $webstoreConfig */
        $webstoreConfig = $webstoreHelper->getCurrentWebstoreConfiguration();

        if(is_null($webstoreConfig))
        {
            return 'error';
        }

        $domain = $webstoreConfig->domainSsl;

        $urls = array();

        switch($mode)
        {
            case self::MODE_PAYPAL:
                $urls['success'] = $domain.'/payPal/checkoutSuccess';
                $urls['cancel'] = $domain.'/payPal/checkoutCancel';
                break;
            case self::MODE_PAYPALEXPRESS:
                $urls['success'] = $domain.'/payPal/expressCheckoutSuccess';
                $urls['cancel'] = $domain.'/payPal/expressCheckoutCancel';
                break;
        }

        return $urls;
    }

    /**
     * Create a payment in plentymarkets from the paypal execution response data
     *
     * @param array $paymentData
     * @return Payment
     */
    public function createPlentyPayment(array $paymentData)
    {
        /** @var Payment $payment */
        $payment = pluginApp( \Plenty\Modules\Payment\Models\Payment::class );

        $payment->mopId             = (int)$this->getPayPalMopId();
        $payment->transactionType   = 2;
        $payment->status            = $this->mapStatus($paymentData['status']);
        $payment->currency          = $paymentData['currency'];
        $payment->amount            = $paymentData['amount'];
        $payment->receivedAt        = $paymentData['entryDate'];

        $paymentProperty = array();

        /**
         * Add payment property with type booking text
         */
        $paymentProperty[] = $this->getPaymentProperty(3, 'TransactionID: '.(string)$paymentData['bookingText']);

        /**
         * read the origin constants from the payment repository
         */
        $originConstants = $this->paymentRepository->getOriginConstants();

        if(!is_null($originConstants) && is_array($originConstants))
        {
            $originValue = (string)$originConstants['plugin'];

            /**
             * Add payment property with type origin
             */
            $paymentProperty[] = $this->getPaymentProperty(23, $originValue);
        }

        $payment->property = $paymentProperty;

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

        return (int)$this->statusMap[$status];
    }
}

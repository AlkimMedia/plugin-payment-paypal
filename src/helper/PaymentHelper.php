<?php //strict

namespace PayPal\Helper;

use Plenty\Modules\Payment\Contracts\PaymentPropertyRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Helper\Services\WebstoreHelper;

use PayPal\Services\SessionStorageService;

/**
 * Class PaymentHelper
 * @package PayPal\Helper
 */
class PaymentHelper
{
      /**
       * @var Application
       */
      private $app;

      /**
       * @var WebstoreHelper
       */
      private $webstoreHelper;

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
       * @var PaymentPropertyRepositoryContract
       */
      private $paymentPropertyRepositoryContract;

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
       * @param Application $app
       * @param PaymentMethodRepositoryContract $paymentMethodRepository
       * @param PaymentRepositoryContract $paymentRepo
       * @param PaymentPropertyRepositoryContract $paymentPropertyRepositoryContract
       * @param PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo
       * @param ConfigRepository $config
       * @param SessionStorageService $sessionService
       * @param OrderRepositoryContract $orderRepo
       * @param WebstoreHelper $webstoreHelper
       */
      public function __construct(Application $app,
                                  PaymentMethodRepositoryContract $paymentMethodRepository,
                                  PaymentRepositoryContract $paymentRepo,
                                  PaymentPropertyRepositoryContract $paymentPropertyRepositoryContract,
                                  PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo,
                                  ConfigRepository $config,
                                  SessionStorageService $sessionService,
                                  OrderRepositoryContract $orderRepo,
                                  WebstoreHelper $webstoreHelper)
      {
            $this->app                                      = $app;
            $this->webstoreHelper                           = $webstoreHelper;
            $this->config                                   = $config;
            $this->sessionService                           = $sessionService;
            $this->paymentMethodRepository                  = $paymentMethodRepository;
            $this->paymentOrderRelationRepo                 = $paymentOrderRelationRepo;
            $this->paymentRepository                        = $paymentRepo;
            $this->paymentPropertyRepositoryContract        = $paymentPropertyRepositoryContract;
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
       * Get the REST cancellation URL
       *
       * @return string
       */
      public function getRestCancelURL()
      {
            $webstoreConfig = $this->webstoreHelper->getCurrentWebstoreConfiguration();

            if(is_null($webstoreConfig))
            {
                  return 'error';
            }

            $domain = $webstoreConfig->domainSsl;

            return $domain.'/plentyPayPal/payPalCheckoutCancel';
      }

      /**
       * Get the REST success URL
       *
       * @return string
       */
      public function getRestSuccessURL()
      {
            $webstoreConfig = $this->webstoreHelper->getCurrentWebstoreConfiguration();

            if(is_null($webstoreConfig))
            {
                  return 'error';
            }

            $domain = $webstoreConfig->domainSsl;

            return $domain.'/plentyPayPal/payPalCheckoutSuccess';
      }

      /**
       * Create a payment in plentymarkets from the JSON data
       *
       * @param string $json
       * @return Payment
       */
      public function createPlentyPaymentFromJson(string $json)
      {
            $payPalPayment = json_decode($json);

            $paymentData = array();

            // Set the payment data
            $paymentData['mopId']           = (int)$this->getPayPalMopId();
            $paymentData['transactionType'] = 2;
            $paymentData['status']          = $this->mapStatus($payPalPayment->status);
            $paymentData['currency']        = $payPalPayment->currency;
            $paymentData['amount']          = $payPalPayment->amount;
            $paymentData['receivedAt']       = $payPalPayment->entryDate;

            $payment = $this->paymentRepository->createPayment($paymentData);

            /**
             * Add payment property with type booking text
             */
            $this->addPaymentProperty($payment->id, array('typeId'=>3, 'value'=>'PayPalPayID: '.(string)$payPalPayment->bookingText));

            /**
             * Add payment property with type origin
             */
            $originConstants        = $this->paymentRepository->getOriginConstants();
            $paymentPropertyValue      = '';
            if(!is_null($originConstants) && is_array($originConstants))
            {
                  $paymentPropertyValue = (string)$originConstants['plugin'];
            }
            $this->addPaymentProperty($payment->id, array('typeId'=>23, 'value'=>$paymentPropertyValue));

            return $payment;
      }

      /**
       * @param int $paymentId
       * @param array $data
       */
      private function addPaymentProperty(int $paymentId, array $data)
      {
            $paymentPropertyData['paymentId'] = $paymentId;
            $paymentPropertyData['typeId'] = $data['typeId'];
            $paymentPropertyData['value'] = $data['value'];

            $this->paymentPropertyRepositoryContract->createProperty($paymentPropertyData);
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

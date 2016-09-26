<?php //strict

namespace PayPal\Services;

use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;

use PayPal\Helper\PaymentHelper;

/**
 * Class PaymentService
 * @package PayPal\Services
 */
class PaymentService
{
      /**
      * @var PaymentMethodRepositoryContract
      */
      private $paymentMethodRepository;

      /**
      * @var PaymentRepositoryContract
      */
      private $paymentRepository;

      /**
      * @var PaymentHelper
      */
      private $paymentHelper;

      /**
      * @var LibraryCallContract
      */
      private $libCall;

      /**
      * @var AddressRepositoryContract
      */
      private $addressRepo;

      /**
      * @var ConfigRepository
      */
      private $config;

      /**
      * @var bool
      */
      private $sandbox = true;

      /**
      * @var string
      */
      private $returnType = '';

      /**
       * PaymentService constructor.
       *
       * @param PaymentMethodRepositoryContract $paymentMethodRepository
       * @param PaymentRepositoryContract $paymentRepository
       * @param ConfigRepository $config
       * @param PaymentHelper $paymentHelper
       * @param LibraryCallContract $libCall
       * @param AddressRepositoryContract $addressRepo
       */
      public function __construct(  PaymentMethodRepositoryContract $paymentMethodRepository, PaymentRepositoryContract $paymentRepository,
                                    ConfigRepository $config                                , PaymentHelper $paymentHelper,
                                    LibraryCallContract $libCall                            , AddressRepositoryContract $addressRepo)
      {
            $this->paymentMethodRepository    = $paymentMethodRepository;
            $this->paymentRepository          = $paymentRepository;
            $this->paymentHelper              = $paymentHelper;
            $this->libCall                    = $libCall;
            $this->addressRepo                = $addressRepo;
            $this->config                     = $config;

            /*
             * Read from plugin config
             */
            if($config->get('PayPal.environment') == 1)
            {
                  $this->sandbox = true;
            }
      }

      /**
       * Get PayPal payment content
       *
       * @param Basket $basket
       * @return string
       */
      public function getPaymentContent(Basket $basket):string
      {

            $payPalRequestParams = $this->getPaypalParams($basket);

            /*
             * Prepare the PayPal payment
             */
            $result = $this->libCall->call('PayPal::preparePayment', $payPalRequestParams);

            /*
             * Check for errors
             */
            if(is_array($result) && $result['error'])
            {
              $this->returnType = 'errorCode';
              return $result['error_msg'];
            }

            $resultJson = json_decode($result);

            /*
             * Store the PayPal pay ID in the session
             */
            $ppPayId = $resultJson->id;
            if(strlen($ppPayId))
            {
                $this->paymentHelper->setPayPalPayID($ppPayId);
            }

            /*
             * Get PayPal container content
             */
            $paymentContent = '';
            $links = $resultJson->links;
            if(is_array($links))
            {
                  foreach($links as $key => $value)
                  {
                        /*
                         * Get redirect URLs for the content
                         */
                        if($value->method == 'REDIRECT')
                        {
                              $paymentContent = $value->href;
                              $this->returnType = 'redirectUrl';
                        }
                  }
            }

            /*
             * Check if content is set, other wise return error code
             */
            if(!strlen($paymentContent))
            {
              $this->returnType = 'errorCode';
              return 'An unknown error occured, please try again.';
            }

            return $paymentContent;
      }

      /**
       * Return Type of Payment PayPal container content
       *
       * @return string
       */
      public function getReturnType()
      {
            return $this->returnType;
      }

      /**
       * Execute PayPal payment
       *
       * @return string
       */
      public function executePayment()
      {
            /*
            * Load mandatory PayPal data from session
            */
            $ppPayId    = $this->paymentHelper->getPayPalPayID();
            $ppPayerId  = $this->paymentHelper->getPayPalPayerID();

            /*
             * Set PayPal payment execute parameters
             */
            $executeParams = array( 'clientSecret'    => $this->config->get('PayPal.clientSecret'),
                                    'clientId'        => $this->config->get('PayPal.clientId'    ));
            $executeParams['sandbox']   = $this->sandbox;
            $executeParams['payId']     = $ppPayId;
            $executeParams['payerId']   = $ppPayerId;

            /*
             * Execute the PayPal payment
             */
            $executeResponse = $this->libCall->call('PayPal::executePayment', $executeParams);

            /*
             * Check for errors
             */
            if(is_array($executeResponse) && $executeResponse['error'])
            {
                  $this->returnType = 'errorCode';
                  return $executeResponse['error'].': '.$executeResponse['error_msg'];
            }

            $result = json_encode($executeResponse);

            /*
             * Clear the session params
             */
            $this->paymentHelper->setPayPalPayID(null);
            $this->paymentHelper->setPayPalPayerID(null);

            return (string)$result;
      }

      /**
       * Fill and return Paypal parameters
       *
       * @param Basket $basket
       * @return array
       */
      private function getPaypalParams(Basket $basket)
      {
          $payPalRequestParams = array( 'clientSecret'    => $this->config->get('PayPal.clientSecret'),
                                        'clientId'        => $this->config->get('PayPal.clientId'));

          /*
           * Set PayPal basic parameter
           */
          $payPalRequestParams['webProfileId']      = 'XP-3XH9-EMJG-WX7L-789D';
          $payPalRequestParams['sandbox']           = $this->sandbox;
          $payPalRequestParams['basket']            = $basket;
          $payPalRequestParams['basketItems']       = $basket->basketItems;


          /*
           * Fill Address and Country for PayPal parameters
           */
          $address = array();
          $country = array();
          $address['town']            = 'hofteister';
          $country['isoCode2']        = 'DE';
          $address['postalCode']      = '34369';
          $address['firstname']       = 'Franz';
          $address['lastname']        = 'stock';
          $address['street']          = 'Krizstraße';
          $address['houseNumber']     = '23';

          $payPalRequestParams['shippingAddress']     = $address;
          $payPalRequestParams['country']             = $country;

          /*
           * Get URLs for PayPal parameters
           */
          $urls = array('returnUrl' => $this->paymentHelper->getRestSuccessURL(),
                        'cancelUrl' => $this->paymentHelper->getRestCancelURL());

          $payPalRequestParams['urls'] = $urls;

          return $payPalRequestParams;
      }

}

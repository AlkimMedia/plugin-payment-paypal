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
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;

use PayPal\Helper\PaymentHelper;
use PayPal\Services\SessionStorageService;

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
      * @var CountryRepositoryContract
      */
      private $countryRepo;

     /**
      * @var SessionStorageService
      */
      private $sessionStorage;

    /**
     * PaymentService constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     * @param PaymentRepositoryContract $paymentRepository
     * @param ConfigRepository $config
     * @param PaymentHelper $paymentHelper
     * @param LibraryCallContract $libCall
     * @param AddressRepositoryContract $addressRepo
     * @param CountryRepositoryContract $countryRepo
     * @param SessionStorageService $sessionStorage
     */
      public function __construct(  PaymentMethodRepositoryContract $paymentMethodRepository, PaymentRepositoryContract $paymentRepository,
                                    ConfigRepository $config                                , PaymentHelper $paymentHelper,
                                    LibraryCallContract $libCall                            , AddressRepositoryContract $addressRepo,
                                    CountryRepositoryContract $countryRepo                  , SessionStorageService $sessionStorage)
      {
            $this->paymentMethodRepository    = $paymentMethodRepository;
            $this->paymentRepository          = $paymentRepository;
            $this->paymentHelper              = $paymentHelper;
            $this->libCall                    = $libCall;
            $this->addressRepo                = $addressRepo;
            $this->config                     = $config;
            $this->countryRepo                = $countryRepo;
            $this->sessionStorage             = $sessionStorage;

            // Get the PayPal environment. The environment can be set in the config.json.
            if($config->get('PayPal.environment') == 1)
            {
                  $this->sandbox = true;
            }
      }

      /**
       * Get the PayPal payment content
       *
       * @param Basket $basket
       * @return string
       */
      public function getPaymentContent(Basket $basket, $mode = 'paypal'):string
      {
            $payPalRequestParams = $this->getPaypalParams($basket);

            $payPalRequestParams['mode'] = $mode;

            // Prepare the PayPal payment
            $result = $this->libCall->call('PayPal::preparePayment', $payPalRequestParams);

            // Check for errors
            if(is_array($result) && $result['error'])
            {
                $this->returnType = 'errorCode';
                return $result['error_msg'];
            }

            $resultJson = $result;
            if(is_string($result))
            {
                $resultJson = json_decode((string)$result);
            }

            // Store the PayPal Pay ID in the session
            $ppPayId = $resultJson->id;
            if(strlen($ppPayId))
            {
                $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, $ppPayId);
            }

            // Get the content of the PayPal container
            $paymentContent = '';
            $links = $resultJson->links;
            if(is_array($links))
            {
                  foreach($links as $key => $value)
                  {
                        // Get the redirect URLs for the content
                        if($value->method == 'REDIRECT')
                        {
                              $paymentContent = $value->href;
                              $this->returnType = 'redirectUrl';
                        }
                  }
            }

            // Check whether the content is set. Else, return an error code.
            if(!strlen($paymentContent))
            {
              $this->returnType = 'errorCode';
              return 'An unknown error occured, please try again.';
            }

            return $paymentContent;
      }

      /**
       * Get the type of payment from the content of the PayPal container
       *
       * @return string
       */
      public function getReturnType()
      {
            return $this->returnType;
      }

      /**
       * Execute the PayPal payment
       *
       * @return string
       */
      public function executePayment()
      {
            // Load the mandatory PayPal data from session
            $ppPayId    = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);
            $ppPayerId  = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAYER_ID);

            // Set the execute parameters for the PayPal payment
            $executeParams = array( 'clientSecret'    => $this->config->get('PayPal.clientSecret'),
                                    'clientId'        => $this->config->get('PayPal.clientId'    ));

            $executeParams['sandbox']   = $this->sandbox;
            $executeParams['payId']     = $ppPayId;
            $executeParams['payerId']   = $ppPayerId;

            // Execute the PayPal payment
            $executeResponse = $this->libCall->call('PayPal::executePayment', $executeParams);

            // Check for errors
            if(is_array($executeResponse) && $executeResponse['error'])
            {
                  $this->returnType = 'errorCode';
                  return $executeResponse['error'].': '.$executeResponse['error_msg'];
            }

            $result = json_encode($executeResponse);

            // Clear the session parameters
            $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, null);
            $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAYER_ID, null);

            return (string)$result;
      }

      /**
       * Fill and return the Paypal parameters
       *
       * @param Basket $basket
       * @return array
       */
      public function getPaypalParams(Basket $basket = null)
      {
          // Set the PayPal basic parameters
          $payPalRequestParams = array( 'clientSecret'    => $this->config->get('PayPal.clientSecret'),
                                        'clientId'        => $this->config->get('PayPal.clientId'));

          $payPalRequestParams['webProfileId']  = $this->config->get('PayPal.webProfileID');
          $payPalRequestParams['sandbox']       = $this->sandbox;

          $payPalRequestParams['basket']        = $basket;
          $payPalRequestParams['basketItems']   = $basket->basketItems;

          // Read the shipping address ID from the session
          $shippingAddressId = $this->sessionStorage->getSessionValue(SessionStorageService::DELIVERY_ADDRESS_ID);

          if(!is_null($shippingAddressId) && $shippingAddressId > 0)
          {
              $shippingAddress = $this->addressRepo->findAddressById($shippingAddressId);
              $payPalRequestParams['shippingAddress'] = $shippingAddress;
          }

          // Fill the country for PayPal parameters
          $country['isoCode2'] = $this->countryRepo->findIsoCode($basket->shippingCountryId, 'iso_code_2');
          $payPalRequestParams['country'] = $country;

          // Get the URLs for PayPal parameters
          $urls = array('returnUrl' => $this->paymentHelper->getRestSuccessURL(),
                        'cancelUrl' => $this->paymentHelper->getRestCancelURL());

          $payPalRequestParams['urls'] = $urls;

          return $payPalRequestParams;
      }

      public function preparePayPalExpressPayment(Basket $basket)
      {
          $paymentContent = $this->getPaymentContent($basket, 'paypalexpress');

          $preparePaymentResult = $this->getReturnType();

          if($preparePaymentResult == 'errorCode')
          {
              return 'http://master.plentymarkets.com/basket';
          }
          elseif($preparePaymentResult == 'redirectUrl')
          {
              return $paymentContent;
          }
      }
}

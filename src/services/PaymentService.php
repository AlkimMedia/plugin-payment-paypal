<?php //strict

namespace PayPal\Services;

use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
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
     * @return array
     */
    public function executePayment()
    {
        // Load the mandatory PayPal data from session
        $ppPayId    = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);
        $ppPayerId  = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAYER_ID);

        // Set the execute parameters for the PayPal payment
        $executeParams = $this->getApiContextParams();

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

        // Clear the session parameters
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, null);
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAYER_ID, null);

        return $executeResponse;
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

    /**
     * @param array $paymentData
     * @return array
     */
    public function refundPayment($paymentData = array())
    {
        $requestParams = $this->getApiContextParams();
        $requestParams['payment'] = $paymentData;

        return $this->libCall->call('PayPal::refundPayment', $requestParams);
    }

    /**
     * List all available webhooks
     *
     * @return array
     */
    public function listAvailableWebhooks()
    {
        $requestParams = $this->getApiContextParams();

        $response = $this->libCall->call('PayPal::listAvailableWebhooks', $requestParams);

        return $response;
    }

    /**
     * Fill and return the Paypal parameters
     *
     * @param Basket $basket
     * @return array
     */
    private function getPaypalParams(Basket $basket = null)
    {
        $payPalRequestParams = $this->getApiContextParams();

        // Set the PayPal Web Profile ID
        $payPalRequestParams['webProfileId'] = $this->config->get('PayPal.webProfileID');

        /** @var Basket $basket */
        $payPalRequestParams['basket'] = $basket;

        /** @var \Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract $itemContract */
        $itemContract = pluginApp(\Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract::class);

        /** @var BasketItem $basketItem */
        foreach($basket->basketItems as $basketItem)
        {
            /** @var \Plenty\Modules\Item\Item\Models\Item $item */
            $item = $itemContract->show($basketItem->itemId);

            $basketItem = $basketItem->getAttributes();

            /** @var \Plenty\Modules\Item\Item\Models\ItemText $itemText */
            $itemText = $item->texts;

            $basketItem['name'] = $itemText->first()->name1;

            $payPalRequestParams['basketItems'][] = $basketItem;
        }

        // Read the shipping address ID from the session
        $shippingAddressId = $this->sessionStorage->getSessionValue(SessionStorageService::DELIVERY_ADDRESS_ID);

        if(!is_null($shippingAddressId))
        {
            if($shippingAddressId == -99)
            {
                $shippingAddressId = $this->sessionStorage->getSessionValue(SessionStorageService::BILLING_ADDRESS_ID);
            }

            $shippingAddress = $this->addressRepo->findAddressById($shippingAddressId);

            $payPalRequestParams['shippingAddress']['town']           = $shippingAddress->town;
            $payPalRequestParams['shippingAddress']['postalCode']     = $shippingAddress->postalCode;
            $payPalRequestParams['shippingAddress']['firstname']      = $shippingAddress->firstName;
            $payPalRequestParams['shippingAddress']['lastname']       = $shippingAddress->lastName;
            $payPalRequestParams['shippingAddress']['street']         = $shippingAddress->street;
            $payPalRequestParams['shippingAddress']['houseNumber']    = $shippingAddress->houseNumber;
        }

        // Fill the country for PayPal parameters
        $country['isoCode2'] = $this->countryRepo->findIsoCode($basket->shippingCountryId, 'iso_code_2');
        $payPalRequestParams['country'] = $country;

        // Get the URLs for PayPal parameters
        $urls = array(
            'returnUrl' => $this->paymentHelper->getRestSuccessURL(),
            'cancelUrl' => $this->paymentHelper->getRestCancelURL());

        $payPalRequestParams['urls'] = $urls;

        return $payPalRequestParams;
    }

    private function getApiContextParams()
    {
        $apiContextParams = array();
        $apiContextParams['clientSecret'] = $this->config->get('PayPal.clientSecret');
        $apiContextParams['clientId'] = $this->config->get('PayPal.clientId');

        $apiContextParams['sandbox'] = false;
        if($this->config->get('PayPal.environment') == 1)
        {
            $apiContextParams['sandbox'] = true;
        }

        return $apiContextParams;
    }
}

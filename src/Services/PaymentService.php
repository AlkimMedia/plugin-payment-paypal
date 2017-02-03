<?php //strict

namespace PayPal\Services;

use PayPal\Services\Database\AccountService;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Services\SystemService;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;

use PayPal\Helper\PaymentHelper;
use PayPal\Services\SessionStorageService;
use PayPal\Services\Database\SettingsService;
use PayPal\Services\ContactService;

/**
 * @package PayPal\Services
 */
class PaymentService
{
    /**
     * @var string
     */
    private $returnType = '';

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
     * @var SessionStorageService
     */
    private $sessionStorage;

    /**
     * @var ContactService
     */
    private $contactService;

    /**
     * @var SystemService
     */
    private $systemService;

    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var array
     */
    public $settings = [];

    /**
     * PaymentService constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     * @param PaymentRepositoryContract $paymentRepository
     * @param ConfigRepository $config
     * @param PaymentHelper $paymentHelper
     * @param LibraryCallContract $libCall
     * @param AddressRepositoryContract $addressRepo
     * @param SessionStorageService $sessionStorage
     * @param SystemService $systemService
     * @param SettingsService $settingsService
     */
    public function __construct(  PaymentMethodRepositoryContract $paymentMethodRepository,
                                  PaymentRepositoryContract $paymentRepository,
                                  ConfigRepository $config,
                                  PaymentHelper $paymentHelper,
                                  LibraryCallContract $libCall,
                                  AddressRepositoryContract $addressRepo,
                                  SessionStorageService $sessionStorage,
                                  ContactService $contactService,
                                  SystemService $systemService,
                                  SettingsService $settingsService,
                                  AccountService  $accountService)
    {
        $this->paymentMethodRepository    = $paymentMethodRepository;
        $this->paymentRepository          = $paymentRepository;
        $this->paymentHelper              = $paymentHelper;
        $this->libCall                    = $libCall;
        $this->addressRepo                = $addressRepo;
        $this->config                     = $config;
        $this->sessionStorage             = $sessionStorage;
        $this->contactService             = $contactService;
        $this->systemService              = $systemService;
        $this->settingsService            = $settingsService;
        $this->accountService             = $accountService;
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
     * Get the PayPal payment content
     *
     * @param Basket $basket
     * @param string $mode
     * @return string|array
     */
    public function getPaymentContent(Basket $basket, $mode = ''):string
    {
        if(!strlen($mode))
        {
            $mode = PaymentHelper::MODE_PAYPAL;
        }

        $payPalRequestParams = $this->getPaypalParams($basket, $mode);

        $payPalRequestParams['mode'] = $mode;

        // Prepare the PayPal payment
        $preparePaymentResult = $this->libCall->call('PayPal::preparePayment', $payPalRequestParams);

        // Check for errors
        if(is_array($preparePaymentResult) && $preparePaymentResult['error'])
        {
            $this->returnType = 'errorCode';
            return $preparePaymentResult['error_msg'];
        }

        // Store the PayPal Pay ID in the session
        if(isset($preparePaymentResult['id']) && strlen($preparePaymentResult['id']))
        {
            $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, $preparePaymentResult['id']);
        }

        // Get the content of the PayPal container
        $links = $preparePaymentResult['links'];
        $paymentContent = null;

        if(is_array($links))
        {
            foreach($links as $link)
            {
                // Get the redirect URLs for the content
                if($link['method'] == 'REDIRECT')
                {
                    $paymentContent = $link['href'];
                    $this->returnType = 'redirectUrl';
                }
            }
        }

        // Check whether the content is set. Else, return an error code.
        if(is_null($paymentContent) OR !strlen($paymentContent))
        {
            $this->returnType = 'errorCode';
            return json_encode($preparePaymentResult);
        }

        return $paymentContent;
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

    /**
     * @param $paymentId
     */
    public function handlePayPalCustomer($paymentId)
    {
        $requestParams = $this->getApiContextParams();
        $requestParams['paymentId'] = $paymentId;

        $response = $this->libCall->call('PayPal::getPaymentDetails', $requestParams);

        // update or create a contact
        $this->contactService->handlePayPalContact($response['payer']);
    }

    /**
     * @param Basket $basket
     * @return string
     */
    public function preparePayPalExpressPayment(Basket $basket)
    {
        $paymentContent = $this->getPaymentContent($basket, PaymentHelper::MODE_PAYPALEXPRESS);

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
    public function refundPayment($saleId, $paymentData = array())
    {
        $requestParams = $this->getApiContextParams();
        $requestParams['saleId'] = $saleId;

        if(!empty($paymentData))
        {
            $requestParams['payment'] = $paymentData;
        }

        $response = $this->libCall->call('PayPal::refundPayment', $requestParams);

        return $response;
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
     * @return mixed
     * @throws \Exception
     */
    public function createWebProfile()
    {
        $webProfileParams = $this->getApiContextParams();

        $webProfileParams['editableShipping']   = 0;
        $webProfileParams['addressOverride']    = 0;
        $webProfileParams['shopLogo']           = false;
        $webProfileParams['brandName']          = 'shopDerShops GmbH';
        $webProfileParams['shopName']           = 'SuperDuperShop';

        $webProfileResult = $this->libCall->call('PayPal::createWebProfile', $webProfileParams);

        if(is_array($webProfileResult) && $webProfileResult['error'])
        {
            throw new \Exception($webProfileResult['error_msg']);
        }

        // save the webProfile
//        $settingsService->setSettingsValue(SettingsService::WEB_PROFILE, $webProfileResult);

        return $webProfileResult;
    }

    /**
     * request the paypal sale for the given saleId
     *
     * @param $saleId
     * @return array
     * @throws \Exception
     */
    public function getSaleDetails($saleId)
    {
        $saleDetailsResult = $this->libCall->call('PayPal::getSaleDetails', ['saleId' => $saleId]);

        if(is_array($saleDetailsResult) && $saleDetailsResult['error'])
        {
            throw new \Exception($saleDetailsResult['error_msg']);
        }

        return $saleDetailsResult;
    }

    /**
     * Fill and return the Paypal parameters
     *
     * @param Basket $basket
     * @param String $mode
     * @return array
     */
    public function getPaypalParams(Basket $basket = null, $mode=PaymentHelper::MODE_PAYPAL)
    {
        $payPalRequestParams = $this->getApiContextParams($mode);

        // Set the PayPal Web Profile ID
        $webProfilId = $this->config->get('PayPal.webProfileID');
        if(isset($webProfilId) && strlen($webProfilId) > 0 )
        {
            $payPalRequestParams['webProfileId'] = $webProfilId;
        }

        /** @var Basket $basket */
        $payPalRequestParams['basket'] = $basket;

        /** @var \Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract $itemContract */
        $itemContract = pluginApp(\Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract::class);

        /** declarce the variable as array */
        $payPalRequestParams['basketItems'] = [];

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

        /**
         * Don't send the address to paypal when using paypal plus during the first request
         * The Shipping address will be set during update payment
         */
        if($mode != PaymentHelper::MODE_PAYPAL_PLUS && $mode != PaymentHelper::MODE_PAYPALEXPRESS)
        {
            // Read the shipping address ID from the session
            $shippingAddressId = $basket->customerShippingAddressId;

            if(!is_null($shippingAddressId))
            {
                if($shippingAddressId == -99)
                {
                    $shippingAddressId = $basket->customerInvoiceAddressId;
                }

                if(!is_null($shippingAddressId))
                {
                    $shippingAddress = $this->addressRepo->findAddressById($shippingAddressId);

                    /** declarce the variable as array */
                    $payPalRequestParams['shippingAddress'] = [];
                    $payPalRequestParams['shippingAddress']['town']           = $shippingAddress->town;
                    $payPalRequestParams['shippingAddress']['postalCode']     = $shippingAddress->postalCode;
                    $payPalRequestParams['shippingAddress']['firstname']      = $shippingAddress->firstName;
                    $payPalRequestParams['shippingAddress']['lastname']       = $shippingAddress->lastName;
                    $payPalRequestParams['shippingAddress']['street']         = $shippingAddress->street;
                    $payPalRequestParams['shippingAddress']['houseNumber']    = $shippingAddress->houseNumber;
                }
            }
        }

        /** @var \Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract $countryRepo */
        $countryRepo = pluginApp(\Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract::class);

        // Fill the country for PayPal parameters
        $country = [];
        $country['isoCode2'] = $countryRepo->findIsoCode($basket->shippingCountryId, 'iso_code_2');
        $payPalRequestParams['country'] = $country;

        // Get the URLs for PayPal parameters
        $payPalRequestParams['urls'] = $this->paymentHelper->getRestReturnUrls($mode);

        return $payPalRequestParams;
    }

    /**
     * @return array
     */
    public function getApiContextParams($mode=PaymentHelper::MODE_PAYPAL)
    {
        $settingType = 'paypal';
        if($mode == PaymentHelper::MODE_PAYPAL_INSTALLMENT)
        {
            $settingType = 'paypal_installment';
        }
        $account = $this->loadCurrecntAccountSettings($settingType);

        $apiContextParams = [];
        $apiContextParams['clientSecret'] = $account['clientSecret'];
        $apiContextParams['clientId'] = $account['clientId'];

        $apiContextParams['sandbox'] = false;

        if($account['environment'] == 1)
        {
            $apiContextParams['sandbox'] = true;
        }

        return $apiContextParams;
    }

    /**
     * @param $settingsType
     * @return array|null
     */
    public function loadCurrentSettings($settingsType='paypal')
    {
        $setting = $this->settingsService->loadSetting($this->systemService->getPlentyId(), $settingsType);
        if(is_array($setting) && count($setting) > 0)
        {
            $this->settings = $setting;
        }
    }

    public function loadCurrecntAccountSettings($settingsType='paypal')
    {
        $account = [];
        $accountId = 0;
        if(is_array($this->settings) && count($this->settings) > 0)
        {
            $accountName = $this->settings['account'];
        }
        else
        {
            $this->loadCurrentSettings($settingsType);
            $accountId = $this->settings['account'];
        }

        if($accountId > 0)
        {
            $result = $this->accountService->getAccount($accountId);
            $account = $result[$accountId];
        }

        return $account;
    }
}

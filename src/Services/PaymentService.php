<?php //strict

namespace PayPal\Services;

use PayPal\Services\Database\AccountService;
use Plenty\Plugin\Log\Loggable;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Services\SystemService;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Basket\Models\Basket;
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
    use Loggable;

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
     * @var LibService
     */
    private $libService;

    /**
     * PaymentService constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepository
     * @param PaymentRepositoryContract $paymentRepository
     * @param ConfigRepository $config
     * @param PaymentHelper $paymentHelper
     * @param AddressRepositoryContract $addressRepo
     * @param SessionStorageService $sessionStorage
     * @param SystemService $systemService
     * @param SettingsService $settingsService
     * @param LibService $libService
     */
    public function __construct(  PaymentMethodRepositoryContract $paymentMethodRepository,
                                  PaymentRepositoryContract $paymentRepository,
                                  ConfigRepository $config,
                                  PaymentHelper $paymentHelper,
                                  AddressRepositoryContract $addressRepo,
                                  SessionStorageService $sessionStorage,
                                  ContactService $contactService,
                                  SystemService $systemService,
                                  SettingsService $settingsService,
                                  AccountService  $accountService,
                                  LibService $libService)
    {
        $this->paymentMethodRepository    = $paymentMethodRepository;
        $this->paymentRepository          = $paymentRepository;
        $this->paymentHelper              = $paymentHelper;
        $this->addressRepo                = $addressRepo;
        $this->config                     = $config;
        $this->sessionStorage             = $sessionStorage;
        $this->contactService             = $contactService;
        $this->systemService              = $systemService;
        $this->settingsService            = $settingsService;
        $this->accountService             = $accountService;
        $this->libService                 = $libService;
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
     * @param string $returnType
     */
    public function setReturnType(string $returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * Get the PayPal payment content
     *
     * @param Basket $basket
     * @param string $mode
     * @return string|array|null
     */
    public function getPaymentContent(Basket $basket, $mode = PaymentHelper::MODE_PAYPAL, $additionalRequestParams=[]):string
    {
        if(!strlen($mode))
        {
            $mode = PaymentHelper::MODE_PAYPAL;
        }

        $payPalRequestParams = $this->getPaypalParams($basket, $mode);

        // Add Additional request params
        $payPalRequestParams = array_merge($payPalRequestParams, $additionalRequestParams);

        $payPalRequestParams['mode'] = $mode;

        // Prepare the PayPal payment
        $preparePaymentResult = $this->libService->libPreparePayment($payPalRequestParams);
        $this->getLogger('PayPal_PaymentService')->debug('preparePayment', $preparePaymentResult);

        // Check for errors
        if(is_array($preparePaymentResult) && $preparePaymentResult['error'])
        {
            $this->returnType = 'errorCode';
            return $preparePaymentResult['error_msg']?$preparePaymentResult['error_msg']:$preparePaymentResult['error_description'];
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
     * @return array|string
     */
    public function executePayment($mode = PaymentHelper::MODE_PAYPAL)
    {
        // Load the mandatory PayPal data from session
        $ppPayId    = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);
        $ppPayerId  = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAYER_ID);

        // Set the execute parameters for the PayPal payment
        $executeParams = $this->getApiContextParams($mode);
        $executeParams['mode'] = $mode;

        $executeParams['payId']     = $ppPayId;
        $executeParams['payerId']   = $ppPayerId;

        // Execute the PayPal payment
        $executeResponse = $this->libService->libExecutePayment($executeParams);
        $this->getLogger('PayPal_PaymentService')->debug('executePayment', $executeParams);

        // Check for errors
        if(is_array($executeResponse) && $executeResponse['error'])
        {
            $this->returnType = 'errorCode';
            return $executeResponse['error'].': '.$executeResponse['error_msg'];
        }

        if($mode == PaymentHelper::MODE_PAYPAL_INSTALLMENT)
        {
            $financingCosts = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_INSTALLMENT_COSTS);

            if(is_array($financingCosts) && !empty($financingCosts))
            {
                $executeResponse[SessionStorageService::PAYPAL_INSTALLMENT_COSTS] = $financingCosts;
                $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_INSTALLMENT_COSTS, null);
            }
        }

        // Clear the session parameters
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, null);
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAYER_ID, null);

        return $executeResponse;
    }

    /**
     * @param $paymentId
     */
    public function handlePayPalCustomer($paymentId, $mode=PaymentHelper::MODE_PAYPAL)
    {
        $response = $this->getPaymentDetails($paymentId, $mode);

        // update or create a contact
        $this->contactService->handlePayPalContact($response['payer']);
    }

    /**
     * @param $paymentId
     * @param string $mode
     * @return array
     */
    public function getPaymentDetails($paymentId, $mode=PaymentHelper::MODE_PAYPAL)
    {
        $requestParams = $this->getApiContextParams($mode);
        $requestParams['paymentId'] = $paymentId;
        $requestParams['mode'] = $mode;

        $response = $this->libService->libGetPaymentDetails($requestParams);
        $this->getLogger('PayPal_PaymentService')->debug('getPaymentDetails', $response);

        return $response;
    }

    /**
     * request the paypal sale for the given saleId
     *
     * @param $saleId
     * @return array
     */
    public function getSaleDetails($saleId)
    {
        $params = $this->getApiContextParams();
        $params['saleId'] = $saleId;

        $saleDetailsResult = $this->libService->libGetSaleDetails($params);
        $this->getLogger('PayPal_PaymentService')->debug('getSaleDetails', $saleDetailsResult);

        return $saleDetailsResult;
    }

    /**
     * Refund the given payment
     *
     * @param int $saleId
     * @param array $paymentData
     * @return array
     */
    public function refundPayment($saleId, $paymentData = [])
    {
        $requestParams = $this->getApiContextParams();
        $requestParams['saleId'] = $saleId;

        if(!empty($paymentData))
        {
            $requestParams['payment'] = $paymentData;
        }

        $response = $this->libService->libRefundPayment($requestParams);

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

        $response = $this->libService->libListAvailableWebhooks($requestParams);

        return $response;
    }

    /**
     * Create a web profile for the given account
     *
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

        $webProfileResult = $this->libService->libCreateWebProfile($webProfileParams);

        if(is_array($webProfileResult) && $webProfileResult['error'])
        {
            throw new \Exception($webProfileResult['error_msg']);
        }

        // save the webProfile
//        $settingsService->setSettingsValue(SettingsService::WEB_PROFILE, $webProfileResult);

        return $webProfileResult;
    }

    /**
     * Fill and return the PayPal parameters
     *
     * @param Basket $basket
     * @param String $mode
     * @return array
     */
    public function getPaypalParams(Basket $basket = null, $mode=PaymentHelper::MODE_PAYPAL)
    {
        $payPalRequestParams = $this->getApiContextParams($mode);

        // Set the PayPal Web Profile ID
        $webProfileId = $this->config->get('PayPal.webProfileID');
        if(isset($webProfileId) && strlen($webProfileId) > 0 )
        {
            $payPalRequestParams['webProfileId'] = $webProfileId;
        }

        /** @var Basket $basket */
        $payPalRequestParams['basket'] = $basket;

        /** declarce the variable as array */
        $payPalRequestParams['basketItems'] = [];

        /** @var \Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract $itemContract */
        $itemContract = pluginApp(\Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract::class);

        /** @var BasketItem $basketItem */
        foreach($basket->basketItems as $basketItem)
        {
            $payPalBasketItem['itemId'] = $basketItem->itemId;
            $payPalBasketItem['quantity'] = $basketItem->quantity;
            $payPalBasketItem['price'] = $basketItem->price;

            /** @var \Plenty\Modules\Item\Item\Models\Item $item */
            $item = $itemContract->show($basketItem->itemId);

            /** @var \Plenty\Modules\Item\Item\Models\ItemText $itemText */
            $itemText = $item->texts;

            $payPalBasketItem['name'] = $itemText->first()->name1;

            $payPalRequestParams['basketItems'][] = $payPalBasketItem;
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
     * Return the api params for the authentication
     *
     * @param string $mode
     * @return array
     */
    public function getApiContextParams($mode=PaymentHelper::MODE_PAYPAL)
    {
        $settingType = 'paypal';
        if($mode == PaymentHelper::MODE_PAYPAL_INSTALLMENT)
        {
            $settingType = 'paypal_installment';
        }
        $account = $this->loadCurrentAccountSettings($settingType);

        $apiContextParams = [];
        $apiContextParams['clientSecret'] = $account['clientSecret'];
        $apiContextParams['clientId'] = $account['clientId'];

        $apiContextParams['sandbox'] = true;

        if(!$account['environment'])
        {
            $apiContextParams['sandbox'] = false;
        }

        $apiContextParams['mode'] = $mode;
        return $apiContextParams;
    }

    /**
     * Load the settings from the datebase for the given settings type
     *
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

    /**
     * Load the account settings from the database for the given settings type
     *
     * @param string $settingsType
     * @return array
     */
    public function loadCurrentAccountSettings($settingsType='paypal')
    {
        $account = [];
        $accountId = 0;
        if(is_array($this->settings) && count($this->settings) > 0)
        {
            $accountId = $this->settings['account'];
        }
        else
        {
            $this->loadCurrentSettings($settingsType);
            $accountId = $this->settings['account'];
        }

        if($accountId > 0)
        {
            $result = $this->accountService->getAccount((int)$accountId);
            $account = $result[$accountId];
        }

        return $account;
    }

    /**
     * @return LibService
     */
    public function getLibService(): LibService
    {
        return $this->libService;
    }

    /**
     * @param LibService $libService
     */
    public function setLibService(LibService $libService)
    {
        $this->libService = $libService;
    }

    /**
     * @return PaymentHelper
     */
    public function getPaymentHelper(): PaymentHelper
    {
        return $this->paymentHelper;
    }

    /**
     * @return \PayPal\Services\SessionStorageService
     */
    public function getSessionStorage(): \PayPal\Services\SessionStorageService
    {
        return $this->sessionStorage;
    }

    /**
     * @return AddressRepositoryContract
     */
    public function getAddressRepository(): AddressRepositoryContract
    {
        return $this->addressRepo;
    }


}

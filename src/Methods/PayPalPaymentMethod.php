<?php // strict

namespace PayPal\Methods;

use PayPal\Services\SessionStorageService;
use PayPal\Services\Database\SettingsService;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Services\SystemService;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Frontend\Contracts\Checkout;

/**
 * Class PayPalPaymentMethod
 * @package PayPal\Methods
 */
class PayPalPaymentMethod extends PaymentMethodService
{
    /**
     * @var BasketRepositoryContract
     */
    private $basketRepo;

    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var ContactRepositoryContract
     */
    private $contactRepo;

    /**
     * @var ConfigRepository
     */
    private $configRepo;

    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var SessionStorageService
     */
    private $sessionStorage;

    /**
     * @var AddressRepositoryContract
     */
    private $addressRepo;

    /**
     * @var SystemService
     */
    private $systemService;

    /**
     * PayPalExpressPaymentMethod constructor.
     *
     * @param BasketRepositoryContract $basketRepo
     * @param ContactRepositoryContract $contactRepo
     * @param ConfigRepository $configRepo
     * @param SettingsService $settingsService
     * @param SessionStorageService $sessionStorageService
     * @param AddressRepositoryContract $addressRepositoryContract
     * @param Checkout $checkout
     * @param SystemService $systemService
     */
    public function __construct(BasketRepositoryContract    $basketRepo,
                                ContactRepositoryContract   $contactRepo,
                                ConfigRepository            $configRepo,
                                SettingsService             $settingsService,
                                SessionStorageService       $sessionStorageService,
                                AddressRepositoryContract   $addressRepositoryContract,
                                Checkout                    $checkout,
                                SystemService               $systemService)
    {
        $this->basketRepo       = $basketRepo;
        $this->checkout         = $checkout;
        $this->contactRepo      = $contactRepo;
        $this->configRepo       = $configRepo;
        $this->settingsService  = $settingsService;
        $this->sessionStorage   = $sessionStorageService;
        $this->addressRepo      = $addressRepositoryContract;
        $this->systemService    = $systemService;

        $this->loadCurrentSettings();
    }

    /**
     * Check whether the plugin is active
     *
     * @return bool
     */
    public function isActive()
    {
        return true;

        //TODO: use this part for the new UI
//        /**
//         * Check the allowed shipping countries
//         */
//        if(array_key_exists('shippingCountries', $this->settings))
//        {
//            $shippingCountries = $this->settings['shippingCountries'];
//            if(is_array($shippingCountries) && in_array($this->checkout->getShippingCountryId(), $shippingCountries))
//            {
//                return true;
//            }
//        }
//        return false;
    }

    /**
     * Get the name of the plugin
     *
     * @return string
     */
    public function getName()
    {
        $name = '';
        $lang = 'de';
        if(array_key_exists('language', $this->settings))
        {
            if(array_key_exists($lang, $this->settings['language']))
            {
                if(array_key_exists('name', $this->settings['language'][$lang]))
                {
                    $name = $this->settings['language'][$lang];
                }
            }
        }

        if(!strlen($name))
        {
            $name = 'PayPal';
        }

        return $name;
    }

    /**
     * Get additional costs for PayPal. Additional costs can be entered in the config.json.
     *
     * @return float
     */
    public function getFee()
    {
        $fee = 0;
        $basket = $this->basketRepo->load();
        $basketAmount = $basket->basketAmount;

        $shippingCountryId = $this->checkout->getShippingCountryId();
        if(array_key_exists('markup', $this->settings) && array_key_exists('webstore', $this->settings['markup']))
        {
            if($shippingCountryId && $shippingCountryId != 1)
            {
                if(array_key_exists('flatForeign', $this->settings['markup']['webstore']))
                {
                    $fee += $this->settings['markup']['webstore']['flatForeign'];
                }
                if(array_key_exists('percentageForeign', $this->settings['markup']['webstore']))
                {
                    $fee += $basketAmount / 100 * $this->settings['markup']['webstore']['percentageForeign'];
                }
            }
            else
            {
                if(array_key_exists('flatDomestic', $this->settings['markup']['webstore']))
                {
                    $fee += $this->settings['markup']['webstore']['flatDomestic'];
                }
                if(array_key_exists('percentageDomestic', $this->settings['markup']['webstore']))
                {
                    $fee += $basketAmount / 100 * $this->settings['markup']['webstore']['percentageDomestic'];
                }
            }
        }

        return (float)$fee;
    }

    /**
     * Get the path of the icon
     *
     * @return string
     */
    public function getIcon()
    {
        $lang = 'de';
        if( array_key_exists('language', $this->settings) &&
            array_key_exists($lang, $this->settings['language']) &&
            array_key_exists('logo', $this->settings['language'][$lang]))
        {
            switch ($this->settings['language'][$lang]['logo'])
            {
                case 0:
                    break;
                case 1:
                    break;
                case 2:
                    break;
            }
        }
        $icon = 'layout/plugins/production/paypal/images/logos/de-pp-logo.png';

        return $icon;
    }

    /**
     * Get the description of the payment method. The description can be entered in the config.json.
     *
     * @return string
     */
    public function getDescription()
    {
        $desc = $this->configRepo->get('PayPal.description');

        return $desc;
    }

    protected function loadCurrentSettings()
    {
        $settings = json_decode($this->settingsService->loadSettings(), true);
        if(is_array($settings) && count($settings) > 0)
        {
            $aktStore = 'PID_'.$this->systemService->getWebstoreId();
            foreach ($settings as $set)
            {
                if(array_key_exists($aktStore, $settings))
                {
                    $this->settings = $set[$aktStore];
                    break;
                }
            }
        }
    }
}

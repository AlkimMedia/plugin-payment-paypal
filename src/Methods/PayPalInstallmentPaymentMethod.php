<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 05.01.17
 * Time: 12:23
 */

namespace PayPal\Methods;

use PayPal\Services\PaymentService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Application;

class PayPalInstallmentPaymentMethod extends PaymentMethodService
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
     * @var ConfigRepository
     */
    private $configRepo;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * PayPalPlusPaymentMethod constructor.
     *
     * @param BasketRepositoryContract $basketRepo
     * @param ConfigRepository $configRepo
     * @param Checkout $checkout
     * @param PaymentService $paymentService
     */
    public function __construct( BasketRepositoryContract    $basketRepo,
                                 ConfigRepository            $configRepo,
                                 Checkout                    $checkout,
                                 PaymentService              $paymentService)
    {
        $this->basketRepo       = $basketRepo;
        $this->configRepo       = $configRepo;
        $this->checkout         = $checkout;
        $this->paymentService   = $paymentService;
        $this->paymentService->loadCurrentSettings('paypal_installment');
    }

    /**
     * Check whether the plugin is active
     *
     * @return bool
     */
    public function isActive()
    {
        /**
         * Check the allowed shipping countries
         */
        if(array_key_exists('shippingCountries', $this->paymentService->settings))
        {
            $shippingCountries = $this->paymentService->settings['shippingCountries'];
            if(is_array($shippingCountries) && in_array($this->checkout->getShippingCountryId(), $shippingCountries))
            {
                return true;
            }
        }
        return false;
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
        if(array_key_exists('language', $this->paymentService->settings))
        {
            if(array_key_exists($lang, $this->paymentService->settings['language']))
            {
                if(array_key_exists('name', $this->paymentService->settings['language'][$lang]))
                {
                    $name = $this->paymentService->settings['language'][$lang]['name'];
                }
            }
        }

        if(!strlen($name))
        {
            $name = 'PayPal Installment';
        }

        return $name;
    }

    /**
     * Get additional costs for PayPal.
     * PayPal did not allow additional costs
     *
     * @return float
     */
    public function getFee()
    {
        return 0.00;
    }

    /**
     * Get the path of the icon
     *
     * @return string
     */
    public function getIcon()
    {
        $lang = 'de';
        if( array_key_exists('language', $this->paymentService->settings) &&
            array_key_exists($lang, $this->paymentService->settings['language']) &&
            array_key_exists('logo', $this->paymentService->settings['language'][$lang]))
        {
            switch ($this->paymentService->settings['language'][$lang]['logo'])
            {
                case 0:
                    break;
                case 1:
                    break;
                case 2:
                    break;
            }
        }
        /** @var Application $app */
        $app = pluginApp(Application::class);
        $icon = $app->getUrlPath('paypal').'/images/logos/de-pp-logo.png';

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
        if(strlen($desc) <= 0)
        {
            $desc = '';
        }
        return $desc;
    }
    
    /**
     * Check if it is allowed to switch to this payment method
     *
     * @param int $orderId
     * @return bool
     */
    public function isSwitchableTo($orderId)
    {
        return false;
    }
    
    /**
     * Check if it is allowed to switch from this payment method
     *
     * @param int $orderId
     * @return bool
     */
    public function isSwitchableFrom($orderId)
    {
        return false;
    }
}
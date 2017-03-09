<?php // strict

namespace PayPal\Methods;

use PayPal\Services\PaymentService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
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
     * @var ConfigRepository
     */
    private $configRepo;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * PayPalExpressPaymentMethod constructor.
     *
     * @param BasketRepositoryContract $basketRepo
     * @param ConfigRepository $configRepo
     * @param Checkout $checkout
     * @param PaymentService $paymentService
     */
    public function __construct(BasketRepositoryContract    $basketRepo,
                                ConfigRepository            $configRepo,
                                Checkout                    $checkout,
                                PaymentService              $paymentService)
    {
        $this->basketRepo       = $basketRepo;
        $this->configRepo       = $configRepo;
        $this->checkout         = $checkout;
        $this->paymentService   = $paymentService;
        $this->paymentService->loadCurrentSettings('paypal');
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
        if(!array_key_exists('payPalPlus',$this->paymentService->settings) || (array_key_exists('payPalPlus',$this->paymentService->settings) && $this->paymentService->settings['payPalPlus'] == 0) )
        {
            if(array_key_exists('shippingCountries', $this->paymentService->settings))
            {
                $shippingCountries = $this->paymentService->settings['shippingCountries'];
                if(is_array($shippingCountries) && in_array($this->checkout->getShippingCountryId(), $shippingCountries))
                {
                    return true;
                }
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
        if(array_key_exists('markup', $this->paymentService->settings) && array_key_exists('webstore', $this->paymentService->settings['markup']))
        {
            if($shippingCountryId && $shippingCountryId != 1)
            {
                if(array_key_exists('flatForeign', $this->paymentService->settings['markup']['webstore']))
                {
                    $fee += $this->paymentService->settings['markup']['webstore']['flatForeign'];
                }
                if(array_key_exists('percentageForeign', $this->paymentService->settings['markup']['webstore']))
                {
                    $fee += $basketAmount / 100 * $this->paymentService->settings['markup']['webstore']['percentageForeign'];
                }
            }
            else
            {
                if(array_key_exists('flatDomestic', $this->paymentService->settings['markup']['webstore']))
                {
                    $fee += $this->paymentService->settings['markup']['webstore']['flatDomestic'];
                }
                if(array_key_exists('percentageDomestic', $this->paymentService->settings['markup']['webstore']))
                {
                    $fee += $basketAmount / 100 * $this->paymentService->settings['markup']['webstore']['percentageDomestic'];
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
        if(strlen($desc) <= 0)
        {
            $desc = '';
        }
        return $desc;
    }
}

<?php // strict

namespace PayPal\Methods;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;
use Plenty\Plugin\ConfigRepository;

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
     * @var ContactRepositoryContract
     */
    private $contactRepo;

    /**
     * @var ConfigRepository
     */
    private $configRepo;

    /**
     * PayPalExpressPaymentMethod constructor.
     *
     * @param BasketRepositoryContract $basketRepo
     * @param ContactRepositoryContract $contactRepo
     * @param ConfigRepository $configRepo
     */
    public function __construct(BasketRepositoryContract    $basketRepo,
                                ContactRepositoryContract   $contactRepo,
                                ConfigRepository            $configRepo)
    {
        $this->basketRepo     = $basketRepo;
        $this->contactRepo    = $contactRepo;
        $this->configRepo     = $configRepo;
    }

    /**
     * Check whether the plugin is active
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Get the name of the plugin
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->configRepo->get('PayPal.name');

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
        $fee = $this->configRepo->get('PayPal.fee');

        if(strlen($fee))
        {
            $fee = str_replace(',', '.', $fee);
        }
        else
        {
            $fee = 0;
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
}

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
     * Checks whether the plugin is active
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Gets the name of the plugin
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
     * Gets additional costs for PayPal. Additional costs can be entered in the config.json.
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
     * Gets the path of the icon
     *
     * @return string
     */
    public function getIcon()
    {
        $icon = 'http://i.imgur.com/Qnhkp.png';

        return $icon;
    }

    /**
     * Gets the description of the payment method. The description can be entered in the config.json.
     *
     * @return string
     */
    public function getDescription()
    {
        $desc = $this->configRepo->get('PayPal.description');

        return $desc;
    }
}

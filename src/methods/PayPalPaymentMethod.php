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
     * Is Plugin Active
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Get Plugin Name
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
     * Get PayPal Fee
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
     * Get Icon Path
     *
     * @return string
     */
    public function getIcon()
    {
        $icon = 'http://i.imgur.com/Qnhkp.png';

        return $icon;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        $desc = $this->configRepo->get('PayPal.description');

        return $desc;
    }
}

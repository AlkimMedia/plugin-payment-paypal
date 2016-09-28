<?php // strict

namespace PayPal\Methods;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;

/**
 * Class PayPalExpressPaymentMethod
 * @package PayPal\Methods
 */
class PayPalExpressPaymentMethod extends PaymentMethodService
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
     * PayPalExpressPaymentMethod constructor.
     *
     * @param BasketRepositoryContract $basketRepo
     * @param ContactRepositoryContract $contactRepo
     */
    public function __construct(BasketRepositoryContract $basketRepo,
                                ContactRepositoryContract $contactRepo)
    {
        $this->basketRepo = $basketRepo;
        $this->contactRepo = $contactRepo;
    }

    /**
     * Check whether PayPal Express is active
     *
     * @return bool
     */
    public function isActive():bool
    {
        return false;
    }
}

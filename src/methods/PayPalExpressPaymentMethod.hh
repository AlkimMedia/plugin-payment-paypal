<?hh // strict

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
    private BasketRepositoryContract $basketRepo;
    private ContactRepositoryContract $contactRepo;

    /**
     * PayPalExpressPaymentMethod constructor.
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
     * @return bool
     */
    public function isActive()
    {
        return true;
    }
}

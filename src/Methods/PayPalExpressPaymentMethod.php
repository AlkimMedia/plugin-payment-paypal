<?php // strict

namespace PayPal\Methods;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;

/**
 * Class PayPalExpressPaymentMethod
 * @package PayPal\Methods
 */
class PayPalExpressPaymentMethod extends PaymentMethodService
{
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

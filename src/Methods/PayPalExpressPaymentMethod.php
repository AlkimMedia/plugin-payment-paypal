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
    
    /**
     * Check if it is allowed to switch to this payment method
     *
     * @return bool
     */
    public function switchTo()
    {
        return false;
    }
    
    /**
     * Check if it is allowed to switch from this payment method
     *
     * @return bool
     */
    public function switchFrom()
    {
        return true;
    }
}

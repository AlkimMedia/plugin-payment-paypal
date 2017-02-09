<?php // strict

namespace PayPal\Methods;

use PayPal\Services\PaymentService;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;

/**
 * Class PayPalExpressPaymentMethod
 * @package PayPal\Methods
 */
class PayPalExpressPaymentMethod extends PaymentMethodService
{
    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * PayPalExpressPaymentMethod constructor.
     *
     * @param Checkout $checkout
     * @param PaymentService $paymentService
     */
    public function __construct(Checkout                    $checkout,
                                PaymentService              $paymentService)
    {
        $this->checkout         = $checkout;
        $this->paymentService   = $paymentService;
        $this->paymentService->loadCurrentSettings('paypal');
    }

    /**
     * Check whether PayPal Express is active
     *
     * @return bool
     */
    public function isActive():bool
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
}

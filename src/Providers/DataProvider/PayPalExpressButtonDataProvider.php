<?php

namespace PayPal\Providers\DataProvider;

use PayPal\Services\PaymentService;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Plugin\Templates\Twig;

/**
 * Class PayPalExpressButtonDataProvider
 * @package PayPal\Providers
 */
class PayPalExpressButtonDataProvider
{
    /**
     * @param Twig $twig
     * @param PaymentService $paymentService
     * @param Checkout $checkout
     * @param $args
     * @return string
     */
    public function call(   Twig            $twig,
                            PaymentService  $paymentService,
                            Checkout        $checkout,
                            $args)
    {
        $paymentService->loadCurrentSettings('paypal');
        /**
         * Check the allowed shipping countries
         */
        if(array_key_exists('shippingCountries', $paymentService->settings))
        {
            $shippingCountries = $paymentService->settings['shippingCountries'];
            if(is_array($shippingCountries) && in_array($checkout->getShippingCountryId(), $shippingCountries))
            {
                return $twig->render('PayPal::PayPalExpress.PayPalExpressButton');
            }
        }

        return '';
    }
}
<?php

namespace PayPal\Providers\DataProvider;

use PayPal\Services\PaymentService;
use PayPal\Services\PayPalPlusService;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

class PayPalPlusPaymentWallDataProvider
{
    /**
     * @param Twig $twig
     * @param BasketRepositoryContract  $basketRepositoryContract
     * @param PayPalPlusService         $paypalPlusService
     * @param PaymentService            $paymentService
     * @param Checkout                  $checkout
     * @param CountryRepositoryContract $countryRepositoryContract
     * @return string
     */
    public function call(   Twig                        $twig,
                            BasketRepositoryContract    $basketRepositoryContract,
                            PayPalPlusService           $paypalPlusService,
                            PaymentService              $paymentService,
                            Checkout                    $checkout,
                            CountryRepositoryContract   $countryRepositoryContract)
    {
        $content = '';
        $paymentService->loadCurrentSettings('paypal');

        if(array_key_exists('payPalPlus',$paymentService->settings) && $paymentService->settings['payPalPlus'] == 1)
        {
            $content = $paypalPlusService->getPaymentWallContent($basketRepositoryContract->load(), $checkout, $countryRepositoryContract);
        }

        return $twig->render('PayPal::PayPalPlus.PayPalPlusWall', ['content'=>$content]);
    }
}
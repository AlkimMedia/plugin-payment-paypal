<?php

namespace PayPal\Providers\DataProvider;

use PayPal\Services\PaymentService;
use PayPal\Services\PayPalPlusService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

class PayPalPlusPaymentWallDataProvider
{
    /**
     * @param BasketRepositoryContract $basketRepositoryContract
     * @param PayPalPlusService $paypalPlusService
     * @return string
     */
    public function call(   BasketRepositoryContract    $basketRepositoryContract,
                            PayPalPlusService           $paypalPlusService,
                            PaymentService              $paymentService)
    {
        $content = '';
        $paymentService->loadCurrentSettings('paypal');

        if(array_key_exists('payPalPlus',$paymentService->settings) && $paymentService->settings['payPalPlus'] == 1)
        {
            $content = $paypalPlusService->getPaymentWallContent($basketRepositoryContract->load());
        }

        return $content;
    }
}
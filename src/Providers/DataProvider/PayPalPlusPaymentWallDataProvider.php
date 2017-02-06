<?php

namespace PayPal\Providers\DataProvider;

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
                            PayPalPlusService           $paypalPlusService)
    {
        $content = $paypalPlusService->getPaymentWallContent($basketRepositoryContract->load());
        return $content;
    }
}
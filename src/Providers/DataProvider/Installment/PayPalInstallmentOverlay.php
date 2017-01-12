<?php

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Services\PayPalInstallmentService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentOverlay
{
    /**
     * @param Twig $twig
     */
    public function call(   Twig $twig,
                            BasketRepositoryContract $basketRepositoryContract,
                            PayPalInstallmentService $payPalInstallmentService)
    {

    }
}
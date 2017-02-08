<?php

namespace PayPal\Providers\DataProvider\Installment;


use PayPal\Helper\PaymentHelper;
use PayPal\Services\SessionStorageService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentFinancingCosts
{
    public function call(   Twig $twig,
                            BasketRepositoryContract $basketRepositoryContract,
                            PaymentHelper $paymentHelper,
                            SessionStorageService $sessionStorageService
    )
    {
        $creditFinacingOffered = $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_INSTALLMENT_COSTS);
        if( is_array($creditFinacingOffered)
        )
        {
            $params = [];
            $params['financingCosts'] = $creditFinacingOffered['total_interest']['value'];
            $params['totalCostsIncludeFinancing'] = $creditFinacingOffered['total_cost']['value'];
            $params['currency'] = $creditFinacingOffered['total_cost']['currency'];
            return $twig->render('PayPal::PayPalInstallment.FinancingCosts', $params);
        }

        return '';
    }
}
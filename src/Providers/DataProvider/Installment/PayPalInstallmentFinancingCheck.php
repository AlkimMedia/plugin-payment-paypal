<?php

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Services\SessionStorageService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentFinancingCheck
{
    /**
     * @param Twig $twig
     * @param BasketRepositoryContract $basketRepositoryContract
     * @param SessionStorageService $sessionStorageService
     * @return string
     */
    public function call(   Twig $twig,
                            BasketRepositoryContract $basketRepositoryContract,
                            SessionStorageService $sessionStorageService
    )
    {
        $basket = $basketRepositoryContract->load();
        $creditFinacingOffered = $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_INSTALLMENT_COSTS);
        if( $basket instanceof Basket &&
            $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_INSTALLMENT_CHECK) == 1 &&
            !is_null($sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAY_ID)) &&
            !is_null($sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAYER_ID)) &&
            is_array($creditFinacingOffered)
        )
        {
            $params = [];
            $params['basketItemAmount'] = $basket->itemSum;
            $params['basketShippingAmount'] = $basket->shippingAmount;
            $params['basketAmountNet'] = $basket->basketAmountNet;
            $params['basketAmountGro'] = $basket->basketAmount;
            $params['currency'] = $basket->currency;
            $params['financingCosts'] = $creditFinacingOffered['total_interest']['value'];
            $params['totalCostsIncludeFinancing'] = $creditFinacingOffered['total_cost']['value'];
            $params['paymentId'] = $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);
            $params['payerId'] = $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAYER_ID);
            return $twig->render('PayPal::PayPalInstallment.InstallmentOverview', $params);
        }

        return '';
    }
}
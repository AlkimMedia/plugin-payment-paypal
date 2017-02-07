<?php

namespace PayPal\Providers\DataProvider\Installment;


use PayPal\Helper\PaymentHelper;
use PayPal\Services\SessionStorageService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentFinancingCheck
{
    public function call(   Twig $twig,
                            BasketRepositoryContract $basketRepositoryContract,
                            PaymentHelper $paymentHelper,
                            SessionStorageService $sessionStorageService
    )
    {
        $basket = $basketRepositoryContract->load();
        if( $basket instanceof Basket &&
            $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_INSTALLMENT_CHECK) == 1 &&
            !is_null($sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAY_ID)) &&
            !is_null($sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAYER_ID))
        )
        {
            $params = [];
            $params['basketItemAmount'] = $basket->itemSum;
            $params['basketShippingAmount'] = $basket->shippingAmount;
            $params['basketAmountNet'] = $basket->basketAmountNet;
            $params['basketAmountGro'] = $basket->basketAmount;
            $params['financingCosts'] = 100;
            $params['totalCostsIncludeFinancing'] = 100;
            $params['paymentId'] = $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);
            $params['payerId'] = $sessionStorageService->getSessionValue(SessionStorageService::PAYPAL_PAYER_ID);
            return $twig->render('PayPal::PayPalInstallment.InstallmentOverview', $params);
        }

        return '';
    }
}
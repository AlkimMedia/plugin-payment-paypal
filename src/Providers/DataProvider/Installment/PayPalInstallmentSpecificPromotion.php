<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 12.01.17
 * Time: 09:40
 */

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Services\PayPalInstallmentService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentSpecificPromotion
{
    public function call(   Twig $twig,
                            BasketRepositoryContract $basketRepositoryContract,
                            PayPalInstallmentService $payPalInstallmentService)
    {
        $basket = $basketRepositoryContract->load();
        $qualifyingFinancingOptions = [];

        // TODO Load Config to check show with or without calculated financing options
        if(true)
        {
            /**
             * Load the specific promotion with calculated financing options
             */
            $financingOptions = $payPalInstallmentService->getFinancingOptions($basket->basketAmount);
            if(is_array($financingOptions) && array_key_exists('financing_options', $financingOptions))
            {
                if(is_array($financingOptions['financing_options'][0]) && is_array(($financingOptions['financing_options'][0]['qualifying_financing_options'])))
                {
                    $qualifyingFinancingOptions = $financingOptions['financing_options'][0]['qualifying_financing_options'][0];
                }
            }
        }
        return $twig->render('PayPal::PayPalInstallment.SpecificPromotion', ['basketAmount'=>$basket->basketAmount, 'financingOptions'=>$qualifyingFinancingOptions, 'merchantName'=>'Testfirma', 'merchantAddress'=>'Teststraße 1, 34117 Kassel']);
    }

}
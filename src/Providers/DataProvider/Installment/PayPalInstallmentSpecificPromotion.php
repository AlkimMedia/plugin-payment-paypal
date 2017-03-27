<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 12.01.17
 * Time: 09:40
 */

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Services\PaymentService;
use PayPal\Services\PayPalInstallmentService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\SalesPrice\Models\SalesPriceSearchResponse;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentSpecificPromotion
{
    /**
     * @param Twig $twig
     * @param BasketRepositoryContract $basketRepositoryContract
     * @param PayPalInstallmentService $payPalInstallmentService
     * @param PaymentService $paymentService
     * @param $arg
     * @return string
     */
    public function call(   Twig $twig,
                            BasketRepositoryContract $basketRepositoryContract,
                            PayPalInstallmentService $payPalInstallmentService,
                            PaymentService $paymentService,
                            $arg)
    {
        $id = '';
        $item = null;
        $amount = 0;
        if(is_array($arg) && array_key_exists(0, $arg))
        {
            /** @var Record $item */
            $item = $arg[0];
        }
        if(is_null($item))
        {
            $basket = $basketRepositoryContract->load();
            $amount = $basket->basketAmount;
        }
        elseif (!is_null($item) && is_array($item))
        {
            $calculatedPrices = $item['calculatedPrices']['default'];
            if($calculatedPrices instanceof SalesPriceSearchResponse)
            {
                $amount = $calculatedPrices->price;
            }

            if(array_key_exists('variation', $item))
            {
                $id = $item['variation']['itemId'].'-'.$item['variation']['id'];
            }
            else
            {
                $id = $item['item']['id'];
            }
        }

        if($amount > 0)
        {
            $qualifyingFinancingOptions = [];
            $paymentService->loadCurrentSettings('paypal_installment');
            if($paymentService->settings['calcFinancing'] == 1)
            {
                /**
                 * Load the specific promotion with calculated financing options
                 */
                $financingOptions = $payPalInstallmentService->getFinancingOptions($amount);
                if(is_array($financingOptions) && array_key_exists('financing_options', $financingOptions))
                {
                    if(is_array($financingOptions['financing_options'][0]) && is_array(($financingOptions['financing_options'][0]['qualifying_financing_options'])))
                    {
                        /*
                         * Choose a very high value to check if lower values exist
                         */
                        $lowestMonthlyCosts = 10000000;

                        /*
                         * Use the first financing option as fallback
                         */
                        $actFinancingOption = $financingOptions['financing_options'][0]['qualifying_financing_options'][0];
                        foreach ($financingOptions['financing_options'][0]['qualifying_financing_options'] as $qualifying_financing_option)
                        {
                            if($qualifying_financing_option['monthly_payment']['value'] > 0 && $qualifying_financing_option['monthly_payment']['value'] < $lowestMonthlyCosts)
                            {
                                $lowestMonthlyCosts = $qualifying_financing_option['monthly_payment']['value'];
                                $actFinancingOption = $qualifying_financing_option;
                            }
                        }

                        $qualifyingFinancingOptions = $actFinancingOption;
                    }
                }
            }
        }

        return $twig->render('PayPal::PayPalInstallment.SpecificPromotion', ['amount'=>$amount, 'financingOptions'=>$qualifyingFinancingOptions, 'item'=>$item, 'id'=>$id]);
    }

}
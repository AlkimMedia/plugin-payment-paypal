<?php

namespace PayPal\Services;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentService extends PaymentService
{
    /**
     * @param Basket $basket
     * @return string
     */
    public function getInstallmentContent(Basket $basket): string
    {
        return $this->getPaymentContent($basket, PaymentHelper::MODE_PAYPAL_INSTALLMENT, ['fundingInstrumentType'=>'CREDIT']);
    }

    public function calculateFinancingCosts(Twig $twig, $amount=0)
    {
        if($amount > 98.99 && $amount < 5000)
        {
            $qualifyingFinancingOptions = [];
            $financingOptions = $this->getFinancingOptions($amount);

            if(is_array($financingOptions) && array_key_exists('financing_options', $financingOptions))
            {
                if(is_array($financingOptions['financing_options'][0]) && is_array(($financingOptions['financing_options'][0]['qualifying_financing_options'])))
                {
                    $starExample = [];
                    /**
                     * Sort the financing options
                     * lowest APR and than lowest rate
                     */
                    foreach ($financingOptions['financing_options'][0]['qualifying_financing_options'] as $financingOption)
                    {
                        $starExample[$financingOption['monthly_payment']['value']] = str_pad($financingOption['credit_financing']['term'],2,'0', STR_PAD_LEFT).'-'.$financingOption['credit_financing']['apr'];
                        $qualifyingFinancingOptions[str_pad($financingOption['credit_financing']['term'],2,'0', STR_PAD_LEFT).'-'.$financingOption['credit_financing']['apr'].'-'.$financingOption['monthly_payment']['value']] = $financingOption;
                    }

                    ksort($starExample);
                    $highestApr = 0;
                    $lowestRate = 99999999;
                    $usedTerm = 0;
                    foreach ($starExample as $montlyRate => $termApr)
                    {
                        $termApr = explode('-', $termApr);
                        $term = $termApr[0];
                        $apr = $termApr[1];
                        if($apr >= $highestApr && $montlyRate < $lowestRate)
                        {
                            $highestApr = $apr;
                            $lowestRate = $montlyRate;
                            $usedTerm = $term;
                        }
                    }
                    $qualifyingFinancingOptions[$usedTerm.'-'.$highestApr.'-'.$lowestRate]['star'] = true;

                    ksort($qualifyingFinancingOptions);
                }
            }

            return $twig->render('PayPal::PayPalInstallment.InstallmentOverlay', ['basketAmount'=>$amount, 'financingOptions'=>$qualifyingFinancingOptions, 'merchantName'=>'Testfirma', 'merchantAddress'=>'Teststraße 1, 34117 Kassel']);
        }

        return '';
    }

    /**
     * Get the financing options for the given amount
     *
     * @param int $amount
     * @return array
     */
    public function getFinancingOptions($amount=0)
    {
        $account = $this->loadCurrentAccountSettings('paypal_installment');

        $financingOptions = [];
        $financingOptions['clientSecret'] = $account['clientSecret'];
        $financingOptions['clientId'] = $account['clientId'];

        $financingOptions['sandbox'] = true;

        if(!$account['environment'])
        {
            $financingOptions['sandbox'] = false;
        }

        $financingOptions['financingCountryCode'] = 'DE';
        $financingOptions['amount'] = $amount;
        $financingOptions['currency'] = 'EUR';

        return $this->getLibService()->libCalculateFinancingOptions($financingOptions);
    }

    /**
     * Load the financing costs from the PayPal payment details
     *
     * @param $paymentId
     * @param string $mode
     * @return mixed|null
     */
    public function getFinancingCosts($paymentId, $mode=PaymentHelper::MODE_PAYPAL_INSTALLMENT)
    {
        $response = $this->getPaymentDetails($paymentId, $mode);

        if(is_array($response) && array_key_exists('credit_financing_offered', $response))
        {
            return $response['credit_financing_offered'];
        }
        return null;
    }
}
<?php

namespace PayPal\Services;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Basket\Models\Basket;

class PayPalInstallmentService
{
    /**
     * @var string
     */
    private $returnType = '';

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var LibService
     */
    private $libService;

    /**
     * PayPalPlusService constructor.
     *
     * @param PaymentService $paymentService
     * @param LibService $libService
     */
    public function __construct(    PaymentService  $paymentService,
                                    LibService      $libService
                                )
    {
        $this->paymentService = $paymentService;
        $this->libService = $libService;
    }

    /**
     * @param Basket $basket
     * @return string
     */
    public function getPaymentContent(Basket $basket)
    {
        return $this->paymentService->getPaymentContent($basket, PaymentHelper::MODE_PAYPAL_INSTALLMENT, ['fundingInstrumentType'=>'CREDIT']);
    }

    /**
     * @return string
     */
    public function getReturnType(): string
    {
        return $this->returnType;
    }

    /**
     * @param string $returnType
     */
    public function setReturnType(string $returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * Get the financing options for the given amount
     *
     * @param int $amount
     * @return array
     */
    public function getFinancingOptions($amount=0)
    {
        $account = $this->paymentService->loadCurrentAccountSettings('paypal_installment');

        $financingOptions = [];
        $financingOptions['clientSecret'] = $account['clientSecret'];
        $financingOptions['clientId'] = $account['clientId'];

        $financingOptions['sandbox'] = true;

        if(array_key_exists('environment', $account) && $account['environment'] == 0)
        {
            $financingOptions['sandbox'] = false;
        }

        $financingOptions['financingCountryCode'] = 'DE';
        $financingOptions['amount'] = $amount;
        $financingOptions['currency'] = 'EUR';

        return $this->libService->libCalculateFinancingOptions($financingOptions);
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
        $response = $this->paymentService->getPaymentDetails($paymentId, $mode);

        if(is_array($response) && array_key_exists('credit_financing_offered', $response))
        {
            return $response['credit_financing_offered'];
        }
        return null;
    }
}
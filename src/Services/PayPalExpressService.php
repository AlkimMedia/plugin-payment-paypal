<?php

namespace PayPal\Services;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Basket\Models\Basket;

class PayPalExpressService
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    public function __construct(PaymentService  $paymentService)
    {
        $this->paymentService   = $paymentService;
    }

    /**
     * @param Basket $basket
     * @return string
     */
    public function preparePayPalExpressPayment(Basket $basket)
    {
        $paymentContent = $this->paymentService->getPaymentContent($basket, PaymentHelper::MODE_PAYPALEXPRESS);

        $preparePaymentResult = $this->paymentService->getReturnType();

        if($preparePaymentResult == 'errorCode')
        {
            return '/basket';
        }
        elseif($preparePaymentResult == 'redirectUrl')
        {
            return $paymentContent;
        }
    }

}
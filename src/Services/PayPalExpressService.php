<?php

namespace PayPal\Services;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Order\Models\Order;

class PayPalExpressService extends PaymentService
{
    /**
     * @param Basket $basket
     * @return string
     */
    public function preparePayPalExpressPayment(Basket $basket)
    {
        $paymentContent = $this->getPaymentContent($basket, PaymentHelper::MODE_PAYPALEXPRESS);

        $preparePaymentResult = $this->getReturnType();

        if($preparePaymentResult == 'errorCode')
        {
            return '/basket';
        }
        elseif($preparePaymentResult == 'redirectUrl')
        {
            return $paymentContent;
        }
    }

    /**
     * @param Order $order
     * @return string
     */
    public function preparePayPalExpressPaymentByOrder(Order $order)
    {
        $paymentContent = $this->getPaymentContentByOrder($order, PaymentHelper::MODE_PAYPALEXPRESS);

        return $paymentContent;
    }

}
<?php

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Order\Models\Order;

class PayPalInstallmentFinancingCosts
{
    /**
     * @param Twig $twig
     * @param PaymentHelper $paymentHelper
     * @param Order $order
     * @param PaymentRepositoryContract $paymentRepositoryContract
     * @return string
     */
    public function call(   Twig $twig,
                            PaymentHelper $paymentHelper,
                            PaymentRepositoryContract $paymentRepositoryContract,
                            $arg)
    {
        $order = $arg[0];
        if ($order instanceof Order)
        {
            $payments = $paymentRepositoryContract->getPaymentsByOrderId($order->id);

            $payment = $payments[0];

            $creditFinancing = json_decode($paymentHelper->getPaymentPropertyValue($payment, PaymentProperty::TYPE_PAYMENT_TEXT), true);

            if(!empty($creditFinancing) && is_array($creditFinancing))
            {
                return $twig->render('PayPal::PayPalInstallment.FinancingCosts', $creditFinancing);
            }
        }

        return '';
    }
}
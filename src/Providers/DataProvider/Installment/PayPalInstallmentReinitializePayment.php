<?php

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentReinitializePayment
{
    public function call(Twig $twig, $arg):string
    {
      $paymentHelper = pluginApp(PaymentHelper::class);
      $typeId = $paymentHelper->getPayPalMopIdByPaymentKey('PAYPAL');
      return $twig->render('PayPal::PayPalInstallment.ReinitializePayment', ["order" => $arg[0], "paymentMethodId" => $typeId]);
    }
}

<?php

namespace PayPal\Providers\DataProvider\Installment;

use Plenty\Plugin\Templates\Twig;
use PayPal\Helper\PaymentHelper;

class PayPalReinitializePaymentScript
{

    public function call(Twig $twig):string
    {
      $paymentHelper = pluginApp(PaymentHelper::class);
      $typeId = $paymentHelper->getPayPalMopIdByPaymentKey('PAYPAL');
      return $twig->render('PayPal::PayPalReinitializePaymentScript', ['typeId' => $typeId]);
    }
}

<?php

namespace PayPal\Providers\DataProvider\Installment;

use Plenty\Plugin\Templates\Twig;
use PayPal\Helper\PaymentHelper;

class PayPalReinitializePaymentScript
{

    public function call(Twig $twig):string
    {
      $paymentHelper = pluginApp(PaymentHelper::class);
      $pp = $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPAL);
      $ppp = $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS);
      return $twig->render('PayPal::PayPalReinitializePaymentScript', ['mopIds' => ['pp' => $pp, 'ppp' => $ppp]]);
    }
}

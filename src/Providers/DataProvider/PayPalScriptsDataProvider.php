<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 07.03.17
 * Time: 13:03
 */

namespace PayPal\Providers\DataProvider;


use PayPal\Helper\PaymentHelper;
use Plenty\Plugin\Templates\Twig;

class PayPalScriptsDataProvider
{
    public function call(Twig $twig, PaymentHelper $paymentHelper)
    {
        return $twig->render('PayPal::PayPalScripts', ['installmentPaymentMethodId'=>$paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT)]);
    }
}
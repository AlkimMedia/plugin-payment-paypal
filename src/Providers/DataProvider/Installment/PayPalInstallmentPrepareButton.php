<?php

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentPrepareButton
{
    /**
     * @param Twig $twig
     * @param PaymentHelper $paymentHelper
     * @param Checkout $checkout
     * @return string
     */
    public function call(   Twig $twig,
                            PaymentHelper $paymentHelper,
                            Checkout $checkout)
    {
        $installmentSelected = false;
        if($checkout->getPaymentMethodId() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT))
        {
            $installmentSelected = true;
        }
        return $twig->render('PayPal::PayPalInstallment.PrepareButton', array('installmentSelected'=>$installmentSelected));
    }
}
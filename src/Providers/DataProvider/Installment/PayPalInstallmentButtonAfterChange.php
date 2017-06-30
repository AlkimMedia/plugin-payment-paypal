<?php

namespace PayPal\Providers\DataProvider\Installment;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentButtonAfterChange
{
    public function call(Twig $twig, $arg):string
    {
        return $twig->render('PayPal::PayPalInstallment.ButtonAfterChange', ["order" => $arg[0]]);
    }
}

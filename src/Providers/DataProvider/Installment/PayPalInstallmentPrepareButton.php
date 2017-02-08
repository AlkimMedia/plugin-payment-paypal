<?php

namespace PayPal\Providers\DataProvider\Installment;

use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentPrepareButton
{
    public function call(Twig $twig)
    {
        return $twig->render('PayPal::PayPalInstallment.PrepareButton');
    }
}
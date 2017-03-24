<?php

namespace PayPal\Providers\DataProvider\Installment;

use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentGenericPromotion
{
    public function call(Twig $twig)
    {
        return $twig->render('PayPal::PayPalInstallment.GenericPromotion');
    }
}
<?php

namespace PayPal\Providers\DataProvider;

use Plenty\Plugin\Templates\Twig;

/**
 * Class PayPalExpressButtonDataProvider
 * @package PayPal\Providers
 */
class PayPalExpressButtonDataProvider
{
    /**
     * @param Twig $twig
     * @param $args
     * @return string
     */
    public function call(   Twig $twig,
                            $args)
    {
        return $twig->render('PayPal::PayPalExpress.PayPalExpressButton');
    }
}
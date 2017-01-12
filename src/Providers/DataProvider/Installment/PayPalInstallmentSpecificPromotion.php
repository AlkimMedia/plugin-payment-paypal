<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 12.01.17
 * Time: 09:40
 */

namespace PayPal\Providers\DataProvider\Installment;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Plugin\Templates\Twig;

class PayPalInstallmentSpecificPromotion
{
    public function call(Twig $twig, BasketRepositoryContract $basketRepositoryContract)
    {
        $basket = $basketRepositoryContract->load();
        return $twig->render('PayPal::PayPalInstallment.SpecificPromotion', ['basketAmount'=>$basket->basketAmount]);
    }

}
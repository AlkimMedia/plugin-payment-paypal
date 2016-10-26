<?php

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

require_once __DIR__.'/PayPalHelper.php';

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $basket         =  SdkRestApi::getParam('basket');
    $basketItems    =  SdkRestApi::getParam('basketItems');

    $itemList = new ItemList();

    foreach ($basketItems as $item)
    {

        $itemPayPal = new Item();
        $itemPayPal->setName('asdasdasd');

    }
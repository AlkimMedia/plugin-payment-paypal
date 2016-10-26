<?php

use PayPal\Api\Payer;
use PayPal\Api\ItemList;

require_once __DIR__.'/PayPalHelper.php';

$payer = new Payer();
$payer->setPaymentMethod('paypal');

$basket = SdkRestApi::getParam('basket');
$basketItems = SdkRestApi::getParam('basketItems');

$itemList = new ItemList();

foreach ($basketItems as $item)
{
    
}
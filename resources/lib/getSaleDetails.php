<?php
use PayPal\Api\Sale;

require_once __DIR__.'/PayPalHelper.php';

    /** @var \Paypal\Rest\ApiContext $apiContext */
    $apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                                SdkRestApi::getParam('clientSecret', true),
                                                SdkRestApi::getParam('sandbox', true));

    $saleId = SdkRestApi::getParam('saleId');

    /** @var Sale $sale */
    $sale = Sale::get($saleId, $apiContext);

    return $sale->toArray();
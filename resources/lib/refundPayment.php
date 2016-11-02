<?php
use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;

require_once __DIR__.'/PayPalHelper.php';

    $apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                                SdkRestApi::getParam('clientSecret', true),
                                                SdkRestApi::getParam('sandbox', true));

    $payment = SdkRestApi::getParam('payment');

    $amount = new Amount();
    $amount ->setCurrency($payment['currency'])
            ->setTotal($payment['total']);

    $refundRequest = new RefundRequest();
    $refundRequest->setAmount($amount);

    $sale = new Sale();
    $sale->setId(SdkRestApi::getParam('payId'));

    try
    {
        $refundedSale = $sale->refundSale($refundRequest, $apiContext);
    }
    catch(Exception $ex)
    {
        return print_r($ex->getCode().': '.$ex->getMessage());
    }

    return $refundedSale;
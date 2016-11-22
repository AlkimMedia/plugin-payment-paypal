<?php
use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;

require_once __DIR__.'/PayPalHelper.php';

    /** @var \Paypal\Rest\ApiContext $apiContext */
    $apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                                SdkRestApi::getParam('clientSecret', true),
                                                SdkRestApi::getParam('sandbox', true));

    /** @var RefundRequest $refundRequest */
    $refundRequest = new RefundRequest();

    $payment = SdkRestApi::getParam('payment', null);

    if(!is_null($payment))
    {
        /** @var Amount $amount */
        $amount = new Amount();
        $amount ->setCurrency($payment['currency'])
                ->setTotal($payment['total']);

        $refundRequest->setAmount($amount);
    }

    /** @var Sale $sale */
    $sale = new Sale();
    $sale->setId(SdkRestApi::getParam('payId'));

    /** @var Refund $refundedSale */
    $refundedSale = $sale->refundSale($refundRequest, $apiContext);

    return $refundedSale->toArray();
<?php
use PayPal\Api\Payment;

require_once __DIR__.'/PayPalHelper.php';

    /** @var \Paypal\Rest\ApiContext $apiContext */
    $apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                                SdkRestApi::getParam('clientSecret', true),
                                                SdkRestApi::getParam('sandbox', true));

    $paymentId = SdkRestApi::getParam('payId');

    /** @var Payment $payment */
    $payment = Payment::get($paymentId, $apiContext);

    return $payment->toArray();
<?php
use PayPal\Api\Payment;

require_once __DIR__.'/PayPalHelper.php';

$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

$paymentId = SdkRestApi::getParam('payId');

try
{
    $payment = Payment::get($paymentId, $apiContext);
}
catch(PayPal\Exception\PPConnectionException $pce)
{
    return '<pre>';print_r(json_decode($pce->getData()));
}

return print_r($payment, true);
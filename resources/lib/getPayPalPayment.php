<?php
use PayPal\Api\Payment;

require_once __DIR__.'/PayPalHelper.php';

$apiContext = PayPalHelper::getApiContext(SdkRestApi::getParam('sandbox'));

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
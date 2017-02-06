<?php
use PayPal\Api\Payment;

require_once __DIR__.'/PayPalHelper.php';

/** @var \Paypal\Rest\ApiContext $apiContext */
$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
    SdkRestApi::getParam('clientSecret', true),
    SdkRestApi::getParam('sandbox', true));

$paymentId = SdkRestApi::getParam('paymentId');

try
{
    /** @var Payment $payment */
    $payment = Payment::get($paymentId, $apiContext);
}
catch (PayPal\Exception\PPConnectionException $ex)
{
    return json_decode($ex->getData());
}
catch (Exception $e)
{
    return json_decode($e->getData());
}

return $payment->toArray();
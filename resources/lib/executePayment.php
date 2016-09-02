<?php
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

require_once __DIR__.'/PayPalHelper.php';

$apiContext = PayPalHelper::getApiContext(SdkRestApi::getParam('sandbox'));

$paymentId = SdkRestApi::getParam('paymentId');

$payment = Payment::get($paymentId, $apiContext);

$execution = new PaymentExecution();
$execution->setPayerId(SdkRestApi::getParam('payerId'));

try
{
    $result = $payment->execute($execution, $apiContext);

    $paidPayment = Payment::get($paymentId, $apiContext);
}
catch(Exception $ex)
{
    return (STRING)$ex->getMessage();
}

return (STRING)PayPalHelper::mapPayment($paidPayment);


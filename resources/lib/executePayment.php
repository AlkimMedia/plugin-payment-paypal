<?php
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

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

$execution = new PaymentExecution();

if(is_null(SdkRestApi::getParam('payerId')))
{
    $payer = $payment->getPayer()->getPayerInfo()->getPayerId();
}
else
{
    $payer = SdkRestApi::getParam('payerId');
}

$execution->setPayerId($payer);

try
{
    $result = $payment->execute($execution, $apiContext);

    $paidPayment = Payment::get($paymentId, $apiContext);
}
catch(PayPal\Exception\PPConnectionException $pce)
{
    return '<pre>';print_r(json_decode($pce->getData()));
}

return PayPalHelper::mapPayment($paidPayment);


<?php
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

require_once __DIR__.'/PayPalHelper.php';

$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

$paymentId = SdkRestApi::getParam('payId');

$payment = Payment::get($paymentId, $apiContext);

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

$result = $payment->execute($execution, $apiContext);

$paidPayment = Payment::get($paymentId, $apiContext);

return PayPalHelper::mapPayment($paidPayment);


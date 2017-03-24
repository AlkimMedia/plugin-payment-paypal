<?php
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

require_once __DIR__.'/PayPalHelper.php';

/** @var \Paypal\Rest\ApiContext $apiContext */
$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

$paymentId = SdkRestApi::getParam('payId');

/** @var Payment $payment */
$payment = Payment::get($paymentId, $apiContext);

/** @var PaymentExecution $execution */
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
}
catch (PayPal\Exception\PPConnectionException $ex)
{
    return json_decode($ex->getData());
}
catch (Exception $e)
{
    return json_decode($e->getData());
}

try
{
    /** @var Payment $paidPayment */
    $paidPayment = Payment::get($paymentId, $apiContext);
}
catch (PayPal\Exception\PPConnectionException $ex)
{
    return json_decode($ex->getData());
}
catch (Exception $e)
{
    return json_decode($e->getData());
}

return $paidPayment->toArray();


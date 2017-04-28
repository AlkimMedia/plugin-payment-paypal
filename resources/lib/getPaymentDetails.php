<?php
use PayPal\Api\Payment;

require_once __DIR__.'/PayPalHelper.php';

/** @var \Paypal\Rest\ApiContext $apiContext */
$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

$mode = SdkRestApi::getParam('mode', 'paypal');

switch ($mode)
{
    case 'installment':
        $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', 'Plenty_Cart_Inst');
        break;
    case 'plus':
        $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', 'Plenty_Cart_Plus_2');
        break;
    case 'paypal':
    case 'paypalexpress':
    default:
        $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', 'Plenty_Cart_EC_2');
        break;
}

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
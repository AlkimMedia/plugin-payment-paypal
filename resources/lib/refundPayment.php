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
$sale->setId(SdkRestApi::getParam('saleId'));

try
{
    /** @var Refund $refundedSale */
    $refundedSale = $sale->refundSale($refundRequest, $apiContext);
}
catch (PayPal\Exception\PPConnectionException $ex)
{
    return json_decode($ex->getData());
}
catch (Exception $e)
{
    return json_decode($e->getData());
}

return $refundedSale->toArray();
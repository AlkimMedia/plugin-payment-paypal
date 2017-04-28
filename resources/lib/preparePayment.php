<?php
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ShippingAddress;

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

/** @var Payer $payer */
$payer = new Payer();
$payer->setPaymentMethod('paypal');

$fundingInstrumentType = SdkRestApi::getParam('fundingInstrumentType', false);
if($fundingInstrumentType && strlen($fundingInstrumentType) > 0)
{
    $payer->setExternalSelectedFundingInstrumentType($fundingInstrumentType);
}

$basket         = SdkRestApi::getParam('basket');
$basketItems    = SdkRestApi::getParam('basketItems');

/** @var ItemList $itemList */
$itemList = new ItemList();

foreach($basketItems as $basketItem)
{
    /** @var Item $item */
    $item = new Item();
    $item->
        setName($basketItem['name'])->
        setCurrency($basket['currency'])->
        setQuantity((int)$basketItem['quantity'])->
        setSku($basketItem['itemId'])->
        setPrice(number_format($basketItem['price'], 2));

    $itemList->addItem($item);
}

$address = SdkRestApi::getParam('shippingAddress');
$country = SdkRestApi::getParam('country');
if(is_array($address) && count($address) > 0)
{
    /** @var ShippingAddress $shippingAddress */
    $shippingAddress = new ShippingAddress();
    $shippingAddress->
        setCity($address['town'])->
        setCountryCode($country['isoCode2'])->
        setPostalCode($address['postalCode'])->
        setRecipientName($address['firstname'] . ' ' . $address['lastname'])->
        setLine1($address['street'] . ' ' . $address['houseNumber'])->
        setPreferredAddress(true);

    $itemList->setShippingAddress($shippingAddress);
}

$couponAmount = $basket['couponDiscount'];
$subtotal = $basket['itemSum'];

if($subtotal != round($basket['itemSum'], 2))
{
    $differArticleAmount =  round(($basket['itemSum'] - $subtotal), 2);

    if($differArticleAmount < 0)
    {
        // gutschein
        $couponAmount += $differArticleAmount;
    }
    else
    {
        $pppItem = new Item();
        $pppItem->setName('Rundungsdifferenzartikel'); // i18n
        $pppItem->setCurrency($basket['currency']);
        $pppItem->setQuantity(1);
        $pppItem->setPrice($differArticleAmount);

        $itemList->addItem($pppItem);

        //add the item amount to pppSubtotal
        $subtotal += $differArticleAmount;
    }
}

/** @var Details $details */
$details = new Details();
$details->
    setShipping($basket['shippingAmount'])->
    setSubtotal($subtotal);

if($couponAmount)
{
    //Nachkommastellenrundungsterrorfix, differenz zwischen plenty und paypal:
    $couponFixedAmount = round(($couponAmount), 2);
    $pppTotalAmount = floatval($details->getShipping()) + floatval($details->getSubtotal()) + $couponFixedAmount;
    $totalAmountDiff = $basket['basketAmount'] - $pppTotalAmount;

    if($totalAmountDiff)
    {
        $couponFixedAmount += $totalAmountDiff;
    }

    $details->setShippingDiscount($couponFixedAmount);
}

/** @var Amount $amount */
$amount = new Amount();
$amount->
    setCurrency($basket['currency'])->
    setTotal($basket['basketAmount'])->
    setDetails($details);

/** @var Transaction $transaction */
$transaction = new Transaction();
$transaction->
    setAmount($amount)->
    setItemList($itemList)->
    setDescription('payment description')->
    setInvoiceNumber(uniqid());

$urls = SdkRestApi::getParam('urls');

/** @var RedirectUrls $redirectUrls */
$redirectUrls = new RedirectUrls();
$redirectUrls->
    setReturnUrl($urls['success'])->
    setCancelUrl($urls['cancel']);

/** @var Payment $payment */
$payment = new Payment();
$payment->
    setIntent('sale')->
    setPayer($payer)->
    setRedirectUrls($redirectUrls)->
    setTransactions(array($transaction));

$webProfileId = SdkRestApi::getParam('webProfileId');

if(!is_null($webProfileId))
{
    $payment->setExperienceProfileId($webProfileId);
}

try
{
    $payment->create($apiContext);
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

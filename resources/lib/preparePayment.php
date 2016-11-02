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

    $mode = SdkRestApi::getParam('mode', false);

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $basket         = SdkRestApi::getParam('basket');
    $basketItems    = SdkRestApi::getParam('basketItems');

    $itemList = new ItemList();

    foreach($basketItems as $basketItem)
    {
      $item = new Item();
      $item ->setName('grüner tisch')
            ->setCurrency($basket['currency'])
            ->setQuantity($basketItem['quantity'])
            ->setSku($basketItem['variationId'])
            ->setPrice($basketItem['price']);

      $itemList->addItem($item);
    }

    $address = SdkRestApi::getParam('shippingAddress');
    $country = SdkRestApi::getParam('country');

    if($mode != 'paypalexpress')
    {
        $shippingAddress = new ShippingAddress();
        $shippingAddress->setCity($address['town'])
            ->setCountryCode($country['isoCode2'])
            ->setPostalCode($address['postalCode'])
            ->setRecipientName($address['firstname'] . ' ' . $address['lastname'])
            ->setLine1($address['street'] . ' ' . $address['houseNumber'])
            ->setPreferredAddress(true);

        $itemList->setShippingAddress($shippingAddress);
    }

    $details = new Details();
    $details->setShipping($basket['shippingAmount'])
            ->setSubtotal($basket['itemSum']);

    $amount = new Amount();
    $amount ->setCurrency($basket['currency'])
            ->setTotal($basket['basketAmount'])
            ->setDetails($details);

    $transaction = new Transaction();
    $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription('payment description')
                ->setInvoiceNumber(uniqid());

    $urls = SdkRestApi::getParam('urls');

    $redirectUrls = new RedirectUrls();
    $redirectUrls ->setReturnUrl($urls['returnUrl'])
                  ->setCancelUrl($urls['cancelUrl']);

    $payment = new Payment();
    $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

    $webProfileId = SdkRestApi::getParam('webProfileId');

    if(!is_null($webProfileId))
    {
        $payment->setExperienceProfileId($webProfileId);
    }

    try
    {
        $payment->create($apiContext);
    }
    catch(Exception $ex)
    {
        return (STRING)$ex->getMessage();
    }

    return (STRING)$payment;

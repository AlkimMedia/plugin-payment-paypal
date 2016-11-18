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

    /** @var Payer $payer */
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $basket         = SdkRestApi::getParam('basket');
    $basketItems    = SdkRestApi::getParam('basketItems');

    /** @var ItemList $itemList */
    $itemList = new ItemList();

    foreach($basketItems as $basketItem)
    {
      /** @var Item $item */
      $item = new Item();
      $item ->setName($basketItem['name'])
            ->setCurrency($basket['currency'])
            ->setQuantity((int)$basketItem['quantity'])
            ->setSku($basketItem['itemId'])
            ->setPrice(number_format($basketItem['price'], 2));

      $itemList->addItem($item);
    }

    $address = SdkRestApi::getParam('shippingAddress');
    $country = SdkRestApi::getParam('country');

    if($mode != 'paypalexpress')
    {
        /** @var ShippingAddress $shippingAddress */
        $shippingAddress = new ShippingAddress();
        $shippingAddress->setCity($address['town'])
            ->setCountryCode($country['isoCode2'])
            ->setPostalCode($address['postalCode'])
            ->setRecipientName($address['firstname'] . ' ' . $address['lastname'])
            ->setLine1($address['street'] . ' ' . $address['houseNumber'])
            ->setPreferredAddress(true);

        $itemList->setShippingAddress($shippingAddress);
    }

    /** @var Details $details */
    $details = new Details();
    $details->setShipping($basket['shippingAmount'])
            ->setSubtotal($basket['itemSum']);

    /** @var Amount $amount */
    $amount = new Amount();
    $amount ->setCurrency($basket['currency'])
            ->setTotal($basket['basketAmount'])
            ->setDetails($details);

    /** @var Transaction $transaction */
    $transaction = new Transaction();
    $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription('payment description')
                ->setInvoiceNumber(uniqid());

    $urls = SdkRestApi::getParam('urls');

    /** @var RedirectUrls $redirectUrls */
    $redirectUrls = new RedirectUrls();
    $redirectUrls ->setReturnUrl($urls['success'])
                  ->setCancelUrl($urls['cancel']);

    /** @var Payment $payment */
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

    $payment->create($apiContext);

    return $payment->toArray();

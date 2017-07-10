<?php

namespace PayPal\Services;

use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;

class PayPalPlusService extends PaymentService
{
    /**
     * @param Basket $basket
     * @return string
     */
    public function getPaymentWallContent(Basket $basket, Checkout $checkout, CountryRepositoryContract $countryRepositoryContract)
    {
        $country = 'DE';

        $shippingCountryId = $checkout->getShippingCountryId();
        if($shippingCountryId > 0)
        {
            $country = $countryRepositoryContract->findIsoCode($shippingCountryId, 'isoCode2');
        }
        if($country == 'DE')
        {
            $language = 'de_DE';
        }
        else
        {
            $language = 'en_GB';
        }

        $account = $this->loadCurrentAccountSettings('paypal');
        $mode = 'sandbox';

        if(!$account['environment'])
        {
            $mode = 'live';
        }

        $content = '';
        $approvalUrl = $this->getPaymentContent($basket, PaymentHelper::MODE_PAYPAL_PLUS);
        if($this->getReturnType() == 'redirectUrl')
        {
            /**
             * Load third party payment methods
             */
            /** @var FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepositoryContract */
            $frontendPaymentMethodRepositoryContract = pluginApp(FrontendPaymentMethodRepositoryContract::class);
            $currentPaymentMethods = $frontendPaymentMethodRepositoryContract->getCurrentPaymentMethodsList();
            $thirdPartyPaymentMethods = [];
            $changeCase = [];
            if(is_array($currentPaymentMethods) && count($currentPaymentMethods) > 0)
            {
                /** @var \Plenty\Modules\Helper\Services\WebstoreHelper $webstoreHelper */
                $webstoreHelper = pluginApp(\Plenty\Modules\Helper\Services\WebstoreHelper::class);
                /** @var \Plenty\Modules\System\Models\WebstoreConfiguration $webstoreConfig */
                $webstoreConfig = $webstoreHelper->getCurrentWebstoreConfiguration();
                $domain = $webstoreConfig->domainSsl;

                /** @var PaymentMethod $paymentMethod */
                foreach ($currentPaymentMethods as $paymentMethod)
                {
                    if($paymentMethod->id == $this->getPaymentHelper()->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS))
                    {
                        continue;
                    }

                    if($paymentMethod->id == $this->getPaymentHelper()->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT))
                    {
                        $thirdPartyPaymentMethods[] = [
                            'redirectUrl'   => $domain.'/checkout/',
                            'methodName'    => "PP_Installments",
                        ];
                        $changeCase[] = 'case "PP_Installments": $.post("/payment/payPalPlus/changePaymentMethod/", { "paymentMethod" : "'.$paymentMethod->id.'" }); document.dispatchEvent(new CustomEvent("afterPaymentMethodChanged", {detail: '.$paymentMethod->id.'})); break;';
                    }
                    else
                    {
                        $thirdPartyPaymentMethods[] = [
                            'redirectUrl'   => $domain.'/checkout/',
                            'methodName'    => substr($frontendPaymentMethodRepositoryContract->getPaymentMethodName($paymentMethod, 'de'),0,25),
                            'imageUrl'      => $frontendPaymentMethodRepositoryContract->getPaymentMethodIcon($paymentMethod, 'de'),
                            'description'   => (string)($frontendPaymentMethodRepositoryContract->getPaymentMethodName($paymentMethod, 'de').' '.$frontendPaymentMethodRepositoryContract->getPaymentMethodDescription($paymentMethod, 'de'))
                        ];
                        $changeCase[] = 'case "'.substr($frontendPaymentMethodRepositoryContract->getPaymentMethodName($paymentMethod, 'de'), 0, 25).'": $.post("/payment/payPalPlus/changePaymentMethod/", { "paymentMethod" : "'.$paymentMethod->id.'" } ); document.dispatchEvent(new CustomEvent("afterPaymentMethodChanged", {detail: '.$paymentMethod->id.'})); break;';
                    }
                }
            }

            $content = '<script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js" type="text/javascript"></script>
                                <header class="m-b-1">
                                    <h3>Zahlungsart</h3>
                                </header>
                                <div id="ppplus"> </div>
                                <script type="application/javascript"> var ppp = PAYPAL.apps.PPP({
                                        "approvalUrl": "'.$approvalUrl.'", 
                                        "placeholder": "ppplus",
                                        "mode": "'.$mode.'",
                                        "country": "'.$country.'",
                                        "buttonLocation" : "outside",
                                        "language" : "'.$language.'",
                                        "showPuiOnSandbox": true,
                                        "showLoadingIndicator": true,';

            if(is_array($thirdPartyPaymentMethods) && count($thirdPartyPaymentMethods) > 0)
            {
                $content .= '           "thirdPartyPaymentMethods" : '.json_encode($thirdPartyPaymentMethods).',
                                        "enableContinue": function(){
                                            switch (ppp.getPaymentMethod())
                                            {
                                                '.implode("\n",$changeCase).'
                                                default:
                                                    $.post("/payment/payPalPlus/changePaymentMethod/", { "paymentMethod" : "'.$this->getPaymentHelper()->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS).'" } );
                                                    document.dispatchEvent(new CustomEvent("afterPaymentMethodChanged", {detail: '.$this->getPaymentHelper()->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS).'}));
                                                    break;
                                            }
                                        },';
            }

            $content .= '
                                     });
                                </script>';
        }

        return $content;
    }

    /**
     * update the paypal payment with the item data and the address
     *
     * @param Basket $basket
     * @return string
     */
    public function updatePayment(Basket $basket)
    {
        $payPalRequestParams = $this->getApiContextParams(PaymentHelper::MODE_PAYPAL_PLUS);

        /** Payment Id to from the created payment */
        $payPalRequestParams['paymentId'] = $this->getSessionStorage()->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);

        /** @var Basket $basket */
        $payPalRequestParams['basket'] = $basket;

        /** @var \Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract $itemContract */
        $itemContract = pluginApp(\Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract::class);

        /** declarce the variable as array */
        $payPalRequestParams['basketItems'] = [];

        /** @var BasketItem $basketItem */
        foreach($basket->basketItems as $basketItem)
        {
            /** @var \Plenty\Modules\Item\Item\Models\Item $item */
            $item = $itemContract->show($basketItem->itemId);

            $basketItem = $basketItem->getAttributes();

            /** @var \Plenty\Modules\Item\Item\Models\ItemText $itemText */
            $itemText = $item->texts;

            $basketItem['name'] = $itemText->first()->name1;

            $payPalRequestParams['basketItems'][] = $basketItem;
        }

        // Read the shipping address ID from the session
        $shippingAddressId = $basket->customerShippingAddressId;

        if(!is_null($shippingAddressId))
        {
            if($shippingAddressId == -99)
            {
                $shippingAddressId = $basket->customerInvoiceAddressId;
            }

            if(!is_null($shippingAddressId))
            {
                $shippingAddress = $this->getAddressRepository()->findAddressById($shippingAddressId);

                /** declarce the variable as array */
                $payPalRequestParams['shippingAddress'] = [];
                $payPalRequestParams['shippingAddress']['town']           = $shippingAddress->town;
                $payPalRequestParams['shippingAddress']['postalCode']     = $shippingAddress->postalCode;
                $payPalRequestParams['shippingAddress']['firstname']      = $shippingAddress->firstName;
                $payPalRequestParams['shippingAddress']['lastname']       = $shippingAddress->lastName;
                $payPalRequestParams['shippingAddress']['street']         = $shippingAddress->street;
                $payPalRequestParams['shippingAddress']['houseNumber']    = $shippingAddress->houseNumber;
            }
        }

        /** @var \Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract $countryRepo */
        $countryRepo = pluginApp(\Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract::class);

        // Fill the country for PayPal parameters
        $country = [];
        $country['isoCode2'] = $countryRepo->findIsoCode($basket->shippingCountryId, 'iso_code_2');
        $payPalRequestParams['country'] = $country;

        $updatePaymentResult = $this->getLibService()->libUpdatePayment($payPalRequestParams);

        if($updatePaymentResult)
        {
            $content = '<script type="application/javascript">
                            ppp.doCheckout()
                        </script>';
            $this->setReturnType('htmlContent');
        }
        else
        {
            $content = 'Fehler!!';
            $this->setReturnType('error');
        }

        return $content;
    }
}
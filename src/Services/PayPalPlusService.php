<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 05.01.17
 * Time: 14:28
 */

namespace PayPal\Services;

use PayPal\Api\Payment;
use PayPal\Helper\PaymentHelper;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\PaymentMethod\Contracts\FrontendPaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;

class PayPalPlusService
{
    /**
     * @var string
     */
    private $returnType = '';

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var LibraryCallContract
     */
    private $libraryCallContract;

    /**
     * @var SessionStorageService
     */
    private $sessionStorage;

    /**
     * @var AddressRepositoryContract
     */
    private $addressRepo;

    /**
     * @var FrontendPaymentMethodRepositoryContract
     */
    private $frontendPaymentMethodRepositoryContract;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * PayPalPlusService constructor.
     * @param PaymentService $paymentService
     * @param LibraryCallContract $libraryCallContract
     * @param SessionStorageService $sessionStorage
     * @param AddressRepositoryContract $addressRepo
     * @param FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepositoryContract
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(    PaymentService $paymentService,
                                    LibraryCallContract $libraryCallContract,
                                    SessionStorageService $sessionStorage,
                                    AddressRepositoryContract $addressRepo,
                                    FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepositoryContract,
                                    PaymentHelper $paymentHelper
    )
    {
        $this->paymentService = $paymentService;
        $this->libraryCallContract = $libraryCallContract;
        $this->sessionStorage = $sessionStorage;
        $this->addressRepo = $addressRepo;
        $this->frontendPaymentMethodRepositoryContract = $frontendPaymentMethodRepositoryContract;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param Basket $basket
     * @return string
     */
    public function getPaymentWallContent(Basket $basket)
    {
        /**
         * TODO Params to replace with configs
         */
        $language = 'de_DE';
        $country = 'DE';

        $account = $this->paymentService->loadCurrentAccountSettings('installment');
        $mode = 'sandbox';

        if(array_key_exists('environment', $account) && $account['environment'] == 0)
        {
            $mode = 'live';
        }

        $content = '';
        $approvalUrl = $this->paymentService->getPaymentContent($basket, PaymentHelper::MODE_PAYPAL_PLUS);
        if($this->paymentService->getReturnType() == 'redirectUrl')
        {
            /**
             * Load third party payment methods
             */
            $currentPaymentMethods = $this->frontendPaymentMethodRepositoryContract->getCurrentPaymentMethodsList();
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
                    if($paymentMethod->id == $this->paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS))
                    {
                        continue;
                    }
                    $thirdPartyPaymentMethods[] = [
                        'redirectUrl'   => $domain.'/checkout/',
                        'methodName'    => substr($this->frontendPaymentMethodRepositoryContract->getPaymentMethodName($paymentMethod, 'de'),0,25),
                        'imageUrl'      => $domain.'/'.$this->frontendPaymentMethodRepositoryContract->getPaymentMethodIcon($paymentMethod, 'de'),
                        'description'   => (string)($this->frontendPaymentMethodRepositoryContract->getPaymentMethodName($paymentMethod, 'de').' '.$this->frontendPaymentMethodRepositoryContract->getPaymentMethodDescription($paymentMethod, 'de'))
                    ];

                    $changeCase[] = 'case "'.substr($this->frontendPaymentMethodRepositoryContract->getPaymentMethodName($paymentMethod, 'de'), 0, 25).'": $.post("/payment/payPalPlus/changePaymentMethod/", { "paymentMethod" : "'.$paymentMethod->id.'" } ); break;';
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
                                                    $.post("/payment/payPalPlus/changePaymentMethod/", { "paymentMethod" : "'.$this->paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS).'" } );
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

    public function updatePayment(Basket $basket)
    {
        $payPalRequestParams = $this->paymentService->getApiContextParams();

        /** Payment Id to from the created payment */
        $payPalRequestParams['paymentId'] = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);

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
                $shippingAddress = $this->addressRepo->findAddressById($shippingAddressId);

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

        $updatePaymentResult = $this->libraryCallContract->call('PayPal::updatePayment', $payPalRequestParams);

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

    /**
     * @return string
     */
    public function getReturnType(): string
    {
        return $this->returnType;
    }

    /**
     * @param string $returnType
     */
    public function setReturnType(string $returnType)
    {
        $this->returnType = $returnType;
    }
}
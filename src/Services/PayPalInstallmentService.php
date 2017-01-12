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
use Plenty\Plugin\ConfigRepository;

class PayPalInstallmentService
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
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * PayPalPlusService constructor.
     * @param PaymentService $paymentService
     * @param LibraryCallContract $libraryCallContract
     * @param SessionStorageService $sessionStorage
     * @param AddressRepositoryContract $addressRepo
     * @param FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepositoryContract
     */
    public function __construct(    PaymentService $paymentService,
                                    LibraryCallContract $libraryCallContract,
                                    SessionStorageService $sessionStorage,
                                    AddressRepositoryContract $addressRepo,
                                    FrontendPaymentMethodRepositoryContract $frontendPaymentMethodRepositoryContract,
                                    PaymentHelper $paymentHelper,
                                    ConfigRepository $configRepository
                                )
    {
        $this->paymentService = $paymentService;
        $this->libraryCallContract = $libraryCallContract;
        $this->sessionStorage = $sessionStorage;
        $this->addressRepo = $addressRepo;
        $this->frontendPaymentMethodRepositoryContract = $frontendPaymentMethodRepositoryContract;
        $this->paymentHelper = $paymentHelper;
        $this->configRepository = $configRepository;
    }

    /**
     * @param Basket $basket
     * @return string
     */
    public function getPaymentContent(Basket $basket)
    {
        $payPalRequestParams = $this->paymentService->getPaypalParams($basket, PaymentHelper::MODE_PAYPAL_INSTALLMENT);

        $payPalRequestParams['mode'] = PaymentHelper::MODE_PAYPAL_INSTALLMENT;
        $payPalRequestParams['fundingInstrumentType'] = 'CREDIT';

        // Prepare the PayPal payment
        $preparePaymentResult = $this->libraryCallContract->call('PayPal::preparePayment', $payPalRequestParams);

        // Check for errors
        if(is_array($preparePaymentResult) && $preparePaymentResult['error'])
        {
            $this->returnType = 'errorCode';
            return $preparePaymentResult['error_msg'];
        }

        // Store the PayPal Pay ID in the session
        if(isset($preparePaymentResult['id']) && strlen($preparePaymentResult['id']))
        {
            $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, $preparePaymentResult['id']);
        }

        // Get the content of the PayPal container
        $links = $preparePaymentResult['links'];
        $paymentContent = null;

        if(is_array($links))
        {
            foreach($links as $link)
            {
                // Get the redirect URLs for the content
                if($link['method'] == 'REDIRECT')
                {
                    $paymentContent = $link['href'];
                    $this->returnType = 'redirectUrl';
                }
            }
        }

        // Check whether the content is set. Else, return an error code.
        if(is_null($paymentContent) OR !strlen($paymentContent))
        {
            $this->returnType = 'errorCode';
            return 'An unknown error occured, please try again.';
        }

        return $paymentContent;
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

    public function getFinancingOptions($amount=0)
    {
        $financingOptions = [];
        $financingOptions['sandbox'] = true;
        $financingOptions['clientId'] = $this->configRepository->get('PayPal.clientId');
        $financingOptions['clientSecret'] = $this->configRepository->get('PayPal.clientSecret');
        $financingOptions['financingCountryCode'] = 'DE';
        $financingOptions['amount'] = $amount;
        $financingOptions['currency'] = 'EUR';

        return $this->libraryCallContract->call('PayPal::calculatedFinancingOptions', $financingOptions);
    }
}
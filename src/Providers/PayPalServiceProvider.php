<?php // strict

namespace PayPal\Providers;

use PayPal\Methods\PayPalInstallmentPaymentMethod;
use PayPal\Methods\PayPalPlusPaymentMethod;
use PayPal\Services\PayPalInstallmentService;
use PayPal\Services\PayPalPlusService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Frontend\Events\FrontendShippingCountryChanged;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Pdf\Events\OrderPdfGenerationEvent;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Document\Models\Document;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;
use PayPal\Methods\PayPalExpressPaymentMethod;
use PayPal\Methods\PayPalPaymentMethod;
use PayPal\Procedures\RefundEventProcedure;

/**
 * Class PayPalServiceProvider
 * @package PayPal\Providers
 */
class PayPalServiceProvider extends ServiceProvider
{
    /**
     * Register the route service provider
     */
    public function register()
    {
        $this->getApplication()->register(PayPalRouteServiceProvider::class);

        $this->getApplication()->bind(RefundEventProcedure::class);
    }

    /**
     * Boot additional PayPal services
     *
     * @param Dispatcher               $eventDispatcher
     * @param PaymentHelper            $paymentHelper
     * @param PaymentService           $paymentService
     * @param PayPalPlusService        $payPalPlusService
     * @param PayPalInstallmentService $payPalInstallmentService
     * @param BasketRepositoryContract $basket
     * @param PaymentMethodContainer   $payContainer
     * @param EventProceduresService   $eventProceduresService
     */
    public function boot(   Dispatcher $eventDispatcher,
                            PaymentHelper $paymentHelper,
                            PaymentService $paymentService,
                            PayPalPlusService $payPalPlusService,
                            PayPalInstallmentService $payPalInstallmentService,
                            BasketRepositoryContract $basket,
                            PaymentMethodContainer $payContainer,
                            EventProceduresService $eventProceduresService)
    {
        // Register the PayPal Express payment method in the payment method container
        $payContainer->register('plentyPayPal::'.PaymentHelper::PAYMENTKEY_PAYPALEXPRESS, PayPalExpressPaymentMethod::class,
            [
                AfterBasketChanged::class,
                AfterBasketItemAdd::class,
                AfterBasketCreate::class,
                FrontendLanguageChanged::class,
                FrontendShippingCountryChanged::class
            ]);

        // Register the PayPal payment method in the payment method container
        $payContainer->register('plentyPayPal::'.PaymentHelper::PAYMENTKEY_PAYPAL, PayPalPaymentMethod::class,
            [   AfterBasketChanged::class,
                AfterBasketItemAdd::class,
                AfterBasketCreate::class,
                FrontendLanguageChanged::class,
                FrontendShippingCountryChanged::class
            ]);

        // Register the PayPal payment method in the payment method container
        $payContainer->register('plentyPayPal::'.PaymentHelper::PAYMENTKEY_PAYPALPLUS, PayPalPlusPaymentMethod::class,
            [   AfterBasketChanged::class,
                AfterBasketItemAdd::class,
                AfterBasketCreate::class,
                FrontendLanguageChanged::class,
                FrontendShippingCountryChanged::class
            ]);

        // Register the PayPal payment method in the payment method container
        $payContainer->register('plentyPayPal::'.PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT, PayPalInstallmentPaymentMethod::class,
            [   AfterBasketChanged::class,
                AfterBasketItemAdd::class,
                AfterBasketCreate::class,
                FrontendLanguageChanged::class,
                FrontendShippingCountryChanged::class
            ]);

        // Register PayPal Refund Event Procedure
        $eventProceduresService->registerProcedure(
            'plentyPayPal',
            ProcedureEntry::PROCEDURE_GROUP_ORDER,
            [   'de' => 'Rückzahlung der PayPal-Zahlung',
                'en' => 'Refund the PayPal-Payment'],
            'PayPal\Procedures\RefundEventProcedure@run');

        // Listen for the basket changed event
        $eventDispatcher->listen(AfterBasketChanged::class,
            function (AfterBasketChanged $event) use ($paymentHelper, $eventDispatcher)
            {
                $basket = $event->getBasket();
                if($basket->methodOfPaymentId == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS))
                {

                }
            });

        // Listen for the event that gets the payment method content
        $eventDispatcher->listen(GetPaymentMethodContent::class,
            function(GetPaymentMethodContent $event) use( $paymentHelper,  $basket,  $paymentService, $payPalPlusService, $payPalInstallmentService)
            {
                if($event->getMop() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPAL))
                {
                    $basket = $basket->load();

                    $event->setValue($paymentService->getPaymentContent($basket));
                    $event->setType( $paymentService->getReturnType());
                }
                elseif($event->getMop() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS))
                {
                    /** Load the current basket */
                    $basket = $basket->load();
                    $event->setValue($payPalPlusService->updatePayment($basket));
                    $event->setType($payPalPlusService->getReturnType());
                }
                elseif ($event->getMop() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT))
                {
                    $basket = $basket->load();
                    $event->setValue($payPalInstallmentService->getInstallmentContent($basket));
                    $event->setType($payPalInstallmentService->getReturnType());

                }
            });

        // Listen for the event that executes the payment
        $eventDispatcher->listen(ExecutePayment::class,
            function(ExecutePayment $event) use ( $paymentHelper, $paymentService)
            {
                if( $event->getMop() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPAL) ||
                    $event->getMop() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS) ||
                    $event->getMop() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALEXPRESS) ||
                    $event->getMop() == $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT) )
                {
                    switch ($event->getMop())
                    {
                        case $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT):
                            $mode = PaymentHelper::MODE_PAYPAL_INSTALLMENT;
                            break;
                        case $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS):
                            $mode = PaymentHelper::MODE_PAYPAL_PLUS;
                            break;
                        case $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALEXPRESS):
                            $mode = PaymentHelper::MODE_PAYPALEXPRESS;
                            break;
                        default:
                            $mode = PaymentHelper::MODE_PAYPAL;
                            break;
                    }

                    // Execute the payment
                    $payPalPaymentData = $paymentService->executePayment($mode);

                    // Check whether the PayPal payment has been executed successfully
                    if($paymentService->getReturnType() != 'errorCode')
                    {
                        // Create a plentymarkets payment from the paypal execution params
                        $plentyPayment = $paymentHelper->createPlentyPayment((array)$payPalPaymentData);

                        if($plentyPayment instanceof Payment)
                        {
                            // Assign the payment to an order in plentymarkets
                            $paymentHelper->assignPlentyPaymentToPlentyOrder($plentyPayment, $event->getOrderId());

                            $event->setType('success');
                            $event->setValue('The Payment has been executed successfully!');
                        }
                    }
                    else
                    {
                        $event->setType('error');
                        $event->setValue('The PayPal-Payment could not be executed!');
                    }
                }
            });

        // Listen for the document generation event
        $eventDispatcher->listen(OrderPdfGenerationEvent::class,
            function (OrderPdfGenerationEvent $event) use ( $paymentHelper)
            {
                /** @var Order $order */
                $order = $event->getOrder();
                $docType = $event->getDocType();

                if($docType == Document::INVOICE)
                {
                    /** @var \Plenty\Modules\Payment\Contracts\PaymentRepositoryContract $paymentContract */
                    $paymentContract = pluginApp(\Plenty\Modules\Payment\Contracts\PaymentRepositoryContract::class);

                    $payments = $paymentContract->getPaymentsByOrderId($order->id);

                    if(!is_null($payments) && is_array($payments))
                    {
                        switch($order->methodOfPaymentId)
                        {
                            case $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALINSTALLMENT):

                                /** @var \Plenty\Modules\Payment\Models\Payment $payment */
                                $payment = $payments[0];

                                $creditFinancing = json_decode($paymentHelper->getPaymentPropertyValue($payment, PaymentProperty::TYPE_PAYMENT_TEXT), true);

                                if(!empty($creditFinancing) && is_array($creditFinancing))
                                {
                                    /** @var \Plenty\Modules\Order\Pdf\Models\OrderPdfGeneration $orderPdfGenerationModel */
                                    $orderPdfGenerationModel = pluginApp(\Plenty\Modules\Order\Pdf\Models\OrderPdfGeneration::class);

                                    $sums = [];
                                    $sums['Finanzierungskosten'] = $creditFinancing['financingCosts'];
                                    $sums['Gesamtbetrag (mit Finanzierungskosten)'] = $creditFinancing['totalCostsIncludeFinancing'];

                                    $orderPdfGenerationModel->language = 'de';
                                    $orderPdfGenerationModel->sums = $sums;

                                    $event->addOrderPdfGeneration($orderPdfGenerationModel);
                                }

                                break;

                            case $paymentHelper->getPayPalMopIdByPaymentKey(PaymentHelper::PAYMENTKEY_PAYPALPLUS):

                                /** @var \Plenty\Modules\Payment\Models\Payment $payment */
                                $payment = $payments[0];

                                $bankData = json_decode($paymentHelper->getPaymentPropertyValue($payment, PaymentProperty::TYPE_PAYMENT_TEXT), true);

                                if(!empty($bankData) && is_array($bankData))
                                {
                                    /** @var \Plenty\Modules\Order\Pdf\Models\OrderPdfGeneration $orderPdfGenerationModel */
                                    $orderPdfGenerationModel = pluginApp(\Plenty\Modules\Order\Pdf\Models\OrderPdfGeneration::class);

                                    /** @var ConfigRepository $configRepository */
                                    $configRepository = pluginApp(ConfigRepository::class);
                                    $company = $configRepository->get("system.company");
                                    $advice = $company['name']." hat die Forderung gegen Sie im Rahmen eines laufenden
                                                Factoringvertrages an die PayPal (Europe) S.àr.l. et Cie, S.C.A. abgetreten. Zahlungen
                                                mit schuldbefreiender Wirkung können nur an die PayPal (Europe) S.àr.l. et Cie, S.C.A.
                                                geleistet werden.\n\n";
                                    $advice .=   "Kontoinhaber: ".$bankData['accountHolder']."\n".
                                                "Kreditinstitut".$bankData['bankName']."\n".
                                                "IBAN: ".$bankData['iban']."\n".
                                                "BIC: ".$bankData['bic']."\n".
                                                "Verwendungszweck: ".$bankData['referenceNumber']."\n".
                                                "Zahlbar bis: ".$bankData['paymentDue'];

                                    $orderPdfGenerationModel->language = 'de';
                                    $orderPdfGenerationModel->advice = (string)$advice;

                                    $event->addOrderPdfGeneration($orderPdfGenerationModel);
                                }

                                break;
                        }
                    }
                }
            });
    }

}

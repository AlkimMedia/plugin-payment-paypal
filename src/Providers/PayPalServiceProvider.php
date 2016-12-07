<?php // strict

namespace PayPal\Providers;

use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Frontend\Events\FrontendShippingCountryChanged;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;

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
     * @param BasketRepositoryContract $basket
     * @param PaymentMethodContainer   $payContainer
     * @param EventProceduresService   $eventProceduresService
     */
    public function boot(   Dispatcher $eventDispatcher,
                            PaymentHelper $paymentHelper,
                            PaymentService $paymentService,
                            BasketRepositoryContract $basket,
                            PaymentMethodContainer $payContainer,
                            EventProceduresService $eventProceduresService)
    {
        // Register the PayPal Express payment method in the payment method container
        $payContainer->register('plentyPayPal::PAYPALEXPRESS', PayPalExpressPaymentMethod::class,
            [
                AfterBasketChanged::class,
                AfterBasketItemAdd::class,
                AfterBasketCreate::class,
                FrontendLanguageChanged::class,
                FrontendShippingCountryChanged::class
            ]);

        // Register the PayPal payment method in the payment method container
        $payContainer->register('plentyPayPal::PAYPAL', PayPalPaymentMethod::class,
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
            '\PayPal\Procedures\RefundEventProcedure@run');

        // Listen for the event that gets the payment method content
        $eventDispatcher->listen(GetPaymentMethodContent::class,
            function(GetPaymentMethodContent $event) use( $paymentHelper,  $basket,  $paymentService)
            {
                if($event->getMop() == $paymentHelper->getPayPalMopId())
                {
                    $basket = $basket->load();

                    $event->setValue($paymentService->getPaymentContent($basket));
                    $event->setType( $paymentService->getReturnType());
                }
            });

        // Listen for the event that executes the payment
        $eventDispatcher->listen(ExecutePayment::class,
            function(ExecutePayment $event) use ( $paymentHelper, $paymentService)
            {
                if($event->getMop() == $paymentHelper->getPayPalMopId())
                {
                    // Execute the payment
                    $payPalPaymentData = $paymentService->executePayment();

                    // Check whether the PayPal payment has been executed successfully
                    if($paymentService->getReturnType() != 'errorCode')
                    {
                        // Create a plentymarkets payment from the paypal execution params
                        $plentyPayment = $paymentHelper->createPlentyPayment($payPalPaymentData);

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
    }

}

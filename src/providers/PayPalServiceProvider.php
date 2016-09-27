<?php // strict

namespace PayPal\Providers;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Modules\Payment\Models\Payment;
use \Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use \Plenty\Modules\Payment\Events\Checkout\ExecutePayment;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;
use PayPal\Methods\PayPalExpressPaymentMethod;
use PayPal\Methods\PayPalPaymentMethod;

use \Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use \Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;

/**
 * Class PayPalServiceProvider
 * @package PayPal\Providers
 */
class PayPalServiceProvider extends ServiceProvider
{
      /**
       * Registers the route service provider
       */
      public function register()
      {
      $this->getApplication()->register(PayPalRouteServiceProvider::class);
      }

      /**
       * Boots additional PayPal services
       *
       * @param Dispatcher $eventDispatcher
       * @param PaymentHelper $paymentHelper
       * @param PaymentService $paymentService
       * @param BasketRepositoryContract $basket
       * @param PaymentMethodContainer $payContainer
       */
      public function boot(   Dispatcher $eventDispatcher     , PaymentHelper $paymentHelper     , PaymentService $paymentService,
                              BasketRepositoryContract $basket, PaymentMethodContainer $payContainer)
      {
            // Creates the ID of the payment method if it doesn't exist yet
            $paymentHelper->createMopIfNotExists();

            // Registers the payment method for PayPal Express in the payment method container
            $payContainer->register('plentyPayPal::PAYPALEXPRESS', PayPalExpressPaymentMethod::class,
                                    [ AfterBasketChanged::class, AfterBasketCreate::class  ]);

            // Registers the payment method for PayPal in the payment method container
            $payContainer->register('plentyPayPal::PAYPAL', PayPalPaymentMethod::class,
                                    [ AfterBasketChanged::class, AfterBasketCreate::class  ]);

            // Listens for the event that gets the payment method content
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


            // Listens for the event that executes the payment
            $eventDispatcher->listen(ExecutePayment::class,
                              function(ExecutePayment $event) use ( $paymentHelper, $paymentService)
                              {

                                    if($event->getMop() == $paymentHelper->getPayPalMopId())
                                    {
                                          // Executes the payment
                                          $payPalPayment = $paymentService->executePayment();

                                          // Checks whether the PayPal payment has been executed successfully
                                          if($paymentService->getReturnType() != 'errorCode')
                                          {
                                                // Creates a payment in plentymarkets with the PayPal payment data
                                                $plentyPayment = $paymentHelper->createPlentyPaymentFromJson($payPalPayment);

                                                if($plentyPayment instanceof Payment)
                                                {
                                                      // Assigns the payment to an order in plentymarkets
                                                      $paymentHelper->assignPlentyPaymentToPlentyOrder($plentyPayment, $event->getOrderId());
                                                }
                                          }
                                    }
                              });
      }

}

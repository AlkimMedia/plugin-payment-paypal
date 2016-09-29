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
       * Register the route service provider
       */
      public function register()
      {
      $this->getApplication()->register(PayPalRouteServiceProvider::class);
      }

      /**
       * Boot PayPal Service
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
            /*
             * Create a Method of Payment id
             */
            $paymentHelper->createMopIfNotExists();

            /*
             * Register the Payment Method in the Payment Method Container
             */
            $payContainer->register('plentyPayPal::PAYPALEXPRESS', PayPalExpressPaymentMethod::class,
                                    [ AfterBasketChanged::class, AfterBasketCreate::class  ]);

            /*
             * Register the Payment Method in the Payment Method Container
             */
            $payContainer->register('plentyPayPal::PAYPAL', PayPalPaymentMethod::class,
                                    [ AfterBasketChanged::class, AfterBasketCreate::class  ]);

            /*
             * Listen for the get Payment Method Content Event
             */
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


            /*
             * Listen for the Execute Payment Event
             */
            $eventDispatcher->listen(ExecutePayment::class,
                              function(ExecutePayment $event) use ( $paymentHelper, $paymentService)
                              {

                                    if($event->getMop() == $paymentHelper->getPayPalMopId())
                                    {
                                          /*
                                           * Execute the Payment
                                           */
                                          $payPalPayment = $paymentService->executePayment();

                                          /*
                                           * Check if the PayPal Payment has been executed successfully
                                           */
                                          if($paymentService->getReturnType() != 'errorCode')
                                          {
                                                /*
                                                 * Create the Plenty Payment with the PayPal payment data
                                                 */
                                                $plentyPayment = $paymentHelper->createPlentyPaymentFromJson($payPalPayment);

                                                if($plentyPayment instanceof Payment)
                                                {
                                                      /*
                                                       * Assign the Plenty Payment to the given order
                                                       */
                                                      $paymentHelper->assignPlentyPaymentToPlentyOrder($plentyPayment, $event->getOrderId());
                                                }
                                          }
                                    }
                              });
      }

}
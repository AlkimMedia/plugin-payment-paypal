<?php // strict

namespace PayPal\Providers;

use Plenty\Modules\EventAction\Services\Entries\ActionEntry;
use Plenty\Modules\EventAction\Services\EventActionService;
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
use PayPal\Events\RefundEventProcedure;

use \Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
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
	 * @param EventActionService       $eventActionService
	 */
      public function boot(   Dispatcher $eventDispatcher     , PaymentHelper $paymentHelper     , PaymentService $paymentService,
                              BasketRepositoryContract $basket, PaymentMethodContainer $payContainer, EventActionService $eventProcedureService)
      {
            // Register the PayPal Express payment method in the payment method container
            $payContainer->register('plentyPayPal::PAYPALEXPRESS', PayPalExpressPaymentMethod::class,
                                    [ AfterBasketChanged::class, AfterBasketItemAdd::class, AfterBasketCreate::class  ]);

            // Register the PayPal payment method in the payment method container
            $payContainer->register('plentyPayPal::PAYPAL', PayPalPaymentMethod::class,
                                    [ AfterBasketChanged::class, AfterBasketItemAdd::class, AfterBasketCreate::class  ]);

            // Register PayPal Refund Event Procedure
            $eventProcedureService->registerAction('plentyPayPal',
                                                ActionEntry::ACTION_GROUP_ORDER,
                                                [   'de' => 'Rückzahlung der PayPal-Zahlung',
                                                    'en' => 'Refund the PayPal-Payment'],
                                                '\PayPal\Events\RefundEventProcedure@run');

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
                                          $payPalPayment = $paymentService->executePayment();

                                          // Check whether the PayPal payment has been executed successfully
                                          if($paymentService->getReturnType() != 'errorCode')
                                          {
                                                // Create a payment in plentymarkets with the PayPal payment data
                                                $plentyPayment = $paymentHelper->createPlentyPaymentFromJson($payPalPayment);

                                                if($plentyPayment instanceof Payment)
                                                {
                                                      // Assign the payment to an order in plentymarkets
                                                      $paymentHelper->assignPlentyPaymentToPlentyOrder($plentyPayment, $event->getOrderId());

//                                                      $event->setType(ExecutePayment::RETURN_TYPE_SUCCESS);
//                                                      $event->setValue('The Payment has been executed successfully!');
                                                }
                                          }
                                          else
                                          {
//                                              $event->setType(ExecutePayment::RETURN_TYPE_ERROR);
//                                              $event->setValue('The PayPal-Payment could not be executed!');
                                          }
                                    }
                              });
      }

}

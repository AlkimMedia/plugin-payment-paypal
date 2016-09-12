<?hh // strict

namespace PayPal\Providers;

use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Modules\Payment\Models\Payment;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;
use PayPal\Methods\PayPalExpressPaymentMethod;

/**
 * Class PayPalServiceProvider
 * @package PayPal\Providers
 */
class PayPalServiceProvider extends ServiceProvider
{
  /**
   * register the route service provider
   */
  public function register():void
  {
    $this->getApplication()->register(PayPalRouteServiceProvider::class);
  }

  /**
   * @param Dispatcher $eventDispatcher
   * @param PaymentHelper $paymentHelper
   * @param PaymentService $paymentService
   * @param BasketRepositoryContract $basket
   * @param PaymentMethodContainer $payContainer
   */
  public function boot(Dispatcher $eventDispatcher,
                       PaymentHelper $paymentHelper,
                       PaymentService $paymentService,
                       BasketRepositoryContract $basket,
                       PaymentMethodContainer $payContainer):void
  {
    /*
     * create a method of payment id
     */
    $paymentHelper->createMopIfNotExists();

    /*
     * register the payment method in the payment method container
     */
    $payContainer->register('PayPal::PAYPALEXPRESS', PayPalExpressPaymentMethod::class,
        [ \Plenty\Modules\Basket\Events\Basket\AfterBasketChanged::class,
          \Plenty\Modules\Basket\Events\Basket\AfterBasketCreate::class]);



    /*
     * listen for the get payment method content event
     */
    $eventDispatcher->listen(\Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent::class, ($event) ==> {

      if($event->getMop() == $paymentHelper->getMop())
      {
        $basket = $basket->load();

        $event->setValue($paymentService->getPaymentContent($basket));
        $event->setType($paymentService->getReturnType());
      }
    });



    /*
     * listen for the execute payment event
     */
    $eventDispatcher->listen(\Plenty\Modules\Payment\Events\Checkout\ExecutePayment::class, ($event) ==> {

      if($event->getMop() == $paymentHelper->getMop())
      {
        /*
         * execute the payment
         */
        $payPalPayment = $paymentService->executePayment();

        /*
         * check if the paypal payment has been executed successfully
         */
        if($paymentService->getReturnType() != 'errorCode')
        {
          /*
           * create the plenty payment with the paypal payment data
           */
          $plentyPayment = $paymentHelper->createPlentyPayment($payPalPayment);

          if($plentyPayment instanceof Payment)
          {
            /*
             * assign the plenty payment to the given order
             */
            $paymentHelper->assignPlentyPaymentToPlentyOrder($plentyPayment, $event->getOrderId());
          }
        }
      }
    });
  }
}

<?hh // strict

namespace PayPal\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Payment\PaymentServiceProvider;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Plugin\Events\Dispatcher;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;


class PayPalServiceProvider extends ServiceProvider
{
  public function register():void
  {
    $this->getApplication()->register(PayPalRouteServiceProvider::class);
  }

  public function boot(Dispatcher $eventDispatcher,
                       PaymentHelper $paymentHelper,
                       PaymentService $paymentService,
                       BasketRepositoryContract $basket):void
  {
    $paymentHelper->createMopIfNotExists();

    $eventDispatcher->listen(\Plenty\Modules\Payment\Events\Checkout\AfterPaymentMethodSelected::class, ($event) ==> {

      if($event->getMop() == $paymentHelper->getMop())
      {
        $basket = $basket->load();

        $event->setValue($paymentService->getPayPalContent($basket));
        $event->setType($paymentService->getReturnType());
      }
    });

    $eventDispatcher->listen(\Plenty\Modules\Payment\Events\Checkout\ExecutePayment::class, ($event) ==> {

      if($event->getMop() == $paymentHelper->getMop())
      {
        $payPalPayment = $paymentService->payPalExecutePayment();
        $event->setType($paymentService->getReturnType());

//        if($paymentService->getReturnType() != 'errorCode')
//        {
          $plentyPayment = $paymentHelper->createPlentyPayment($payPalPayment);

//          $orderId = $event->getOrderId();
//
//          $response = $paymentHelper->assignPlentyPaymentToPlentyOrder($plentyPayment, $orderId);
//
//          if($response)
//          {
//            $event->setStatus('success');
//          }
//        }
      }
    });
  }
}

<?hh // strict

namespace PayPal\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Payment\PaymentServiceProvider;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\ConfigRepository;

class PayPalServiceProvider extends ServiceProvider
{
  public function register():void
  {
    $this->getApplication()->register(PayPalRouteServiceProvider::class);
  }

  public function boot(Dispatcher $eventDispatcher, ConfigRepository $config):void
  {
    //$eventDispatcher->listen('tpl.basket');
  }
}

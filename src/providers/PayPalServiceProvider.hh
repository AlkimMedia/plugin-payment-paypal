<?hh // strict
namespace PayPal\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Payment\PaymentServiceProvider;

class PayPalServiceProvider extends ServiceProvider
{
  public function register():void
  {
    $this->getApplication()->register(PayPalRouteServiceProvider::class);
  }
}

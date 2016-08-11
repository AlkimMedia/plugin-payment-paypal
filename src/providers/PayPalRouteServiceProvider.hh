<?hh // strict
namespace PayPal\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class PayPalRouteServiceProvider extends RouteServiceProvider
{
	public function map(Router $router):void
	{
		$router->get('PayPalExpressButton', 'PayPal\Controllers\ContentController@getPayPalExpressButton');
	}
}

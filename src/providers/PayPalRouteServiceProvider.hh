<?hh // strict

namespace PayPal\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

/**
 * Class PayPalRouteServiceProvider
 * @package PayPal\Providers
 */
class PayPalRouteServiceProvider extends RouteServiceProvider
{
	/**
	 * @param Router $router
	 */
	public function map(Router $router):void
	{
		//paypal return urls
		$router->get('payPalCheckoutSuccess', 'PayPal\Controllers\PaymentController@payPalCheckoutSuccess');
		$router->get('payPalCheckoutCancel', 'PayPal\Controllers\PaymentController@payPalCheckoutCancel');
	}
}

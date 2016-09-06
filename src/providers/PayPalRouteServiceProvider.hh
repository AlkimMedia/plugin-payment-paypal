<?hh // strict

namespace PayPal\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;

class PayPalRouteServiceProvider extends RouteServiceProvider
{
	public function map(Router $router, LibraryCallContract $libCall):void
	{
		$router->get('test', () ==> {
			$result = $libCall->call('PayPal::preparePayment', ['foo' => 'bar']);
			return $result;
		});

		$router->get('getPayPalPayment', 'PayPal\Controllers\PaymentController@getPayPalPayment');

		$router->get('payPalExpressButton', 'PayPal\Controllers\PaymentController@showPPExpressButton');

		//paypal return urls
		$router->get('payPalCheckoutSuccess', 'PayPal\Controllers\PaymentController@payPalCheckoutSuccess');
		$router->get('payPalCheckoutCancel', 'PayPal\Controllers\PaymentController@payPalCheckoutCancel');

		//trigger prepare payment
		$router->get('preparePayPalPayment', 'PayPal\Controllers\PaymentController@preparePayment');

		//trigger execute payment
		$router->get('executePayment', 'PayPal\Controllers\PaymentController@executePayment');

	}
}

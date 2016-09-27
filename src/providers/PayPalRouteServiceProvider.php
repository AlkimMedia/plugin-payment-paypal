<?php // strict

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
	public function map(Router $router)
	{
		// Get the PayPal success and cancellation URLs
		$router->get('plentyPayPal/payPalCheckoutSuccess', 'PayPal\Controllers\PaymentController@payPalCheckoutSuccess');
		$router->get('plentyPayPal/payPalCheckoutCancel' , 'PayPal\Controllers\PaymentController@payPalCheckoutCancel' );
	}
}

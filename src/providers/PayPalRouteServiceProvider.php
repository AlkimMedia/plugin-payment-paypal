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
		$router->get('payPal/checkoutSuccess', 'PayPal\Controllers\PaymentController@checkoutSuccess');
		$router->get('payPal/checkoutCancel' , 'PayPal\Controllers\PaymentController@checkoutCancel' );

        $router->get('payPal/expressCheckout', 'PayPal\Controllers\PaymentController@expressCheckout');

        $router->post('payPal/notification', 'PayPal\Controllers\PaymentNotificationController@handleNotification');
	}
}

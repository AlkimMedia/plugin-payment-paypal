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

        // Get the PayPalExpress success and cancellation URLs
        $router->get('payPal/expressCheckoutSuccess', 'PayPal\Controllers\PaymentController@expressCheckoutSuccess');
        $router->get('payPal/expressCheckoutCancel' , 'PayPal\Controllers\PaymentController@expressCheckoutCancel' );

        // Get the PayPalExpress checkout
        $router->get('payPal/expressCheckout', 'PayPal\Controllers\PaymentController@expressCheckout');

        $router->post('payPal/notification', 'PayPal\Controllers\PaymentNotificationController@handleNotification');
    }
}

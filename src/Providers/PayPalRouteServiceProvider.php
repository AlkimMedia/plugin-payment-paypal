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

        $router->post('payPal/settings/', 'PayPal\Controllers\SettingsController@saveSettings');
        $router->get('payPal/settings/', 'PayPal\Controllers\SettingsController@loadSettings');
        $router->get('payPal/setting/{webstore}', 'PayPal\Controllers\SettingsController@loadSetting');

        $router->get('payPal/account/{accountId}', 'PayPal\Controllers\SettingsController@loadAccount');
        $router->get('payPal/accounts/', 'PayPal\Controllers\SettingsController@loadAccounts');
        $router->post('payPal/account/', 'PayPal\Controllers\SettingsController@createAccount');
        $router->put('payPal/account/', 'PayPal\Controllers\SettingsController@updateAccount');
        $router->delete('payPal/account/', 'PayPal\Controllers\SettingsController@deleteAccount');
    }
}

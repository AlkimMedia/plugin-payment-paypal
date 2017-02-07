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
        $router->get('payment/payPal/checkoutSuccess/{mode}', 'PayPal\Controllers\PaymentController@checkoutSuccess');
        $router->get('payment/payPal/checkoutCancel/{mode}' , 'PayPal\Controllers\PaymentController@checkoutCancel' );

        // Get the PayPalExpress success and cancellation URLs
        $router->get('payment/payPal/expressCheckoutSuccess', 'PayPal\Controllers\PaymentController@expressCheckoutSuccess');
        $router->get('payment/payPal/expressCheckoutCancel' , 'PayPal\Controllers\PaymentController@expressCheckoutCancel' );

        // Get the PayPalExpress checkout
        $router->get('payment/payPal/expressCheckout', 'PayPal\Controllers\PaymentController@expressCheckout');

        // PayPal Webhook handler
        $router->post('payment/payPal/notification', 'PayPal\Controllers\PaymentNotificationController@handleNotification');

        /**
         * Routes for the PayPal Settings
         */
        $router->post('payment/payPal/settings/', 'PayPal\Controllers\SettingsController@saveSettings');
        $router->get('payment/payPal/settings/{settingType}', 'PayPal\Controllers\SettingsController@loadSettings');
        $router->get('payment/payPal/setting/{webstore}', 'PayPal\Controllers\SettingsController@loadSetting');

        $router->get('payment/payPal/account/{accountId}', 'PayPal\Controllers\SettingsController@loadAccount');
        $router->get('payment/payPal/accounts/', 'PayPal\Controllers\SettingsController@loadAccounts');
        $router->post('payment/payPal/account/', 'PayPal\Controllers\SettingsController@createAccount');
        $router->put('payment/payPal/account/', 'PayPal\Controllers\SettingsController@updateAccount');
        $router->delete('payment/payPal/account/', 'PayPal\Controllers\SettingsController@deleteAccount');

        /**
         * Routes for the PayPal Plus Wall and Checkout
         */
        $router->post('payment/payPalPlus/changePaymentMethod/', 'PayPal\Controllers\PaymentController@changePaymentMethod');

        /**
         * Routes for the PayPal Installment
         */
        $router->get('payment/payPalInstallment/financingOptions/{amount}', 'PayPal\Controllers\PaymentController@calculateFinancingOptions');
        $router->get('payment/payPalInstallment/prepareInstallment', 'PayPal\Controllers\PaymentController@prepareInstallment');
    }
}

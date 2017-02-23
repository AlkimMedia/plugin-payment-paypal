<?php // strict

namespace PayPal\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use Plenty\Plugin\Routing\ApiRouter;

/**
 * Class PayPalRouteServiceProvider
 * @package PayPal\Providers
 */
class PayPalRouteServiceProvider extends RouteServiceProvider
{
    /**
     * @param Router $router
     * @param ApiRouter $apiRouter
     */
    public function map(Router $router, ApiRouter $apiRouter)
    {
        // PayPal-Settings routes
        $apiRouter->version(['v1'], ['namespace' => 'PayPal\Controllers', 'middleware' => 'oauth'],
            function ($apiRouter)
            {
                $apiRouter->post('payment/payPal/settings/', 'SettingsController@saveSettings');
                $apiRouter->get('payment/payPal/settings/{settingType}', 'SettingsController@loadSettings');
                $apiRouter->get('payment/payPal/setting/{webstore}', 'SettingsController@loadSetting');

                $apiRouter->get('payment/payPal/account/{accountId}', 'SettingsController@loadAccount');
                $apiRouter->get('payment/payPal/accounts/', 'SettingsController@loadAccounts');
                $apiRouter->post('payment/payPal/account/', 'SettingsController@createAccount');
                $apiRouter->put('payment/payPal/account/', 'SettingsController@updateAccount');
                $apiRouter->delete('payment/payPal/account/', 'SettingsController@deleteAccount');
            });

        // Get the PayPal success and cancellation URLs
        $router->get('payment/payPal/checkoutSuccess/{mode}', 'PayPal\Controllers\PaymentController@checkoutSuccess');
        $router->get('payment/payPal/checkoutCancel/{mode}' , 'PayPal\Controllers\PaymentController@checkoutCancel');

        // Get the PayPalExpress success and cancellation URLs
        $router->get('payment/payPal/expressCheckoutSuccess', 'PayPal\Controllers\PaymentController@expressCheckoutSuccess');
        $router->get('payment/payPal/expressCheckoutCancel' , 'PayPal\Controllers\PaymentController@expressCheckoutCancel');

        // Get the PayPalExpress checkout
        $router->get('payment/payPal/expressCheckout', 'PayPal\Controllers\PaymentController@expressCheckout');

        // PayPal Webhook handler
        $router->post('payment/payPal/notification', 'PayPal\Controllers\PaymentNotificationController@handleNotification');

        // Routes for the PayPal Plus Wall and Checkout
        $router->post('payment/payPalPlus/changePaymentMethod/', 'PayPal\Controllers\PaymentController@changePaymentMethod');

        // Routes for the PayPal Installment
        $router->get('payment/payPalInstallment/financingOptions/{amount}', 'PayPal\Controllers\PaymentController@calculateFinancingOptions');
        $router->get('payment/payPalInstallment/prepareInstallment', 'PayPal\Controllers\PaymentController@prepareInstallment');
    }
}

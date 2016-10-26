<?php //strict

namespace PayPal\Controllers;

use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Http\Request;

use PayPal\Helper\PaymentHelper;

/**
 * Class PaymentController
 * @package PayPal\Controllers
 */
class PaymentController extends Controller
{
      /**
      * @var Application
      */
      protected $app;

      /**
      * @var Twig
      */
      private $twig;

      /**
      * @var Request
      */
      private $request;

      /**
      * @var ConfigRepository
      */
      private $config;

      /**
      * @var PaymentHelper
      */
      private $payHelper;

      /**
      * PaymentController constructor.
      *
      * @param Application $app
      * @param Twig $twig
      * @param ConfigRepository $config
      * @param Request $request
      * @param PaymentHelper $payHelper
      */
      public function __construct(  Application $app, Twig $twig, Request $request,
                                    ConfigRepository $config, PaymentHelper $payHelper)
      {
            $this->app          = $app;
            $this->twig         = $twig;
            $this->request      = $request;
            $this->config       = $config;
            $this->payHelper    = $payHelper;
      }

      /**
      * PayPal redirects to this page if the payment could not be executed or other problems occurred
      */
      public function payPalCheckoutCancel()
      {
            // Redirects to the cancellation page. The URL can be entered in the config.json.
            header("Location: ".$this->config->get('PayPal.cancelUrl'));
            exit();
      }

      /**
      * PayPal redirects to this page if the payment was executed correctly
      */
      public function payPalCheckoutSuccess()
      {
            // Get the PayPal payment data from the request
            $paymentId    = $this->request->get('paymentId');
            $payerId      = $this->request->get('PayerID');

            // Get the PayPal Pay ID from the session
            $ppPayId = $this->payHelper->getPayPalPayID();

            // Check whether the Pay ID from the session is equal to the given Pay ID by PayPal
            if($paymentId != $ppPayId)
            {
                  $this->payPalCheckoutCancel();
            }

            // Set the PayPal data in the session
            $this->payHelper->setPayPalPayID($paymentId);
            $this->payHelper->setPayPalPayerID($payerId);

            // Redirect to the success page. The URL can be entered in the config.json.
            header("Location: ".$this->config->get('PayPal.successUrl'));
            exit();
      }

      public function payPalExpressCheckout()
      {
          header("Location: http://www.plentymarkets.com");
          exit();
      }
}

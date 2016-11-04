<?php //strict

namespace PayPal\Controllers;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Http\Request;

use PayPal\Services\SessionStorageService;
use Paypal\Services\PaymentService;
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
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var BasketRepositoryContract
     */
    private $basketContract;

    /**
     * @var SessionStorageService
     */
    private $sessionStorage;

    /**
     * PaymentController constructor.
     *
     * @param Application $app
     * @param Twig $twig
     * @param Request $request
     * @param ConfigRepository $config
     * @param PaymentHelper $payHelper
     * @param PaymentService $paymentService
     * @param BasketRepositoryContract $basketContract
     * @param SessionStorageService $sessionStorage
     */
    public function __construct(  Application $app, Twig $twig, Request $request,
                                  ConfigRepository $config, PaymentHelper $payHelper,
                                  PaymentService $paymentService, BasketRepositoryContract $basketContract,
                                  SessionStorageService $sessionStorage)
    {
        $this->app              = $app;
        $this->twig             = $twig;
        $this->request          = $request;
        $this->config           = $config;
        $this->payHelper        = $payHelper;
        $this->paymentService   = $paymentService;
        $this->basketContract   = $basketContract;
        $this->sessionStorage   = $sessionStorage;
    }

    /**
    * PayPal redirects to this page if the payment could not be executed or other problems occurred
    */
    public function payPalCheckoutCancel()
    {
        // clear the PayPal session values
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, null);
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAYER_ID, null);

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
        $ppPayId    = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);

        // Check whether the Pay ID from the session is equal to the given Pay ID by PayPal
        if($paymentId != $ppPayId)
        {
              $this->payPalCheckoutCancel();
        }

        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAYER_ID, $payerId);

        // Redirect to the success page. The URL can be entered in the config.json.
        header("Location: ".$this->config->get('PayPal.successUrl'));
        exit();
    }

    /**
     * Redirect to PayPal Express Checkout
     *
     */
    public function payPalExpressCheckout()
    {
        $basket = $this->basketContract->load();

        // get the paypal-express redirect URL
        $redirectURL = $this->paymentService->preparePayPalExpressPayment($basket);

        header("Location: " . $redirectURL);
        exit();
    }
}

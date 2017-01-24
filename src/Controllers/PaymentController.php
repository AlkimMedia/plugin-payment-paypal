<?php //strict

namespace PayPal\Controllers;

use PayPal\Services\PayPalInstallmentService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

use PayPal\Services\SessionStorageService;
use Paypal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;
use Plenty\Plugin\Templates\Twig;

/**
 * Class PaymentController
 * @package PayPal\Controllers
 */
class PaymentController extends Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

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
     * @param Request $request
     * @param Response $response
     * @param ConfigRepository $config
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param BasketRepositoryContract $basketContract
     * @param SessionStorageService $sessionStorage
     */
    public function __construct(  Request $request,
                                  Response $response,
                                  ConfigRepository $config,
                                  PaymentHelper $paymentHelper,
                                  PaymentService $paymentService,
                                  BasketRepositoryContract $basketContract,
                                  SessionStorageService $sessionStorage)
    {
        $this->request          = $request;
        $this->response         = $response;
        $this->config           = $config;
        $this->paymentHelper    = $paymentHelper;
        $this->paymentService   = $paymentService;
        $this->basketContract   = $basketContract;
        $this->sessionStorage   = $sessionStorage;
    }

    /**
     * PayPal redirects to this page if the payment could not be executed or other problems occurred
     */
    public function checkoutCancel()
    {
        // clear the PayPal session values
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAY_ID, null);
        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAYER_ID, null);

        // Redirects to the cancellation page. The URL can be entered in the config.json.
        return $this->response->redirectTo($this->config->get('PayPal.cancelUrl'));
    }

    /**
     * PayPal redirects to this page if the payment was executed correctly
     */
    public function checkoutSuccess()
    {
        // Get the PayPal payment data from the request
        $paymentId    = $this->request->get('paymentId');
        $payerId      = $this->request->get('PayerID');

        // Get the PayPal Pay ID from the session
        $ppPayId    = $this->sessionStorage->getSessionValue(SessionStorageService::PAYPAL_PAY_ID);

        // Check whether the Pay ID from the session is equal to the given Pay ID by PayPal
        if($paymentId != $ppPayId)
        {
            return $this->checkoutCancel();
        }

        $this->sessionStorage->setSessionValue(SessionStorageService::PAYPAL_PAYER_ID, $payerId);

        // update or create a contact
        $this->paymentService->handlePayPalCustomer($paymentId);

        // Redirect to the success page. The URL can be entered in the config.json.
        return $this->response->redirectTo('place-order');
    }

    /**
     * PayPal redirects to this page if the express payment could not be executed or other problems occurred
     */
    public function expressCheckoutCancel()
    {
        return $this->checkoutCancel();
    }

    /**
     * PayPal redirects to this page if the express payment was executed correctly
     */
    public function expressCheckoutSuccess()
    {
        return $this->checkoutSuccess();
    }

    /**
     * Redirect to PayPal Express Checkout
     */
    public function expressCheckout()
    {
        /** @var Basket $basket */
        $basket = $this->basketContract->load();

        /** @var Checkout $checkout */
        $checkout = pluginApp(\Plenty\Modules\Frontend\Contracts\Checkout::class);

        $checkout->setPaymentMethodId($this->paymentHelper->getPayPalExpressMopId());

        // get the paypal-express redirect URL
        $redirectURL = $this->paymentService->preparePayPalExpressPayment($basket);

        return $this->response->redirectTo($redirectURL);
    }

    /**
     * Change the payment method in the basket when user select a none paypal plus method
     *
     * @param $paymentMethod
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePaymentMethod(Checkout $checkout, Request $request)
    {
        $paymentMethod = $request->get('paymentMethod');
        if(isset($paymentMethod) && $paymentMethod > 0)
        {
            $checkout->setPaymentMethodId($paymentMethod);

            // TODO change to setter
            $this->sessionStorage->setSessionValue('MethodOfPaymentID', $paymentMethod);
        }
    }

    public function calculateFinancingOptions(PayPalInstallmentService $payPalInstallmentService, Twig $twig, $amount)
    {
        if($amount > 99 && $amount < 5000)
        {
            $qualifyingFinancingOptions = [];
            $financingOptions = $payPalInstallmentService->getFinancingOptions($amount);

            if(is_array($financingOptions) && array_key_exists('financing_options', $financingOptions))
            {
                if(is_array($financingOptions['financing_options'][0]) && is_array(($financingOptions['financing_options'][0]['qualifying_financing_options'])))
                {
                    $qualifyingFinancingOptions = $financingOptions['financing_options'][0]['qualifying_financing_options'];
                }
            }

            return $twig->render('PayPal::PayPalInstallment.InstallmentOverlay', ['basketAmount'=>$amount, 'financingOptions'=>$qualifyingFinancingOptions, 'merchantName'=>'Testfirma', 'merchantAddress'=>'Teststraße 1, 34117 Kassel']);
        }
    }

}

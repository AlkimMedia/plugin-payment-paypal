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
  public function __construct(Application $app,
                              Twig $twig,
                              Request $request,
                              ConfigRepository $config,
                              PaymentHelper $payHelper)
  {
    $this->app = $app;
    $this->twig = $twig;
    $this->request = $request;
    $this->config = $config;
    $this->payHelper = $payHelper;
  }

  /**
   * this is where paypal will redirect to if issues occured
   */
  public function payPalCheckoutCancel()
  {
    /*
     * redirect to the cancel page
     */
    header("Location: ".$this->config->get('PayPal.cancelUrl'));
    exit();
  }

  /**
   * this is where paypal will redirect to if everything went fine
   */
  public function payPalCheckoutSuccess()
  {
    /*
     * get the paypal payment data from the request
     */
    $paymentId = $this->request->get('paymentId');
    $payerId = $this->request->get('PayerID');

    /*
     * get the paypal payId from the session
     */
    $ppPayId = $this->payHelper->getPPPayID();

    /*
     * check if the payId from the session is equal to the given payId by paypal
     */
    if($paymentId != $ppPayId)
    {
      $this->payPalCheckoutCancel();
    }

    /*
     * set the paypal data in the session
     */
    $this->payHelper->setPPPayID($paymentId);
    $this->payHelper->setPPPayerID($payerId);

    /*
     * redirect to the confirmation page
     */
    header("Location: ".$this->config->get('PayPal.successUrl'));
    exit();
  }
}
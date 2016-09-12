<?hh //strict

namespace PayPal\Controllers;

use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Plugin\Http\Request;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;

/**
 * Class PaymentController
 * @package PayPal\Controllers
 */
class PaymentController extends Controller
{
  protected Application $app;
  private Twig $twig;
  private Dispatcher $event;
  private Request $request;

  private OrderRepositoryContract $orderRepo;
  private PaymentHelper $payHelper;

  /**
   * PaymentController constructor.
   * @param Application $app
   * @param Twig $twig
   * @param Dispatcher $event
   * @param OrderRepositoryContract $orderRep
   * @param PaymentHelper $payHelper
   * @param Request $request
   */
  public function __construct(Application $app,
                              Twig $twig,
                              Dispatcher $event,
                              OrderRepositoryContract $orderRep,
                              PaymentHelper $payHelper,
                              Request $request)
  {
    $this->app = $app;
    $this->twig = $twig;
    $this->event = $event;
    $this->orderRepo = $orderRep;
    $this->payHelper = $payHelper;
    $this->request = $request;
  }

  /**
   * @param Twig $twig
   * @return string
   */
  public function showPPExpressButton(Twig $twig):string
  {
    return $twig->render('PayPal::content.PayPalExpressButton');
  }

  /**
   * this is where paypal will redirect to if issues occured
   */
  public function payPalCheckoutCancel():void
  {
    /*
     * redirect to the cancel page
     */
    header("Location: http://master.plentymarkets.com/checkout");
    exit();
  }

  /**
   * this is where paypal will redirect to if everything went fine
   */
  public function payPalCheckoutSuccess():void
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
    header("Location: http://master.plentymarkets.com/confirmation?paymentId=".(string)$paymentId."&paymentIdNew=".(string)$ppPayId."&payer=".(string)$payerId);
    exit();
  }

}

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

class PaymentController extends Controller
{
  protected Application $app;
  private Twig $twig;
  private Dispatcher $event;
  private Request $request;

  private OrderRepositoryContract $orderRepo;
  private PaymentHelper $payHelper;

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

  public function showPPExpressButton(Twig $twig):string
  {
    return $twig->render('PayPal::content.PayPalExpressButton');
  }

  public function getPayPalPayment(PaymentService $paymentService):string
  {
    return $paymentService->getPayPalPayment();
  }

  public function preparePayment(PaymentService $paymentService):void
  {
    $paymentService->preparePayment();
  }

  public function executePayment(PaymentService $paymentService):void
  {
    $paymentService->executePayment();
  }

  public function payPalCheckoutCancel():void
  {
    header("Location: http://master.plentymarkets.com/34poepoe/zahlungsarten/");
    exit();
  }

  public function payPalCheckoutSuccess():void
  {
    $paymentId = $this->request->get('paymentId');
    $payerId = $this->request->get('PayerID');

    $ppPayId = $this->payHelper->getPPPayID();

    // Check if the Pay ID has changed
    if($paymentId != $ppPayId)
    {
      header("Location: http://master.plentymarkets.com/34poepoe/zahlungsarten?paymentId=".(string)$paymentId."&paymentIdNew=".(string)$ppPayId."&payer=".(string)$payerId);
      exit();
    }

    $this->payHelper->setPPPayID($paymentId);
    $this->payHelper->setPPPayerID($payerId);

    header("Location: http://master.plentymarkets.com/34poepoe/bestellbestaetigung?paymentId=".(string)$paymentId."&paymentIdNew=".(string)$ppPayId."&payer=".(string)$payerId);
    exit();
  }

}

<?hh //strict

namespace PayPal\Controllers;

use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Events\Dispatcher;

use PayPal\Services\PaymentService;

class PaymentController extends Controller
{
  protected Application $app;
  private Twig $twig;
  private Dispatcher $event;

  public function __construct(Application $app, Twig $twig, Dispatcher $event)
  {
    $this->app = $app;
    $this->twig = $twig;
    $this->event = $event;
  }

  public function showPPExpressButton(Twig $twig):string
  {
    return $twig->render('PayPal::content.PayPalExpressButton');
  }

  public function preparePayment(PaymentService $paymentService):void
  {
    $paymentService->preparePayment();
  }

  public function ppCheckoutCancel():void
  {
    header("Location: http://master.plentymarkets.com/34poepoe/zahlungsarten/");
    exit();
  }

  public function ppCheckoutSuccess():void
  {
    header("Location: http://master.plentymarkets.com/34poepoe/bestellbestaetigung/");
    exit();
  }

}

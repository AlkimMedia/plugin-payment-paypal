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

  public function createPayPalPayment(string $paymentEvent, array<string, mixed> $paymentData, PaymentService $paymentService):void
  {
    $paymentStatus = array();
    $paymentStatus['name'] = 'awaiting-approval';
    $paymentStatus['lang'] = 'de';

    $paymentData['status'] = $paymentStatus;

    $paymentService->createPayment($paymentData);

    $this->event->fire('paymentCreated');
  }

  public function updatePayPalPayment(string $paymentEvent, array<string, mixed> $paymentData, PaymentService $paymentService):void
  {
    $paymentStatus = array();
    $paymentStatus['name'] = 'paid';
    $paymentStatus['lang'] = 'de';

    $paymentData['status'] = $paymentStatus;

    $paymentService->updatePayment($paymentData);

    $this->event->fire('paymentUpdated', $paymentStatus);
  }

}

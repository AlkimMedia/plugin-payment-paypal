<?hh //strict

namespace PayPal\Helper;

use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;

use PayPal\Services\SessionStorageService;

class PaymentHelper
{
  private Application $app;
  private PaymentMethodRepositoryContract $paymentMethodRepository;
  private ConfigRepository $config;
  private SessionStorageService $sessionService;
  private PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo;
  private PaymentProperty $paymentProperty;
  private PaymentRepositoryContract $paymentRepo;
  private Payment $payment;

  public function __construct(Application $app,
                              PaymentMethodRepositoryContract $paymentMethodRepository,
                              PaymentRepositoryContract $paymentRepo,
                              PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo,
                              ConfigRepository $config,
                              SessionStorageService $sessionService,
                              Payment $payment,
                              PaymentProperty $paymentProperty)
  {
    $this->app = $app;
    $this->paymentMethodRepository = $paymentMethodRepository;
    $this->paymentOrderRelationRepo = $paymentOrderRelationRepo;
    $this->paymentProperty = $paymentProperty;
    $this->paymentRepo = $paymentRepo;
    $this->config = $config;
    $this->sessionService = $sessionService;
    $this->payment = $payment;
  }

  public function createMopIfNotExists():void
  {
    if($this->getMop() == 'no_paymentmethod_found')
    {
      $paymentMethodData = array( 'pluginKey' => 'PayPal',
                                  'paymentKey' => 'PAYPALEXPRESS',
                                  'name' => 'PayPal');

      $this->paymentMethodRepository->createPaymentMethod($paymentMethodData);
    }
  }

  public function getMop():mixed
  {
    $paymentMethods = $this->paymentMethodRepository->allForPlugin('PayPal');

    if(count($paymentMethods))
    {
      foreach($paymentMethods as $paymentMethod)
      {
        if($paymentMethod->paymentKey == 'PAYPALEXPRESS')
        {
          return $paymentMethod->id;
        }
      }
    }

    return 'no_paymentmethod_found';
  }

  public function getCancelURL():string
  {
    return 'http://master.plentymarkets.com/payPalCheckoutCancel';
  }

  public function getSuccessURL():string
  {
    return 'http://master.plentymarkets.com/payPalCheckoutSuccess';
  }

  public function setPPPayID(mixed $value):void
  {
    $this->sessionService->setSessionValue('PayPalPayId', $value);
  }

  public function getPPPayID():mixed
  {
    return $this->sessionService->getSessionValue('PayPalPayId');
  }

  public function setPPPayerID(mixed $value):void
  {
    $this->sessionService->setSessionValue('PayPalPayerId', $value);
  }

  public function getPPPayerID():mixed
  {
    return $this->sessionService->getSessionValue('PayPalPayerId');
  }

  public function createPlentyPayment(string $json):Payment
  {
    $payPalPayment = json_decode($json);

    $this->payment->mopId = (int)$this->getMop();
    $this->payment->currency = $payPalPayment->currency;
    $this->payment->amount = $payPalPayment->amount;
    $this->payment->entryDate = $payPalPayment->entryDate;
    $this->payment->status = $this->mapStatus($payPalPayment->status);
    $this->payment->transactionType = 2;

    /** @var PaymentProperty $paymentProp */
    $paymentProp = $this->paymentProperty;

    $paymentProp->typeId = 3;
    $paymentProp->value = $payPalPayment->bookingText;

    $prop1 = $paymentProp->toArray();

    $paymentProp->typeId = 6;
    $paymentProp->value = $this->paymentRepo->getOriginConstants('plugin');

    $prop2 = $paymentProp->toArray();

    $paymentProps = array($prop1, $prop2);

    $this->payment->property = $paymentProps;

    $payment = $this->paymentRepo->createPayment(array($this->payment->toArray()));

    return $payment;
  }

  public function assignPlentyPaymentToPlentyOrder(Payment $payment, Order $order):void
  {
    $this->paymentOrderRelationRepo->createOrderRelation($payment, $order);
  }

  public function mapStatus(string $status):int
  {
    $statusMap = array( 'created'               => $this->paymentRepo->getStatusConstants('captured'),
                        'approved'              => $this->paymentRepo->getStatusConstants('approved'),
                        'failed'                => $this->paymentRepo->getStatusConstants('refused'),
                        'partially_completed'   => $this->paymentRepo->getStatusConstants('partially_captured'),
                        'completed'             => $this->paymentRepo->getStatusConstants('captured'),
                        'in_progress'           => $this->paymentRepo->getStatusConstants('awaiting_approval'),
                        'pending'               => $this->paymentRepo->getStatusConstants('awaiting_approval'),
                        'refunded'              => $this->paymentRepo->getStatusConstants('refunded'),
                        'denied'                => $this->paymentRepo->getStatusConstants('refused'));

    return (int)$statusMap[$status];
  }

}

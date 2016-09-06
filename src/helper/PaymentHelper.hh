<?hh //strict

namespace PayPal\Helper;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Models\PaymentOrderRelation;

use PayPal\Services\SessionStorageService;

class PaymentHelper
{
  private PaymentMethodRepositoryContract $paymentMethodRepository;
  private ConfigRepository $config;
  private SessionStorageService $sessionService;
  private PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo;
  private PaymentOrderRelation $paymentOrderRelation;
  private PaymentRepositoryContract $paymentRepo;
  private Payment $payment;

  public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                              PaymentRepositoryContract $paymentRepo,
                              PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo,
                              ConfigRepository $config,
                              SessionStorageService $sessionService,
                              Payment $payment,
                              PaymentOrderRelation $paymentOrderRelation)
  {
    $this->paymentMethodRepository = $paymentMethodRepository;
    $this->paymentOrderRelationRepo = $paymentOrderRelationRepo;
    $this->paymentOrderRelation = $paymentOrderRelation;
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
      return $paymentMethods->first()->id;
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

    $paymentProperties = array();

    $this->payment->mopId = (int)$this->getMop();
    $this->payment->currency = $payPalPayment->currency;
    $this->payment->amount = $payPalPayment->amount;
    $this->payment->entryDate = $payPalPayment->entryDate;
    $this->payment->origin = 6;//$this->paymentRepo->getOriginConstants('plugin');
    $this->payment->status = 2;//$this->mapStatus($payPalPayment->status);
    $this->payment->transactionType = 2;

//    $this->payment->property($paymentProperties);

    $payment = $this->paymentRepo->createPayment($this->payment->toArray());

    return $payment;
  }

  public function assignPlentyPaymentToPlentyOrder(Payment $payment, Order $order):string
  {
    $pay = $payment;
    $ord = $order;

    return 'success';
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

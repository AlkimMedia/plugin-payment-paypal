<?hh //strict

namespace PayPal\Helper;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;

use PayPal\Services\SessionStorageService;

class PaymentHelper
{
  private PaymentMethodRepositoryContract $paymentMethodRepository;
  private ConfigRepository $config;
  private SessionStorageService $sessionService;
  private PaymentOrderRelationRepositoryContract $paymentOrderRelation;
  private PaymentRepositoryContract $paymentRepo;
  private Payment $payment;

  public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                              PaymentRepositoryContract $paymentRepo,
                              PaymentOrderRelationRepositoryContract $paymentOrderRelation,
                              ConfigRepository $config,
                              SessionStorageService $sessionService,
                              Payment $payment)
  {
    $this->paymentMethodRepository = $paymentMethodRepository;
    $this->paymentOrderRelation = $paymentOrderRelation;
    $this->paymentRepo = $paymentRepo;
    $this->config = $config;
    $this->sessionService = $sessionService;
    $this->payment = $payment;
  }

  public function createMopIfNotExists():void
  {
    if(!strlen($this->getMop()))
    {
      $paymentMethodData = array( 'pluginKey' => 'paypal',
                                  'paymentKey' => 'standard',
                                  'name' => 'PayPal');

      $this->paymentMethodRepository->createPaymentMethod($paymentMethodData);
    }
  }

  public function getMop():string
  {
    $mop = 'plenty.paypal';

    //$mops = $this->paymentMethodRepository->allForPlugin('PayPal');

    //$mop = $mops['standard'];

    return $mop;
  }

  public function getCancelURL():string
  {
    return 'http://master.plentymarkets.com/payPalCheckoutCancel';
  }

  public function getSuccessURL():string
  {
    return 'http://master.plentymarkets.com/payPalCheckoutSuccess';
  }

  public function setPPPayID(string $value):void
  {
    $this->sessionService->setSessionValue('PayPalPayId', $value);
  }

  public function getPPPayID():string
  {
    return (string)$this->sessionService->getSessionValue('PayPalPayId');
  }

  public function setPPPayerID(string $value):void
  {
    $this->sessionService->setSessionValue('PayPalPayerId', $value);
  }

  public function getPPPayerID():string
  {
    return (string)$this->sessionService->getSessionValue('PayPalPayerId');
  }

  public function createPlentyPayment(mixed $json):Payment
  {
    $payPalPayment = $json;

    $paymentProperties = array();

    $this->payment->mopId = $this->getMop();
    $this->payment->currency = $payPalPayment->currency;
    $this->payment->amount = $payPalPayment->amount;
    $this->payment->entryDate = $payPalPayment->entryDate;
    $this->payment->origin = Payment::ORIGIN_PLUGIN;
    $this->payment->status = $this->mapStatus($payPalPayment->status);


    $this->payment->property($paymentProperties);

    $payment = $this->paymentRepo->createPayment($this->payment->toArray());

    return $payment;
  }

  public function assignPlentyPaymentToPlentyOrder(Payment $payment, Order $order):string
  {


    return 'success';
  }

  public function mapStatus(string $status):int
  {
    $statusMap = array( 'created'               => Payment::STATUS_CAPTURED,
                        'approved'              => Payment::STATUS_APPROVED,
                        'failed'                => Payment::STATUS_REFUSED,
                        'partially_completed'   => Payment::STATUS_PARTIALLY_CAPTURED,
                        'completed'             => Payment::STATUS_CAPTURED,
                        'in_progress'           => Payment::STATUS_AWAITING_APPROVAL,
                        'pending'               => Payment::STATUS_AWAITING_APPROVAL,
                        'refunded'              => Payment::STATUS_REFUNDED,
                        'denied'                => Payment::STATUS_REFUSED);

    return $statusMap[$status];
  }

}

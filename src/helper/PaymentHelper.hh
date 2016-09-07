<?hh //strict

namespace PayPal\Helper;

use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentOrderRelationRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
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
  private OrderRepositoryContract $orderRepo;

  public function __construct(Application $app,
                              PaymentMethodRepositoryContract $paymentMethodRepository,
                              PaymentRepositoryContract $paymentRepo,
                              PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo,
                              ConfigRepository $config,
                              SessionStorageService $sessionService,
                              Payment $payment,
                              PaymentProperty $paymentProperty,
                              OrderRepositoryContract $orderRepo)
  {
    $this->app = $app;
    $this->config = $config;
    $this->sessionService = $sessionService;
    $this->paymentMethodRepository = $paymentMethodRepository;
    $this->paymentOrderRelationRepo = $paymentOrderRelationRepo;
    $this->paymentRepo = $paymentRepo;
    $this->paymentProperty = $paymentProperty;
    $this->orderRepo = $orderRepo;
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
    $paymentProp1 = $this->paymentProperty;

    $paymentProp2 = clone $paymentProp1;

    $paymentProp1->typeId = 3;
    $paymentProp1->value = 'PayPalPayID: '.(string)$payPalPayment->bookingText;

    $paymentProp2->typeId = 23;
    $paymentProp2->value = (string)$this->paymentRepo->getOriginConstants('plugin');

    $paymentProps = array($paymentProp1, $paymentProp2);

    $this->payment->property = $paymentProps;

    $payment = $this->paymentRepo->createPayment($this->payment);

    return $payment;
  }

  public function assignPlentyPaymentToPlentyOrder(Payment $payment, int $orderId):void
  {
    $order = $this->orderRepo->findOrderById($orderId);

    if(!is_null($order) && $order instanceof Order)
    {
      $this->paymentOrderRelationRepo->createOrderRelation($payment, $order);
    }
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

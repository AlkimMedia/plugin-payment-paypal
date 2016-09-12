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

/**
 * Class PaymentHelper
 * @package PayPal\Helper
 */
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
  private array<string, int> $statusMap;

  /**
   * PaymentHelper constructor.
   * @param Application $app
   * @param PaymentMethodRepositoryContract $paymentMethodRepository
   * @param PaymentRepositoryContract $paymentRepo
   * @param PaymentOrderRelationRepositoryContract $paymentOrderRelationRepo
   * @param ConfigRepository $config
   * @param SessionStorageService $sessionService
   * @param Payment $payment
   * @param PaymentProperty $paymentProperty
   * @param OrderRepositoryContract $orderRepo
   */
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
    $this->statusMap = array();
  }

  /**
   * create the payment method id
   */
  public function createMopIfNotExists():void
  {
    /*
     * check if the payment method is already created
     */
    if($this->getMop() == 'no_paymentmethod_found')
    {
      $paymentMethodData = array( 'pluginKey' => 'PayPal',
                                  'paymentKey' => 'PAYPALEXPRESS',
                                  'name' => 'PayPal');

      $this->paymentMethodRepository->createPaymentMethod($paymentMethodData);
    }
  }

  /**
   * @return mixed
   */
  public function getMop():mixed
  {
    /*
     * get all payment methods for the given plugin
     */
    $paymentMethods = $this->paymentMethodRepository->allForPlugin('PayPal');

    if( !is_null($paymentMethods) )
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

  /**
   * @return string
   */
  public function getCancelURL():string
  {
    return 'http://master.plentymarkets.com/payPalCheckoutCancel';
  }

  /**
   * @return string
   */
  public function getSuccessURL():string
  {
    return 'http://master.plentymarkets.com/payPalCheckoutSuccess';
  }

  /**
   * @param mixed $value
   */
  public function setPPPayID(mixed $value):void
  {
    $this->sessionService->setSessionValue('PayPalPayId', $value);
  }

  /**
   * @return mixed
   */
  public function getPPPayID():mixed
  {
    return $this->sessionService->getSessionValue('PayPalPayId');
  }

  /**
   * @param mixed $value
   */
  public function setPPPayerID(mixed $value):void
  {
    $this->sessionService->setSessionValue('PayPalPayerId', $value);
  }

  /**
   * @return mixed
   */
  public function getPPPayerID():mixed
  {
    return $this->sessionService->getSessionValue('PayPalPayerId');
  }

  /**
   * @param string $json
   * @return Payment
   */
  public function createPlentyPayment(string $json):Payment
  {
    $payPalPayment = json_decode($json);

    /** @var Payment $payment */
    $payment = clone $this->payment;

    /*
     * set the payment data
     */
    $payment->mopId           = (int)$this->getMop();
    $payment->transactionType = 2;
    $payment->status          = $this->mapStatus($payPalPayment->status);
    $payment->currency        = $payPalPayment->currency;
    $payment->amount          = $payPalPayment->amount;
    $payment->entryDate       = $payPalPayment->entryDate;

    /** @var PaymentProperty $paymentProp1 */
    $paymentProp1 = clone $this->paymentProperty;

    /** @var PaymentProperty $paymentProp2 */
    $paymentProp2 = clone $this->paymentProperty;

    /*
     * set the payment properties
     */
    $paymentProp1->typeId = 3;
    $paymentProp1->value = 'PayPalPayID: '.(string)$payPalPayment->bookingText;

    /*
     * set the payment properties
     */
    $paymentProp2->typeId = 23;
    $originConstants = $this->paymentRepo->getOriginConstants();
    if(!is_null($originConstants) && is_array($originConstants))
    {
      $paymentProp2->value = (string)$originConstants['plugin'];
    }

    /** @var PaymentProperty[] $paymentProps */
    $paymentProps = array($paymentProp1,
                          $paymentProp2);

    /*
     * append the properties to the payment
     */
    $payment->property = $paymentProps;

    $payment = $this->paymentRepo->createPayment($payment);

    return $payment;
  }

  /**
   * @param Payment $payment
   * @param int $orderId
   */
  public function assignPlentyPaymentToPlentyOrder(Payment $payment, int $orderId):void
  {
    /*
     * get the order by the given orderId
     */
    $order = $this->orderRepo->findOrderById($orderId);

    /*
     * check if the order truly exists
     */
    if(!is_null($order) && $order instanceof Order)
    {
      /*
       * assign the given payment to the given order
       */
      $this->paymentOrderRelationRepo->createOrderRelation($payment, $order);
    }
  }

  /**
   * @param string $status
   * @return int
   *
   * this function maps the paypal payment status to the plenty payment status
   */
  public function mapStatus(string $status):int
  {
    if(!is_array($this->statusMap) || count($this->statusMap) <= 0)
    {
      $this->statusMap = array();
      $statusConstants = $this->paymentRepo->getStatusConstants();

      if(!is_null($statusConstants) && is_array($statusConstants))
      {
        $this->statusMap['created'] = $statusConstants['captured'];
        $this->statusMap['approved'] = $statusConstants['approved'];
        $this->statusMap['failed'] = $statusConstants['refused'];
        $this->statusMap['partially_completed'] = $statusConstants['partially_captured'];
        $this->statusMap['completed'] = $statusConstants['captured'];
        $this->statusMap['in_progress'] = $statusConstants['awaiting_approval'];
        $this->statusMap['pending'] = $statusConstants['awaiting_approval'];
        $this->statusMap['refunded'] = $statusConstants['refunded'];
        $this->statusMap['denied'] = $statusConstants['refused'];
      }
    }

    return (int)$this->statusMap[$status];
  }
}

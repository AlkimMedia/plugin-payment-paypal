<?hh //strict

namespace PayPal\Helper;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\ConfigRepository;
use PayPal\Services\SessionStorageService;

class PaymentHelper
{
  private PaymentMethodRepositoryContract $paymentMethodRepository;
  private ConfigRepository $config;
  private SessionStorageService $sessionService;

  public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                              ConfigRepository $config,
                              SessionStorageService $sessionService)
  {
    $this->paymentMethodRepository = $paymentMethodRepository;
    $this->config = $config;
    $this->sessionService = $sessionService;
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
}

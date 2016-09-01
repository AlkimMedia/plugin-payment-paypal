<?hh //strict

namespace PayPal\Helper;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\ConfigRepository;

class PaymentHelper
{
  private PaymentMethodRepositoryContract $paymentMethodRepository;
  private ConfigRepository $config;

  public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                              ConfigRepository $config)
  {
    $this->paymentMethodRepository = $paymentMethodRepository;
    $this->config = $config;
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
    return 'http://master.plentymarkets.com/ppCheckoutCancel';
  }

  public function getSuccessURL():string
  {
    return 'http://master.plentymarkets.com/ppCheckoutSuccess';
  }
}

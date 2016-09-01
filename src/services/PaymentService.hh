<?hh //strict

namespace PayPal\Services;

use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;

use PayPal\Helper\PaymentHelper;

class PaymentService
{
    private PaymentMethodRepositoryContract $paymentMethodRepository;
    private PaymentRepositoryContract $paymentRepository;
    private PaymentHelper $paymentHelper;
    private LibraryCallContract $libCall;

    private bool $sandbox = false;
    private string $payPalAccount = '';

    private string $returnType = '';

    public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                                PaymentRepositoryContract $paymentRepository,
                                ConfigRepository $config,
                                PaymentHelper $paymentHelper,
                                LibraryCallContract $libCall)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentHelper = $paymentHelper;
        $this->libCall = $libCall;

        if($config->get('PayPal.environment') == 1)
        {
          $this->sandbox = true;
        }

        $account = $config->get('PayPal.account');
        if(strlen($account))
        {
          $this->payPalAccount = $account;
        }
    }

    public function preparePayment():void
    {
        $this->paymentMethodRepository->preparePaymentMethod($this->paymentHelper->getMop());
    }

    public function getPayPalContent(Basket $basket):string
    {
      $payPalRequestParams = array();
      $redirectUrl = '';

      $payPalRequestParams['sandbox'] = $this->sandbox;

      $amount = array('currency' => $basket->currency,
                      'total' => $basket->basketAmount);

      $payPalRequestParams['amount'] = $amount;

      $urls = array('returnUrl' => $this->paymentHelper->getSuccessURL(),
                    'cancelUrl' => $this->paymentHelper->getCancelURL());

      $payPalRequestParams['urls'] = $urls;

      // make the prepare call for paypal
      $result = $this->libCall->call('PayPal::preparePayment', $payPalRequestParams);

      if(is_array($result) && $result['error'])
      {
        $this->returnType = 'errorCode';
        return $result['error_msg'];
      }

      $resultJson = json_decode($result);

      //TO-DO: check here if the result is valid

      $transactions = $resultJson->transactions;

      //save this on in the session
      $payerId = $resultJson->id;

      $links = $resultJson->links;

      if(is_array($links))
      {
        foreach($links as $key => $value)
        {
          if($value->method == 'REDIRECT')
          {
            $redirectUrl = $value->href;
            $this->returnType = 'redirectUrl';
          }
        }
      }

      if(!strlen($redirectUrl))
      {
        $this->returnType = 'errorCode';
        return 'An unknown error occured, please try again.';
      }

      return $redirectUrl;
    }

    public function getReturnType():string
    {
      return $this->returnType;
    }

}

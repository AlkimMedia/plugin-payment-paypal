<?hh //strict

namespace PayPal\Services;

use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;

use PayPal\Helper\PaymentHelper;

class PaymentService
{
    private PaymentMethodRepositoryContract $paymentMethodRepository;
    private PaymentRepositoryContract $paymentRepository;
    private PaymentHelper $paymentHelper;
    private LibraryCallContract $libCall;
    private AddressRepositoryContract $addressRepo;

    private bool $sandbox = false;
    private string $payPalAccount = '';

    private string $returnType = '';

    public function __construct(PaymentMethodRepositoryContract $paymentMethodRepository,
                                PaymentRepositoryContract $paymentRepository,
                                ConfigRepository $config,
                                PaymentHelper $paymentHelper,
                                LibraryCallContract $libCall,
                                AddressRepositoryContract $addressRepo)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentHelper = $paymentHelper;
        $this->libCall = $libCall;
        $this->addressRepo = $addressRepo;

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

    public function getPayPalPayment():string
    {
        return print_r($this->libCall->call('PayPal::getPayPalPayment', array('payId' => 'PAY-4RW9947250679234JK7HLVCY', 'sandbox' => true)), true);
    }

    public function executePayment():void
    {
        $this->paymentMethodRepository->executePayment('plenty.paypal', 2);
    }

    public function getPayPalContent(Basket $basket):string
    {
      $payPalRequestParams = array();
      $redirectUrl = '';
      $payPalMerchantParams = array();

//    $webProfileId = $this->libCall->call('PayPal::createWebProfile', $payPalMerchantParams);

      $payPalRequestParams['webProfileId'] = 'XP-3XH9-EMJG-WX7L-789D';

      $payPalRequestParams['sandbox'] = $this->sandbox;

      $payPalRequestParams['basket'] = $basket;

      $payPalRequestParams['basketItems'] = $basket->basketItems;

//      $shippingAddress = $this->addressRepo->findAddressById($basket->customerShippingAddressId);

        $address= array();
        $country = array();
        $address['town'] = 'hofteister';
        $country['isoCode2'] = 'DE';
        $address['postalCode'] = '34369';
        $address['firstname'] = 'Franz';$address['lastname'] = 'stock';
        $address['street'] = 'Krizstraße';$address['houseNumber'] = '23';

      $payPalRequestParams['shippingAddress'] = $address;

      $payPalRequestParams['country'] = $country;

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

      //save this in the session
      $ppPayId = $resultJson->id;
      if(strlen($ppPayId))
      {
          $this->paymentHelper->setPPPayID($ppPayId);
      }

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

    public function payPalExecutePayment():string
    {
        $ppPayId = $this->paymentHelper->getPPPayID();
        $ppPayerId = $this->paymentHelper->getPPPayerID();

        $executeParams = array();

        $executeParams['sandbox'] = $this->sandbox;
        $executeParams['payId'] = $ppPayId;
        $executeParams['payerId'] = $ppPayerId;

        $executeResponse = $this->libCall->call('PayPal::executePayment', $executeParams);

        if(is_array($executeResponse) && $executeResponse['error'])
        {
            $this->returnType = 'errorCode';
            return $executeResponse['error'].': '.$executeResponse['error_msg'];
        }

        $result = $executeResponse;

        // unset the session params
        $this->paymentHelper->setPPPayID('');
        $this->paymentHelper->setPPPayerID('');

        return (string)$result;
    }

}

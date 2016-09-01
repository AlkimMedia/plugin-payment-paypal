<?php
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

/**
 * ### getBaseUrl function
 * // utility function that returns base url for
 * // determining return/cancel urls
 *
 * @return string
 */
function getBaseUrl()
{
  if (PHP_SAPI == 'cli')
  {
    $trace=debug_backtrace();
    $relativePath = substr(dirname($trace[0]['file']), strlen(dirname(dirname(__FILE__))));
    echo "Warning: This sample may require a server to handle return URL. Cannot execute in command line. Defaulting URL to http://localhost$relativePath \n";
    return "http://localhost" . $relativePath;
  }

  $protocol = 'http';

  if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'))
  {
    $protocol .= 's';
  }

  $host = $_SERVER['HTTP_HOST'];
  $request = $_SERVER['PHP_SELF'];

  return dirname($protocol . '://' . $host . $request);
}

/**
 * Helper method for getting an APIContext for all calls
 * @param string $clientId Client ID
 * @param string $clientSecret Client Secret
 * @return PayPal\Rest\ApiContext
 */
function getApiContext($clientId, $clientSecret)
{
  if(SdkRestApi::getParam('sandbox', true))
  {
    $mode = 'sandbox';
    $endpoint = "https://test-api.sandbox.paypal.com";
  }
  else
  {
    //live environment
    $mode = 'sandbox';
    $endpoint = "https://test-api.sandbox.paypal.com";
  }

  $apiContext = new ApiContext(
      new OAuthTokenCredential(
          $clientId,
          $clientSecret
      )
  );

  $apiContext->setConfig(
      array(
          'mode' => $mode,
          'service.EndPoint'  => $endpoint,
          'log.LogEnabled' => false,
          'cache.enabled' => false,
      )
  );

  return $apiContext;
}

// Replace these values by entering your own ClientId and Secret by visiting https://developer.paypal.com/webapps/developer/applications/myapps
$clientId = 'AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS';
$clientSecret = 'EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs6NKYQvQSBngp4PTTVWkPZRbL';

/** @var \Paypal\Rest\ApiContext $apiContext */
$apiContext = getApiContext($clientId, $clientSecret);

$payer = new Payer();
$payer->setPaymentMethod("paypal");

$basketAmount = SdkRestApi::getParam('amount');

if(is_null($basketAmount))
{
  return 'error';
}

$amount = new Amount();
$amount ->setCurrency($basketAmount['currency'])
        ->setTotal($basketAmount['total']);

$transaction = new Transaction();
$transaction->setAmount($amount);

$baseUrl = getBaseUrl();

$urls = SdkRestApi::getParam('urls');

if(is_null($urls))
{
  return 'error';
}

$redirectUrls = new RedirectUrls();
$redirectUrls ->setReturnUrl($urls['returnUrl'])
              ->setCancelUrl($urls['cancelUrl']);

$payment = new Payment();
$payment->setIntent("sale")
        ->setPayer($payer)
        ->setRedirectUrls($redirectUrls)
        ->setTransactions(array($transaction));

$payment->create($apiContext);

return (STRING)$payment;

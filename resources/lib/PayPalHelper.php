<?php
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;
use PayPal\Api\Payment;

class PayPalHelper
{
    /**
     * Creates the ApiContext with the given params
     *
     * @param string $clientId Client ID
     * @param string $clientSecret Client Secret
     * @param bool $sandbox
     * @return PayPal\Rest\ApiContext
     */
    static function getApiContext($clientId, $clientSecret, $sandbox = true)
    {
        if($sandbox)
        {
            $mode = 'sandbox';
            $endpoint = "https://api.sandbox.paypal.com";
        }
        else
        {
            $mode = 'live';
            $endpoint = "https://api.paypal.com";
        }

        /** @var ApiContext $apiContext */
        $apiContext = new ApiContext(
            new OAuthTokenCredential(   $clientId,
                                        $clientSecret));

        $apiContext->setConfig(
            array(  'mode'              => $mode,
                    'service.EndPoint'  => $endpoint,
                    'log.LogEnabled'    => false,
                    'cache.enabled'     => false,));

        return $apiContext;
    }

    /**
     * @param Payment $payment
     * @return mixed
     */
    static function mapPayment(Payment $payment)
    {
        $transaction = $payment->getTransactions()[0];

        $returnArray['saleId'] = $transaction->getRelatedResources()[0]->getSale()->getId();
        $returnArray['amount'] = $transaction->getAmount()->getTotal();
        $returnArray['currency'] = $transaction->getAmount()->getCurrency();
        $returnArray['entryDate'] = $payment->getCreateTime();
        $returnArray['status'] = $payment->getState();
        $returnArray['payerInfo'] = $payment->getPayer()->getPayerInfo();

        return $returnArray;
    }
}

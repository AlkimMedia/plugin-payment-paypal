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
     * Helper method for getting an APIContext for all calls
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

    static function mapPayment(Payment $payment)
    {
        $returnArray = array();

        $transaction = $payment->getTransactions()[0];

        $returnArray['bookingText'] = $payment->getId();
        $returnArray['amount'] = $transaction->getAmount()->getTotal();
        $returnArray['currency'] = $transaction->getAmount()->getCurrency();
        $returnArray['entryDate'] = $payment->getCreateTime();
        $returnArray['status'] = $payment->getState();

        return $returnArray;
    }
}

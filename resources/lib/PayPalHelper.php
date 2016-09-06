<?php
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;
use PayPal\Api\Payment;


class PayPalHelper
{
    static $payPalStatus = array(   'created'                => 1,
                                    'approved'               => 'approved',
                                    'failed'                 => 'failed',
                                    'partially_completed'    => 'partially_completed',
                                    'completed'              => 'completed',
                                    'in_progress'            => 'in_progress',
                                    'pending'                => 'pending',
                                    'refunded'               => 'refunded',
                                    'denied'                 => 'denied');

    /**
     * Helper method for getting an APIContext for all calls
     * @param string $clientId Client ID
     * @param string $clientSecret Client Secret
     * @return PayPal\Rest\ApiContext
     */
    static function getApiContext($sandbox = true)
    {
        // Replace these values by entering your own ClientId and Secret by visiting https://developer.paypal.com/webapps/developer/applications/myapps
        $clientId = 'AR0llxlDLgU8PEK32N2axJBvZOyULkseGs30YnXp6xPNaj3d_Iv15T52Kb6ba6gPDlT44NbBS3n9qMty';
        $clientSecret = 'EM0hx_v2uuKeaQ3DylABSrG7mA7sjhIuizNpiaPiva9dVAAChkqkg8oenBRfxd1fAuETw5qFu5NOe8Hn';

        if($sandbox)
        {
            $mode = 'sandbox';
            $endpoint = "https://test-api.sandbox.paypal.com";
        }
        else
        {
            //live environment
            $mode = 'sandbox';
            $endpoint = "https://api.sandbox.paypal.com";
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

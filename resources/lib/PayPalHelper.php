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
        }
        else
        {
            $mode = 'live';
        }

        /** @var ApiContext $apiContext */
        $apiContext = new ApiContext(
            new OAuthTokenCredential(   $clientId,
                                        $clientSecret));

        $apiContext->setConfig(
            array(  'mode'              => $mode,
                    'log.LogEnabled'    => false,
                    'cache.enabled'     => false,
                    'http.CURLOPT_CONNECTTIMEOUT' => 30));

        return $apiContext;
    }
}

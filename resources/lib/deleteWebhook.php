<?php

use PayPal\Api\Webhook;
use PayPal\Api\WebhookList;
use PayPal\Api\WebhookEventType;

require_once __DIR__.'/PayPalHelper.php';

$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

try
{
    $webhookList = \PayPal\Api\Webhook::getAll($apiContext);
}
catch(Exception $ex)
{
    return false;
}

if($webhookList instanceof PayPal\Api\WebhookList)
{
    try
    {
        foreach ($webhookList->getWebhooks() as $webhook)
        {
            $webhook->delete($apiContext);
        }
    }
    catch(Exception $ex)
    {
        return false;
    }
}

return true;
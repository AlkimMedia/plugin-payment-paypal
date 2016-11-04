<?php

use PayPal\Api\Webhook;
use PayPal\Api\WebhookEventType;

$result = false;

$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

$webhook = new Webhook();
$notificationUrl = SdkRestApi::getParam('notificationUrl', false);

if($notificationUrl)
{
    $webhook->setUrl($notificationUrl);

    $webhookEvents = SdkRestApi::getParam('webhookEvents', array());

    $webhookEventTypes = array();

    if(is_array($webhookEvents) && count($webhookEvents) > 0)
    {
        foreach ($webhookEvents as $event)
        {
            $webhookEventTypes[] = new WebhookEventType('
            {
                "name":"'.$event.'"
            }');
        }
    }

    $webhook->setEventTypes($webhookEventTypes);

    try
    {
        $result = $webhook->create($apiContext);
    }
    catch (Exception $e)
    {

    }
}

return $result;
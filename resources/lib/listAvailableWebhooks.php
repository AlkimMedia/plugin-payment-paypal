<?php

use PayPal\Api\WebhookEventType;

$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

$result = false;

try
{
    $result = WebhookEventType::availableEventTypes($apiContext);
}
catch (Exception $e)
{
    $result = false;
}

return $result;
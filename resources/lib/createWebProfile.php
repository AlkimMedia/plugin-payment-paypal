<?php
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;

require_once __DIR__.'/PayPalHelper.php';

/** @var \Paypal\Rest\ApiContext $apiContext */
$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

/** @var InputFields $inputFields */
$inputFields = new InputFields();
$inputFields
    ->setNoShipping(SdkRestApi::getParam('editableShipping', 0))
    ->setAddressOverride(SdkRestApi::getParam('addressOverride', 1));

/** @var Presentation $presentation */
$presentation = new Presentation();
$presentation
    ->setBrandName(SdkRestApi::getParam('brandName', ''));

if(SdkRestApi::getParam('shopLogo', false))
{
    $presentation->setLogoImage(SdkRestApi::getParam('shopLogo', false));
}

/** @var WebProfile $webProfile */
$webProfile = new WebProfile();
$webProfile
    ->setName(SdkRestApi::getParam('shopName', '').uniqid())
    ->setInputFields($inputFields)
    ->setPresentation($presentation);

try
{
    $webProfResponse = $webProfile->create($apiContext);
}
catch (PayPal\Exception\PPConnectionException $ex)
{
    return json_decode($ex->getData());
}
catch (Exception $e)
{
    return json_decode($e->getData());
}

return $webProfResponse->toArray();
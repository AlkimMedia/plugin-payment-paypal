<?php
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;

require_once __DIR__.'/PayPalHelper.php';

$apiContext = PayPalHelper::getApiContext(  SdkRestApi::getParam('clientId', true),
                                            SdkRestApi::getParam('clientSecret', true),
                                            SdkRestApi::getParam('sandbox', true));

$inputFields = new InputFields();
$inputFields
    ->setNoShipping(SdkRestApi::getParam('editableShipping', 0))
    ->setAddressOverride(1);

$presentation = new Presentation();
$presentation
    ->setBrandName(SdkRestApi::getParam('shopName', 'Bitte Shopnamen angeben!'));

if(SdkRestApi::getParam('shopLogo', false))
{
    $presentation->setLogoImage(SdkRestApi::getParam('shopLogo', false));
}

$webProfile = new WebProfile();
$webProfile
    ->setName('PayPalSHOOOOP'.uniqid())
    ->setInputFields($inputFields)
    ->setPresentation($presentation);

try
{
    $webProfResponse = $webProfile->create($apiContext);
}
catch(Exception $ex)
{
    return (STRING)$ex->getMessage();
}


return (STRING)$webProfResponse->getId();


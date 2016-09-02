<?php
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;

require_once __DIR__.'/PayPalHelper.php';

$inputFields = new InputFields();
$inputFields->setNoShipping(0)
    ->setAddressOverride(1);

$presentation = new Presentation();
$presentation   //->setLogoImage('')
->setBrandName('PayPalSHOOOOP');

$webProfile = new WebProfile();
$webProfile ->setName('PayPalSHOOOOP'.uniqid())
    ->setInputFields($inputFields)
    ->setPresentation($presentation);

try
{
    $webProfResponse = $webProfile->create(PayPalHelper::getApiContext(true));
}
catch(Exception $ex)
{
    return (STRING)$ex->getMessage();
}


return (STRING)$webProfResponse->getId();


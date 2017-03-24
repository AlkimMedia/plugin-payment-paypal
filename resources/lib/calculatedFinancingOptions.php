<?php

$sandbox = SdkRestApi::getParam('sandbox', true);
$clientId = SdkRestApi::getParam('clientId', false);
$clientSecret = SdkRestApi::getParam('clientSecret', false);

if($sandbox)
{
    $url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
}
else
{
    $url = 'https://api.paypal.com/v1/oauth2/token';
}

$auth = base64_encode($clientId.':'.$clientSecret);

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "grant_type=client_credentials",
    CURLOPT_HTTPHEADER => array(
        "authorization: Basic ".$auth."",
        "content-type: application/x-www-form-urlencoded"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err)
{
    return  "cURL Error #:" . $err;
}
else
{
    $authResult = json_decode($response, true);
}

if(isset($authResult['access_token']))
{
    $financingCountryCode = SdkRestApi::getParam('financingCountryCode', 'DE');
    $value = SdkRestApi::getParam('amount', 0);
    $currencyCode = SdkRestApi::getParam('currency','EUR');

    $data = [];
    $data['financing_country_code'] = $financingCountryCode;
    $data['transaction_amount'] = ['value'=> $value, 'currency_code' => $currencyCode];

    if($sandbox)
    {
        $url = 'https://api.sandbox.paypal.com/v1/credit/calculated-financing-options';
    }
    else
    {
        $url = 'https://api.paypal.com/v1/credit/calculated-financing-options';
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$authResult['access_token']."",
            "content-type: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err)
    {
        echo "cURL Error #:" . $err;
    }
    else
    {
        return json_decode($response);
    }
}

return $authResult;
<?php

namespace app\Http\Controllers\admin;
use App\Models\api;

class HonorApi
{
public function api()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.honourworld.com.ng/api/v1/generate/api/key',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
    "email": "boluwatifemoses001@gmail.com",
    "password": "Moses@21"
}',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
    $data=json_decode($response, true);
$msg=$data['message'];

    $charp = api::create([
        'message' => $msg,
    ]);
}

}

<?php

namespace App\Http\Controllers;
use Adrianorosa\GeoLocation\GeoLocation;
use App\Models\big;
use App\Models\data;
use Illuminate\Http\Request;
use Torann\GeoIP\GeoIP;


class listdata
{
public function list(Request $request)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://integration.mcd.5starcompany.com.ng/api/reseller/list',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('service' => 'data','coded' => '9'),
        CURLOPT_HTTPHEADER => array(
            'Authorization: mcd_key_75rq4][oyfu545eyuriup1q2yue4poxe3jfd'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

    $d= json_decode($response, true);
$data=$d['data'];
//return $data;
foreach ($data as $plan){
    $success =$plan["type"];
    $planid = $plan["code"];
    $price= $plan['amount'];
    $allowance=$plan['name'];
//    $validity =$plan['validity'];
    $insert= data::create([
        'plan_id' =>$planid,
        'network' =>$success,
        'plan' =>$allowance,
        'code' =>$planid,
        'amount'=>$price,
        'tamount'=>$price,
        'ramount'=>$price,
        'cat_id'=>$planid,
    ]);
}

//    return $product;

//    return view('pam', compact('product'));


}
public function lis()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.honourworld.com.ng/api/v1/set/webhook/url',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
    "url" : "https://mobile.Amazing-Data.com.ng/api/honor"
}',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer {{Token}}',
            'Content-Type: application/json',
            'Accept: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;

}
public function getlog(Request $request)
{

    $userIp = $request->ip();

    $details = GeoLocation::lookup($userIp);
    echo $userIp;

    var_dump($details->toArray());
}

}

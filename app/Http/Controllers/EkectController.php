<?php

namespace App\Http\Controllers;

use App\Mail\Emailtrans;
use App\Models\bo;
use App\Models\data;
use App\Models\easy;
use App\Models\User;
use App\Models\wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;

class EkectController
{
    public function listelect()
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://app2.mcd.5starcompany.com.ng/api/reseller/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('service' => 'electricity'),
            CURLOPT_HTTPHEADER => array(
                'Authorization: MCDKEY_903sfjfi0ad833mk8537dhc03kbs120r0h9a'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
        $data = json_decode($response, true);
        $plan= $data["data"];
        foreach ($plan as $pla) {
            $name = $pla['name'];
            $code = $pla['code'];
//return $response;
            $bo = data::create([
                'plan_id' => 'electricity',
                'plan' => 'elect',
                'network' => $name,
                'amount' => '0',
                'tamount' => '0',
                'cat_id' => $code,
            ]);
        }
    }
    public function electric(Request $request)
    {
        if (Auth::check()) {
            $user = User::find($request->user()->id);
            $tv = easy::where('network', 'elect')->get();

            return  view('elect', compact('user', 'tv'));

        }
        return redirect("login")->withSuccess('You are not allowed to access');

    }
    public function verifyelect($value1, $value2)
    {
        if (Auth::check()) {
            $tv = easy::where('id', $value2)->first();

//            return response()->json($value2);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://easyaccess.com.ng/api/verifyelectricity.php",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => array(
                    'company' =>$tv->code,
                    'metertype' =>01,
                    'meterno' =>$value1,
                    'amount' =>10000,
                ),
                CURLOPT_HTTPHEADER => array(
                    "AuthorizationToken: 61a6704775b3bd32b4499f79f0b623fc", //replace this with your authorization_token
                    "cache-control: no-cache"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
//            echo $response;
//                        return response()->json($response);

            $data = json_decode($response, true);
            $success= $data["success"];
            if ($success == "true"){
                $name=$data["message"]["content"]["Customer_Name"];

                $log=$name;
            }else{
                $log= "Unable to Identify meter Number";
            }
            return response()->json($log);


        }
    }
    public function payelect(Request $request)
    {
        if (Auth::check()) {
            $user = User::find($request->user()->id);
            $tv = data::where('id', $request->productid)->first();

//            $wallet = wallet::where('username', $user->username)->first();


            if ($user->wallet < $request->amount) {
                $mg = "You Cant Make Purchase Above" . "NGN" . $request->amount . " from your wallet. Your wallet balance is NGN $user->wallet. Please Fund Wallet And Retry or Pay Online Using Our Alternative Payment Methods.";
               return response()->json($mg, Response::HTTP_BAD_REQUEST );
            }
            if ($request->amount < 0) {

                $mg = "error transaction";
                return response()->json($mg, Response::HTTP_BAD_REQUEST );

            }
            if ($request->amount < 500) {

                $mg = "Amount Must be more than 500";
                return response()->json($mg, Response::HTTP_BAD_REQUEST);

            }
            $bo = bo::where('refid', $request->refid)->first();
            if (isset($bo)) {
                $mg = "duplicate transaction";
                return response()->json($mg, Response::HTTP_CONFLICT);

            } else {
                $gt = $user->wallet - $request->amount;


                $user->wallet = $gt;
                $user->save();
                $resellerURL = 'https://renomobilemoney.com/api/';


                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://easyaccess.com.ng/api/payelectricity.php",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => array(
                        'company' =>$tv->code,
                        'metertype' =>01,
                        'meterno' =>$request->number,
                        'amount' =>$request->amount,
                    ),
                    CURLOPT_HTTPHEADER => array(
                        "AuthorizationToken: 904cc8b30fb06707862323030783481b", //replace this with your authorization_token
                        "cache-control: no-cache"
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
//                echo $response;
                $data = json_decode($response, true);
                $success = $data["success"];
                $tran1 = $data["discountAmount"];
                $tran2 = $data["token"];

//                        return $response;
                if ($success == "true") {

                    $bo = bo::create([
                        'username' => $user->username,
                        'plan' => $tv->network,
                        'amount' => $request->amount,
                        'server_res' => $response,
                        'result' => $success,
                        'phone' => $request->number,
                        'refid' => $request->refid,
                        'discountamoun' => $tran1,
                        'token' => $tran2,
                        'fbalance'=>$user->wallet,
                        'balance'=>$gt,
                    ]);


                    $name = $tv->plan;
                    $am = $tv->network."was Successful to";
                    $ph = $request->number."| Token:".$tran2;

                    $receiver = $user->email;
                    $admin = 'info@amazingdata.com.ng';

//                    Mail::to($receiver)->send(new Emailtrans($bo));
//                    Mail::to($admin)->send(new Emailtrans($bo));
                    Alert::success('Success', $am.' '.$ph);
                    return redirect('dashboard');

                }elseif ($success==0){
                    $zo=$user->wallet+$tv->tamount;
                    $user->wallet = $zo;
                    $user->save();

                    $name= $tv->network;
                    $am= "NGN $request->amount Was Refunded To Your Wallet";
                    $ph=", Transaction fail";
                    Alert::error('Fail', $am.' '.$ph);
                    return redirect('dashboard');

                }
            }
        }
    }

}

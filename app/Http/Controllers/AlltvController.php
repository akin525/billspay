<?php

namespace app\Http\Controllers;

use App\Mail\Emailtrans;
use App\Models\bo;
use App\Models\data;
use App\Models\easy;
use App\Models\Messages;
use App\Models\refer;
use App\Models\User;
use App\Models\wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;

class AlltvController
{
    public function listtv(Request $request)
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
            CURLOPT_POSTFIELDS => array('service' => 'tv'),
            CURLOPT_HTTPHEADER => array(
                'Authorization: mcd_key_75rq4][oyfu545eyuriup1q2yue4poxe3jfd'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
//        echo $response;
        $data = json_decode($response, true);
        $plan= $data["data"];
foreach ($plan as $pla) {
    $id = $pla['type'];
    $name = $pla['name'];
    $amount = $pla['amount'];
    $code = $pla['code'];
//return $response;
    $bo = data::create([
        'plan_id' => $code,
        'code' => $code,
        'plan' => $name,
        'network' => $id,
        'amount' => $amount,
        'tamount' => $amount,
        'ramount' => $amount,
        'cat_id' => $code,
    ]);
}
    }

    public function verifytv($value1, $value2)
    {
        $resellerURL='https://app2.mcd.5starcompany.com.ng/api/reseller/';

//        return response()->json($value2);

        $options = easy::where('network', $value2)->first();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://easyaccess.com.ng/api/verifytv.php",
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
                'company' =>$options->code,
                'iucno' => $value1,
            ),
            CURLOPT_HTTPHEADER => array(
                "AuthorizationToken: 61a6704775b3bd32b4499f79f0b623fc", //replace this with your authorization_token
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        $success= $data["success"];
        if($success== "true"){
            $name=$data["message"]["content"]["Customer_Name"];

            $log=$name;
        }else{
            $log= $data["message"];
        }
        return response()->json($log);


    }
//    public function process(Request $request)
//    {
//        if (Auth::check()) {
//            $user = User::find($request->user()->id);
//       g     $tv = data::where('id', $request->id)->first();
//
//            return  view('tvp', compact('user', 'request'));
//
//        }
//        return redirect("login")->withSuccess('You are not allowed to access');
//
//    }
    public function tv(Request $request)
    {
        $tv = easy::where('network', $request->id)->get();
        return  response()->json($tv);

    }

        public function paytv(Request $request)
        {
            if (Auth::check()) {
                $user = User::find($request->user()->id);
                $tv = easy::where('id', $request->productid)->first();

//                $wallet = wallet::where('username', $user->username)->first();
//                return response()->json($tv);
                if ($user->wallet < $tv->tamount) {

                    $mg = "You Cant Make Purchase Above" . "NGN" . $tv->tamount . " from your wallet. Your wallet balance is NGN $user->wallet. Please Fund Wallet And Retry or Pay Online Using Our Alternative Payment Methods.";
                  return response()->json($mg, Response:: HTTP_BAD_REQUEST);

                }
                if ($tv->tamount < 0) {

                    $mg = "error transaction";
                    return response()->json($mg, Response:: HTTP_BAD_REQUEST);

                }
                $bo = bo::where('refid', $request->refid)->first();
                if (isset($bo)) {
                    $mg = "duplicate transaction";
                    return response()->json($mg, Response:: HTTP_CONFLICT);

                } else {
                    $gt = $user->wallet - $tv->tamount;


                    $user->wallet= $gt;
                    $user->save();

                    $resellerURL = 'https://renomobilemoney.com/api/';

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://easyaccess.com.ng/api/paytv.php",
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
                            'iucno' => $request['number'],
                            'package' =>$tv['plan_id'],
                        ),
                        CURLOPT_HTTPHEADER => array(
                            "AuthorizationToken: 61a6704775b3bd32b4499f79f0b623fc", //replace this with your authorization_token
                            "cache-control: no-cache"
                        ),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);

//                    return response()->json($response);
                    $data = json_decode($response, true);
                    $success = $data["success"];

                    if ($success == "true") {

                        $bo = bo::create([
                            'username' => $user->username,
                            'plan' => $tv->network,
                            'amount' => $tv->tamount,
                            'server_res' => $response,
                            'result' => $success,
                            'phone' => $request->number,
                            'refid' => $data['reference_no'],
                            'discountamoun' => 0,
                            'fbalance'=>$user->wallet,
                            'balance'=>$gt,
                        ]);


                        $name = $tv->plan;
                        $am = $tv->network."was Successful to";
                        $ph = $request->number;

                        $receiver = $user->email;
                        $admin = 'info@amazingdata.com.ng';

//                        Mail::to($receiver)->send(new Emailtrans($bo));
//                        Mail::to($admin)->send(new Emailtrans($bo));
                        $mg= $am." ".$ph;
                        return response()->json([
                            'status' => 'success',
                            'message' => $am.' '.$ph,
                            'id'=>$bo['id'],
                        ]);

                    }elseif ($success==0){
                        $zo=$user->wallet+$tv->tamount;
                        $user->wallet = $zo;
                        $user->save();

                        $name= $tv->network;
                        $am= "NGN $request->amount Was Refunded To Your Wallet";
                        $ph=", Transaction fail";

                        return response()->json([
                            'status' => 'fail',
                            'message' => $am.' ' .$ph,
//                            'data' => $responseData // If you want to include additional data
                        ]);

                    }
                }
            }
        }

}

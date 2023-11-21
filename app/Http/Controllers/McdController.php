<?php

namespace app\Http\Controllers;
use App\Mail\Emailfund;
use App\Mail\Emailtrans;
use App\Models\bo;
use App\Models\data;
use App\Models\deposit;
use App\Models\profit;
use App\Models\setting;
use App\Models\wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class McdController

{

    public function bill(Request $request)
    {
        $request->validate([
            'productid' => 'required',
        ]);
        if (Auth::check()) {
            $user = User::find($request->user()->id);
            $wallet = wallet::where('username', $user->username)->first();


            if ($wallet->balance < $request->amount) {
                $mg = "You Cant Make Purchase Above" . "NGN" . $request->amount . " from your wallet. Your wallet balance is NGN $wallet->balance. Please Fund Wallet And Retry or Pay Online Using Our Alternative Payment Methods.";

                return view('bill', compact('user', 'mg'));

            }
            if ($request->amount < 0) {

                $mg = "error transaction";
                return view('bill', compact('user', 'mg'));

            }
            $bo = bo::where('refid', $request->id)->first();
            if (isset($bo)) {
                $mg = "duplicate transaction";
                return view('bill', compact('user', 'mg'));

            } else {
                $user = User::find($request->user()->id);
                $bt = data::where("id", $request->productid)->get();
                $wallet = wallet::where('username', $user->username)->first();


                $gt = $wallet->balance - $request->amount;


                $wallet->balance = $gt;
                $wallet->save();

                foreach ($bt as $fg) {

                    if ($fg->plan == "airtime") {

                        $resellerURL = 'https://integration.mcd.5starcompany.com.ng/api/reseller/';
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $resellerURL . 'pay',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_SSL_VERIFYHOST => 0,
                            CURLOPT_SSL_VERIFYPEER => 0,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => array('service' => 'data', 'coded' => 'MTN1GB', 'phone' => '081602452854'),
                            CURLOPT_HTTPHEADER => array(
                                'Authorization: mcd_key_75rq4][oyfu545eyuriup1q2yue4poxe3jfd'
                            )));

                        $response = curl_exec($curl);

                        curl_close($curl);
                        echo $response;
                    }
                }
            }
        }
    }
}

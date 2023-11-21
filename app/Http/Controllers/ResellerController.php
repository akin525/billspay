<?php

namespace App\Http\Controllers;
use App\Models\bo;
use App\Models\data;
use App\Models\deposit;
use App\Models\setting;
use App\Models\wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RealRashid\SweetAlert\Facades\Alert;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class ResellerController
{
    public function sell(Request $request)
    {
        if (Auth::check()) {
            $user = User::find($request->user()->id);
//            $wallet->account_number = $number;-
//            $wallet->account_name = $account;
//            $wallet->save();
            Alert::info('Upgrade', 'You can request for a website after you upgrade. You will have access to cheaper prices of products too!');
            return view('reseller', compact('user'));



        }
    }
    public function apiaccess(Request $request)
    {
        if (Auth::check()) {
            $user = User::find($request->user()->id);
//            $wallet = wallet::where('username', $user->username)->first();


//            $wallet->account_number = $number;-
//            $wallet->account_name = $account;
//            $wallet->save();

            return view('upgrade', compact('user'));



        }
    }
    public function reseller(Request $request)
    {
        if (Auth::check()) {
            $user = User::find($request->user()->id);
//            $wallet = wallet::where('username', $user->username)->first();

            if ($user->apikey != null){

                $mg = "User Already A Reseller";
                return response()->json($mg, Response::HTTP_BAD_REQUEST);

            }


            if ($user->wallet < $request->amount) {
                $mg = "You Cant Upgrade Your Account" . "NGN" . $request->amount . " from your wallet. Your wallet balance is NGN $user->wallet. Please Fund Wallet And Retry or Pay Online Using Our Alternative Payment Methods.";

                return response()->json($mg, Response::HTTP_BAD_REQUEST);

            }
            if ($request->amount < 0) {

                $mg = "error transaction";
                return response()->json($mg, Response::HTTP_BAD_REQUEST);


            }else {
                $user = User::find($request->user()->id);

                $gt = $user->wallet - $request->amount;


                $user->wallet= $gt;
                $user->save();


                $token = uniqid('AMAZING',true);

                $user->apikey = $token;
                $user->save();
                $message='You have successful upgrade your account! Thanks';
                return response()->json([
                    'status' => 'success',
                    'message' => $message
                ]);
            }


        }
    }
}

<?php
namespace App\Http\Controllers;
use App\Mail\Emailfund;
use App\Mail\Emailtrans;
use App\Models\big;
use App\Models\bo;
use App\Models\data;
use App\Models\deposit;
use App\Models\profit;
use App\Models\easy;
use App\Models\server;
use App\Models\setting;
use App\Models\wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{

    public function bill(Request $request)
    {
        $request->validate([
            'productid' => 'required',
        ]);
//        return response()->json($request);
        if (Auth::check()) {
            $user = User::find($request->user()->id);
//            $wallet = wallet::where('username', $user->username)->first();
            $serve = server::where('status', '1')->first();
            if ($serve->name == 'honorworld') {
                $product = big::where('id', $request->productid)->first();
            } elseif ($serve->name == 'mcd') {
                $product = data::where('id', $request->productid)->first();
            }elseif ($serve->name == 'easyaccess') {
            $product = easy::where('id', $request->productid)->first();
            }


//return $product;
            if ($user->apikey == '') {
                $amount = $product->tamount;
            } elseif ($user != '') {
                $amount = $product->ramount;
            }

            if ($user->wallet < $amount) {
                $mg = "You Cant Make Purchase Above" . "NGN" . $amount . " from your wallet. Your wallet balance is NGN $user->wallet. Please Fund Wallet And Retry or Pay Online Using Our Alternative Payment Methods.";
                return response()->json($mg, Response::HTTP_BAD_REQUEST);

            }
            if ($request->amount < 0) {

                $mg = "error transaction";
                return response()->json($mg, Response::HTTP_BAD_REQUEST);

            }
            $bo = bo::where('refid', $request->refid)->first();
            if (isset($bo)) {
                $mg = "duplicate transaction";
                return response()->json($mg, Response::HTTP_CONFLICT);

            } else {
                $user = User::find($request->user()->id);
//                $bt = data::where("id", $request->productid)->first();
//                $wallet = wallet::where('username', $user->username)->first();


                $gt = $user->wallet - $amount;


                $user->wallet = $gt;
                $user->save();
                $product['number']=$request->number;
                $product['refid']=$request->id;
//return $product;
                $object = json_decode($product);
                $object->number = $request->number;
                $object->id = $request->id;
                $json = json_encode($object);
//return $object;
                $daterserver = new DataserverController();
                $mcd = server::where('status', "1")->first();
//return $mcd;
                if ($mcd->name == "easyaccess") {
                    $response = $daterserver->easyaccess($object);

                    $data = json_decode($response, true);
//                    return response()->json($response);
                    $success = "";
                    if ($data['success'] == 'true') {
                        $success = 1;
                        $ms = $data['message'];

//                    echo $success;

                        $po = $amount - $product->amount;

                        $bo = bo::create([
                            'username' => $user->username,
                            'plan' => $product->network . '|' . $product->plan,
                            'amount' => $product->tamount,
                            'server_res' => $response,
                            'result' => $success,
                            'phone' => $request->number,
                            'refid' => $request->refid,
                            'fbalance'=>$user->wallet,
                            'balance'=>$gt,
                        ]);

                        $profit = profit::create([
                            'username' => $user->username,
                            'plan' => $product->network . '|' . $product->plan,
                            'amount' => $po,
                        ]);

                        $name = $product->plan;
                        $am = "$product->plan  was successful delivered to";
                        $ph = $request->number;
                        $receiver = $user->email;
                        $admin = 'info@amazingdata.com.ng';

                        Mail::to($receiver)->send(new Emailtrans($bo));
                        Mail::to($admin)->send(new Emailtrans($bo));
                        return response()->json([
                            'status' => 'success',
                            'message' => $am.' '.$ph,
                            'id'=>$bo['id'],
                        ]);


                    } elseif ($data['success'] == 'false') {
                        $success = 0;
                        $zo = $user->wallet + $request->amount;
                        $user->wallet = $zo;
                        $user->save();

                        $name = $product->plan;
                        $am = "NGN $request->amount Was Refunded To Your Wallet";
                        $ph = ", Transaction fail";
                        return response()->json([
                            'status' => 'fail',
                            'message' => $am.' ' .$ph,
//                            'data' => $responseData // If you want to include additional data
                        ]);
                    }
                } else if ($mcd->name == "mcd") {
                    $response = $daterserver->mcdbill($object);

                    $data = json_decode($response, true);
//return $data;
                    if ($data['success']==1) {

//                    echo $success;
                        $success = "1";
                        $po = $amount - $product->amount;

                        $bo = bo::create([
                            'username' => $user->username,
                            'plan' => $product->network . '|' . $product->plan,
                            'amount' => $amount,
                            'server_res' => $response,
                            'result' => $success,
                            'fbalance'=>$user->wallet,
                            'balance'=>$gt,
                            'phone' => $request->number,
                            'refid' => $request->refid,
                        ]);

                        $profit = profit::create([
                            'username' => $user->username,
                            'plan' => $product->network . '|' . $product->plan,
                            'amount' => $po,
                        ]);

                        $name = $product->plan;
                        $am = "$product->plan  was successful delivered to";
                        $ph = $request->number;

                        return response()->json([
                            'status' => 'success',
                            'message' => $am.' '.$ph,
                        ]);

                    }elseif ($data['success']==0) {
                        $success = 0;
                        $zo = $user->wallet + $amount;
                        $user->wallet = $zo;
                        $user->save();

                        $name = $product->plan;
                        $am = "NGN $amount Was Refunded To Your Wallet";
                        $ph = ", Transaction fail";
                        return response()->json([
                            'status' => 'fail',
                            'message' => $am.' ' .$ph,
//                            'data' => $responseData // If you want to include additional data
                        ]);
                    }

                }


//return $response;
            }
        }
    }
}





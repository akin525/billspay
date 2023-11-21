<?php

namespace App\Http\Controllers;

use App\Console\encription;
use App\Models\bill_payment;
use App\Models\bo;
use App\Models\data;
use App\Models\easy;
use App\Models\neco;
use App\Models\server;
use App\Models\User;
use App\Models\waec;
use App\Models\wallet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class EducationController
{

public function indexw()
{
    $waec=easy::where('network', 'WAEC')->first();
    $wa=waec::where('username', Auth::user()->username)->get();
return view('waec', compact('waec', 'wa'));

}
public function indexn()
{
    $neco=easy::where('network', 'NECO')->first();
    $ne=neco::where('username', Auth::user()->username)->get();

    return view('neco', compact('neco', 'ne'));

}
public function waec(Request $request)
{
$request->validate([
    'value'=>'required',
    'amount'=>'required',
]);
    $user = User::find($request->user()->id);
    $serve = server::where('status', '1')->first();
    $product=easy::where('network', 'WAEC')->first();

    if ($user->apikey == '') {
        $amount = $product->tamount *$request->value;
    } elseif ($user != '') {
        $amount = $product->ramount *$request->value;
    }

    if ($user->wallet < $amount) {
        $mg = "You Cant Make Purchase Above" . "NGN" . $amount . " from your wallet. Your wallet balance is NGN $user->wallet. Please Fund Wallet And Retry or Pay Online Using Our Alternative Payment Methods.";

       return response()->json($mg, Response::HTTP_BAD_REQUEST );

    }
    if ($request->amount < 0) {

        $mg = "error transaction";
        return response()->json($mg, Response::HTTP_BAD_REQUEST );


    }
    if ($request->amount < 500) {

        $mg = "Your amount must be at least 500";
        return response()->json($mg, Response::HTTP_BAD_REQUEST );


    }
    $bo = bo::where('refid', $request->id)->first();
    if (isset($bo)) {
        $mg="Duplicate Transaction";
        return response()->json($mg, Response::HTTP_CONFLICT);


    } else {

        $user = User::find($request->user()->id);
//                $bt = data::where("id", $request->productid)->first();


        $gt = $user->wallet - $amount;


        $user->wallet = $gt;
        $user->save();
        $bo = bo::create([
            'username' => $user->username,
            'plan' => $product->network ,
            'amount' => $request->amount,
            'server_res' => 'ur fault',
            'result' => 1,
            'phone' => 'no',
            'refid' => $request->id,
            'discountamoun'=>0,
            'fbalance'=>$user->wallet,
            'balance'=>$gt,
        ]);
        $resellerURL = 'https://app2.mcd.5starcompany.com.ng/api/reseller/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://easyaccess.com.ng/api/waec_v2.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array(
                'no_of_pins' =>$request->value,
            ),
            CURLOPT_HTTPHEADER => array(
                "AuthorizationToken: 61a6704775b3bd32b4499f79f0b623fc", //replace this with your authorization_token
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
//        echo $response;
        $data = json_decode($response, true);
//        return $data;

        if ($data['success']=="true") {
            $ref=$data['reference_no'];
            $token=$data['pin'];
//return $token1;

                $insert=waec::create([
                    'username'=>$user->username,
                    'seria'=>'serial_number',
                    'pin'=>$token,
                    'ref'=>$ref,
                ]);

            $mg='Waec Checker Successful Generated, kindly check your pin';
            return response()->json([
                'status' => 'success',
                'message' =>$mg,
                'id'=>$bo['id'],
            ]);

        }elseif($data['success']=="false"){

            Alert::error('Fail', $response);
            return redirect('waec')->with('error', $response);
        }
return $response;
    }

}
public function neco(Request $request)
{
    $request->validate([
        'value'=>'required',
        'amount'=>'required',
    ]);
    $user = User::find($request->user()->id);
    $serve = server::where('status', '1')->first();
    $product=easy::where('network', 'NECO')->first();

    if ($user->apikey == '') {
        $amount = $product->tamount *$request->value;
    } elseif ($user != '') {
        $amount = $product->ramount *$request->value;
    }

    if ($user->wallet < $amount) {
        $mg = "You Cant Make Purchase Above" . "NGN" . $amount . " from your wallet. Your wallet balance is NGN $user->wallet. Please Fund Wallet And Retry or Pay Online Using Our Alternative Payment Methods.";

        return response()->json($mg, Response::HTTP_BAD_REQUEST );


    }
    if ($request->amount < 0) {

        $mg = "error transaction";
        return response()->json($mg, Response::HTTP_BAD_REQUEST );


    }
    $bo = bo::where('refid', $request->id)->first();
    if (isset($bo)) {
        $mg = "duplicate transaction";
        return response()->json($mg, Response::HTTP_CONFLICT );


    } else {

        $user = User::find($request->user()->id);
//                $bt = data::where("id", $request->productid)->first();


        $gt = $user->wallet - $amount;


        $user->wallet = $gt;
        $user->save();
        $bo = bo::create([
            'username' => $user->username,
            'plan' => $product->network ,
            'amount' => $amount,
            'server_res' => 'ur fault',
            'result' => 1,
            'phone' => 'no',
            'refid' => $request->id,
            'discountamoun'=>0,
            'fbalance'=>$user->wallet,
            'balance'=>$gt,
        ]);


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://easyaccess.com.ng/api/neco_v2.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array(
                'no_of_pins' =>$request->value,
            ),
            CURLOPT_HTTPHEADER => array(
                "AuthorizationToken: 61a6704775b3bd32b4499f79f0b623fc", //replace this with your authorization_token
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        if ($data['success']=="true") {
            $ref=$data['reference_no'];
            $token=$data['pin'];
                $insert=neco::create([
                    'username'=>$user->username,
                    'pin'=>$token,
                    'ref'=>$ref,
                ]);

            $mg='Waec Checker Successful Generated, kindly check your pin';
            return response()->json([
                'status' => 'success',
                'message' => $mg,
                'id'=>$bo['id'],
            ]);

        }elseif($data['success']=="false"){

            return response()->json([
                'status' => 'fail',
                'message' => $response,
                'id'=>$bo['id'],
            ]);
        }
        return $response;
    }
}

public function adminneco()
{
    $all=neco::all();
    return view('admin/neco', compact('all'));
}

public function adminwaec()
{
    $all=waec::all();
    return view('admin/waec', compact('all'));
}

public function waecpdfview($request)
{
    $waec=waec::where('id', $request)->first();

    return view('wpin', compact('waec'));
}
public function necopdfview($request)
{
    $waec=neco::where('id', $request)->first();

    return view('npin', compact('waec'));
}

public function waecpdfdownload($request)
{
    $waec=waec::where('id', $request)->first();
    $pdf = PDF::loadView('wpin1', compact('waec'));
    return $pdf->download('waecpin.pdf');
}
public function necopdfdownload($request)
{
    $waec=neco::where('id', $request)->first();
    $pdf = PDF::loadView('npin1', compact('waec'));
    return $pdf->download('necopin.pdf');
}

}


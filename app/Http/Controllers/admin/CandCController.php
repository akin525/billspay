<?php

namespace app\Http\Controllers\admin;

use App\Mail\Emailcharges;
use App\Mail\Emailfund;
use App\Models\charp;
use App\Models\deposit;
use App\Models\setting;
use App\Models\User;
use App\Models\wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use RealRashid\SweetAlert\Facades\Alert;

class CandCController
{
    public function cr()
    {
        if (Auth()->user()->role == "admin") {

            $totalwallet = User::sum('wallet');
            return view('admin/credit', compact('totalwallet'));
        }
        return redirect("admin/login")->with('status', 'You are not allowed to access');


    }
public function credit(Request $request)
{
    $request->validate([
        'username' => 'required',
        'amount' => 'required',
        'refid' => 'required',
    ]);
    if (Auth()->user()->role == "admin") {


        $user = User::where('username', $request->username)->first();
        if (!isset($user)){
            Alert::warning('Admin', 'Username not found');
            return redirect("admin/credit");

        }
        $wallet = User::where('username', $request->username)->first();

        $depo = deposit::where('payment_ref', $request->refid)->first();
        if (isset($depo)) {
            Alert::warning('Admin', 'Duplicate Transaction');

            return redirect("admin/credit");

        } else {
            $gt = $wallet->wallet + $request->amount;
            $deposit = deposit::create([
                'username' => $user->username,
                'payment_ref' => $request->refid,
                'amount' => $request->amount,
                'iwallet' => $wallet->wallet,
                'fwallet' => $gt,
            ]);

            $wallet->wallet = $gt;
            $wallet->save();
//            $admin = 'admin@Amazing-Data.com.ng';
//            $admin2 = 'Amazing-Data18@gmail.com';

            $receiver = $user->email;
//            Mail::to($receiver)->send(new Emailfund($deposit));
//            Mail::to($admin)->send(new Emailfund($deposit));
//            Mail::to($admin2)->send(new Emailfund($deposit));

            $mg= $request->amount . " was credited to " . $user->username . ' successfully';
            Alert::success('Admin', $mg);

            return redirect(route('admin/credit'));

        }
    }
    return redirect("admin/login")->with('status', 'You are not allowed to access');


}
public function sp()
{
    $ch=charp::get();
    return view('admin/charge', compact('ch'));

}
public function charge(Request $request)
{
    $request->validate([
        'username' => 'required',
        'amount' => 'required',
        'refid' => 'required',
    ]);
    if (Auth()->user()->role == "admin") {
        $user = User::where('username', $request->username)->first();
        if (!isset($user)){
            Alert::warning('Admin', 'Username not found');
            return redirect("admin/charge");

        }
        $wallet = User::where('username', $request->username)->first();


        $gt = $wallet->wallet - $request->amount;
        $charp = charp::create([
            'username' => $user->username,
            'payment_ref' => $request->refid,
            'amount' => $request->amount,
            'iwallet' => $wallet->wallet,
            'fwallet' => $gt,
        ]);

        $wallet->wallet = $gt;
        $wallet->save();


//        $admin = 'admin@Amazing-Data.com.ng';
//        $admin2 = 'Amazing-Data18@gmail.com';

        $receiver = $user->email;
//        Mail::to($receiver)->send(new Emailcharges($charp));
//        Mail::to($admin)->send(new Emailcharges($charp));
//        Mail::to($admin2)->send(new Emailcharges($charp));

        $mg= $request->amount . " was charge from " . $user->username . ' wallet successfully';
        Alert::success('Admin', $mg);

        return redirect(route('admin/charge'));

    }
    return redirect("admin/login")->with('status', 'You are not allowed to access');

}

}

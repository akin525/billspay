<?php

namespace App\Http\Controllers;

use App\Models\refer;
use App\Models\wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefersController
{
public function index()
{
    $refer=refer::where('username', Auth::user()->username)->sum('amount');
return view('referwith', compact('refer'));
}
public function with(Request $request)
{
    $request->validate([
        'amount' => 'required',
        'username' => 'required',
    ]);
    $walle=wallet::where('username', $request->username)->first();
    $refer1=refer::where('username', Auth::user()->username)->get();
    $refer=refer::where('username', Auth::user()->username)->sum('amount');

    if ($refer < $request->amount)
    {
        $msg= "You didn't have enough amount in your refer bonus";

        return redirect("referwith")->with('error', $msg);

    }
    if ($request->amount<100)
    {
        $msg= "Your amount must be up to 100";

        return redirect("referwith")->with('error', $msg);
    }
    foreach ($refer1 as $pa) {
        $pa->amount = 0;
        $pa->save();
    }
$tp=$walle->balance + $request->amount;
    $walle->balance=$tp;
    $walle->save();
$msg=$request->amount." Was successfully withdraw";

    return redirect("referwith")->with('status', $msg);


}
}

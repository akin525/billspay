<?php

namespace app\Http\Controllers\admin;

use App\Models\admin;
use App\Models\bo;
use App\Models\charp;
use App\Models\deposit;
use App\Models\Messages;
use App\Models\profit;
use App\Models\refer;
use App\Models\User;
use App\Models\wallet;
use App\Models\webook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class DashboardController
{
public function dashboard(Request $request)
{
    if (Auth()->user()->role=="admin") {
        $user = User::where('role', 'admin')->first();
        $me = Messages::where('status', 1)->first();
        $refer = refer::get();
        $totalrefer = 0;
        foreach ($refer as $de) {
            $totalrefer += $de->amount;

        }
        $count = refer::count();
        $alluser = User::count();
        $profit = profit::get();
        $totalprofit = 0;
        foreach ($profit as $pro) {
            $totalprofit += $pro->amount;
        }
        $totalwallet=User::sum('wallet');

        $deposite = deposit::get();
        $totaldeposite = 0;
        foreach ($deposite as $depo) {
            $totaldeposite += (int)$depo->amount;

        }
    $charge=charp::get();
    $totalcharge= 0;
        foreach ($charge as $ch) {
            $totalcharge += (int)$ch->amount;

        }
        $bil2 = bo::get();
        $bill = 0;
        $lock=0;
        foreach ($bil2 as $bill1) {
            $bill += (int)$bill1->amount;
            $lock += (int)$bill1->discountamoun;

        }
        $resellerURL = 'https://integration.mcd.5starcompany.com.ng/api/reseller/';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://renomobilemoney.com/api/dashboard',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'apikey: RENO-63939122379b03.42488714'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
//        echo $response;

//                                                        return $response;
        $data = json_decode($response, true);
//        $success = $data["success"];
        $tran = $data["wallet"][0]['balance'];
        $pa = $data["me"];
        $today = Carbon::now()->format('Y-m-d');


        $data['bill'] = bo::where([['result', '=', '1'], ['created_at', 'LIKE', $today . '%']])->count();
        $data['deposit'] = deposit::where([['status', '=', '1'], ['created_at', 'LIKE', $today . '%']])->count();
        $data['user'] = User::where([['created_at', 'LIKE', $today . '%']])->count();
        $data['nou'] = User::where([['updated_at', 'LIKE', $today . '%']])->count();
        $data['sum_deposits'] = deposit::where([['created_at', 'LIKE', '%' . $today . '%']])->sum('amount');
        $data['sum_bill'] = bo::where([['created_at', 'LIKE', '%' . $today . '%']])->sum('amount');


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://easyaccess.com.ng/api/wallet_balance.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "AuthorizationToken: 61a6704775b3bd32b4499f79f0b623fc", //replace this with your authorization_token
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
//        return $response;
        $data1 = json_decode($response, true);


        if ($data1['success']=='true'){
            $easy=$data1['balance'];
        }
        return view('admin/dashboard', compact('user',  'data', 'lock', 'totalcharge', 'pa',  'tran', 'alluser', 'totaldeposite', 'totalwallet', 'deposite', 'me', 'bil2', 'bill', 'totalrefer', 'totalprofit',  'count', 'easy'));

    }
    return redirect("admin/login")->with('status', 'You are not allowed to access');

}
public function mcdtran()
{
    if (Auth()->user()->role == "admin") {

        $resellerURL = 'https://integration.mcd.5starcompany.com.ng/api/reseller/';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://renomobilemoney.com/api/dashboard',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'apikey: RENO-63939122379b03.42488714'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
//        echo $response;

//                                                        return $response;
        $data = json_decode($response, true);
        $success = $data["purchase"];
        return view('admin/renotransaction', compact('success' ));

    }
    return redirect("admin/login")->with('status', 'You are not allowed to access');
}
public function webbook()
{
    $book=webook::orderBy('id', 'desc')->paginate(30);
    return view("admin/webbook", compact("book"));
}
public function ref()
{

    $count = refer::where('username', '!=', '')->count();
$refer=refer::where('username', '!=', '')->get();


    return view('admin/refer', compact('count', 'refer' ));


}
}

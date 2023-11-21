<?php

namespace app\Http\Controllers\admin;

use App\Models\bill_payment;
use App\Models\bo;
use App\Models\deposit;
use App\Models\safe_lock;
use App\Models\User;
use App\Models\wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController
{
public function index()
{
    $all=deposit::paginate(50);

    return view('admin/finddeposite', compact('all'));
}
    public function finduser(Request $request)
    {
        $input = $request->all();
        $user_name = $input['user_name'];
        $phoneno = $input['phoneno'];
        $reference = $input['reference'];
        $amount = $input['amount'];
        $date = $input['date'];

        // Instantiates a Query object
        $query = deposit::Where('username', 'LIKE', "%$user_name%")
            ->orWhere('payment_ref', 'LIKE', "%$reference%")
            ->orWhere('date', 'LIKE', "%$date%")
            ->OrderBy('id', 'desc')
            ->limit(5)
            ->get();
        if(!$query){
            return redirect('admin/finddeposite')->with("error", "details does not exist");
        }
        $cquery = deposit::Where('username','LIKE',  "%$user_name%")
            ->orWhere('payment_ref', 'LIKE', "%$reference%")
            ->orWhere('date', 'LIKE', "%$date%")
            ->count();

        return view('admin/finddeposite', ['datas' => $query, 'count' => $cquery, 'result' => true]);
    }
    public function in(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');


        $data =deposit::orderBy('id', 'desc')->paginate(25);
        $tt = deposit::count();
        $ft = deposit::where([['created_at', 'like', Carbon::now()->format('Y-m-d') . '%']])->count();
        $st = deposit::where([['created_at', 'like', Carbon::now()->subDay()->format('Y-m-d') . '%']])->count();
        $rt = deposit::where([['created_at', 'like', Carbon::now()->subDays(2)->format('Y-m-d') . '%']])->count();
        $amount=deposit::sum('amount');
        $am=deposit::where([['created_at', 'LIKE', '%' . $today . '%']])->sum('amount');
        $am1=deposit::where([['created_at', 'like', '%'. Carbon::now()->subDay()->format('y-m-d'). '%']])->sum('amount');
        $am2=deposit::where([['created_at', 'like', '%'. Carbon::now()->subDays(2)->format('y-m-d'). '%']])->sum('amount');


        return view('admin/deposits', ['data' => $data,'amount'=>$amount, 'am'=>$am, 'am1'=>$am1, 'am2'=>$am2,  'tt' => $tt, 'ft' => $ft, 'st' => $st, 'rt' => $rt]);

    }
    public function bill()
    {
        $today = Carbon::now()->format('Y-m-d');


        $data =bo::orderBy('id', 'desc')->paginate(25);
        $tt = bo::count();
        $ft = bo::where([['created_at', 'like', Carbon::now()->format('Y-m-d') . '%']])->count();
        $st = bo::where([['created_at', 'like', Carbon::now()->subDay()->format('Y-m-d') . '%']])->count();
        $rt = bo::where([['created_at', 'l n ike', Carbon::now()->subDays(2)->format('Y-m-d') . '%']])->count();
        $amount=bo::sum('amount');
        $am=bo::where([['created_at', 'LIKE', '%' . $today . '%']])->sum('amount');
        $am1=bo::where([['created_at', 'like', '%'. Carbon::now()->subDay()->format('y-m-d'). '%']])->sum('amount');
        $am2=bo::where([['created_at', 'like', '%'. Carbon::now()->subDays(2)->format('y-m-d'). '%']])->sum('amount');

        return view('admin/bills', ['data' => $data,'amount'=>$amount, 'am'=>$am, 'am1'=>$am1, 'am2'=>$am2,  'tt' => $tt, 'ft' => $ft, 'st' => $st, 'rt' => $rt]);

    }

    public function getTransactions()
    {
        $transactions = deposit::selectRaw('DATE(date) as date, SUM(amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $dates = $transactions->pluck('date')->toArray();
        $amounts = $transactions->pluck('total_amount')->toArray();

        return response()->json([
            'dates' => $dates,
            'amounts' => $amounts,
        ]);
    }
    public function getTransactions1()
    {
        $transactions = bo::selectRaw('DATE(date) as date, SUM(amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $dates = $transactions->pluck('date')->toArray();
        $amounts = $transactions->pluck('total_amount')->toArray();

        return response()->json([
            'dates' => $dates,
            'amounts' => $amounts,
        ]);
    }
    public function showPieChart()
    {
        $today = Carbon::now()->format('Y-m-d');

        $totalUsers = User::count();
        $activeUsers =  User::where([['created_at', 'LIKE', $today . '%']])->count();

        return response()->json([
            'tusers' => $totalUsers,
            'nusers' => $activeUsers,
        ]);
    }
    public function lockPieChart()
    {
        $transactions = User::selectRaw('DATE(created_at) as date, SUM(wallet) as total_amount')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $dates = $transactions->pluck('date')->toArray();
        $amounts = $transactions->pluck('total_amount')->toArray();

        return response()->json([
            'dates' => $dates,
            'amounts' => $amounts,
        ]);
    }

}

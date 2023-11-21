<?php

namespace App\Http\Controllers;

use App\Models\bill_payment;
use App\Models\bo;
use App\Models\deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Transaction1Controller extends Controller
{
    public function getTransactions()
    {
        $transactions = deposit::where('username', Auth::user()->username)->selectRaw('DATE(date) as date, SUM(amount) as total_amount')
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
        $transactions = bo::where('username', Auth::user()->username)->selectRaw('DATE(date) as date, SUM(amount) as total_amount')
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

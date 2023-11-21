<?php

namespace App\Http\Controllers;

use App\Models\bo;
use App\Models\deposit;
use http\Env\Response;
use Illuminate\Http\Request;

class SelfserviceController
{

    function airtimedata(Request $request)
    {
        $check=bo::where('refid', $request->refid)->first();

        if ($check) {
            $message = "Transaction was successful delivered";

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        }else{
            $message="Transaction not found";
            return response()->json([
                'status' => 'fail',
                'message' => $message
            ]);
        }

    }
    function deposit(Request $request)
    {
        $check=deposit::where('payment_ref', $request->refid)->first();
        if ($check) {
            $message = "Payment Funded";
            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        }else{
            $message="Payment Not Found";
            return response()->json([
                'status' => 'fail',
                'message' => $message
            ]);

        }
    }
}

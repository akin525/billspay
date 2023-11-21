<?php

namespace App\Http\Controllers;

use App\Models\bo;
use Barryvdh\DomPDF\Facade\Pdf;


class PdfController
{
    function viewpdf($request)
    {
        $bo=bo::where('id', $request)->first();
        return view('receipt', compact('bo'));
    }

    function dopdf($request)
    {
        $bo=bo::where('id', $request)->first();
        $pdf = PDF::loadView('receipt1', compact('bo'));
        return $pdf->download('receipt.pdf');
    }

}

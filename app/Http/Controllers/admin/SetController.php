<?php
namespace app\Http\Controllers\admin;


use App\Models\airtimecon;
use App\Models\big;
use App\Models\charp;
use App\Models\data;
use App\Models\setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetController
{
    public function index()
    {
        $charge=setting::first();

        return view("admin/setcharge", compact("charge"));
    }

    public function charge(Request $request)
    {
        $request->validate([
           'body'=>'required',
        ]);

        $charge=setting::where('id',1)->first();

        $charge->charges=$request->body;
        $charge->save();
        return redirect('admin/setcharge')->with('status', 'Charges Updated');

    }
    public function index1()
    {
        $min=setting::first();

        return view("admin/setmin", compact("min"));
    }
    public  function min(Request $request)
    {
        $request->validate([
            'body'=>'required',
        ]);
        $min=setting::where('id',1)->first();
        $min->len=$request->body;
        $min->save();

        return redirect('admin/setmin')->with('status', 'Minimum Fund Updated');
    }
}

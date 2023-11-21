<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\web;
use App\Models\webook;

class Easy extends Controller
{
    public function webook()
    {
        $web=web::orderBy('id', 'desc')->get();
        return view('admin/look', compact('web'));
    }

    public function easybalance()
    {

    }


}

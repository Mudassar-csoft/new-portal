<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(): View
    {
        return view('voucher');
    }
}

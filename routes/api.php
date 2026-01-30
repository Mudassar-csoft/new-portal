<?php

use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/campus/count/{abbr}', function (string $abbr) {
    $abbr = strtoupper(preg_replace('/[^A-Z]/i', '', $abbr));
    $count = Campus::where('city_abbr', $abbr)->count();
    return response()->json(['count' => $count]);
});

<?php
use \App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/{app?}',[HomeController::class,'index'])->where('app','.*');


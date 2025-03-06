<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AttributeController;

Route::get('/', function () {
    return view('welcome');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PointsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('table_edit');
});

// Would define as resource endpoint generally, but we do some fancy stuff by conglomerating where requests are routed
Route::get('/points/getRelatedPoints', 'App\Http\Controllers\PointsController@getRelatedPoints');
Route::get('/points/{id?}', 'App\Http\Controllers\PointsController@index');
Route::post('/points/{id?}', 'App\Http\Controllers\PointsController@create');
Route::delete('/points/{id?}', 'App\Http\Controllers\PointsController@destroy');
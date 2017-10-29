<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('login', 'Api\UserController@login');
Route::post('register', 'Api\UserController@register');

Route::group(['middleware' => 'auth:api'], function(){
    Route::resource('unit', 'Api\UnitController');
    Route::resource('type', 'Api\TypeController');
    Route::resource('product', 'Api\ProductController');
    Route::resource('supplier', 'Api\SupplierController');
    Route::resource('customer', 'Api\CustomerController');
    Route::resource('warehouse', 'Api\WarehouseController');
});
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

    Route::get('product/{key}', 'Api\ProductController@getProductByKey');
    Route::resource('product', 'Api\ProductController');

    Route::resource('supplier', 'Api\SupplierController');

    Route::get('customer/{key}', 'Api\CustomerController@getCustomerByKey');
    Route::resource('customer', 'Api\CustomerController');

    Route::post('warehouse/transfer', 'Api\WarehouseController@transfer');
    Route::resource('warehouse', 'Api\WarehouseController');
    Route::resource('purchase', 'Api\PurchaseController');
    Route::resource('product', 'Api\ProductController');
    Route::resource('order', 'Api\OrderController');

    // 统计分析
    Route::post('analysis/supplier/{id}', 'Api\AnalysisController@supplierAna');
});
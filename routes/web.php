<?php

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

// 前端 react-router 使用 react, browse-router 返回 index.html
Route::get('{reactRoutes}', function () {
    return view('home'); // start view
})->where('reactRoutes', '^((?!api).)*$'); // except 'api' word

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::group([],function($router){
    foreach(glob(base_path('routes//app//Api').'/*.php') as $file){
        require ($file);
        app()->make('Routes\\App\\Api\\'.basename($file,'.php'))->map($router);
    }
});
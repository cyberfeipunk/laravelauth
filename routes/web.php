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

Route::get('/', function () {
    return view('welcome');
});


Route::group([],function($router){
   foreach(glob(base_path('routes//app//site').'/*.php') as $file){
       require($file);
       app()->make('Routes\\App\\Site\\'.basename($file,'.php'))->map($router);
   }
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

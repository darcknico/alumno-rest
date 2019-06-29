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

Route::get('/img/{path}', 'NotificacionController@images')->where('path', '.*');

Route::get('/artisan', function () {
	//dd(public_path('something'));

	$cmd = request()->query('cmd',null);
	if(!empty($cmd)){
    	return Artisan::call($cmd);
	}
	//return Artisan::call('passport:install');
});
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

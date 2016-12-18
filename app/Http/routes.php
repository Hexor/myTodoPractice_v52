<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::get('/', function(){
    if (Auth::guest()) {
        return redirect('/guest/tasks');
    } else {
        return redirect('/user/tasks');
    }
});
//Route::get('/', 'HomeController@index');


Route::get('guest/tasks', function () {
    return view('guest_tasks');
});

Route::auth();

Route::group(['middleware' => 'auth'], function () {
    Route::get('user/tasks', function () {
        return view('tasks',['user_id' => \Illuminate\Support\Facades\Auth::user()->id]);
    });

    Route::resource('api/tasks','TaskController');
});


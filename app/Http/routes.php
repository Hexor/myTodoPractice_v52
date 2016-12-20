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

// 使用了 LaravelLogViewer 插件，使用之后可以在网页上查看生成的 laravel.log 日志文件
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::auth();

Route::get('/', function(){

    // 如果用户未登录，就让用户使用 guest 页面的本地缓存的任务列表功能
    if (Auth::guest()) {
        return redirect('/guest/tasks');
    } else {
        return redirect('/user/tasks');
    }
});


Route::get('guest/tasks', function () {
    return view('guest_tasks');
});


Route::group(['middleware' => 'auth'], function () {
    // 想要操作服务器端的数据，就需要先登录，引入 auth 中间件

    Route::get('user/tasks', function () {
        return view('tasks',['user_id' => \Illuminate\Support\Facades\Auth::user()->id]);
    });

    Route::resource('api/tasks','TaskController');
});


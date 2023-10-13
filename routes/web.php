<?php

use Illuminate\Support\Facades\Http;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return [
        "Name" => "Asuransi Jiwa  Reliance Indonesia Unit Syariah",
        "URL" => "http://api.ajrius.id",
    ];
});


$router->get('phpinfo', function () use ($router) {
    phpinfo();
});
Route::post('auth-login', 'AuthController@login'); 
Route::post('insapi/v1/insurance/covernote','PanController@store');
Route::post('insapi/v1/insurance/kafalah','PanController@store');
<?php

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
    return $router->app->version();
});

$router->group(['prefix'=>'api/v1'], function() use ($router)
{
    // Customer Route
    $router->post('customer', 'CustomerController@insert');
    $router->put('customer/{id}', 'CustomerController@update');
    $router->delete('customer/{id}', 'CustomerController@delete');
    $router->get('customer', 'CustomerController@getAll');
    $router->get('customer/{id}', 'CustomerController@getById');

    // Order Route
    $router->post('order', 'OrderController@insert');
    $router->put('order/{id}', 'OrderController@update');
    $router->delete('order/{id}', 'OrderController@delete');
    $router->get('order', 'OrderController@getAll');
    $router->get('order/{id}', 'OrderController@getById');

    // Product Route
    $router->post('product', 'ProductController@insert');
    $router->put('product/{id}', 'ProductController@update');
    $router->delete('product/{id}', 'ProductController@delete');
    $router->get('product', 'ProductController@getAll');
    $router->get('product/{id}', 'ProductController@getById');

    // Payment Route
    $router->post('payment', 'PaymentController@create');
    $router->put('payment/{id}', 'PaymentController@update');
    $router->delete('payment/{id}', 'PaymentController@delete');
    $router->get('payment', 'PaymentController@getAll');
    $router->get('payment/{id}', 'PaymentController@getById');
    $router->post('payment/midtrans/push', 'PaymentController@pushMidtrans');


});
<?php

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


// Return the analytics script with replaced URLs
$router->get(env('GA_SCRIPT_NAME'), 'TagController@getAnalyticsJs');
$router->get(env('GA_TAG_NAME'), 'TagController@getGtagJs');


// Proxy collect requests and append uip to the query
$router->get(env('GA_COLLECT_ENDPOINT'), 'ProxyController@proxy');
$router->post(env('GA_COLLECT_ENDPOINT'), 'ProxyController@proxy');

// Also allow r/ and j/ endpoints that are used by gtag.js
$router->get('r' . env('GA_COLLECT_ENDPOINT'), 'ProxyController@proxy');
$router->post('r' . env('GA_COLLECT_ENDPOINT'), 'ProxyController@proxy');
$router->get('j' . env('GA_COLLECT_ENDPOINT'), 'ProxyController@proxy');
$router->post('j' . env('GA_COLLECT_ENDPOINT'), 'ProxyController@proxy');

// Allows testing both collection scripts
$router->group(['prefix' => 'test'], function() use ($router){
    $router->get('gtag', function() {
        return view('test.gtag');
    });

    $router->get('analytics', function() {
        return view('test.analytics');
    });
});

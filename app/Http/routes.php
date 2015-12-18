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

Route::get('/', 'Controller@index');
Route::post('build-private', 'Controller@buildPrivate');
Route::post('build-public', 'Controller@buildPublic');

Route::group(['namespace' => 'Api', 'prefix' => '/api'], function() {
    Route::get('repository/{repositoryId?}', 'RepositoryController@get');
    Route::post('repository', 'RepositoryController@add');
    Route::put('repository/{repositoryId}', 'RepositoryController@update');
    Route::delete('repository/{repositoryId}', 'RepositoryController@delete');

    Route::get('package/{packageId?}', 'PackageController@get');
    Route::post('package', 'PackageController@add');
    Route::put('package/{packageId}', 'PackageController@update');
    Route::delete('package/{packageId}', 'PackageController@delete');
});

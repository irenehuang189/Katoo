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

Route::get('/movie/popular/{pageNum}', 'MovieController@getPopular');
Route::get('/movie/upcoming/{pageNum}', 'MovieController@getUpcoming');
Route::get('/movie/{movieId}', 'MovieController@getDetails');
Route::get('/movie/{movieId}/reviews', 'MovieController@getReviews');

Route::get('/restaurant/{id}', 'RestaurantController@get');
Route::get('/restaurant/{id}/rating', 'RestaurantController@getRating');
Route::get('/restaurant/{id}/menu', 'RestaurantController@getMenu');
Route::get('/restaurant/{id}/reviews', 'RestaurantController@getReviews');
Route::get('/restaurants/nearby', 'RestaurantController@getNearby');
Route::get('/restaurants/location', 'RestaurantController@getByLocationQuery');
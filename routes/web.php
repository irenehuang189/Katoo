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

Route::post('/', 'LINEController@index');
Route::get('test', 'LINEController@test');

Route::get('/movies/all', 'DatabaseController@getAllMovies');

Route::get('/movie/popular/{pageNum}', 'MovieController@getPopular');
Route::get('/movie/upcoming/{pageNum}', 'MovieController@getUpcoming');
Route::get('/movie/{imdbId}/{dbId}/{state}', 'MovieController@getDetailsById');
Route::get('/movie/{movieId}/reviews', 'MovieController@getReviews');

Route::get('/restaurant/{id}/reviews', 'RestaurantController@getReviews');

Route::post('/database', 'DatabaseController@resetDatabase');
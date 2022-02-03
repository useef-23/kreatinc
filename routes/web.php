<?php

use Illuminate\Support\Facades\Route;

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
})->name('welcome');

Auth::routes();




Route::group(['prefix' => 'auth/facebook'], function () {
    Route::get('/', [\App\Http\Controllers\SocialController::class, 'redirectToProvider'])->name('connectFacebook');
    Route::get('/callback', [\App\Http\Controllers\SocialController::class, 'handleProviderCallback']);
    
    
});

Route::get('/home', 'SocialController@goToHomePage')->name('home');
Route::get('/getPostesByPage/{id}-{token}','SocialController@goToPostIndex')->name('Postes');
Route::post('/savePost',"SocialController@savePost")->name('savepost');


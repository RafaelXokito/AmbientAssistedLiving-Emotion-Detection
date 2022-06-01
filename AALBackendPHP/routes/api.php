<?php

use App\Http\Controllers\api\AdministratorController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ClientController;
use App\Http\Controllers\api\EmotionController;
use App\Http\Controllers\api\FrameController;
use App\Http\Controllers\api\IterationController;
use App\Http\Controllers\api\StatisticCoontroller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' =>  'auth:api'], function() {
    Route::get('/emotions', [EmotionController::class, 'index']);
    Route::get('/emotions/group/{group}', [EmotionController::class, 'showEmotionsByGroup']);

    Route::get('/frames/clients/{client}/graphData', [FrameController::class, 'showGraphData']);
    Route::get('/frames/graphData', [FrameController::class, 'showClassificationGraphData']);

    Route::get('/statistics', [StatisticCoontroller::class, 'index']);
});

// Admin restrict
Route::group(['middleware' =>  'auth:api', 'admin'], function() {
    Route::resources([
        'administrators' => AdministratorController::class,
        'clients' => ClientController::class,
        'emotions' => EmotionController::class,
    ]);
});

// Client restrict
Route::group(['middleware' =>  'auth:api', 'client'], function() {
    Route::resources([
        'iterations' => IterationController::class,
        'frames' => FrameController::class,
    ]);

    Route::get('/frames/iteration/{iteration}', [FrameController::class, 'showFramesByIteration']);
    Route::patch('/frames/{frame}/classify', [FrameController::class, 'classifyFrame']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::patch('/activateClient', [AuthController::class, 'activateClient']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'userProfile']);
});



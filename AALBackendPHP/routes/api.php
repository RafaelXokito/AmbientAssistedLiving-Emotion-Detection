<?php

use App\Http\Controllers\api\StatisticController;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\FrameController;
use App\Http\Controllers\api\ClientController;
use App\Http\Controllers\api\EmotionController;
use App\Http\Controllers\api\IterationController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\AdministratorController;
use App\Http\Controllers\api\EmotionsNotificationController;
use App\Http\Controllers\api\LogController;

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
    Route::get('/emotions/groups/{group}', [EmotionController::class, 'showEmotionsByGroup']);

    Route::get('/frames/clients/{client}/graphData', [FrameController::class, 'showGraphData']);
    Route::get('/frames/graphData', [FrameController::class, 'showClassificationGraphData']);

    Route::get('/iterations/graphData', [IterationController::class, 'showGraphData']);

    Route::get('/statistics', [StatisticController::class, 'index']);

    Route::resources([
        'notifications' => NotificationController::class
    ]);
});

// Admin restrict
Route::group(['middleware' =>  'auth:api', 'admin'], function() {
    Route::resources([
        'administrators' => AdministratorController::class,
        'clients' => ClientController::class,
        'emotions' => EmotionController::class,
        'logs' => LogController::class
    ]);
});

// Client restrict
Route::group(['middleware' =>  'auth:api', 'client'], function() {
    Route::resources([
        'iterations' => IterationController::class,
        'frames' => FrameController::class,
        'emotionsNotification' => EmotionsNotificationController::class,
    ]);

    Route::get('/frames/iteration/{iteration}', [FrameController::class, 'showFramesByIteration']);
    Route::patch('/frames/{frame}/classify', [FrameController::class, 'classifyFrame']);
    Route::get('/frames/download/{frame}', [FrameController::class, 'showFoto']);
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
    Route::put('/update', [AuthController::class, 'update']);
});





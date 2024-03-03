<?php

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\LogController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\FrameController;
use App\Http\Controllers\api\ClientController;
use App\Http\Controllers\api\SpeechController;
use App\Http\Controllers\api\ContentController;
use App\Http\Controllers\api\EmotionController;
use App\Http\Controllers\api\IterationController;
use App\Http\Controllers\api\StatisticController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\AdministratorController;
use App\Http\Controllers\api\EmotionExpressionController;
use App\Http\Controllers\api\MultiModalEmotionController;
use App\Models\MultiModalEmotion;
use App\Http\Controllers\api\EmotionsNotificationController;
use App\Http\Controllers\api\GeriatricQuestionnaireController;
use App\Http\Controllers\api\OxfordHappinessQuestionnaireController;
use App\Http\Controllers\api\MessageController;
use App\Http\Controllers\api\QuestionnaireTypeController;
use App\Http\Controllers\api\EmotionRegulationMechanismController;
use App\Http\Controllers\api\RegulationMechanismController;

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

// RASA uses this endpoint
Route::get('/questionnaires/{questionnaire}', [QuestionnaireTypeController::class, 'show']);

Route::group(['middleware' =>  'auth:api'], function() {
    Route::get('/emotions', [EmotionController::class, 'index']);
    Route::get('/emotions/groups/{group}', [EmotionController::class, 'showEmotionsByGroup']);
    Route::get('/emotions/groups', [EmotionController::class, 'showGroups']);

    Route::get('/frames/clients/{client}/graphData', [FrameController::class, 'showGraphData']);

    Route::get('/frames/graphData', [FrameController::class, 'showClassificationGraphData']);
    Route::get('/contents/graphData', [ContentController::class, 'showClassificationGraphData']);

    Route::get('/frames/last', [FrameController::class, 'last']); // Isto devia de esta
    Route::get('/multiModalEmotions/last', [MultiModalEmotionController::class, 'last']); // Isto devia de estar apenas para os clientsr apenas para os clients

    Route::get('/iterations/graphData', [IterationController::class, 'showGraphData']);

    Route::get('/statistics', [StatisticController::class, 'index']);

    Route::get('/notifications/download/{notification}', [NotificationController::class, 'showFoto']);
    Route::get('/notifications/top', [NotificationController::class, 'top']);

    Route::get('/speeches/last', [SpeechController::class, 'last']);
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
        'emotionExpressions' => EmotionExpressionController::class,
        'multiModalEmotions' => MultiModalEmotionController::class,
        'speeches' => SpeechController::class,
        'GeriatricQuestionnaires' => GeriatricQuestionnaireController::class,
        'OxfordHappinessQuestionnaires' => OxfordHappinessQuestionnaireController::class,
        'messages' => MessageController::class,
        'emotionRegulationMechanisms' => EmotionRegulationMechanismController::class,
        'regulationMechanisms' => RegulationMechanismController::class,
    ]);

    Route::get('/me', [ClientController::class, 'getMe']);

    Route::get('/emotionExpressions/emotion/{emotion}', [EmotionExpressionController::class, 'showByEmotion']);
    Route::get('/frames/iteration/{iteration}', [FrameController::class, 'showFramesByIteration']);
    Route::patch('/frames/{frame}/classify', [FrameController::class, 'classifyFrame']);
    Route::get('/frames/download/{frame}', [FrameController::class, 'showFoto']);


    Route::get('/speeches/iteration/{iteration}', [SpeechController::class, 'showSpeechesByIteration']);
    Route::get('/speeches/{speech}/predictions', [SpeechController::class, 'showSpeechClassification']);
    Route::patch('/speeches/{speech}/classify', [SpeechController::class, 'classifySpeech']);

    Route::get('/questionnairesTypes', [QuestionnaireTypeController::class, 'index']);  
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
    Route::patch('/notifiable', [AuthController::class, 'toggleNotifiable']);
    Route::put('/update', [AuthController::class, 'update']);
});





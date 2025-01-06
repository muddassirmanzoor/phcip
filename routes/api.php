<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(\App\Http\Controllers\api\TraineeController::class)->prefix('phcip')->group(function() {
    Route::get('questionsList','questionsList');
    Route::get('answerList','answerList');
    Route::get('districtList','districtList');
    Route::get('tehsilList','tehsilList');
    Route::get('schoolDetail','schoolDetail');
    Route::post('participantInfoAnswers','participantInfoAnswers');

});

Route::controller(\App\Http\Controllers\api\LoginController::class)->prefix('phcip')->group(function() {
    Route::post('mt/login','mtLogin');
});

Route::controller(\App\Http\Controllers\api\MTController::class)->middleware(['auth:sanctum'])->prefix('phcip/mt/')->group(function () {
    Route::get('questionsList','questionsList');
    Route::get('observationQuestionsList','observationQuestionsList');
    Route::post('mtInfoAnswers','mtInfoAnswers');
    Route::post('qaInfoAnswers','qaInfoAnswers');


});

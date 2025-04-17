<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\MatchSimulationController;

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

Route::get('/teams', [TeamController::class, 'index']);
Route::post('/teams', [TeamController::class, 'store']);
Route::patch('/teams/{id}', [TeamController::class, 'update']);
Route::delete('/teams/{id}', [TeamController::class, 'destroy']);

Route::get('/matches', [MatchController::class, 'index']);
Route::get('/matches/last-week', [MatchController::class, 'lastWeekMatches']);
Route::post('/matches', [MatchController::class, 'store']);
Route::patch('/matches/{matchId}', [MatchController::class, 'update']);

Route::post('/match-simulation/simulate-week', [MatchSimulationController::class, 'simulateNextWeek']);
Route::post('/match-simulation/simulate-all', [MatchSimulationController::class, 'simulateAllWeeks']);

Route::post('/league/reset', [LeagueController::class, 'resetLeague']);
Route::get('/league/predictions', [LeagueController::class, 'predictions']);


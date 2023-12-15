<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveStatusController;
use App\Http\Controllers\DashbordController;
use App\Http\Controllers\DashboardController_1;
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


Route::post('/leave-data', [LeaveStatusController::class, 'Leaves_alldata']);


Route::post('/dasboard-data', [DashbordController::class, 'Dashbord_alldata']);

Route::post('/dasboard-data1', [DashboardController_1::class, 'Dashbord_alldata1']);



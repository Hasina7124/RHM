<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\test;

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

// User
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('postLogin');
Route::get('login', function(){return view('login');})->name('login');
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/password-reset', [AuthController::class, 'resetPassword']);
Route::get('/password-reset', function () {return view('password_reset');});


// Route::post('logout', [AuthController::class, 'logout']);

Route::group(['middleware' => 'auth:api'], function () {
    // User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'getUser']);

    // Project
    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::put('projects/modify/{id}', [ProjectController::class, 'updateProject']);
    Route::delete('projects/delete/{id}', [ProjectController::class, 'destroyProject']);


    // Tickets
    Route::get('tickets/{project_id}', [TicketController::class, 'index']);
    Route::post('tickets/store/{project_id}', [TicketController::class, 'store']);
    Route::put('tickets/update/{project_id}', [TicketController::class, 'updateTicket']);
    Route::delete('tickets/delete/{project_id}', [TicketController::class, 'destroyTicket']);

});

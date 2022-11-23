<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/** --------------Public routes-------------- */
Route::get('user/{id}',[UserController::class,'show']);
Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);



/** --------------Protected routes-------------- */

Route::group(['middleware' => ['auth:sanctum']],function (){

    /** -----User routes----- */
    Route::get('profile',[UserController::class,'index']);
    Route::patch('profile',[UserController::class,'update']);
    Route::delete('profile',[UserController::class,'destroy']);
    Route::post('logout',[AuthController::class,'logout']);

    /** -----GOALS routes----- */
    Route::get('goals',[GoalController::class,'index']);
    Route::get('goals/{id}',[GoalController::class,'show']);
    Route::post('goals/add',[GoalController::class,'store']);
    Route::patch('goals/{id}',[GoalController::class,'update']);
    Route::delete('goals/{id}',[GoalController::class,'destroy']);

    /** -----TASKS routes----- */
    Route::get('tasks',[TaskController::class,'index']);
    Route::get('tasks/{id}',[TaskController::class,'show']);
    Route::post('tasks/add',[TaskController::class,'store']);
    Route::patch('tasks/{id}',[TaskController::class,'update']);
    Route::delete('tasks/{id}',[TaskController::class,'destroy']);

});

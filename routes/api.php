<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;

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

Route::get('student', [StudentController::class, 'index']);

Route::post('student', [StudentController::class, 'upload']);

Route::put('student/edit/{id}', [StudentController::class, 'edit']);

Route::delete('student/edit/{id}', [StudentController::class, 'delete']);


Route::group(
    ['prefix' => "/users"],
    function () {
//        Route::get('/', function () {
//            dd(111);
//        })->name('api.user.list');
        Route::get('/', [UserController::class, 'list'])->name('api.user.list');
    }
);


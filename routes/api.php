<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductionLineController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentToolController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuditItemController;
use App\Http\Controllers\AuditPhotoController;
use App\Http\Controllers\AuditReviewController;
use App\Http\Controllers\AuthController;


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

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String()
    ]);
});

Route::get('/user', function (Request $request) {
    $user = "Soy Navarrete";
    return $response = [
        'status' => 'success',
        'data' => $user
    ];
});

Route::prefix('v1')->group(function () {

    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);
    Route::post('auth/verify-code', [AuthController::class, 'verifyCode']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',     [AuthController::class, 'me']);


        //PRODUCTION LINES
        Route::get('/lines', [ProductionLineController::class, 'index']);
        Route::post('/lines', [ProductionLineController::class, 'store']);
        Route::get('/lines/{line}', [ProductionLineController::class, 'show']);
        Route::put('/lines/{line}', [ProductionLineController::class, 'update']);
        Route::delete('/lines/{line}', [ProductionLineController::class, 'destroy']);

        //TOOLS
        Route::get('/tools', [ToolController::class, 'index']);
        Route::post('/tools', [ToolController::class, 'store']);
        Route::get('/tools/{tool}', [ToolController::class, 'show']);
        Route::put('/tools/{tool}', [ToolController::class, 'update']);
        Route::delete('/tools/{tool}', [ToolController::class, 'destroy']);

        //EMPLOYEES
        // Route::get('/employees', [EmployeeController::class, 'index']);
        // Route::post('/employees', [EmployeeController::class, 'store']);
        // Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
        // Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
        // Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

        // Route::get('/employees/validate', [EmployeeController::class, 'validateNumber']);

        //ASSIGNMENTS
        Route::get('assignments',              [AssignmentController::class, 'index']);
        Route::post('assignments',             [AssignmentController::class, 'store']);
        Route::get('assignments/{assignment}', [AssignmentController::class, 'show']);
        Route::put('assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::patch('assignments/{assignment}/status', [AssignmentController::class, 'updateStatus']);
        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy']);

        // ASSIGNMENTS TOOLS
        Route::get('assignments/{assignment}/tools',            [AssignmentToolController::class, 'index']);
        Route::post('assignments/{assignment}/tools',           [AssignmentToolController::class, 'attach']);
        Route::delete('assignments/{assignment}/tools/{tool}',  [AssignmentToolController::class, 'detach']);

        //AUDITS
        Route::get('audits',              [AuditController::class, 'index']);
        Route::post('audits',             [AuditController::class, 'store']);
        Route::get('audits/{audit}',      [AuditController::class, 'show']);
        Route::put('audits/{audit}',      [AuditController::class, 'update']);
        Route::post('audits/{audit}/submit', [AuditController::class, 'submit']);

        //AUDIT ITEMS
        Route::get('audits/{audit}/items',  [AuditItemController::class, 'index']);
        Route::post('audits/{audit}/items', [AuditItemController::class, 'store']);
        Route::put('audit-items/{item}',    [AuditItemController::class, 'update']);

        //AUDIT PHOTOS
        Route::get('audit-items/{item}/photos',    [AuditPhotoController::class, 'index']);
        Route::post('audit-items/{item}/photos',   [AuditPhotoController::class, 'store']);
        Route::delete('audit-photos/{photo}',      [AuditPhotoController::class, 'destroy']);

        // REVIEWS
        Route::get('audits/{audit}/reviews',  [AuditReviewController::class, 'index']);
        Route::post('audits/{audit}/reviews', [AuditReviewController::class, 'store']);

        // REOOPEN IF NEEDED CHANGES
        Route::post('audits/{audit}/reopen',  [AuditReviewController::class, 'reopen']);
    });
});

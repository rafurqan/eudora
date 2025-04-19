<?php

use App\Http\Controllers\API\Master\ReligionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Authentication\AuthController;
use App\Http\Controllers\API\Log\LogController;
use App\Http\Controllers\API\Master\{
    DocumentTypeController,
    EducationLevelController,
    GuardianRelationshipController,
    NationalityController,
    PermissionController,
    RolePermissionController,
    SpecialNeedController,
    TransportationModeController
};
use App\Http\Controllers\API\Student\{
    StudentClassController,
    StudentDocumentController
};
use App\Http\Controllers\API\Teacher\TeacherController;
use App\Http\Middleware\CheckPermission;

Route::prefix('v1')->group(function () {

    // AUTH ROUTES
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    });

    // LOG
    Route::get('logs', [LogController::class, 'index']);

    // PROTECTED ROUTES
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // MASTER DATA
        Route::prefix('master')->group(function () {

            Route::apiResource('education-levels', EducationLevelController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('teachers', TeacherController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('document-types', DocumentTypeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('guardian-relationships', GuardianRelationshipController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('nationalities', NationalityController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('special-needs', SpecialNeedController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('transportation-modes', TransportationModeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('student-classes', StudentClassController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('religions', ReligionController::class)->only(['index', 'store', 'update', 'destroy']);

            Route::middleware(CheckPermission::class . ':List Permission')->get('permissions', [PermissionController::class, 'all']);
            Route::middleware(CheckPermission::class . ':Add Permission')->post('permissions', [PermissionController::class, 'create']);
            Route::middleware(CheckPermission::class . ':Remove Permission')->delete('permissions/{id}', [PermissionController::class, 'destroy']);
            Route::middleware(CheckPermission::class . ':Update Permission')->put('permissions/{id}', [PermissionController::class, 'update']);

            Route::middleware(CheckPermission::class . ':List Role Permission')->get('role-permissions', [RolePermissionController::class, 'all']);
            Route::middleware(CheckPermission::class . ':Add Role Permission')->post('role-permissions', [RolePermissionController::class, 'create']);
            Route::middleware(CheckPermission::class . ':Remove Role Permission')->delete('role-permissions/{id}', [RolePermissionController::class, 'destroy']);
            Route::middleware(CheckPermission::class . ':Update Role Permission')->put('role-permissions/{id}', [RolePermissionController::class, 'update']);
        });

        // STUDENT DOCUMENTS
        Route::apiResource('student-documents', StudentDocumentController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});



<?php

use App\Http\Controllers\API\Log\LogController;
use App\Http\Controllers\API\Master\DocumentTypeController;
use App\Http\Controllers\API\Master\EducationLevelController;
use App\Http\Controllers\API\Master\GuardianRelationshipController;
use App\Http\Controllers\API\Master\NationalityController;
use App\Http\Controllers\API\Master\PermissionController;
use App\Http\Controllers\API\Master\RolePermissionController;
use App\Http\Controllers\API\Master\SpecialNeedController;
use App\Http\Controllers\API\Master\StudentDocumentController;
use App\Http\Controllers\API\Master\TransportationModeController;
use App\Http\Controllers\API\Student\StudentClassController;
use App\Http\Controllers\API\Teacher\TeacherController;
use App\Http\Controllers\API\Authentication\AuthController;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::prefix('master')->group(function () {
        Route::get('education-levels', [EducationLevelController::class, 'all'])->middleware(['auth:sanctum']);
        Route::post('education-levels', [EducationLevelController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('education-levels/{id}', [EducationLevelController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('education-levels/{id}', [EducationLevelController::class, 'update'])->middleware(['auth:sanctum']);

        Route::get('permissions', [PermissionController::class, 'all'])->middleware(['auth:sanctum', CheckPermission::class . ':List Permission']);
        Route::post('permissions', [PermissionController::class, 'create'])->middleware(['auth:sanctum', CheckPermission::class . ':Add Permission']);
        Route::delete('permissions/{id}', [PermissionController::class, 'destroy'])->middleware(['auth:sanctum', CheckPermission::class . ':Remove Permission']);
        Route::put('permissions/{id}', [PermissionController::class, 'update'])->middleware(['auth:sanctum', CheckPermission::class . ':Update Permission']);

        Route::get('role-permissions', [RolePermissionController::class, 'all'])->middleware(['auth:sanctum', CheckPermission::class . ':List Role Permission']);
        Route::post('role-permissions', [RolePermissionController::class, 'create'])->middleware(['auth:sanctum', CheckPermission::class . ':Add Role Permission']);
        Route::delete('role-permissions/{id}', [RolePermissionController::class, 'destroy'])->middleware(['auth:sanctum', CheckPermission::class . ':Remove Role Permission']);
        Route::put('role-permissions/{id}', [RolePermissionController::class, 'update'])->middleware(['auth:sanctum', CheckPermission::class . ':Update Role Permission']);

        Route::get('teachers', [TeacherController::class, 'all'])->middleware(['auth:sanctum']);
        Route::post('teachers', [TeacherController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('teachers/{id}', [TeacherController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('teachers/{id}', [TeacherController::class, 'update'])->middleware(['auth:sanctum']);

        Route::get('student-classes', [StudentClassController::class, 'all'])->middleware(['auth:sanctum', CheckPermission::class . ':List Education Level']);
        Route::post('student-classes', [StudentClassController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('student-classes/{id}', [StudentClassController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('student-classes/{id}', [StudentClassController::class, 'update'])->middleware(['auth:sanctum']);

        Route::get('document-types', [DocumentTypeController::class, 'all'])->middleware(['auth:sanctum']);
        Route::post('document-types', [DocumentTypeController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('document-types/{id}', [DocumentTypeController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('document-types/{id}', [DocumentTypeController::class, 'update'])->middleware(['auth:sanctum']);

        Route::get('guardian-relationships', [GuardianRelationshipController::class, 'all'])->middleware(['auth:sanctum']);
        Route::post('guardian-relationships', [GuardianRelationshipController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('guardian-relationships/{id}', [GuardianRelationshipController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('guardian-relationships/{id}', [GuardianRelationshipController::class, 'update'])->middleware(['auth:sanctum']);

        Route::get('nationalities', [NationalityController::class, 'all'])->middleware(['auth:sanctum']);
        Route::post('nationalities', [NationalityController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('nationalities/{id}', [NationalityController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('nationalities/{id}', [NationalityController::class, 'update'])->middleware(['auth:sanctum']);

        Route::get('special-needs', [SpecialNeedController::class, 'all'])->middleware(['auth:sanctum']);
        Route::post('special-needs', [SpecialNeedController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('special-needs/{id}', [SpecialNeedController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('special-needs/{id}', [SpecialNeedController::class, 'update'])->middleware(['auth:sanctum']);

        Route::get('transportation-modes', [TransportationModeController::class, 'all'])->middleware(['auth:sanctum']);
        Route::post('transportation-modes', [TransportationModeController::class, 'create'])->middleware(['auth:sanctum']);
        Route::delete('transportation-modes/{id}', [TransportationModeController::class, 'destroy'])->middleware(['auth:sanctum']);
        Route::put('transportation-modes/{id}', [TransportationModeController::class, 'update'])->middleware(['auth:sanctum']);

    });

    Route::get('student-documents', [StudentDocumentController::class, 'all'])->middleware(['auth:sanctum']);
    Route::post('student-documents', [StudentDocumentController::class, 'create'])->middleware(['auth:sanctum']);
    Route::delete('student-documents/{id}', [StudentDocumentController::class, 'destroy'])->middleware(['auth:sanctum']);
    Route::put('student-documents/{id}', [StudentDocumentController::class, 'update'])->middleware(['auth:sanctum']);

    Route::get('logs', [LogController::class, 'all']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
    });
});




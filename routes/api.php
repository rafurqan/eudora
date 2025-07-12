<?php

use App\Http\Controllers\API\Master\CityController;
use App\Http\Controllers\API\Master\ContactTypeController;
use App\Http\Controllers\API\Master\IncomeRangeController;
use App\Http\Controllers\API\Master\ProgramController;
use App\Http\Controllers\API\Master\ProvinceController;
use App\Http\Controllers\API\Master\ReligionController;
use App\Http\Controllers\API\Master\SchoolTypeController;
use App\Http\Controllers\API\Master\SpecialConditionController;
use App\Http\Controllers\API\Master\SubDistrictController;
use App\Http\Controllers\API\Master\VillageController;
use App\Http\Controllers\API\ProspectiveStudent\ProspectiveStudentController;
use App\Http\Controllers\API\ProspectiveStudent\RegistrationCodeController;
use App\Http\Controllers\API\Student\StudentAddressController;
use App\Http\Controllers\API\Student\StudentContactController;
use App\Http\Controllers\API\Student\StudentController;
use App\Http\Controllers\API\Student\StudentOriginSchoolController;
use App\Http\Controllers\API\Student\StudentParentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Authentication\AuthController;
use App\Http\Controllers\API\Log\LogController;
use App\Http\Controllers\API\Master\{
    DocumentTypeController,
    DonationsTypesController,
    DonorsController,
    EducationLevelController,
    GuardianRelationshipController,
    NationalityController,
    PermissionController,
    RolePermissionController,
    SpecialNeedController,
    TransportationModeController,
    ServiceController,
    RateController,
    RatePackageController,
    GrantsController,
};
use App\Http\Controllers\API\Student\{
    StudentClassController,
    StudentDocumentController,
    ClassMembershipController
};
use App\Http\Controllers\API\Teacher\TeacherController;
use App\Http\Middleware\CheckPermission;
use App\HTTP\Controllers\API\Finance\{InvoiceController, PaymentController};
use App\Models\Payment;
use PHPUnit\Architecture\Services\ServiceContainer;

Route::prefix('v1')->group(function () {

    // AUTH ROUTES
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    });

    // LOG
    Route::get('logs', [LogController::class, 'index'])->middleware('auth:sanctum');

    // PROTECTED ROUTES
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // MASTER DATA
        Route::prefix('master')->group(function () {

            Route::apiResource('programs', ProgramController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('education-levels', EducationLevelController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('document-types', DocumentTypeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('guardian-relationships', GuardianRelationshipController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('nationalities', NationalityController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('special-needs', SpecialNeedController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('transportation-modes', TransportationModeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('student-classes', StudentClassController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('religions', ReligionController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('income-ranges', IncomeRangeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('contact-types', ContactTypeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('special-conditions', SpecialConditionController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('school-types', SchoolTypeController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('villages', VillageController::class)->only(['index']);
            Route::apiResource('sub-districts', SubDistrictController::class)->only(['index']);
            Route::apiResource('cities', CityController::class)->only(['index']);
            Route::apiResource('provinces', ProvinceController::class)->only(['index']);

            // Master Biaya
            Route::apiResource('services', ServiceController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('rates', RateController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('rates-package', RatePackageController::class)->only(['index', 'store', 'update', 'destroy']);

            // Master Donasi
            Route::apiResource('donors', DonorsController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('donation-types', DonationsTypesController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('grants', GrantsController::class)->only(['index', 'store', 'update', 'destroy']);
            // Endpoint untuk reset dana hibah
            Route::post('grants/{id}/reset', [GrantsController::class, 'reset'])->name('grants.reset');


            Route::middleware(CheckPermission::class . ':List Permission')->get('permissions', [PermissionController::class, 'all']);
            Route::middleware(CheckPermission::class . ':Add Permission')->post('permissions', [PermissionController::class, 'create']);
            Route::middleware(CheckPermission::class . ':Remove Permission')->delete('permissions/{id}', [PermissionController::class, 'destroy']);
            Route::middleware(CheckPermission::class . ':Update Permission')->put('permissions/{id}', [PermissionController::class, 'update']);

            Route::middleware(CheckPermission::class . ':List Role Permission')->get('role-permissions', [RolePermissionController::class, 'all']);
            Route::middleware(CheckPermission::class . ':Add Role Permission')->post('role-permissions', [RolePermissionController::class, 'create']);
            Route::middleware(CheckPermission::class . ':Remove Role Permission')->delete('role-permissions/{id}', [RolePermissionController::class, 'destroy']);
            Route::middleware(CheckPermission::class . ':Update Role Permission')->put('role-permissions/{id}', [RolePermissionController::class, 'update']);
        });


        Route::get('students/all', [StudentController::class, 'getAllStudent']);
        Route::apiResource('students', StudentController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        // STUDENT DOCUMENTS
        Route::apiResource('students/{id}/documents', StudentDocumentController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        Route::apiResource('students/{id}/origin-schools', StudentOriginSchoolController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        Route::apiResource('students/{id}/addresses', StudentAddressController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        Route::apiResource('students/{id}/parents', StudentParentController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        Route::apiResource('students/{id}/contacts', StudentContactController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        Route::post('students/{id}/change-status', [StudentController::class, 'changeStatus']);
        Route::get('class-memberships/all-students', [ClassMembershipController::class, 'getAllUniqueStudentsWithActiveClass']);
        Route::apiResource('class-memberships', ClassMembershipController::class)->only(['index', 'store']);

        //Invoice Routes
        Route::get('finance/invoices/statistics', [InvoiceController::class, 'statistics']);
        Route::get('finance/invoices/generate-invoice-code', [InvoiceController::class, 'generateInvoiceCode']);
        Route::apiResource('finance/invoices', InvoiceController::class)->only(['index', 'store', 'update', 'destroy', 'show']);

        //Payment Routes
        Route::get('finance/payments/statistics', [PaymentController::class, 'statistics']);
        Route::get('finance/payments/generate-payment-code', [PaymentController::class, 'generatePaymentCode']);
        Route::apiResource('finance/payments', PaymentController::class)->only(['index', 'store', 'update', 'destroy', 'show']);

        Route::apiResource('teachers', TeacherController::class)->only(['index', 'store', 'update', 'destroy', 'show']);

        Route::apiResource('prospective-students', ProspectiveStudentController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        Route::post('prospective-students/{id}/approve', [ProspectiveStudentController::class, 'approve']);
        Route::get('prospective-students/registration-code/generate', [RegistrationCodeController::class, 'getNext']);

    });
});



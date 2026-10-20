<?php

use App\Enums\UserType;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\LabController;
use App\Http\Controllers\Admin\MedicineController;
use App\Http\Controllers\Admin\MedicineImportController;
use App\Http\Controllers\Admin\ScanController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::bind('admin', function ($value) {
    return User::where('type', UserType::ADMIN)->findOrFail($value);
});

Route::bind('adminRole', function ($value) {
    return Role::whereNull('doctor_id')->findOrFail($value);
});

Route::prefix('web-admin')->name('web-admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'web-admin.only'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

        Route::post('doctors/{doctor}/reinstate', [DoctorController::class, 'reinstate'])->name('doctors.reinstate');
        Route::resource('doctors', DoctorController::class);
        Route::resource('admins', AdminController::class);
        Route::resource('labs', LabController::class);
        Route::resource('scans', ScanController::class);
        Route::get('medicines/import', [MedicineImportController::class, 'create'])->name('medicines.import');
        Route::post('medicines/import', [MedicineImportController::class, 'store'])->name('medicines.import.store');
        Route::resource('medicines', MedicineController::class);
        Route::resource('roles', AdminRoleController::class)->parameters(['roles' => 'adminRole']);
        Route::get('permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');
    });
});

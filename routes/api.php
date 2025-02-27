<?php

use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
});

Route::middleware(['auth:api'])->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::get('me', 'me');
        Route::post('logout', 'logout');
    });

    Route::middleware(['role:super admin|admin'])->prefix('admin')->group(function () {
        Route::apiResource('brands', BrandController::class)->except(['show', 'update']);
        Route::post('brands/update/{brand}', [BrandController::class, 'update']);
        Route::put('brands/restore/{brand}', [BrandController::class, 'restore']);
        Route::post('brands/delete/{brand}', [BrandController::class, 'delete']);

        Route::apiResource('categories', CategoryController::class)->except(['show']);
        Route::put('categories/restore/{brand}', [CategoryController::class, 'restore']);
        Route::post('categories/delete/{brand}', [CategoryController::class, 'delete']);

        Route::apiResource('customers', CustomerController::class)->except(['store', 'update']);
        Route::apiResource('products', ProductController::class);
    });
});

Route::controller(MasterController::class)->prefix('master')->group(function () {
    Route::get('parent-categories', 'parentCategories');
    Route::get('child-categories', 'childCategories');
    Route::get('brands', 'brands');
});

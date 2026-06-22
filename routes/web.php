<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserVerificationController;
use App\Http\Controllers\Admin\ProjectVerificationController;
use App\Http\Controllers\Admin\TransactionManagementController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\AuditLogController;


// ========================================
// 1. AUTHENTICATION ROUTES
// ========================================

Route::middleware('guest')-> group(function(){
    Route::get('/register',     [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',    [AuthController::class, 'register'])->name('register.process');
    Route::get('/login',        [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',       [AuthController::class, 'login'])->name('login.process');
});
Route::post('/logout',      [AuthController::class, 'logout'])->name('logout');

// ========================================
// 2. PUBLIC PAGES
// ========================================

// Landing Page (Homepage)
Route::get('/', [LandingController::class, 'index'])->name('home');

// Calculator Page 
Route::get('/kalkulator', function () {
    return view('main_page.calculator.calculator');
})->name('calculator');

// Education Page - Edukasi Carbon Offset
Route::get('/edukasi', function () {
    return view('main_page.edukasi.edukasi');
})->name('edukasi');

// About Page - Tentang Kami
Route::get('/tentang', function () {
    return view('main_page.tentang.tentang');
})->name('about');

// Projects Page
Route::get('/proyek',           [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{id}',    [ProjectController::class, 'show'])->name('projects.show');

// Cart actions 
Route::get('/cart',              [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add',         [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update',      [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove',      [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear',       [CartController::class, 'clear'])->name('cart.clear');
Route::post('/watchlist/toggle', [ProjectController::class, 'toggleWatchlist'])->name('watchlist.toggle');

// ========================================
// 3. PROTECTED ROUTES (Wajib Login)
// ========================================
Route::middleware('auth')->group(function () {
    // DashboardsSeller
    Route::get('/seller/dashboard',       [SellerDashboardController::class, 'index'])->middleware('role:seller')->name('seller.dashboard');
    Route::get('/dashboard',              [BuyerDashboardController::class, 'index'])->middleware('role:buyer')->name('dashboard');
    Route::delete('/seller/projects/{id}',[SellerDashboardController::class, 'destroy'])->middleware('role:seller')->name('seller.projects.destroy');
    Route::get('/seller/projects/create', [SellerDashboardController::class, 'create'])->middleware('role:seller')->name('seller.projects.create');
    Route::post('/seller/projects',       [SellerDashboardController::class, 'store'])->middleware('role:seller')->name('seller.projects.store');
    Route::get('/seller/projects/{id}/edit', [SellerDashboardController::class, 'edit'])->middleware('role:seller')->name('seller.projects.edit');
    Route::put('/seller/projects/{id}',   [SellerDashboardController::class, 'update'])->middleware('role:seller')->name('seller.projects.update');
    Route::get('/transactions',           [SellerDashboardController::class, 'transactions'])->middleware('role:seller')->name('transactions.index');
    // DashboardsBuyer
    Route::get('/buyer/transactions', [BuyerDashboardController::class, 'transactions'])->middleware('role:buyer')->name('buyer.transactions');

    //Simpan Perhitungan
    Route::post('/calculator/save', [CalculatorController::class, 'store'])->middleware('role:buyer');
    Route::post('/calculator/store', [CalculatorController::class, 'store'])->middleware('role:buyer');
    Route::delete('/calculator/clear', [CalculatorController::class, 'clear'])->middleware('role:buyer')->name('calculator.clear');
    //Oerder
    Route::post('/orders/confirm',        [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::get('/orders/{id}/success',     [OrderController::class, 'success'])->name('orders.success');
    Route::get('/orders/{id}/checkout',   [OrderController::class, 'checkout'])->name('orders.checkout.view');
    Route::post('/orders/store',          [OrderController::class, 'store'])->middleware('role:buyer')->name('orders.store');
    Route::get('/orders/{id}',            [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders',                [ProjectController::class, 'checkout'])->middleware('role:buyer')->name('orders.checkout');
    Route::get('/orders/success/multi',   [OrderController::class, 'successMulti'])->name('orders.success.multi');
    Route::get('/orders',                 [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/checkout/confirm', [OrderController::class, 'checkoutConfirm'])->name('orders.checkout.confirm');
    Route::post('/orders/checkout/confirm', [App\Http\Controllers\OrderController::class, 'confirm'])->name('orders.checkout.confirm.submit');

    // Midtrans callback routes (redirect dari payment popup)
    Route::get('/orders/midtrans/finish',       [OrderController::class, 'midtransFinish'])->name('orders.midtrans.finish');
    Route::get('/orders/midtrans/error',        [OrderController::class, 'midtransError'])->name('orders.midtrans.error');
    Route::get('/orders/midtrans/pending',      [OrderController::class, 'midtransPending'])->name('orders.midtrans.pending');
    Route::post('/orders/midtrans/notification', [App\Http\Controllers\OrderController::class, 'midtransNotification']);

    });

Route::middleware(['auth', 'role:admin,super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->middleware('permission:admin.dashboard')
            ->name('dashboard');

        Route::middleware('permission:users.verify')->group(function () {
            Route::get('/users', [UserVerificationController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [UserVerificationController::class, 'show'])->name('users.show');
            Route::patch('/users/{user}/status', [UserVerificationController::class, 'updateStatus'])
                ->name('users.status');
        });

        Route::middleware('permission:documents.verify')->group(function () {
            Route::patch('/documents/{document}', [UserVerificationController::class, 'updateDocument'])
                ->name('documents.update');
            Route::get('/documents/{document}/download', [UserVerificationController::class, 'download'])
                ->name('documents.download');
        });

        Route::middleware('permission:projects.verify')->group(function () {
            Route::get('/projects', [ProjectVerificationController::class, 'index'])->name('projects.index');
            Route::get('/projects/{project}', [ProjectVerificationController::class, 'show'])->name('projects.show');
            Route::patch('/projects/{project}', [ProjectVerificationController::class, 'update'])
                ->name('projects.update');
        });

        Route::middleware('permission:transactions.manage')->group(function () {
            Route::get('/transactions', [TransactionManagementController::class, 'index'])
                ->name('transactions.index');
            Route::patch('/transactions/{order}', [TransactionManagementController::class, 'update'])
                ->name('transactions.update');
        });

        Route::middleware(['role:super_admin', 'permission:admins.manage'])->group(function () {
            Route::get('/administrators', [AdminManagementController::class, 'index'])
                ->name('admins.index');
            Route::post('/administrators', [AdminManagementController::class, 'store'])
                ->name('admins.store');
            Route::patch('/administrators/{admin}', [AdminManagementController::class, 'update'])
                ->name('admins.update');
        });

        Route::middleware(['role:super_admin', 'permission:permissions.manage'])->group(function () {
            Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
            Route::put('/roles/{role}/permissions', [RolePermissionController::class, 'update'])
                ->name('roles.update');
        });

        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->middleware('permission:audit.view')
            ->name('audit.index');
    });

Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard'); 
    Route::get('/projects/create', [SellerDashboardController::class, 'create'])->name('projects.create');
});



// ========================================
// 4. MIDTRANS WEBHOOK (tanpa auth — harus public)
// ========================================
// Daftarkan URL ini di: Midtrans Dashboard → Settings → Payment → Notification URL
// Contoh: https://yourdomain.com/orders/midtrans/notification
Route::post('/orders/midtrans/notification', [OrderController::class, 'midtransNotification'])
    ->name('orders.midtrans.notification');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\SaleController;

// Home route - protected
Route::get('/', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Registration Routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware(['throttle:6,1'])->name('verification.send');
});

// Logout Route
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Customer Routes - Protected with auth middleware
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class);
    
    // Sales Routes - Gerenciamento de Vendas
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::put('/{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel');
        Route::get('/api/statistics', [SaleController::class, 'statistics'])->name('statistics');
    });
    
    // POS Routes - Sistema de PDV
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/search-customers', [PosController::class, 'searchCustomers'])->name('search.customers');
        Route::get('/search-products', [PosController::class, 'searchProducts'])->name('search.products');
        Route::post('/cart/add', [PosController::class, 'addToCart'])->name('cart.add');
        Route::put('/cart/update', [PosController::class, 'updateCart'])->name('cart.update');
        Route::delete('/cart/remove', [PosController::class, 'removeFromCart'])->name('cart.remove');
        Route::delete('/cart/clear', [PosController::class, 'clearCart'])->name('cart.clear');
        Route::post('/finalize', [PosController::class, 'finalizeSale'])->name('finalize');
    });
    
    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
    });
    
    // Configuration Routes - Only for administrators
    Route::middleware('admin')->prefix('configurations')->name('configurations.')->group(function () {
        Route::get('/', [ConfigurationController::class, 'index'])->name('index');
        Route::get('/edit', [ConfigurationController::class, 'edit'])->name('edit');
        Route::put('/update', [ConfigurationController::class, 'update'])->name('update');
        
        // User management routes
        Route::get('/users', [ConfigurationController::class, 'users'])->name('users');
        Route::put('/users/{user}', [ConfigurationController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}/permissions', [ConfigurationController::class, 'updateUserPermissions'])->name('users.permissions');
    });
});

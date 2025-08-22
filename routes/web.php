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
use App\Http\Controllers\DanfeController;
use App\Http\Controllers\NfceController;
use App\Http\Controllers\InutilizationController;
use App\Http\Controllers\ReportController;

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
    
    // DANFE Routes - Geração de DANFE/Cupom Fiscal
    Route::prefix('danfe')->name('danfe.')->group(function () {
        Route::get('/{sale}/download', [DanfeController::class, 'download'])->name('download');
        Route::get('/{sale}/print', [DanfeController::class, 'print'])->name('print');
        Route::get('/{sale}/info', [DanfeController::class, 'info'])->name('info');
    });
    
    // NFC-e Routes - Gerenciamento de NFC-e
    Route::prefix('nfce')->name('nfce.')->group(function () {
        Route::get('/', [NfceController::class, 'index'])->name('index');
        Route::get('/{sale}/xml', [NfceController::class, 'viewXml'])->name('xml');
        Route::get('/{sale}/download-pdf', [NfceController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{sale}/reprint', [NfceController::class, 'reprint'])->name('reprint');
        Route::post('/{sale}/cancel', [NfceController::class, 'cancel'])->name('cancel');
        Route::post('/{sale}/print-network', [NfceController::class, 'printToNetwork'])->name('print-network');
    });
    
    // Inutilization Routes - Inutilização de NFC-e
    Route::prefix('inutilizations')->name('inutilizations.')->group(function () {
        Route::get('/', [InutilizationController::class, 'index'])->name('index');
        Route::get('/create', [InutilizationController::class, 'create'])->name('create');
        Route::post('/', [InutilizationController::class, 'store'])->name('store');
        Route::get('/{inutilization}', [InutilizationController::class, 'show'])->name('show');
        Route::post('/{inutilization}/reprocess', [InutilizationController::class, 'reprocess'])->name('reprocess');
        Route::get('/{inutilization}/download', [InutilizationController::class, 'download'])->name('download');
        Route::get('/api/search', [InutilizationController::class, 'apiSearch'])->name('api.search');
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
    
    // Reports Routes - Relatórios de Vendas
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales-by-period', [ReportController::class, 'salesByPeriod'])->name('sales-by-period');
        Route::get('/sales-by-payment', [ReportController::class, 'salesByPaymentMethod'])->name('sales-by-payment');
        Route::get('/sales-by-customer', [ReportController::class, 'salesByCustomer'])->name('sales-by-customer');
        Route::get('/top-products', [ReportController::class, 'topProducts'])->name('top-products');
        
        // Export Routes - Exportação de Relatórios
        Route::get('/export/sales-by-period/csv', [ReportController::class, 'exportSalesByPeriodCsv'])->name('export.sales-by-period.csv');
        Route::get('/export/sales-by-period/pdf', [ReportController::class, 'exportSalesByPeriodPdf'])->name('export.sales-by-period.pdf');
        Route::get('/export/sales-by-payment/csv', [ReportController::class, 'exportSalesByPaymentCsv'])->name('export.sales-by-payment.csv');
        Route::get('/export/top-products/csv', [ReportController::class, 'exportTopProductsCsv'])->name('export.top-products.csv');
    });
    
    // Configuration Routes - Only for administrators
    Route::middleware('admin')->prefix('configurations')->name('configurations.')->group(function () {
        Route::get('/', [ConfigurationController::class, 'index'])->name('index');
        Route::get('/edit', [ConfigurationController::class, 'edit'])->name('edit');
        Route::put('/update', [ConfigurationController::class, 'update'])->name('update');
        
        // User management routes
        Route::get('/users', [ConfigurationController::class, 'users'])->name('users');
        Route::put('/users/{user}', [ConfigurationController::class, 'updateUserPermissions'])->name('users.update');
        
        // Certificate management routes
        Route::get('/certificates', [ConfigurationController::class, 'certificates'])->name('certificates');
        Route::post('/certificates/upload', [ConfigurationController::class, 'uploadCertificate'])->name('certificates.upload');
        Route::patch('/certificates/{certificate}/set-default', [ConfigurationController::class, 'setDefaultCertificate'])->name('certificates.set-default');
        Route::get('/certificates/{certificate}/details', [ConfigurationController::class, 'certificateDetails'])->name('certificates.details');
        Route::delete('/certificates/{certificate}', [ConfigurationController::class, 'deleteCertificate'])->name('certificates.delete');
    });
});

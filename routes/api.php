<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\EfdSettingsController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\WhatsAppReceiptController;
use App\Http\Controllers\Api\V1\WhatsAppSettingsController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\MobileAccessController;
use App\Http\Controllers\Api\V1\OnboardingApiController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\POSController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// API Version 1
Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Authentication Routes (No Auth Required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/login/pin', [AuthController::class, 'loginWithPin']);
        Route::post('/register', [OnboardingApiController::class, 'register']);
        Route::get('/verify-email/{id}/{hash}', [OnboardingApiController::class, 'verifyEmailFromApp']);
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Auth Management
        |--------------------------------------------------------------------------
        */
        Route::prefix('auth')->group(function () {
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::post('/pin', [AuthController::class, 'setPin']);
            Route::post('/pin/change', [AuthController::class, 'changePin']);
            Route::delete('/pin', [AuthController::class, 'removePin']);
            Route::post('/password', [AuthController::class, 'changePassword']);
        });

        /*
        |--------------------------------------------------------------------------
        | Mobile Access (Request & Device Management)
        |--------------------------------------------------------------------------
        */
        Route::prefix('mobile-access')->group(function () {
            Route::post('/request', [MobileAccessController::class, 'request']);
            Route::get('/status', [MobileAccessController::class, 'status']);
            Route::post('/register-device', [MobileAccessController::class, 'registerDevice']);
            Route::patch('/device/push-token', [MobileAccessController::class, 'updatePushToken']);
            Route::get('/devices', [MobileAccessController::class, 'devices']);
            Route::delete('/devices/{device_identifier}', [MobileAccessController::class, 'deactivateDevice']);
        });

        /*
        |--------------------------------------------------------------------------
        | Onboarding (New Users - No Mobile Access Required)
        |--------------------------------------------------------------------------
        */
        Route::prefix('auth')->group(function () {
            Route::post('/resend-verification', [OnboardingApiController::class, 'resendVerification']);
            Route::get('/verify-status', [OnboardingApiController::class, 'verifyStatus']);
            Route::post('/update-email', [OnboardingApiController::class, 'updateEmail']);
        });
        Route::prefix('onboarding')->group(function () {
            Route::post('/business', [OnboardingApiController::class, 'saveBusiness']);
            Route::post('/complete', [OnboardingApiController::class, 'complete']);
        });

        /*
        |--------------------------------------------------------------------------
        | POS Routes (Require Mobile Access Approved)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['mobile.approved', 'device.registered', 'device.activity'])->group(function () {
            /*
            |--------------------------------------------------------------------------
            | Products & Categories
            |--------------------------------------------------------------------------
            */
            Route::prefix('pos')->group(function () {
                Route::get('/products', [ProductController::class, 'index']);
                Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
                Route::get('/products/scan/{barcode}', [ProductController::class, 'scanBarcode']);
                Route::get('/products/{identifier}', [ProductController::class, 'show']);
                Route::get('/categories', [ProductController::class, 'categories']);

                // Checkout
                Route::post('/checkout', [POSController::class, 'checkout']);

                // Transactions
                Route::get('/transactions', [TransactionController::class, 'index']);
                Route::get('/transactions/today', [TransactionController::class, 'today']);
                Route::get('/transactions/mine', [TransactionController::class, 'mine']);
                Route::get('/transactions/summary', [TransactionController::class, 'summary']);
                Route::get('/transactions/{id}', [TransactionController::class, 'show']);
                Route::get('/transactions/{id}/receipt', [POSController::class, 'receipt']);
                Route::post('/transactions/{id}/void', [POSController::class, 'voidTransaction']);
                Route::post('/transactions/{id}/whatsapp', [WhatsAppReceiptController::class, 'send']);
                Route::get('/transactions/{id}/whatsapp/status', [WhatsAppReceiptController::class, 'status']);

                // Orders
                Route::get('/orders', [OrderController::class, 'index']);
                Route::post('/orders', [OrderController::class, 'store']);
                Route::get('/orders/{id}', [OrderController::class, 'show']);
                Route::put('/orders/{id}', [OrderController::class, 'update']);
                Route::post('/orders/{id}/convert', [OrderController::class, 'convertToSale']);
                Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
                Route::get('/orders/{id}/proforma', [OrderController::class, 'proforma']);
            });

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */
            Route::prefix('inventory')->group(function () {
                Route::get('/', [InventoryController::class, 'index']);
                Route::get('/summary', [InventoryController::class, 'summary']);
                Route::post('/{product}/adjust', [InventoryController::class, 'adjust']);
                Route::get('/{product}/history', [InventoryController::class, 'history']);
            });

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */
            Route::prefix('reports')->group(function () {
                Route::get('/dashboard', [ReportController::class, 'dashboard']);
                Route::get('/sales', [ReportController::class, 'sales']);
            });

            /*
            |--------------------------------------------------------------------------
            | Customers (Wateja)
            |--------------------------------------------------------------------------
            */
            Route::prefix('customers')->group(function () {
                Route::get('/', [CustomerController::class, 'index']);
                Route::get('/search', [CustomerController::class, 'search']);
                Route::post('/', [CustomerController::class, 'store']);
                Route::get('/{id}', [CustomerController::class, 'show']);
                Route::put('/{id}', [CustomerController::class, 'update']);
                Route::get('/{id}/transactions', [CustomerController::class, 'transactions']);
                Route::get('/{id}/credit-history', [CustomerController::class, 'creditHistory']);
                Route::post('/{id}/credit-payment', [CustomerController::class, 'creditPayment']);
                Route::post('/{id}/credit-adjustment', [CustomerController::class, 'creditAdjustment']);
            });

            /*
            |--------------------------------------------------------------------------
            | Expenses (Matumizi)
            |--------------------------------------------------------------------------
            */
            Route::prefix('expenses')->group(function () {
                Route::get('/', [ExpenseController::class, 'index']);
                Route::get('/today', [ExpenseController::class, 'today']);
                Route::get('/categories', [ExpenseController::class, 'categories']);
                Route::post('/categories', [ExpenseController::class, 'storeCategory']);
                Route::get('/summary', [ExpenseController::class, 'summary']);
                Route::get('/suppliers', [ExpenseController::class, 'suppliers']);
                Route::post('/', [ExpenseController::class, 'store']);
                Route::get('/{id}', [ExpenseController::class, 'show']);
                Route::put('/{id}', [ExpenseController::class, 'update']);
                Route::delete('/{id}', [ExpenseController::class, 'destroy']);
            });

            /*
            |--------------------------------------------------------------------------
            | Store Settings
            |--------------------------------------------------------------------------
            */
            Route::prefix('settings')->group(function () {
                Route::get('/', [SettingsController::class, 'index']);
                Route::put('/', [SettingsController::class, 'update']);
                Route::post('/logo', [SettingsController::class, 'uploadLogo']);
                Route::delete('/logo', [SettingsController::class, 'removeLogo']);

                // WhatsApp Receipt Settings
                Route::get('/whatsapp', [WhatsAppSettingsController::class, 'index']);
                Route::put('/whatsapp', [WhatsAppSettingsController::class, 'update']);

                // EFD Settings
                Route::get('/efd', [EfdSettingsController::class, 'index']);
                Route::put('/efd', [EfdSettingsController::class, 'update']);
                Route::post('/efd/register', [EfdSettingsController::class, 'register']);
                Route::post('/efd/test', [EfdSettingsController::class, 'test']);
                Route::get('/efd/pending', [EfdSettingsController::class, 'pending']);
                Route::post('/efd/retry', [EfdSettingsController::class, 'retry']);
            });

            /*
            |--------------------------------------------------------------------------
            | Sync (Offline Support)
            |--------------------------------------------------------------------------
            */
            Route::prefix('sync')->group(function () {
                Route::get('/pull', [SyncController::class, 'pull']);
                Route::post('/push', [SyncController::class, 'push']);
                Route::get('/status', [SyncController::class, 'status']);
            });
        });
    });
});

/*
|--------------------------------------------------------------------------
| API Health Check
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => 'v1',
    ]);
});

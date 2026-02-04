<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\MobileAccessController;
use App\Http\Controllers\Api\V1\POSController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
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
                Route::get('/transactions/{id}', [TransactionController::class, 'show']);
                Route::get('/transactions/{id}/receipt', [POSController::class, 'receipt']);
                Route::post('/transactions/{id}/void', [POSController::class, 'voidTransaction']);
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

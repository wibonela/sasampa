<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\Admin\DocumentationArticleController;
use App\Http\Controllers\Admin\DocumentationCategoryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchSwitchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyRegistrationController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SandukuController;
use Illuminate\Support\Facades\Route;

// Sanduku feedback (public API)
Route::post('/api/sanduku', [SandukuController::class, 'store'])->name('sanduku.store');

// Landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch')
    ->where('locale', 'en|sw');

// Public Documentation
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [DocumentationController::class, 'index'])->name('index');
    Route::get('/search', [DocumentationController::class, 'search'])->name('search');
    Route::get('/{category}', [DocumentationController::class, 'category'])->name('category');
    Route::get('/{category}/{article}', [DocumentationController::class, 'show'])->name('show');
});

// Onboarding - Step 1 (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/register', [OnboardingController::class, 'showStep1'])->name('onboarding.step1');
    Route::post('/register', [OnboardingController::class, 'processStep1']);
    // Keep alias for old route
    Route::get('/onboarding', fn() => redirect()->route('onboarding.step1'))->name('company.register');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Onboarding - Steps 2-4
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        // Step 2: Email Verification
        Route::get('/verify-email', [OnboardingController::class, 'showStep2'])->name('step2');
        Route::post('/verify-email/resend', [OnboardingController::class, 'resendVerification'])->name('step2.resend');

        // Step 3: Business Details (requires verified email)
        Route::middleware('verified')->group(function () {
            Route::get('/business-details', [OnboardingController::class, 'showStep3'])->name('step3');
            Route::post('/business-details', [OnboardingController::class, 'processStep3']);

            // Step 4: Complete
            Route::get('/complete', [OnboardingController::class, 'showStep4'])->name('step4');
            Route::post('/complete', [OnboardingController::class, 'finishOnboarding']);
        });
    });

    // Company pending status page (no company approval needed)
    Route::get('/company/pending', [CompanyRegistrationController::class, 'pending'])
        ->name('company.pending');

    // Profile routes (accessible without approved company)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Platform Admin routes
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('platform.admin')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');
            Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'chartData'])
                ->name('dashboard.chart-data');

            // Notifications
            Route::get('/notifications', [AdminNotificationController::class, 'index'])
                ->name('notifications.index');
            Route::get('/notifications/dropdown', [AdminNotificationController::class, 'dropdown'])
                ->name('notifications.dropdown');
            Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])
                ->name('notifications.read');
            Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])
                ->name('notifications.read-all');

            // Companies
            Route::get('/companies', [CompanyManagementController::class, 'index'])
                ->name('companies.index');
            Route::get('/companies/{company}', [CompanyManagementController::class, 'show'])
                ->name('companies.show');
            Route::post('/companies/{company}/approve', [CompanyManagementController::class, 'approve'])
                ->name('companies.approve');
            Route::post('/companies/{company}/reject', [CompanyManagementController::class, 'reject'])
                ->name('companies.reject');

            // Documentation Management
            Route::prefix('documentation')->name('documentation.')->group(function () {
                Route::resource('categories', DocumentationCategoryController::class)
                    ->except(['show']);
                Route::resource('articles', DocumentationArticleController::class)
                    ->except(['show']);
                Route::post('articles/{article}/toggle-publish', [DocumentationArticleController::class, 'togglePublish'])
                    ->name('articles.toggle-publish');
            });
        });

    // Company routes (require approved company)
    Route::middleware('company.approved')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Branch Switching
        Route::post('/branch/switch/{branch}', [BranchSwitchController::class, 'switch'])
            ->name('branch.switch');

        // Branch Management (company owner only)
        Route::resource('branches', BranchController::class)->except(['show']);
        Route::get('/branches/{branch}/users', [BranchController::class, 'users'])
            ->name('branches.users');
        Route::post('/branches/{branch}/users', [BranchController::class, 'assignUser'])
            ->name('branches.assign-user');
        Route::delete('/branches/{branch}/users/{user}', [BranchController::class, 'removeUser'])
            ->name('branches.remove-user');
        Route::post('/branches/{branch}/users/{user}/default', [BranchController::class, 'setDefaultBranch'])
            ->name('branches.set-default');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Products
        Route::resource('products', ProductController::class);

        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
        Route::get('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjust.store');

        // Point of Sale
        Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
        Route::get('/pos/products', [POSController::class, 'getProducts'])->name('pos.products');
        Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{transaction}', [POSController::class, 'receipt'])->name('pos.receipt');
        Route::get('/pos/receipt/{transaction}/pdf', [POSController::class, 'receiptPdf'])->name('pos.receipt.pdf');

        // Transactions
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::patch('/transactions/{transaction}/void', [TransactionController::class, 'void'])->name('transactions.void');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::delete('/settings/logo', [SettingsController::class, 'removeLogo'])->name('settings.remove-logo');
    });
});

require __DIR__.'/auth.php';

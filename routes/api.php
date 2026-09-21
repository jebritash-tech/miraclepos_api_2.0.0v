<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Api\{
    CategoryController,
    MedicineController,
    SupplierController,
    PurchaseController,
    SaleController,
    InventoryLogController,
    BranchController,
    AdminController,
    UserController,
    AuthController,
    ReportController,
    RefundController,
    PasswordResetController,
    AnalyticsController,
    ShiftController,
    ExpenseController,
    DebtController,
    UnitController,
    MedicineUnitController,
    MedicineBatchesController,
    PriceEngineRuleController,
    PriceEngineController,
    InventoryController,
    SalaryController,
    EmployeeFinanceController,
    SearchController,
    BackupController,
    SettingsController,
    PinController,
    FinancialReportController,
    AuditLogController,
    InventoryCleanupController,
    BatchPricingController,
    BulkPricingController
};

// ============================================================
// PUBLIC ROUTES
// ============================================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

// ============================================================
// PROTECTED ROUTES
// ============================================================
Route::middleware(['auth:sanctum', 'session.timeout'])->group(function () {

    /* ============================================================
       PRICING — Bulk Recalculate
       ============================================================ */
    Route::prefix('pricing/bulk-recalculate')->group(function () {
        Route::post('/preview', [BulkPricingController::class, 'preview']);
        Route::post('/apply',   [BulkPricingController::class, 'apply']);
    });

    /* ============================================================
       PRICING — Batch & Medicine Pricing
       ============================================================ */
    Route::get('/medicines/{medicine}/available-batches',
        [BatchPricingController::class, 'availableBatches']);

    Route::post('/batches/{batch}/override-pricing',
        [BatchPricingController::class, 'overrideBatchPricing']);

    Route::post('/medicine-prices/{price}/lock',
        [BatchPricingController::class, 'lockPrice']);
    Route::post('/medicine-prices/{price}/unlock',
        [BatchPricingController::class, 'unlockPrice']);

    Route::post('/medicines/{medicine}/override-pricing',
        [BatchPricingController::class, 'overrideMedicinePricing']);
    Route::get('/medicines/{medicine}/pricing-info',
        [BatchPricingController::class, 'pricingInfo']);

    /* ============================================================
       INVENTORY CLEANUP
       ============================================================ */
    Route::prefix('inventory/cleanup')->group(function () {
        Route::get ('/batch/{id}/check',    [InventoryCleanupController::class, 'checkBatch']);
        Route::post('/batch/{id}',          [InventoryCleanupController::class, 'deleteBatch']);
        Route::get ('/purchase/{id}/check', [InventoryCleanupController::class, 'checkPurchase']);
        Route::post('/purchase/{id}',       [InventoryCleanupController::class, 'deletePurchase']);
    });

    /* ============================================================
       FINANCIAL REPORTS
       ============================================================ */
    Route::get('/financial-reports', [FinancialReportController::class, 'index']);

    /* ============================================================
       AUDIT LOGS
       ============================================================ */
    Route::prefix('audit-logs')->group(function () {
        Route::get('/',         [AuditLogController::class, 'index']);
        Route::get('/summary',  [AuditLogController::class, 'summary']);
        Route::get('/filters',  [AuditLogController::class, 'filters']);
        Route::get('/{id}',     [AuditLogController::class, 'show']);
    });

    /* ============================================================
       SETTINGS
       ============================================================ */
    Route::get('/settings/public', [SettingsController::class, 'publicSettings']);
    Route::get('/settings',        [SettingsController::class, 'index']);
    Route::put('/settings',        [SettingsController::class, 'update']);

    /* ============================================================
       PIN
       ============================================================ */
    Route::prefix('pin')->group(function () {
        Route::post('/set',    [PinController::class, 'setPin']);
        Route::post('/remove', [PinController::class, 'removePin']);
        Route::post('/verify', [PinController::class, 'verify']);
        Route::get('/status',  [PinController::class, 'status']);
    });

    /* ============================================================
       BACKUPS
       ============================================================ */
    Route::prefix('backups')->group(function () {
        Route::get('/',              [BackupController::class, 'index']);
        Route::post('/create',       [BackupController::class, 'create']);
        Route::get('/{filename}/download', [BackupController::class, 'download']);
        Route::post('/{filename}/restore', [BackupController::class, 'restore']);
        Route::delete('/{filename}', [BackupController::class, 'destroy']);
    });

    /* ============================================================
       SEARCH
       ============================================================ */
    Route::get('/search', [ReportController::class, 'search']);
    Route::get('/search/details/{type}/{id}', [SearchController::class, 'details']);

    /* ============================================================
       INVENTORY
       ============================================================ */
    Route::get('/inventories', [InventoryController::class, 'index']);
    Route::post('/inventories/adjust', [InventoryController::class, 'adjust']);

    /* ============================================================
       PRICING ENGINE
       ============================================================ */
    Route::post('price-engine/regenerate-all',
        [PriceEngineController::class, 'regenerateAll']);

    Route::patch('/medicines/{medicine}/pricing-rule',
        [MedicineController::class, 'updatePricingRule']);

    Route::prefix('price-engine')->group(function () {
        Route::post('/regenerate-current', [PriceEngineController::class, 'regenerateCurrent']);
        Route::get ('/rules',              [PriceEngineController::class, 'rules']);
        Route::get ('rules',               [PriceEngineController::class, 'index']);
        Route::post('rules',               [PriceEngineController::class, 'store']);
        Route::put ('rules/{rule}',        [PriceEngineController::class, 'update']);
        Route::delete('rules/{rule}',      [PriceEngineController::class, 'destroy']);
        Route::post('rules/{rule}/toggle',[PriceEngineController::class, 'toggle']);
        Route::post('simulate',            [PriceEngineController::class, 'simulate']);
    });

    Route::post('batches/{batch}/regenerate-prices',
        [PriceEngineController::class, 'regenerate']);

    /* ============================================================
       SALARIES
       ============================================================ */
    Route::prefix('salaries')->group(function () {
        Route::get   ('/',                [SalaryController::class, 'index']);
        Route::get   ('/dashboard',       [SalaryController::class, 'dashboard']);
        Route::post  ('/',                [SalaryController::class, 'store']);
        Route::put   ('/{salary}',        [SalaryController::class, 'update']);
        Route::post  ('/{salary}/pay',    [SalaryController::class, 'pay']);
        Route::delete('/{salary}',        [SalaryController::class, 'destroy']);
    });

    Route::post('/salaries/generate', [SalaryController::class, 'generate']);

    /* ============================================================
       UNITS
       ============================================================ */
    Route::apiResource('price-engine-rules', PriceEngineRuleController::class)
        ->only(['index', 'update']);

    Route::get('/units', [UnitController::class, 'index']);

    /* ============================================================
       MEDICINE UNITS
       ============================================================ */
    Route::get   ('/medicines/{medicine}/units',    [MedicineUnitController::class, 'index']);
    Route::post  ('/medicine-units',                [MedicineUnitController::class, 'store']);
    Route::put   ('/medicine-units/{medicineUnit}', [MedicineUnitController::class, 'update']);
    Route::delete('/medicine-units/{medicineUnit}', [MedicineUnitController::class, 'destroy']);

    /* ============================================================
       AUTHENTICATED USER & SESSION ROUTES
       ============================================================ */
    Route::middleware('auth:sanctum')->group(function () {

        /* ============================================================
           EXPENSES
           ============================================================ */
        Route::get('/expenses',      [ExpenseController::class, 'index']);
        Route::post('/expenses',     [ExpenseController::class, 'store']);
        Route::get('/expenses/{id}', [ExpenseController::class, 'show']);

        /* ============================================================
           DEBTS — ⚠️ ترتيب حاسم!
           ============================================================
           المسارات المحددة (specific) قبل المسارات ذات المعاملات ({id})
           وإلا يلتقط {id} كلمات مثل "pending-for-me"
           ============================================================ */

        // ✅ 1. المسارات المحددة أولاً
        Route::get('/debts/pending-for-me', [DebtController::class, 'pending']);

        // ✅ 2. المسارات العامة ثم ذات المعاملات
        Route::get   ('/debts',              [DebtController::class, 'index']);
        Route::post  ('/debts',              [DebtController::class, 'store']);
        Route::get   ('/debts/{id}',         [DebtController::class, 'show']);
        Route::put   ('/debts/{id}',         [DebtController::class, 'update']);
        Route::delete('/debts/{id}',         [DebtController::class, 'destroy']);
        Route::post  ('/debts/{id}/payment', [DebtController::class, 'payment']);

        /* ============================================================
           SHIFTS
           ============================================================ */
        Route::get ('/shift/current', [ShiftController::class, 'current']);
        Route::get('/shifts', [ShiftController::class,'index']);
        Route::get('/shifts/{shift}',[ShiftController::class,'show']);
        Route::post('/shift/open',    [ShiftController::class, 'open']);
        Route::post('/shift/close',   [ShiftController::class, 'close']);
        Route::post('/shift/withdraw',    [ShiftController::class, 'withdraw']);
        Route::post('/shift/debt-payment', [ShiftController::class, 'debtPayment']);

        /* ============================================================
           CURRENT USER
           ============================================================ */
        Route::get('/current-user', [AuthController::class, 'me']);

        /* ============================================================
           ADMIN API
           ============================================================ */
        Route::prefix('admin')->group(function () {
            Route::middleware(EnsureUserIsAdmin::class)->group(function () {
                Route::get ('/stats',              [AdminController::class, 'getStats']);
                Route::get ('/medicine-catalogue', [AdminController::class, 'getMedicineCatalogue']);
                Route::post('/medicine-catalogue', [AdminController::class, 'storeMedicine']);
                Route::post('/medicines',          [AdminController::class, 'storeMedicine']);
                Route::get ('/purchases',          [AdminController::class, 'indexPurchases']);
                Route::post('/purchases',          [AdminController::class, 'storePurchase']);
                Route::get ('/analytics',          [AdminController::class, 'getAnalytics']);
                Route::get ('/analytics-data',     [ReportController::class, 'getAnalyticsData']);
                Route::get ('/analytics/dashboard',[AnalyticsController::class, 'getDashboardData']);
            });
        });

        /* ============================================================
           BRANCHES
           ============================================================ */
        Route::apiResource('branches', BranchController::class);

        /* ============================================================
           REPORTS
           ============================================================ */
        Route::get('reports/sales',     [ReportController::class, 'getRecentSales']);
        Route::get('reports/low-stock', [ReportController::class, 'lowStockReport']);
        Route::get('/admin/overview-stats', [ReportController::class, 'overviewStats']);
        Route::get('/analytics/dashboard',  [AnalyticsController::class, 'dashboard']);

        /* ============================================================
           RETURNS & REFUNDS
           ============================================================ */
        Route::post('returns',                   [\App\Http\Controllers\Api\ReturnController::class, 'store']);
        Route::post('/refunds',                  [RefundController::class, 'store']);
        Route::get ('/sales/{id}/details',       [ReportController::class, 'getSaleDetails']);

        /* ============================================================
           INVENTORY LOGS
           ============================================================ */
        Route::get('logs/medicine/{medicineId}', [InventoryLogController::class, 'getMedicineLogs']);
        Route::get('logs',                       [InventoryLogController::class, 'index']);

        /* ============================================================
           CATEGORIES
           ============================================================ */
        Route::apiResource('categories', CategoryController::class);

        /* ============================================================
           MEDICINES
           ============================================================ */
        Route::apiResource('medicines', MedicineController::class);

        /* ============================================================
           SUPPLIERS
           ============================================================ */
        Route::apiResource('suppliers', SupplierController::class);

        /* ============================================================
           PURCHASES
           ============================================================ */
        Route::get ('/purchases',     [PurchaseController::class, 'index']);
        Route::get ('/purchases/{id}',[PurchaseController::class, 'show']);
        Route::post('/purchases',     [PurchaseController::class, 'store']);

        /* ============================================================
           BRANCHES (additional)
           ============================================================ */
        Route::get('/branches',      [BranchController::class, 'index']);
        Route::get('/branches/{id}', [BranchController::class, 'show']);

        /* ============================================================
           DASHBOARD DATA
           ============================================================ */
        Route::get('/admin/dashboard-data', [ReportController::class, 'getDashboardData']);

        /* ============================================================
           SALES (POS)
           ============================================================ */
        Route::post('sales', [SaleController::class, 'store']);

        Route::prefix('sales')->group(function () {
            Route::get('/medicines', [SaleController::class, 'medicines']);
        });

        /* ============================================================
           USERS
           ============================================================ */
        Route::apiResource('users', UserController::class);
    });
});

// ============================================================
// Misc
// ============================================================
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/create-admin-dev', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'jebritash@gmail.com',
        'password' => Hash::make('12345678'),
        'role' => 'admin',
        'branch_id' => null,
    ]);

    return response()->json(['message' => 'Admin created successfully', 'user' => $admin]);
});
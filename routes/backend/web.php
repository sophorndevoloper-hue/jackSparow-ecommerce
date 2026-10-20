<?php

use App\Http\Controllers\Backend\AnalyticsReportController;
use App\Http\Controllers\Backend\Auth\BackendAuthController;
use App\Http\Controllers\Backend\BackendMenuController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\CacheController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\CustomerGroupController;
use App\Http\Controllers\Backend\CustomerGroupPaymentTermController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DeliveryAgentController;
use App\Http\Controllers\Backend\DeliveryController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\OrderFulfillmentController;
use App\Http\Controllers\Backend\PaymentMethodController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\PricingEngineController;
use App\Http\Controllers\Backend\ProductCollectionController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductImageController;
use App\Http\Controllers\Backend\ProductModelController;
use App\Http\Controllers\Backend\ProfileController as BackendProfileController;
use App\Http\Controllers\Backend\QuoteController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SerialNumberController;
use App\Http\Controllers\Backend\ShippingMethodController;
use App\Http\Controllers\Backend\StaffAttendanceController;
use App\Http\Controllers\Backend\StaffController;
use App\Http\Controllers\Backend\StaffReportController;
use App\Http\Controllers\Backend\StaffSalaryController;
use App\Http\Controllers\Backend\StockAdjustmentController;
use App\Http\Controllers\Backend\StockController;
use App\Http\Controllers\Backend\StockTransferController;
use App\Http\Controllers\Backend\StorefrontMenuController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\UserHistoryController;
use App\Http\Controllers\Backend\WarehouseController;
use App\Http\Controllers\Backend\WebsiteProductController;
use App\Http\Controllers\Backend\WebsiteSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend Admin Portal & Dashboard Routes
|--------------------------------------------------------------------------
|
| Protected routes for store administrators to manage hardware inventory,
| categories, manufacturers, orders, customer accounts, and settings.
| All actions are protected and role-controlled via CheckBackendPermission.
|
*/

// Guest Admin Authentication
Route::prefix('admin')->as('admin.')->group(function () {
    Route::middleware('guest:backend')->group(function () {
        Route::get('login', [BackendAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [BackendAuthController::class, 'login']);
        Route::get('register', [BackendAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [BackendAuthController::class, 'register']);
    });

    Route::post('logout', [BackendAuthController::class, 'logout'])->middleware('auth:backend')->name('logout');
});

// Protected Admin Portal (Role & Permission controlled via CheckBackendPermission)
Route::prefix('admin')->middleware(['auth:backend', 'admin.permission'])->as('admin.')->group(function () {
    // 1. Dashboard & Profile
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profile', [BackendProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [BackendProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [BackendProfileController::class, 'updatePassword'])->name('profile.password');

    // 2. Product Images & Media Gallery
    Route::get('products/images', [ProductImageController::class, 'index'])->name('products.images.index');
    Route::post('products/images/save', [ProductImageController::class, 'saveGallery'])->name('products.images.save');
    Route::post('products/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::patch('products/images/{image}/primary', [ProductImageController::class, 'setPrimary'])->name('products.images.primary');
    Route::delete('products/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    // 3. Stock Management & Serials
    Route::get('stock/adjustments/product-serials', [StockAdjustmentController::class, 'getProductSerials'])->name('stock.adjustments.product-serials');
    Route::resource('stock/adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show'])->names('stock.adjustments');
    Route::get('stock/transfers/product-serials', [StockTransferController::class, 'getProductSerials'])->name('stock.transfers.product-serials');
    Route::resource('stock/transfers', StockTransferController::class)->only(['index', 'create', 'store', 'show'])->names('stock.transfers');
    Route::patch('stock/transfers/{transfer}/status', [StockTransferController::class, 'updateStatus'])->name('stock.transfers.status');
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('stock/{product}', [StockController::class, 'update'])->name('stock.update');
    Route::post('products/{product}/serials', [ProductController::class, 'storeSerials'])->name('products.serials.store');
    Route::post('serial-numbers/import', [SerialNumberController::class, 'import'])->name('serial-numbers.import');
    Route::post('serial-numbers/parse-preview', [SerialNumberController::class, 'parsePreview'])->name('serial-numbers.parse-preview');
    Route::get('serial-numbers/templates/{format}', [SerialNumberController::class, 'downloadTemplate'])->name('serial-numbers.templates.download');
    Route::get('serial-numbers/warranties/print', [SerialNumberController::class, 'printWarranties'])->name('serial-numbers.warranties.print');
    Route::get('serial-numbers/{serialNumber}/warranty', [SerialNumberController::class, 'warranty'])->name('serial-numbers.warranty');
    Route::resource('serial-numbers', SerialNumberController::class)->only(['index', 'store', 'update', 'destroy']);

    // 4. Warehouses
    Route::resource('warehouses', WarehouseController::class);

    // 5. Hardware Catalog (Products, Categories, Brands, Collections)
    Route::get('products/import/template/{format?}', [ProductController::class, 'downloadImportTemplate'])->name('products.import.template');
    Route::post('products/import-preview', [ProductController::class, 'importPreview'])->name('products.import-preview');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class);
    Route::resource('collections', ProductCollectionController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('brands', BrandController::class)->except(['show']);
    Route::get('models/by-brand/{brand?}', [ProductModelController::class, 'byBrand'])->name('models.by-brand');
    Route::resource('models', ProductModelController::class)->except(['show']);

    // 6. Manage Sale (Orders, RFQ Quotes, Dynamic Pricing, Fulfillment, Payment Methods)
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/mark-deposit-paid', [OrderController::class, 'markDepositPaid'])->name('orders.mark-deposit-paid');

    // RFQ & Quotes
    Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::patch('quotes/{quote}/pricing', [QuoteController::class, 'updatePricing'])->name('quotes.pricing');
    Route::post('quotes/{quote}/send', [QuoteController::class, 'sendQuote'])->name('quotes.send');
    Route::post('quotes/{quote}/convert-to-order', [QuoteController::class, 'convertToOrder'])->name('quotes.convert-to-order');

    // Dynamic Pricing Engine
    Route::get('pricing', [PricingEngineController::class, 'index'])->name('pricing.index');
    Route::post('pricing/calculate', [PricingEngineController::class, 'calculate'])->name('pricing.calculate');

    // Warehouse Order Fulfillment & Serial Scanning
    Route::get('fulfillment', [OrderFulfillmentController::class, 'index'])->name('fulfillment.index');
    Route::get('fulfillment/{order}', [OrderFulfillmentController::class, 'show'])->name('fulfillment.show');
    Route::post('fulfillment/{order}/attach-serial', [OrderFulfillmentController::class, 'attachSerial'])->name('fulfillment.attach-serial');
    Route::delete('fulfillment/{order}/serials/{serial_number}', [OrderFulfillmentController::class, 'detachSerial'])->name('fulfillment.detach-serial');
    Route::post('fulfillment/{order}/complete', [OrderFulfillmentController::class, 'completeFulfillment'])->name('fulfillment.complete');

    // Payment Methods
    Route::patch('payment-methods/{payment_method}/toggle-active', [PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle-active');
    Route::resource('payment-methods', PaymentMethodController::class);

    // Analytics & Financial Reports
    Route::get('reports', [AnalyticsReportController::class, 'index'])->name('reports.index');
    Route::get('reports/data', [AnalyticsReportController::class, 'data'])->name('reports.data');
    Route::get('reports/export', [AnalyticsReportController::class, 'export'])->name('reports.export');

    // 6.1 Delivery & Shipping
    Route::patch('deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('deliveries.status');
    Route::resource('deliveries', DeliveryController::class);
    Route::patch('shipping-methods/{shipping_method}/toggle-active', [ShippingMethodController::class, 'toggleActive'])->name('shipping-methods.toggle-active');
    Route::resource('shipping-methods', ShippingMethodController::class);

    // 7. B2B Accounts & People (Customer Groups, Customers, Suppliers, Delivery Agents)
    Route::post('customer-groups/{customer_group}/assign-users', [CustomerGroupController::class, 'assignUsers'])->name('customer-groups.assign-users');
    Route::delete('customer-groups/{customer_group}/remove-user/{customer}', [CustomerGroupController::class, 'removeUser'])->name('customer-groups.remove-user');
    Route::resource('customer-groups', CustomerGroupController::class);
    Route::patch('customer-group-payment-terms/{customer_group_payment_term}/toggle-status', [CustomerGroupPaymentTermController::class, 'toggleStatus'])->name('customer-group-payment-terms.toggle-status');
    Route::resource('customer-group-payment-terms', CustomerGroupPaymentTermController::class);
    Route::post('customers/bulk-assign-group', [CustomerController::class, 'bulkAssignGroup'])->name('customers.bulk-assign-group');
    Route::resource('customers', CustomerController::class);
    Route::patch('customers/{customer}/toggle-special', [CustomerController::class, 'toggleSpecial'])->name('customers.toggle-special');
    Route::patch('customers/{customer}/toggle-block', [CustomerController::class, 'toggleBlock'])->name('customers.toggle-block');
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::patch('delivery-agents/{delivery_agent}/toggle-active', [DeliveryAgentController::class, 'toggleActive'])->name('delivery-agents.toggle-active');
    Route::resource('delivery-agents', DeliveryAgentController::class);

    // Staff Management, Attendance & Salaries
    Route::get('staff/monthly-report', [StaffReportController::class, 'monthlyReport'])->name('staff.monthly-report');
    Route::patch('staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    Route::resource('staff', StaffController::class);

    Route::post('staff-attendance/check-in', [StaffAttendanceController::class, 'checkIn'])->name('staff-attendance.check-in');
    Route::post('staff-attendance/check-out', [StaffAttendanceController::class, 'checkOut'])->name('staff-attendance.check-out');
    Route::post('staff-attendance/record', [StaffAttendanceController::class, 'record'])->name('staff-attendance.record');
    Route::delete('staff-attendance/{attendance}', [StaffAttendanceController::class, 'destroy'])->name('staff-attendance.destroy');
    Route::get('staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');

    Route::post('staff-salaries/generate', [StaffSalaryController::class, 'generatePayroll'])->name('staff-salaries.generate');
    Route::patch('staff-salaries/{payroll}/status', [StaffSalaryController::class, 'updatePayrollStatus'])->name('staff-salaries.update-status');
    Route::post('staff-salaries/{payroll}/record-payment', [StaffSalaryController::class, 'recordPayment'])->name('staff-salaries.record-payment');
    Route::get('staff-salaries', [StaffSalaryController::class, 'index'])->name('staff-salaries.index');

    // 8. System Settings, Access Control & User History
    Route::delete('user-history/clear', [UserHistoryController::class, 'clear'])->name('user-history.clear');
    Route::get('user-history/{activity}', [UserHistoryController::class, 'show'])->name('user-history.show');
    Route::get('user-history', [UserHistoryController::class, 'index'])->name('user-history.index');

    Route::post('clear-cache', [CacheController::class, 'clear'])->name('cache.clear');
    Route::patch('users/{user}/toggle-approval', [UserController::class, 'toggleApproval'])->name('users.toggle-approval');
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->only(['index', 'create', 'store']);

    // 9. Menu Setup
    Route::get('menus', [BackendMenuController::class, 'index'])->name('menus.index');
    Route::get('menus/create', [BackendMenuController::class, 'create'])->name('menus.create');
    Route::post('menus', [BackendMenuController::class, 'store'])->name('menus.store');
    Route::post('menus/sync', [BackendMenuController::class, 'sync'])->name('menus.sync');
    Route::post('menus/reset-visibility', [BackendMenuController::class, 'resetVisibility'])->name('menus.reset-visibility');
    Route::get('menus/{menu}/edit', [BackendMenuController::class, 'edit'])->name('menus.edit');
    Route::put('menus/{menu}', [BackendMenuController::class, 'update'])->name('menus.update');
    Route::delete('menus/{menu}', [BackendMenuController::class, 'destroy'])->name('menus.destroy');
    Route::patch('menus/{menu}/sort', [BackendMenuController::class, 'updateSort'])->name('menus.sort');
    Route::patch('menus/{menu}/toggle-active', [BackendMenuController::class, 'toggleActive'])->name('menus.toggle-active');

    // 10. Website Setup / Storefront CMS
    Route::get('website-settings', [WebsiteSettingController::class, 'index'])->name('website-settings.index');
    Route::get('website-settings/hero', [WebsiteSettingController::class, 'hero'])->name('website-settings.hero');
    Route::get('website-settings/branding', [WebsiteSettingController::class, 'branding'])->name('website-settings.branding');
    Route::get('website-settings/features', [WebsiteSettingController::class, 'features'])->name('website-settings.features');
    Route::get('website-settings/footer', [WebsiteSettingController::class, 'footer'])->name('website-settings.footer');
    Route::post('website-settings', [WebsiteSettingController::class, 'update'])->name('website-settings.update');

    // 11. Website Storefront Menus (Top, Main, Footer)
    Route::patch('storefront-menus/{storefront_menu}/toggle-active', [StorefrontMenuController::class, 'toggleActive'])->name('storefront-menus.toggle-active');
    Route::post('storefront-menus/sort', [StorefrontMenuController::class, 'updateSort'])->name('storefront-menus.sort');
    Route::resource('storefront-menus', StorefrontMenuController::class)->except(['create', 'show', 'edit']);

    // 12. Website Storefront Products Showcase
    Route::patch('website-products/{storefrontProduct}/toggle-active', [WebsiteProductController::class, 'toggleActive'])->name('website-products.toggle-active');
    Route::resource('website-products', WebsiteProductController::class)
        ->parameters(['website-products' => 'storefrontProduct'])
        ->only(['index', 'store', 'update', 'destroy']);
});

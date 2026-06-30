<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DeliveryTaskController as AdminDeliveryTaskController;
use App\Http\Controllers\Admin\DriverController as AdminDriverController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ServiceRequestController as AdminServiceRequestController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Public\ServiceRequestController;
use Illuminate\Support\Facades\Route;

/*
| الصفحة الرئيسية المؤقتة (صفحة بداية للتأكد أن النظام يعمل — المرحلة 1).
| سيتم استبدالها بالواجهات الفعلية في المراحل القادمة حسب PLAN.md.
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
| تبديل لغة الواجهة (عربي/إنجليزي) وتخزينها في الجلسة، ثم العودة للصفحة السابقة.
*/
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('lang.switch');

/*
| صفحة العميل الخارجية لطلب الخدمة (عامة بدون تسجيل دخول).
*/
Route::get('/request-service', [ServiceRequestController::class, 'create'])->name('request-service.create');
Route::post('/request-service', [ServiceRequestController::class, 'store'])->name('request-service.store');
Route::get('/request-service/thanks', [ServiceRequestController::class, 'thanks'])->name('request-service.thanks');

/*
| المصادقة (تسجيل الدخول / الخروج).
*/
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
| لوحة التحكم الداخلية — تتطلب تسجيل دخول.
| غير المسجّل عند فتح /dashboard يُحوّل تلقائيًا إلى /login.
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    | إدارة طلبات العملاء (المرحلة 5).
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/service-requests', [AdminServiceRequestController::class, 'index'])->name('service-requests.index');
        Route::get('/service-requests/{serviceRequest}', [AdminServiceRequestController::class, 'show'])->name('service-requests.show');
        Route::post('/service-requests/{serviceRequest}/contacted', [AdminServiceRequestController::class, 'markContacted'])->name('service-requests.contacted');
        Route::post('/service-requests/{serviceRequest}/cancel', [AdminServiceRequestController::class, 'cancel'])->name('service-requests.cancel');
        Route::get('/service-requests/{serviceRequest}/convert', [AdminServiceRequestController::class, 'convertForm'])->name('service-requests.convert.form');
        Route::post('/service-requests/{serviceRequest}/convert', [AdminServiceRequestController::class, 'convert'])->name('service-requests.convert');

        /*
        | الطلبات الرسمية — سير عمل المحاسب (المرحلة 6).
        */
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/orders/{order}/receipt', [AdminOrderController::class, 'receipt'])->name('orders.receipt');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/payments', [AdminOrderController::class, 'storePayment'])->name('orders.payments.store');
        // إنشاء مهمة توصيل من طلب رسمي
        Route::post('/orders/{order}/delivery-tasks', [AdminDeliveryTaskController::class, 'store'])->name('orders.delivery-tasks.store');

        /*
        | العملاء والخدمات (المرحلة 12.5).
        | العرض متاح للموظف والمدير؛ الإضافة/التعديل للمدير فقط.
        */
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::middleware('role:admin')->group(function () {
            Route::get('/customers/create', [AdminCustomerController::class, 'create'])->name('customers.create');
            Route::post('/customers', [AdminCustomerController::class, 'store'])->name('customers.store');
            Route::get('/customers/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('customers.edit');
            Route::put('/customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');

            Route::get('/services/create', [AdminServiceController::class, 'create'])->name('services.create');
            Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
            Route::get('/services/{service}/edit', [AdminServiceController::class, 'edit'])->name('services.edit');
            Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
        });
        Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');

        /*
        | السائقون والتوصيلات (المرحلة 8).
        */
        Route::get('/drivers', [AdminDriverController::class, 'index'])->name('drivers.index');
        Route::get('/drivers/create', [AdminDriverController::class, 'create'])->name('drivers.create');
        Route::post('/drivers', [AdminDriverController::class, 'store'])->name('drivers.store');
        Route::get('/drivers/{driver}', [AdminDriverController::class, 'show'])->name('drivers.show');
        Route::get('/drivers/{driver}/edit', [AdminDriverController::class, 'edit'])->name('drivers.edit');
        Route::put('/drivers/{driver}', [AdminDriverController::class, 'update'])->name('drivers.update');
        Route::post('/drivers/{driver}/payments', [AdminDriverController::class, 'storePayment'])->name('drivers.payments.store');

        Route::get('/delivery-tasks', [AdminDeliveryTaskController::class, 'index'])->name('delivery-tasks.index');
        Route::post('/delivery-tasks/{deliveryTask}/status', [AdminDeliveryTaskController::class, 'updateStatus'])->name('delivery-tasks.status');

        /*
        | المخزون البسيط (المرحلة 9).
        */
        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/create', [AdminInventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [AdminInventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/{inventoryItem}', [AdminInventoryController::class, 'show'])->name('inventory.show');
        Route::get('/inventory/{inventoryItem}/edit', [AdminInventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{inventoryItem}', [AdminInventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/{inventoryItem}/add', [AdminInventoryController::class, 'addQuantity'])->name('inventory.add');
        Route::post('/inventory/{inventoryItem}/dispense', [AdminInventoryController::class, 'dispenseQuantity'])->name('inventory.dispense');
        // التعديل اليدوي للمدير فقط
        Route::post('/inventory/{inventoryItem}/adjust', [AdminInventoryController::class, 'adjust'])->middleware('role:admin')->name('inventory.adjust');

        /*
        | الإعدادات الشاملة — للمدير فقط (المرحلة 10).
        */
        Route::middleware('role:admin')->group(function () {
            Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

            /*
            | التقارير ولوحة المؤشرات — للمدير فقط (المرحلة 11).
            */
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
            Route::get('/reports/orders-status', [AdminReportController::class, 'ordersStatus'])->name('reports.orders-status');
            Route::get('/reports/customer-dues', [AdminReportController::class, 'customerDues'])->name('reports.customer-dues');
            Route::get('/reports/drivers', [AdminReportController::class, 'drivers'])->name('reports.drivers');
            Route::get('/reports/inventory-low', [AdminReportController::class, 'inventoryLow'])->name('reports.inventory-low');
        });
    });
});

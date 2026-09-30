<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DapurController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PengadaanController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return match ($user->role) {
            'owner' => redirect()->route('owner.dashboard'),
            'gudang' => redirect()->route('gudang.dashboard'),
            'pengadaan' => redirect()->route('pengadaan.dashboard'),
            'kasir' => redirect()->route('kasir.dashboard'),
            'dapur' => redirect()->route('dapur.dashboard'),
            'customer' => redirect()->route('customer.menu'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
})->name('home');

// ==================== AUTHENTICATION ====================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::post('/auth/google', [AuthController::class, 'googleSignIn'])->name('auth.google');

// ==================== CUSTOMER (PUBLIC / TABLE QR) ====================
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/order', [CustomerController::class, 'menu'])->name('menu');
    Route::post('/order', [CustomerController::class, 'placeOrder'])->name('order.place');
    Route::get('/order/{order}/midtrans', [CustomerController::class, 'midtransPay'])->name('midtrans.pay');
    Route::post('/order/{order}/midtrans-simulate', [CustomerController::class, 'midtransSimulateSuccess'])->name('midtrans.simulate');
    Route::get('/order/{order}/voucher', [CustomerController::class, 'cashVoucher'])->name('cash.voucher');
    Route::get('/order/{order}/status', [CustomerController::class, 'orderStatus'])->name('order.status');
});
Route::get('/api/customer/order/{order}/status', [CustomerController::class, 'apiOrderStatus'])->name('customer.api.order.status');

// ==================== OWNER ====================
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [OwnerController::class, 'users'])->name('users.index');
    Route::get('/users/create', [OwnerController::class, 'createUser'])->name('users.create');
    Route::post('/users', [OwnerController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [OwnerController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [OwnerController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [OwnerController::class, 'destroyUser'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-ban', [OwnerController::class, 'toggleBanUser'])->name('users.toggle_ban');

    // Reports
    Route::get('/reports/sales', [OwnerController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/stock', [OwnerController::class, 'stockReport'])->name('reports.stock');
    Route::get('/reports/procurement', [OwnerController::class, 'procurementReport'])->name('reports.procurement');
    Route::get('/reports/profit-loss', [OwnerController::class, 'profitLossReport'])->name('reports.profit_loss');

    // Procurement Approvals
    Route::get('/procurements', [OwnerController::class, 'pendingProcurements'])->name('procurements.index');
    Route::post('/procurements/{procurement}/approve', [OwnerController::class, 'approveProcurement'])->name('procurements.approve');
    Route::post('/procurements/{procurement}/reject', [OwnerController::class, 'rejectProcurement'])->name('procurements.reject');
});

// ==================== BAGIAN GUDANG ====================
Route::middleware(['auth', 'role:gudang,owner'])->prefix('gudang')->name('gudang.')->group(function () {
    Route::get('/dashboard', [GudangController::class, 'dashboard'])->name('dashboard');

    // Inbound Supplier
    Route::get('/inbound', [GudangController::class, 'inboundIndex'])->name('inbound.index');
    Route::get('/inbound/{procurement}', [GudangController::class, 'inboundShow'])->name('inbound.show');
    Route::post('/inbound/{procurement}/receive', [GudangController::class, 'receiveInbound'])->name('inbound.receive');

    // Outbound to Toko (Transfers)
    Route::get('/transfers', [GudangController::class, 'transfersIndex'])->name('transfers.index');
    Route::get('/transfers/create', [GudangController::class, 'createTransfer'])->name('transfers.create');
    Route::post('/transfers', [GudangController::class, 'storeTransfer'])->name('transfers.store');
    Route::get('/transfers/{transfer}', [GudangController::class, 'showTransfer'])->name('transfers.show');

    // Stocks & Mutations
    Route::get('/stocks', [GudangController::class, 'stocks'])->name('stocks.index');
    Route::get('/mutations', [GudangController::class, 'mutations'])->name('stocks.mutations');
});

// ==================== BAGIAN PENGADAAN ====================
Route::middleware(['auth', 'role:pengadaan,owner'])->prefix('pengadaan')->name('pengadaan.')->group(function () {
    Route::get('/dashboard', [PengadaanController::class, 'dashboard'])->name('dashboard');
    Route::get('/stock-check', [PengadaanController::class, 'checkStock'])->name('stock.check');

    Route::get('/procurements', [PengadaanController::class, 'index'])->name('index');
    Route::get('/procurements/create', [PengadaanController::class, 'create'])->name('create');
    Route::post('/procurements', [PengadaanController::class, 'store'])->name('store');
    Route::get('/procurements/{procurement}', [PengadaanController::class, 'show'])->name('show');
    Route::get('/procurements/{procurement}/print', [PengadaanController::class, 'print'])->name('print');
    Route::post('/procurements/{procurement}/send-email', [PengadaanController::class, 'sendEmailToSupplier'])->name('send_email');
});

// ==================== BAGIAN KASIR (TOKO) ====================
Route::middleware(['auth', 'role:kasir,owner'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [KasirController::class, 'dashboard'])->name('dashboard');

    // Inbound from Gudang
    Route::get('/transfers', [KasirController::class, 'transfersIndex'])->name('transfers.index');
    Route::post('/transfers/{transfer}/receive', [KasirController::class, 'receiveTransfer'])->name('transfers.receive');

    // Menu & BOM Management
    Route::get('/menus', [KasirController::class, 'menuIndex'])->name('menus.index');
    Route::get('/menus/create', [KasirController::class, 'createMenu'])->name('menus.create');
    Route::post('/menus', [KasirController::class, 'storeMenu'])->name('menus.store');
    Route::get('/menus/{menu}/edit', [KasirController::class, 'editMenu'])->name('menus.edit');
    Route::put('/menus/{menu}', [KasirController::class, 'updateMenu'])->name('menus.update');
    Route::patch('/menus/{menu}/toggle', [KasirController::class, 'toggleMenuAvailability'])->name('menus.toggle');
    Route::delete('/menus/{menu}', [KasirController::class, 'destroyMenu'])->name('menus.destroy');

    // POS Walk-in
    Route::get('/pos', [KasirController::class, 'pos'])->name('pos');
    Route::post('/pos/order', [KasirController::class, 'createManualOrder'])->name('pos.order');

    // Cash Payment Processing
    Route::get('/payment/scan', [KasirController::class, 'cashPaymentScan'])->name('payment.scan');
    Route::post('/payment/process', [KasirController::class, 'processCashPayment'])->name('payment.process');

    // Call customer when kitchen done
    Route::post('/orders/{order}/call', [KasirController::class, 'callCustomer'])->name('orders.call');
});
Route::get('/api/kasir/lookup-order', [KasirController::class, 'lookupOrder'])->name('kasir.api.lookup_order');

// ==================== BAGIAN DAPUR ====================
Route::middleware(['auth', 'role:dapur,owner'])->prefix('dapur')->name('dapur.')->group(function () {
    Route::get('/dashboard', [DapurController::class, 'dashboard'])->name('dashboard');
    Route::post('/orders/{order}/done', [DapurController::class, 'markDone'])->name('orders.done');
});
Route::get('/api/dapur/active-orders', [DapurController::class, 'apiActiveOrders'])->name('dapur.api.active_orders');

// ==================== REAL-TIME NOTIFICATIONS API ====================
Route::get('/api/notifications/poll', [NotificationController::class, 'poll'])->name('api.notifications.poll');
Route::post('/api/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read_all');

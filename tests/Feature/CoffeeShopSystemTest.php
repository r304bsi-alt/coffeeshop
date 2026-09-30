<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuRecipe;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoffeeShopSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Kopi Senja');
    }

    public function test_quick_role_login_works()
    {
        $response = $this->get('/quick-login/owner');
        $response->assertRedirect('/owner/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('owner', auth()->user()->role);
    }

    public function test_role_middleware_restricts_unauthorized_roles()
    {
        $kasir = User::where('role', 'kasir')->first();
        $response = $this->actingAs($kasir)->get('/owner/users');
        $response->assertRedirect('/kasir/dashboard');
    }

    public function test_owner_can_create_user_and_toggle_ban()
    {
        $owner = User::where('role', 'owner')->first();

        // Create new user
        $response = $this->actingAs($owner)->post('/owner/users', [
            'name' => 'Barista Baru',
            'email' => 'barista.baru@coffeeshop.test',
            'password' => 'password123',
            'role' => 'dapur',
            'phone' => '0899999999',
        ]);

        $response->assertRedirect('/owner/users');
        $this->assertDatabaseHas('users', ['email' => 'barista.baru@coffeeshop.test']);

        $newUser = User::where('email', 'barista.baru@coffeeshop.test')->first();

        // Ban user
        $banResponse = $this->actingAs($owner)->patch("/owner/users/{$newUser->id}/toggle-ban");
        $banResponse->assertRedirect();
        $this->assertTrue($newUser->fresh()->is_banned);

        // Banned user cannot access
        $bannedAttempt = $this->actingAs($newUser->fresh())->get('/dapur/dashboard');
        $bannedAttempt->assertRedirect('/login');
    }

    public function test_owner_can_approve_procurement_po()
    {
        $owner = User::where('role', 'owner')->first();
        $pendingPo = Procurement::where('status', 'pending')->first();

        $response = $this->actingAs($owner)->post("/owner/procurements/{$pendingPo->id}/approve");
        $response->assertRedirect();

        $this->assertEquals('approved', $pendingPo->fresh()->status);
        $this->assertEquals($owner->id, $pendingPo->fresh()->approved_by);
    }

    public function test_owner_reports_are_accessible()
    {
        $owner = User::where('role', 'owner')->first();

        $this->actingAs($owner)->get('/owner/reports/sales')->assertStatus(200);
        $this->actingAs($owner)->get('/owner/reports/stock')->assertStatus(200);
        $this->actingAs($owner)->get('/owner/reports/procurement')->assertStatus(200);
        $this->actingAs($owner)->get('/owner/reports/profit-loss')->assertStatus(200);
    }

    public function test_gudang_can_receive_inbound_stock_from_supplier()
    {
        $gudang = User::where('role', 'gudang')->first();
        $approvedPo = Procurement::where('status', 'approved')->first();

        if (!$approvedPo) {
            $approvedPo = Procurement::where('status', 'pending')->first();
            $approvedPo->update(['status' => 'approved', 'approved_by' => 1]);
        }

        $item = $approvedPo->items->first();
        $initialGudangStock = $item->ingredient->gudang_quantity;

        $response = $this->actingAs($gudang)->post("/gudang/inbound/{$approvedPo->id}/receive", [
            'received' => [
                $item->id => 500,
            ],
            'notes' => 'Diterima utuh',
        ]);

        $response->assertRedirect('/gudang/inbound');
        $this->assertEquals($initialGudangStock + 500, $item->ingredient->fresh()->gudang_quantity);
    }

    public function test_gudang_can_dispatch_to_toko_and_kasir_can_receive_it()
    {
        $gudang = User::where('role', 'gudang')->first();
        $kasir = User::where('role', 'kasir')->first();
        $coffee = Ingredient::where('code', 'ING-001')->first();

        $initialTokoStock = $coffee->toko_quantity;

        // 1. Gudang dispatches
        $response = $this->actingAs($gudang)->post('/gudang/transfers', [
            'notes' => 'Kirim stok kopi ke kasir',
            'items' => [
                [
                    'ingredient_id' => $coffee->id,
                    'quantity' => 1000,
                ]
            ]
        ]);

        $response->assertRedirect('/gudang/transfers');
        $transfer = StockTransfer::latest('id')->first();
        $this->assertEquals('dispatched', $transfer->status);

        // 2. Kasir receives
        $receiveResponse = $this->actingAs($kasir)->post("/kasir/transfers/{$transfer->id}/receive");
        $receiveResponse->assertRedirect();

        $this->assertEquals('received', $transfer->fresh()->status);
        $this->assertEquals($kasir->id, $transfer->fresh()->received_by);
        $this->assertEquals($initialTokoStock + 1000, $coffee->fresh()->toko_quantity);
    }

    public function test_kasir_can_create_menu_with_precise_bom_recipes()
    {
        $kasir = User::where('role', 'kasir')->first();
        $cat = Category::first();
        $coffee = Ingredient::where('code', 'ING-001')->first();
        $milk = Ingredient::where('code', 'ING-002')->first();

        $response = $this->actingAs($kasir)->post('/kasir/menus', [
            'category_id' => $cat->id,
            'name' => 'Kopi Susu Uji Coba',
            'price' => 25000,
            'description' => 'Menu racikan baru',
            'is_available' => true,
            'recipes' => [
                ['ingredient_id' => $coffee->id, 'amount' => 18.50],
                ['ingredient_id' => $milk->id, 'amount' => 150.00],
            ]
        ]);

        $response->assertRedirect('/kasir/menus');
        $this->assertDatabaseHas('menus', ['name' => 'Kopi Susu Uji Coba']);

        $menu = Menu::where('name', 'Kopi Susu Uji Coba')->first();
        $this->assertCount(2, $menu->recipes);
    }

    public function test_precision_stock_deduction_on_order_payment()
    {
        $menu = Menu::where('name', 'Es Kopi Susu Senja Aren')->first();
        $coffee = Ingredient::where('code', 'ING-001')->first();
        $milk = Ingredient::where('code', 'ING-002')->first();
        $sugar = Ingredient::where('code', 'ING-004')->first();

        $initCoffee = $coffee->toko_quantity;
        $initMilk = $milk->toko_quantity;
        $initSugar = $sugar->toko_quantity;

        // Place customer order: 2 cups
        $order = Order::create([
            'order_number' => 'TEST-ORD-001',
            'customer_name' => 'Tester',
            'table_number' => 'Meja 02',
            'order_type' => 'dine_in',
            'total_amount' => $menu->price * 2,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'pending',
            'order_status' => 'pending_payment',
        ]);

        $order->items()->create([
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'price' => $menu->price,
            'quantity' => 2,
            'subtotal' => $menu->price * 2,
        ]);

        // Simulate payment success
        $order->markAsPaid('SIM-MIDTRANS-123');

        // Check precise stock deduction:
        // Recipe: 18g coffee x 2 = 36g
        // 120ml milk x 2 = 240ml
        // 25ml sugar x 2 = 50ml
        $this->assertEquals($initCoffee - 36.00, $coffee->fresh()->toko_quantity);
        $this->assertEquals($initMilk - 240.00, $milk->fresh()->toko_quantity);
        $this->assertEquals($initSugar - 50.00, $sugar->fresh()->toko_quantity);

        // Verify kitchen notification is created
        $this->assertDatabaseHas('shop_notifications', [
            'type' => 'order_paid_for_kitchen',
            'target_role' => 'dapur',
        ]);
    }

    public function test_kitchen_can_mark_done_and_notify_cashier()
    {
        $dapur = User::where('role', 'dapur')->first();
        $order = Order::where('order_status', 'in_kitchen')->first();

        $response = $this->actingAs($dapur)->post("/dapur/orders/{$order->id}/done");
        $response->assertRedirect();

        $this->assertEquals('ready', $order->fresh()->order_status);

        // Verify notification to cashier is created
        $this->assertDatabaseHas('shop_notifications', [
            'type' => 'order_ready_for_cashier',
            'target_role' => 'kasir',
        ]);
    }

    public function test_cashier_can_scan_cash_payment_token()
    {
        $kasir = User::where('role', 'kasir')->first();
        $order = Order::create([
            'order_number' => 'CASH-ORD-099',
            'customer_name' => 'Pak Joko',
            'table_number' => 'Meja 04',
            'order_type' => 'dine_in',
            'total_amount' => 50000,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'pending_payment',
            'payment_token' => 'CASH-TOKEN-TEST',
        ]);

        $response = $this->actingAs($kasir)->post('/kasir/payment/process', [
            'order_identifier' => 'CASH-TOKEN-TEST',
            'cash_tendered' => 50000,
        ]);

        $response->assertRedirect();
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals('in_kitchen', $order->fresh()->order_status);
        $this->assertEquals(50000, $order->fresh()->cash_tendered);
        $this->assertEquals(0, $order->fresh()->change_amount);
    }

    public function test_kasir_pos_order_with_cash_tendered_and_change()
    {
        $kasir = User::where('role', 'kasir')->first();
        $menu = Menu::first(); // e.g. price 22000

        $response = $this->actingAs($kasir)->post('/kasir/pos/order', [
            'customer_name' => 'Pelanggan POS Tunai',
            'order_type' => 'dine_in',
            'table_number' => 'Meja 03',
            'payment_method' => 'cash',
            'cash_tendered' => 50000,
            'items' => [
                [
                    'menu_id' => $menu->id,
                    'quantity' => 1,
                    'notes' => 'Gula sedikit',
                ]
            ],
        ]);

        $response->assertRedirect('/kasir/pos');
        $response->assertSessionHas('success');

        $order = Order::where('customer_name', 'Pelanggan POS Tunai')->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('in_kitchen', $order->order_status);
        $this->assertEquals($menu->price, $order->total_amount);
        $this->assertEquals(50000, $order->cash_tendered);
        $this->assertEquals(50000 - $menu->price, $order->change_amount);

        // Verify notification to kitchen
        $this->assertDatabaseHas('shop_notifications', [
            'type' => 'order_paid_for_kitchen',
            'target_role' => 'dapur',
        ]);
    }
}

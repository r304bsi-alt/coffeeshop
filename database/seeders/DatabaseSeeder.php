<?php

namespace Database\Seeders;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuRecipe;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\ShopNotification;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users for each role
        $owner = User::create([
            'name' => 'Bapak Hartono (Owner)',
            'email' => 'owner@coffeeshop.test',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'phone' => '081234567890',
            'is_banned' => false,
        ]);

        $gudang = User::create([
            'name' => 'Mas Rudi (Gudang)',
            'email' => 'gudang@coffeeshop.test',
            'password' => Hash::make('password123'),
            'role' => 'gudang',
            'phone' => '081234567891',
            'is_banned' => false,
        ]);

        $pengadaan = User::create([
            'name' => 'Mba Sinta (Pengadaan)',
            'email' => 'pengadaan@coffeeshop.test',
            'password' => Hash::make('password123'),
            'role' => 'pengadaan',
            'phone' => '081234567892',
            'is_banned' => false,
        ]);

        $kasir = User::create([
            'name' => 'Dina Kasir (Toko)',
            'email' => 'kasir@coffeeshop.test',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'phone' => '081234567893',
            'is_banned' => false,
        ]);

        $dapur = User::create([
            'name' => 'Chef Bayu (Barista/Dapur)',
            'email' => 'dapur@coffeeshop.test',
            'password' => Hash::make('password123'),
            'role' => 'dapur',
            'phone' => '081234567894',
            'is_banned' => false,
        ]);

        $customer = User::create([
            'name' => 'Andi Pratama (Customer)',
            'email' => 'customer@coffeeshop.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'phone' => '081234567895',
            'is_banned' => false,
        ]);

        // 2. Categories
        $catSignature = Category::create([
            'name' => 'Signature Coffee',
            'slug' => 'signature-coffee',
            'icon' => 'fa-mug-hot',
            'is_active' => true,
        ]);

        $catEspresso = Category::create([
            'name' => 'Espresso Based',
            'slug' => 'espresso-based',
            'icon' => 'fa-coffee',
            'is_active' => true,
        ]);

        $catNonCoffee = Category::create([
            'name' => 'Non-Coffee & Tea',
            'slug' => 'non-coffee-tea',
            'icon' => 'fa-leaf',
            'is_active' => true,
        ]);

        $catPastry = Category::create([
            'name' => 'Pastry & Bakery',
            'slug' => 'pastry-bakery',
            'icon' => 'fa-bread-slice',
            'is_active' => true,
        ]);

        // 3. Ingredients (Bahan Baku)
        $ingredientsData = [
            ['code' => 'ING-001', 'name' => 'Biji Kopi Espresso Blend (Arabica-Robusta)', 'unit' => 'gram', 'minimum_stock' => 1000, 'cost_per_unit' => 180, 'gudang' => 20000, 'toko' => 2500],
            ['code' => 'ING-002', 'name' => 'Susu Segar (Fresh Milk Greenfield)', 'unit' => 'ml', 'minimum_stock' => 3000, 'cost_per_unit' => 24, 'gudang' => 30000, 'toko' => 6000],
            ['code' => 'ING-003', 'name' => 'Susu Gandum (Oat Milk Barista)', 'unit' => 'ml', 'minimum_stock' => 2000, 'cost_per_unit' => 45, 'gudang' => 15000, 'toko' => 3000],
            ['code' => 'ING-004', 'name' => 'Sirup Gula Aren Organik', 'unit' => 'ml', 'minimum_stock' => 1500, 'cost_per_unit' => 30, 'gudang' => 10000, 'toko' => 2000],
            ['code' => 'ING-005', 'name' => 'Sirup Karamel Monin', 'unit' => 'ml', 'minimum_stock' => 500, 'cost_per_unit' => 120, 'gudang' => 5000, 'toko' => 1200],
            ['code' => 'ING-006', 'name' => 'Sirup Vanilla Monin', 'unit' => 'ml', 'minimum_stock' => 500, 'cost_per_unit' => 120, 'gudang' => 4000, 'toko' => 1000],
            ['code' => 'ING-007', 'name' => 'Bubuk Matcha Premium Uji', 'unit' => 'gram', 'minimum_stock' => 400, 'cost_per_unit' => 650, 'gudang' => 3000, 'toko' => 600],
            ['code' => 'ING-008', 'name' => 'Bubuk Dark Chocolate Belgia', 'unit' => 'gram', 'minimum_stock' => 500, 'cost_per_unit' => 320, 'gudang' => 4000, 'toko' => 800],
            ['code' => 'ING-009', 'name' => 'Croissant Mentega Beku', 'unit' => 'pcs', 'minimum_stock' => 15, 'cost_per_unit' => 11000, 'gudang' => 80, 'toko' => 20],
        ];

        $ingredientsMap = [];
        foreach ($ingredientsData as $ing) {
            $createdIng = Ingredient::create([
                'code' => $ing['code'],
                'name' => $ing['name'],
                'unit' => $ing['unit'],
                'minimum_stock' => $ing['minimum_stock'],
                'cost_per_unit' => $ing['cost_per_unit'],
            ]);
            $ingredientsMap[$ing['code']] = $createdIng;

            // Gudang stock
            Stock::create([
                'ingredient_id' => $createdIng->id,
                'location' => 'gudang',
                'quantity' => $ing['gudang'],
            ]);

            // Toko stock
            Stock::create([
                'ingredient_id' => $createdIng->id,
                'location' => 'toko',
                'quantity' => $ing['toko'],
            ]);

            // Initial mutation logs
            StockMutation::create([
                'ingredient_id' => $createdIng->id,
                'location' => 'gudang',
                'type' => 'inbound_supplier',
                'reference_number' => 'INITIAL-SETUP',
                'quantity_change' => $ing['gudang'],
                'balance_before' => 0,
                'balance_after' => $ing['gudang'],
                'user_id' => $gudang->id,
                'notes' => 'Saldo awal stok gudang saat setup sistem',
            ]);

            StockMutation::create([
                'ingredient_id' => $createdIng->id,
                'location' => 'toko',
                'type' => 'transfer_in',
                'reference_number' => 'INITIAL-SETUP',
                'quantity_change' => $ing['toko'],
                'balance_before' => 0,
                'balance_after' => $ing['toko'],
                'user_id' => $kasir->id,
                'notes' => 'Saldo awal stok toko kasir saat setup sistem',
            ]);
        }

        // 4. Menus & Recipes (Bill of Materials)
        $menus = [
            [
                'category_id' => $catSignature->id,
                'name' => 'Es Kopi Susu Senja Aren',
                'slug' => 'es-kopi-susu-senja-aren',
                'description' => 'Espresso ganda mantap dipadu susu segar pilihan dan gula aren asli yang manis legit.',
                'price' => 22000,
                'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-001', 'amount' => 18.00], // 18g espresso blend
                    ['code' => 'ING-002', 'amount' => 120.00], // 120ml fresh milk
                    ['code' => 'ING-004', 'amount' => 25.00],  // 25ml gula aren
                ],
            ],
            [
                'category_id' => $catEspresso->id,
                'name' => 'Caffe Latte Barista Blend',
                'slug' => 'caffe-latte-barista-blend',
                'description' => 'Kombinasi klasik espresso lembut dengan susu steamed yang bertekstur creamy.',
                'price' => 28000,
                'image' => 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-001', 'amount' => 18.00],
                    ['code' => 'ING-002', 'amount' => 180.00],
                ],
            ],
            [
                'category_id' => $catEspresso->id,
                'name' => 'Caramel Macchiato Gold',
                'slug' => 'caramel-macchiato-gold',
                'description' => 'Lapisan vanilla lembut, steamed milk, shot espresso kaya aroma dan drizzle karamel premium.',
                'price' => 34000,
                'image' => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-001', 'amount' => 18.00],
                    ['code' => 'ING-002', 'amount' => 160.00],
                    ['code' => 'ING-005', 'amount' => 20.00],
                    ['code' => 'ING-006', 'amount' => 10.00],
                ],
            ],
            [
                'category_id' => $catEspresso->id,
                'name' => 'Oat Milk Latte Artisan',
                'slug' => 'oat-milk-latte-artisan',
                'description' => 'Alternatif nabati terbaik bagi pencinta kopi vegan. Gurih alami dan lembut di tenggorokan.',
                'price' => 35000,
                'image' => 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-001', 'amount' => 18.00],
                    ['code' => 'ING-003', 'amount' => 180.00],
                ],
            ],
            [
                'category_id' => $catEspresso->id,
                'name' => 'Americano On The Rocks',
                'slug' => 'americano-on-the-rocks',
                'description' => 'Ekstrak espresso bold dengan aroma nutty dan cokelat yang menyegarkan dahaga.',
                'price' => 20000,
                'image' => 'https://images.unsplash.com/photo-1551030173-122aabc4489c?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-001', 'amount' => 18.00],
                ],
            ],
            [
                'category_id' => $catNonCoffee->id,
                'name' => 'Uji Kyoto Matcha Latte',
                'slug' => 'uji-kyoto-matcha-latte',
                'description' => 'Matcha ceremonial grade dari Kyoto dipadukan dengan kelembutan fresh milk.',
                'price' => 32000,
                'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-007', 'amount' => 12.00],
                    ['code' => 'ING-002', 'amount' => 180.00],
                ],
            ],
            [
                'category_id' => $catNonCoffee->id,
                'name' => 'Belgian Dark Choco Supreme',
                'slug' => 'belgian-dark-choco-supreme',
                'description' => 'Cokelat hitam Belgia asli dengan kekayaan cita rasa manis dan pahit yang harmonis.',
                'price' => 30000,
                'image' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-008', 'amount' => 25.00],
                    ['code' => 'ING-002', 'amount' => 180.00],
                ],
            ],
            [
                'category_id' => $catPastry->id,
                'name' => 'Golden Butter Croissant',
                'slug' => 'golden-butter-croissant',
                'description' => 'Pastry khas Perancis yang dipanggang renyah berlayer dengan aroma mentega murni.',
                'price' => 24000,
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=600&q=80',
                'is_available' => true,
                'recipes' => [
                    ['code' => 'ING-009', 'amount' => 1.00],
                ],
            ],
        ];

        $createdMenus = [];
        foreach ($menus as $m) {
            $createdMenu = Menu::create([
                'category_id' => $m['category_id'],
                'name' => $m['name'],
                'slug' => $m['slug'],
                'description' => $m['description'],
                'price' => $m['price'],
                'image' => $m['image'],
                'is_available' => $m['is_available'],
            ]);
            $createdMenus[] = $createdMenu;

            foreach ($m['recipes'] as $rec) {
                if (isset($ingredientsMap[$rec['code']])) {
                    MenuRecipe::create([
                        'menu_id' => $createdMenu->id,
                        'ingredient_id' => $ingredientsMap[$rec['code']]->id,
                        'amount' => $rec['amount'],
                    ]);
                }
            }
        }

        // 5. Tables (Meja 01 s/d Meja 10)
        for ($i = 1; $i <= 10; $i++) {
            $tableNum = sprintf('%02d', $i);
            CafeTable::create([
                'table_number' => "Meja {$tableNum}",
                'qr_token' => 'TBL-QR-' . strtoupper(Str::random(10)),
                'status' => $i === 3 ? 'occupied' : 'available',
            ]);
        }

        // 6. Procurements (PO)
        // PO 1: Approved and Completed
        $po1 = Procurement::create([
            'po_number' => 'PO-20260920-001',
            'created_by' => $pengadaan->id,
            'supplier_name' => 'PT Roastery Nusantara Abadi',
            'supplier_email' => 'order@roasterynusantara.com',
            'supplier_phone' => '021-5551234',
            'status' => 'completed',
            'approved_by' => $owner->id,
            'approved_at' => now()->subDays(10),
            'total_cost' => 3600000,
            'notes' => 'Pengadaan rutin biji kopi espresso',
        ]);

        ProcurementItem::create([
            'procurement_id' => $po1->id,
            'ingredient_id' => $ingredientsMap['ING-001']->id,
            'quantity_requested' => 20000, // 20 kg
            'quantity_received' => 20000,
            'unit_price' => 180,
            'subtotal' => 3600000,
        ]);

        // PO 2: Pending Owner Approval
        $po2 = Procurement::create([
            'po_number' => 'PO-20260929-002',
            'created_by' => $pengadaan->id,
            'supplier_name' => 'CV Dairy Fresh Sentosa',
            'supplier_email' => 'sales@dairyfresh.co.id',
            'supplier_phone' => '08119876543',
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'total_cost' => 1140000,
            'notes' => 'Pengadaan stok susu menjelang weekend',
        ]);

        ProcurementItem::create([
            'procurement_id' => $po2->id,
            'ingredient_id' => $ingredientsMap['ING-002']->id,
            'quantity_requested' => 30000, // 30 liter
            'quantity_received' => 0,
            'unit_price' => 24,
            'subtotal' => 720000,
        ]);

        ProcurementItem::create([
            'procurement_id' => $po2->id,
            'ingredient_id' => $ingredientsMap['ING-004']->id,
            'quantity_requested' => 14000,
            'quantity_received' => 0,
            'unit_price' => 30,
            'subtotal' => 420000,
        ]);

        // 7. Stock Transfer (Gudang -> Toko)
        $transfer1 = StockTransfer::create([
            'transfer_number' => 'TRF-20260929-001',
            'dispatched_by' => $gudang->id,
            'received_by' => $kasir->id,
            'status' => 'received',
            'dispatch_date' => now()->subDay(),
            'receipt_date' => now()->subDay()->addHours(1),
            'notes' => 'Kebutuhan stok operasional kasir toko',
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer1->id,
            'ingredient_id' => $ingredientsMap['ING-001']->id,
            'quantity' => 2000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer1->id,
            'ingredient_id' => $ingredientsMap['ING-002']->id,
            'quantity' => 5000,
        ]);

        // 8. Orders for demonstration
        // Order 1: Completed earlier
        $order1 = Order::create([
            'order_number' => 'ORD-20260930-001',
            'user_id' => $customer->id,
            'customer_name' => 'Budi Santoso',
            'table_number' => 'Meja 01',
            'order_type' => 'dine_in',
            'total_amount' => 50000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'payment_reference' => 'MIDTRANS-QRIS-991283',
            'paid_at' => now()->subMinutes(50),
            'kitchen_done_at' => now()->subMinutes(35),
            'cashier_called_at' => now()->subMinutes(34),
            'cashier_id' => $kasir->id,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'menu_id' => $createdMenus[0]->id,
            'menu_name' => $createdMenus[0]->name,
            'price' => $createdMenus[0]->price,
            'quantity' => 1,
            'subtotal' => 22000,
            'notes' => 'Less sugar',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'menu_id' => $createdMenus[1]->id,
            'menu_name' => $createdMenus[1]->name,
            'price' => $createdMenus[1]->price,
            'quantity' => 1,
            'subtotal' => 28000,
            'notes' => 'Hot',
        ]);

        // Order 2: In Kitchen right now! (FIFO demonstration for Bagian Dapur)
        $order2 = Order::create([
            'order_number' => 'ORD-20260930-002',
            'user_id' => $customer->id,
            'customer_name' => 'Citra Lestari',
            'table_number' => 'Meja 03',
            'order_type' => 'dine_in',
            'total_amount' => 58000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_status' => 'in_kitchen',
            'payment_reference' => 'CASH-REC-0021',
            'paid_at' => now()->subMinutes(12),
            'kitchen_done_at' => null,
            'cashier_id' => $kasir->id,
            'notes' => 'Tolong sedotannya 2 ya kak',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'menu_id' => $createdMenus[2]->id, // Caramel Macchiato
            'menu_name' => $createdMenus[2]->name,
            'price' => $createdMenus[2]->price,
            'quantity' => 1,
            'subtotal' => 34000,
            'notes' => 'Extra drizzle karamel',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'menu_id' => $createdMenus[7]->id, // Croissant
            'menu_name' => $createdMenus[7]->name,
            'price' => $createdMenus[7]->price,
            'quantity' => 1,
            'subtotal' => 24000,
            'notes' => 'Hangatkan sebentar',
        ]);

        // Order 3: Ready for Cashier Call demonstration
        $order3 = Order::create([
            'order_number' => 'ORD-20260930-003',
            'user_id' => null,
            'customer_name' => 'Doni Kusuma',
            'table_number' => 'Meja 05',
            'order_type' => 'dine_in',
            'total_amount' => 32000,
            'payment_method' => 'midtrans_qris',
            'payment_status' => 'paid',
            'order_status' => 'ready',
            'payment_reference' => 'MIDTRANS-QRIS-991285',
            'paid_at' => now()->subMinutes(18),
            'kitchen_done_at' => now()->subMinutes(2),
            'cashier_called_at' => null,
            'cashier_id' => null,
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'menu_id' => $createdMenus[5]->id, // Matcha Latte
            'menu_name' => $createdMenus[5]->name,
            'price' => $createdMenus[5]->price,
            'quantity' => 1,
            'subtotal' => 32000,
            'notes' => 'Iced Matcha',
        ]);

        // Notifications
        ShopNotification::create([
            'type' => 'order_paid_for_kitchen',
            'title' => 'Pesanan Masuk #ORD-20260930-002',
            'message' => 'Pesanan #ORD-20260930-002 untuk Meja 03 telah dibayar dan siap disiapkan.',
            'target_role' => 'dapur',
            'data' => [
                'order_id' => $order2->id,
                'order_number' => $order2->order_number,
                'table_number' => $order2->table_number,
                'customer_name' => $order2->customer_name,
            ],
            'is_read' => false,
        ]);

        ShopNotification::create([
            'type' => 'order_ready_for_cashier',
            'title' => 'Pesanan Siap Dipanggil #ORD-20260930-003',
            'message' => 'Pesanan #ORD-20260930-003 untuk Meja 05 telah selesai disiapkan oleh Dapur. Silakan panggil customer!',
            'target_role' => 'kasir',
            'data' => [
                'order_id' => $order3->id,
                'order_number' => $order3->order_number,
                'table_number' => $order3->table_number,
                'customer_name' => $order3->customer_name,
                'items' => '1x Uji Kyoto Matcha Latte',
            ],
            'is_read' => false,
        ]);
    }
}

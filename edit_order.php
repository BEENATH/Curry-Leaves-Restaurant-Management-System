<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';


if (!isset($_GET['id'])) {
    header("Location: view_orders.php");
    exit;
}
$order_id = intval($_GET['id']);


$sql = "SELECT * FROM orders WHERE order_id = $order_id";
$order_res = $conn->query($sql);
if ($order_res->num_rows == 0) {
    header("Location: view_orders.php");
    exit;
}
$order_data = $order_res->fetch_assoc();


$customers = [];
$c_sql = "SELECT customer_id, name, phone, address FROM customers ORDER BY name";
$c_res = $conn->query($c_sql);
while($row = $c_res->fetch_assoc()) { $customers[] = $row; }


$menu_items = [];
$m_sql = "SELECT item_id, name, price, category, image_path FROM menu_items WHERE is_available = 1 ORDER BY category, name";
$m_res = $conn->query($m_sql);
while($row = $m_res->fetch_assoc()) {
    if (empty($row['image_path'])) $row['image_path'] = 'uploads/default-food.jpg';
    $menu_items[] = $row;
}


$current_items = [];
$items_sql = "SELECT oi.*, mi.name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.item_id WHERE oi.order_id = $order_id";
$items_res = $conn->query($items_sql);
while($row = $items_res->fetch_assoc()) {
    $current_items[] = [
        'id' => $row['menu_item_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity']
    ];
}

$error = '';
$success = '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_type = $_POST["order_type"];
    $notes = mysqli_real_escape_string($conn, $_POST["notes"] ?? '');
    $items = json_decode($_POST["items"], true);
    $customer_id = intval($_POST["customer_id"]);
    $delivery_address = mysqli_real_escape_string($conn, $_POST["delivery_address"] ?? '');
    $customer_phone = mysqli_real_escape_string($conn, $_POST["customer_phone"] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    if (empty($items)) {
        $error = "Cart cannot be empty";
    } else {
        $total = 0;
        foreach ($items as $item) { $total += $item['price'] * $item['quantity']; }
        
        $conn->autocommit(FALSE);
        try {
            
            $stmt = $conn->prepare("UPDATE orders SET customer_id=?, order_type=?, total=?, notes=?, delivery_address=?, customer_phone=?, status=? WHERE order_id=?");
            $stmt->bind_param("isdssssi", $customer_id, $order_type, $total, $notes, $delivery_address, $customer_phone, $status, $order_id);
            $stmt->execute();
            
            
            $conn->query("DELETE FROM order_items WHERE order_id = $order_id");
            foreach ($items as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            
            $conn->commit();
            $success = "Order #$order_id updated successfully!";
            echo "<script>setTimeout(() => { window.location.href='view_orders.php'; }, 1500);</script>";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Update failed: " . $e->getMessage();
        }
        $conn->autocommit(TRUE);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order #<?= $order_id ?> | Curry Leaves</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; height: 100vh; overflow: hidden; }
        .price-text { font-family: 'Inter', sans-serif; color: #064e3b; font-weight: 800; }
        .order-panel-fixed {
            height: calc(100vh - 100px);
            display: flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid #cbd5e1;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
        }
        .active-type { border: 2px solid #10b981 !important; background: #ecfdf5 !important; color: #065f46 !important; }
        #cartItems::-webkit-scrollbar { width: 4px; }
        #cartItems::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .menu-grid-scroll { height: calc(100vh - 180px); overflow-y: auto; padding-right: 10px; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 bg-white border-r border-slate-200 hidden lg:flex flex-col fixed h-full z-50">
        <div class="p-8"><div class="text-emerald-600 font-extrabold text-2xl"><i class="fas fa-leaf"></i> <span>Curry Leaves</span></div></div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50"><i class="fas fa-home"></i> Dashboard</a>
            <a href="view_orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-600 text-white font-bold"><i class="fas fa-receipt"></i> Order History</a>
        </nav>
    </aside>

    <main class="flex-1 lg:ml-64 p-6 overflow-hidden">
        <?php if($success): ?>
            <div class="fixed top-6 right-6 z-[100] bg-emerald-500 text-white p-4 rounded-2xl shadow-2xl flex items-center gap-3 animate-bounce">
                <i class="fas fa-check-circle text-xl"></i> <span><?= $success ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 h-full">
            <div class="xl:col-span-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-900">Modify Items</h1>
                        <p class="text-slate-500 text-xs">Edit items for Order #<?= $order_id ?></p>
                    </div>
                    <select id="categoryFilter" class="bg-white border border-slate-300 rounded-xl px-4 py-2 text-sm font-bold shadow-sm">
                        <option value="">All Categories</option>
                        <?php foreach(array_unique(array_column($menu_items, 'category')) as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="menu-grid-scroll">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($menu_items as $item): ?>
                            <div class="menu-card bg-white rounded-3xl border border-slate-200 p-3 shadow-sm hover:shadow-md transition-all group" data-category="<?= htmlspecialchars($item['category']) ?>">
                                <div class="h-28 mb-3 overflow-hidden rounded-2xl">
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                </div>
                                <div class="mb-3 px-1">
                                    <h3 class="font-bold text-slate-800 text-xs truncate"><?= htmlspecialchars($item['name']) ?></h3>
                                    <p class="price-text text-sm">Rs. <?= number_format($item['price'], 0) ?></p>
                                </div>
                                <button onclick="addToCart(<?= $item['item_id'] ?>)" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black py-3 rounded-xl transition-all uppercase">
                                    Add Item
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-4">
                <div class="order-panel-fixed rounded-[2rem] p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">EDIT PANEL</h2>
                        <a href="view_orders.php" class="text-[10px] font-bold text-slate-400 uppercase">Back</a>
                    </div>

                    <form id="orderForm" method="POST" onsubmit="return validateForm()" class="flex flex-col h-full overflow-hidden">
                        
                        <div class="flex-none space-y-3 mb-4">
                            <div class="flex gap-2 items-center bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="text-[9px] font-bold text-slate-400 uppercase px-2">Status:</span>
                                <select name="status" class="flex-1 bg-transparent border-0 text-xs font-black text-emerald-700 focus:ring-0">
                                    <option value="pending" <?= $order_data['status']=='pending'?'selected':'' ?>>PENDING</option>
                                    <option value="processing" <?= $order_data['status']=='processing'?'selected':'' ?>>PROCESSING</option>
                                    <option value="completed" <?= $order_data['status']=='completed'?'selected':'' ?>>COMPLETED</option>
                                    <option value="cancelled" <?= $order_data['status']=='cancelled'?'selected':'' ?>>CANCELLED</option>
                                </select>
                            </div>

                            <select name="customer_id" id="customerSelect" class="w-full bg-slate-100 border-0 rounded-xl px-4 py-3 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['customer_id'] ?>" 
                                            data-address="<?= htmlspecialchars($c['address']) ?>" 
                                            data-phone="<?= htmlspecialchars($c['phone']) ?>"
                                            <?= $order_data['customer_id'] == $c['customer_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="grid grid-cols-3 gap-2">
                                <?php foreach(['dine_in' => 'chair', 'delivery' => 'truck', 'takeaway' => 'bag-shopping'] as $type => $icon): ?>
                                <label class="order-type-btn border border-slate-200 rounded-xl p-2 text-center cursor-pointer flex flex-col items-center transition-all <?= ($order_data['order_type'] == $type) ? 'active-type' : '' ?>">
                                    <input type="radio" name="order_type" value="<?= $type ?>" class="hidden" <?= ($order_data['order_type'] == $type) ? 'checked' : '' ?> onchange="toggleType(this)">
                                    <i class="fas fa-<?= $icon ?> text-xs mb-1"></i>
                                    <span class="text-[8px] font-black uppercase"><?= str_replace('_', ' ', $type) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div id="cartItems" class="flex-grow overflow-y-auto space-y-2 pr-1 mb-4">
                            </div>

                        <div class="flex-none pt-4 border-t border-slate-100 bg-white">
                            <div id="deliveryFields" class="<?= $order_data['order_type'] != 'delivery' ? 'hidden' : '' ?> space-y-2 mb-3 bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                                <input type="text" name="customer_phone" id="display_phone" value="<?= htmlspecialchars($order_data['customer_phone']) ?>" placeholder="Phone" class="w-full bg-white border border-emerald-100 rounded-lg px-3 py-2 text-xs font-bold">
                                <textarea name="delivery_address" id="display_address" placeholder="Address" class="w-full bg-white border border-emerald-100 rounded-lg px-3 py-2 text-xs font-bold h-14"><?= htmlspecialchars($order_data['delivery_address']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <input type="text" name="notes" value="<?= htmlspecialchars($order_data['notes']) ?>" placeholder="Update Notes..." class="w-full bg-slate-50 border-0 rounded-lg px-4 py-2 text-xs">
                            </div>

                            <div class="flex justify-between items-center mb-4 px-1">
                                <span class="text-xs font-bold text-slate-400 uppercase">New Total</span>
                                <span id="cartTotal" class="text-xl font-black text-slate-900 tracking-tighter">Rs. 0</span>
                            </div>
                            
                            <input type="hidden" id="cartData" name="items" value="">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-2xl shadow-xl transition-all uppercase tracking-widest text-xs">
                                Update Order #<?= $order_id ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Load current items from PHP into JS
        let cart = <?= json_encode($current_items) ?>;
        const menuItems = <?= json_encode(array_column($menu_items, null, 'item_id')); ?>;

        // Auto-fill Logic
        document.getElementById('customerSelect').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            document.getElementById('display_phone').value = opt.dataset.phone || '';
            document.getElementById('display_address').value = opt.dataset.address || '';
        });

        function addToCart(itemId) {
            const item = menuItems[itemId];
            const existing = cart.find(i => i.id == itemId);
            if (existing) { existing.quantity++; } 
            else { cart.push({ id: itemId, name: item.name, price: parseFloat(item.price), quantity: 1 }); }
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const container = document.getElementById('cartItems');
            const totalEl = document.getElementById('cartTotal');
            let total = 0;
            
            container.innerHTML = '';
            cart.forEach((item, idx) => {
                total += item.price * item.quantity;
                container.innerHTML += `
                    <div class="flex items-center justify-between bg-white border border-slate-200 p-3 rounded-2xl shadow-sm">
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-[11px] font-black text-slate-800 truncate">${item.name}</p>
                            <p class="text-[10px] font-bold text-emerald-600">Rs. ${parseFloat(item.price).toLocaleString()}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="updateQty(${idx}, -1)" class="w-6 h-6 bg-slate-50 border rounded flex items-center justify-center text-xs hover:bg-rose-50">-</button>
                            <span class="text-xs font-black text-slate-700">${item.quantity}</span>
                            <button type="button" onclick="updateQty(${idx}, 1)" class="w-6 h-6 bg-slate-50 border rounded flex items-center justify-center text-xs hover:bg-emerald-50">+</button>
                        </div>
                    </div>`;
            });
            
            totalEl.innerText = 'Rs. ' + total.toLocaleString();
            document.getElementById('cartData').value = JSON.stringify(cart);
        }

        function updateQty(idx, delta) {
            cart[idx].quantity += delta;
            if (cart[idx].quantity <= 0) cart.splice(idx, 1);
            updateCartDisplay();
        }

        function toggleType(radio) {
            document.querySelectorAll('.order-type-btn').forEach(btn => btn.classList.remove('active-type'));
            radio.parentElement.classList.add('active-type');
            document.getElementById('deliveryFields').classList.toggle('hidden', radio.value !== 'delivery');
        }

        function validateForm() {
            if (cart.length === 0) { alert('Cart cannot be empty'); return false; }
            return true;
        }

        // Initialize display
        updateCartDisplay();

        // Category Filter
        document.getElementById('categoryFilter').addEventListener('change', function() {
            const cat = this.value;
            document.querySelectorAll('.menu-card').forEach(card => {
                card.style.display = (!cat || card.dataset.category === cat) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
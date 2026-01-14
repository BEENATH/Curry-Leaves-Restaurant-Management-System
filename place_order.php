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


if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit;
}


$error = '';
$success = '';
$order_type = 'dine_in';

$customers = [];
$customer_sql = "SELECT customer_id, name, phone, address FROM customers ORDER BY name";
$customer_res = $conn->query($customer_sql);
if ($customer_res && $customer_res->num_rows > 0) {
    while($row = $customer_res->fetch_assoc()) {
        $customers[] = $row;
    }
}

$menu_items = [];
$menu_sql = "SELECT item_id, name, price, category, image_path FROM menu_items WHERE is_available = 1 ORDER BY category, name";
$menu_res = $conn->query($menu_sql);
if ($menu_res && $menu_res->num_rows > 0) {
    while($row = $menu_res->fetch_assoc()) {
        if (empty($row['image_path'])) {
            $row['image_path'] = 'uploads/default-food.jpg';
        }
        $menu_items[] = $row;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_type = $_POST["order_type"] ?? 'dine_in';
    $notes = trim($_POST["notes"] ?? '');
    $items_json = $_POST["items"] ?? '[]';
    $items = json_decode($items_json, true);
    $customer_id = intval($_POST["customer_id"] ?? 0);
    $delivery_address = trim($_POST["delivery_address"] ?? '');
    $customer_phone = trim($_POST["customer_phone"] ?? '');

    if (empty($customer_id)) {
        $error = "Please select a customer";
    } elseif (empty($items)) {
        $error = "Please add items to the cart";
    } else {
        $total = 0;
        foreach ($items as $item) { 
            $total += ($item['price'] * $item['quantity']); 
        }
        
        $conn->autocommit(FALSE);
        try {
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_type, total, notes, delivery_address, customer_phone, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("isdsss", $customer_id, $order_type, $total, $notes, $delivery_address, $customer_phone);
            $stmt->execute();
            
            $order_id = $conn->insert_id;
            
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                
                $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }
            
            $conn->commit();
            $success = "Order #$order_id placed successfully!";
            
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed: " . $e->getMessage();
        }
        $conn->autocommit(TRUE);
    }
}

$page_title = "Place Order | Curry Leaves";
include 'includes/head.php';
?>

<style>
    .order-panel {
        height: calc(100vh - 140px);
        position: sticky;
        top: 100px;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-8 lg:p-12 transition-all duration-300">
    <div class="fade-in">
        
        
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">New Order</h1>
                    <p class="text-slate-500 mt-1">Select items to add to the customer's order</p>
                </div>
                
                <div class="flex gap-4 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="searchInput" placeholder="Search menu..." 
                               class="w-full pl-10 pr-4 py-3 bg-white border-0 shadow-sm rounded-xl text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 placeholder-slate-400">
                    </div>
                    <a href="view_orders.php" class="bg-slate-800 text-white px-5 py-3 rounded-xl font-bold hover:bg-slate-700 transition flex items-center shadow-lg shrink-0">
                        <i class="fas fa-history mr-2"></i> History
                    </a>
                </div>
            </div>

            
            <div class="flex gap-3 overflow-x-auto pb-4 scrollbar-hide snap-x" id="categoryContainer">
                <button onclick="filterCategory('')" class="category-pill active bg-slate-800 text-white px-5 py-2 rounded-full font-bold text-sm whitespace-nowrap shadow-md hover:scale-105 transition-transform snap-start">
                    All Items
                </button>
                <?php foreach(array_unique(array_column($menu_items, 'category')) as $cat): ?>
                    <button onclick="filterCategory('<?= htmlspecialchars($cat) ?>')" 
                            class="category-pill bg-white text-slate-600 px-5 py-2 rounded-full font-bold text-sm whitespace-nowrap shadow-sm border border-slate-100 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all snap-start"
                            data-category="<?= htmlspecialchars($cat) ?>">
                        <?= htmlspecialchars($cat) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if($success): ?>
            <div class="bg-emerald-100 text-emerald-800 p-4 rounded-2xl mb-8 flex items-center gap-3 border border-emerald-200 shadow-sm">
                <i class="fas fa-check-circle text-xl"></i> 
                <span class="font-bold"><?= $success ?></span>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-rose-100 text-rose-800 p-4 rounded-2xl mb-8 flex items-center gap-3 border border-rose-200 shadow-sm">
                <i class="fas fa-exclamation-triangle text-xl"></i> 
                <span class="font-bold"><?= $error ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            
            
            <div class="xl:col-span-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($menu_items as $item): ?>
                        <div class="menu-card bg-white rounded-[2rem] border border-slate-100 p-4 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group cursor-pointer" 
                             onclick="addToCart(<?= $item['item_id'] ?>)" 
                             data-category="<?= htmlspecialchars($item['category']) ?>">
                            
                            <div class="relative h-48 mb-4 overflow-hidden rounded-[1.5rem]">
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                                <button class="absolute bottom-3 right-3 bg-white text-emerald-600 h-10 w-10 rounded-full flex items-center justify-center shadow-lg transform translate-y-10 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                    <i class="fas fa-plus font-bold"></i>
                                </button>
                            </div>
                            
                            <div class="px-2 pb-2">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-slate-800 text-lg leading-tight line-clamp-2"><?= htmlspecialchars($item['name']) ?></h3>
                                    <span class="bg-emerald-50 text-emerald-700 text-xs font-black px-2 py-1 rounded-lg uppercase tracking-wide shrink-0 ml-2">
                                        <?= htmlspecialchars($item['category']) ?>
                                    </span>
                                </div>
                                <p class="text-xl font-black text-slate-900">Rs. <?= number_format($item['price'], 0) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            
            <div class="xl:col-span-4 relative">
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-5 order-panel flex flex-col">
                    <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-100">
                        <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-receipt text-emerald-500"></i> Current Order
                        </h2>
                        <button type="button" onclick="clearCart()" class="text-[10px] font-bold text-rose-500 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg transition-colors uppercase tracking-wider">
                            Clear
                        </button>
                    </div>

                    <form id="orderForm" method="POST" onsubmit="return validateForm()" class="flex flex-col flex-1 min-h-0 bg-white">
                        
                        
                        <div class="bg-slate-50 p-2.5 rounded-xl mb-2 border border-slate-100">
                            
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <select name="customer_id" id="customerSelect" class="w-full bg-white border border-slate-200 rounded-lg pl-9 pr-8 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none appearance-none" required>
                                    <option value="" data-address="" data-phone="">Select Customer...</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['customer_id'] ?>" 
                                                data-address="<?= htmlspecialchars($c['address']) ?>" 
                                                data-phone="<?= htmlspecialchars($c['phone']) ?>">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        
                        <div class="mb-2">
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <?php foreach(['dine_in' => 'chair', 'delivery' => 'motorcycle', 'takeaway' => 'bag-shopping'] as $type => $icon): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="order_type" value="<?= $type ?>" class="peer hidden" <?= ($order_type == $type) ? 'checked' : '' ?> onchange="toggleType(this)">
                                    <div class="text-center py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 peer-checked:bg-slate-800 peer-checked:text-white peer-checked:border-slate-800 transition-all">
                                        <!-- <i class="fas fa-<?= $icon ?> mb-0.5 text-xs"></i> -->
                                        <div class="text-[9px] font-bold uppercase flex items-center justify-center gap-1">
                                            <i class="fas fa-<?= $icon ?>"></i> <?= str_replace('_', ' ', $type) ?>
                                        </div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>

                            
                            <div id="deliveryFields" class="hidden bg-slate-50 p-2 rounded-xl border border-slate-100 space-y-2 animate-fade-in text-xs">
                                <div class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <input type="text" name="customer_phone" id="display_phone" placeholder="Phone" class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1.5 font-bold text-slate-700 placeholder-slate-400 text-[10px]">
                                    </div>
                                    <div class="flex-[2]">
                                        <input type="text" name="delivery_address" id="display_address" placeholder="Address" class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1.5 font-bold text-slate-700 placeholder-slate-400 text-[10px]">
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="flex-1 flex flex-col min-h-0 mb-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Order Items</label>
                            <div id="cartItems" class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50 rounded-2xl p-2 space-y-3 border border-slate-100">
                                
                                <div class="flex flex-col items-center justify-center h-full text-slate-400">
                                    <i class="fas fa-shopping-basket text-4xl mb-3 opacity-20"></i>
                                    <p class="text-xs font-bold uppercase tracking-widest">Cart is empty</p>
                                </div>
                            </div>
                        </div>

                        
                        <div class="mt-auto">
                            <input type="text" name="notes" placeholder="Add kitchen notes..." class="w-full bg-slate-50 border-0 rounded-lg px-3 py-2 text-xs font-bold mb-3 focus:ring-1 focus:ring-slate-300">
                            
                            <div class="flex justify-between items-end mb-4 px-1">
                                <div class="text-slate-500 text-xs font-bold">Total Payable</div>
                                <div id="cartTotal" class="text-2xl font-black text-slate-900 tracking-tight">Rs. 0</div>
                            </div>
                            
                            <input type="hidden" id="cartData" name="items" value="">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition-all flex justify-center items-center gap-2 group active:scale-95">
                                <span>Place Order</span>
                                <i class="fas fa-chevron-right text-xs group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</main>

<script>
    let cart = [];
    // Key by item_id (integer)
    const menuItems = <?php echo json_encode(array_column($menu_items, null, 'item_id')); ?>;



    function addToCart(itemId) {
        // Ensure itemId is treated as int/string consistently
        const item = menuItems[itemId];
        if(!item) return;

        const existing = cart.find(i => i.id == itemId);
        if (existing) { 
            existing.quantity++; 
        } else { 
            cart.push({ 
                id: itemId, 
                name: item.name, 
                price: parseFloat(item.price), 
                quantity: 1,
                image: item.image_path
            }); 
        }
        updateCartDisplay();
        
        // Optional: User feedback
        // alert(item.name + " added!");
    }

    function updateCartDisplay() {
        const container = document.getElementById('cartItems');
        const totalEl = document.getElementById('cartTotal');
        let total = 0;
        
        if (cart.length === 0) {
            container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-slate-400">
                <i class="fas fa-shopping-basket text-4xl mb-3 opacity-20"></i>
                <p class="text-xs font-bold uppercase tracking-widest">Cart is empty</p>
            </div>`;
        } else {
            container.innerHTML = '';
            cart.forEach((item, idx) => {
                total += item.price * item.quantity;
                const itemTotal = item.price * item.quantity;
                
                container.innerHTML += `
                    <div class="flex items-center gap-3 bg-white p-3 rounded-2xl shadow-sm border border-slate-50 group animate-slide-in hover:border-emerald-100 transition-colors">
                        
                        <div class="h-14 w-14 rounded-xl overflow-hidden shrink-0 border border-slate-100">
                            <img src="${item.image}" class="w-full h-full object-cover" alt="${item.name}">
                        </div>
                        
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black text-slate-800 truncate leading-tight mb-0.5" title="${item.name}">${item.name}</p>
                            <p class="text-[10px] font-bold text-slate-400">@ Rs. ${item.price.toLocaleString()}</p>
                        </div>

                        
                        <div class="flex flex-col items-end gap-1">
                            <p class="text-sm font-black text-emerald-600">Rs. ${itemTotal.toLocaleString()}</p>
                            
                            <div class="flex items-center gap-2 bg-slate-50 rounded-lg p-0.5 border border-slate-100">
                                <button type="button" onclick="updateQty(${idx}, -1)" class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] font-bold text-slate-500 hover:bg-white hover:text-rose-500 hover:shadow-sm transition-all"><i class="fas fa-minus"></i></button>
                                <span class="text-xs font-extrabold text-slate-700 w-4 text-center">${item.quantity}</span>
                                <button type="button" onclick="updateQty(${idx}, 1)" class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] font-bold text-slate-500 hover:bg-white hover:text-emerald-500 hover:shadow-sm transition-all"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>`;
            });
        }
        totalEl.innerText = 'Rs. ' + total.toLocaleString();
        document.getElementById('cartData').value = JSON.stringify(cart);
    }

    function updateQty(idx, delta) {
        cart[idx].quantity += delta;
        if (cart[idx].quantity <= 0) cart.splice(idx, 1);
        updateCartDisplay();
    }

    function clearCart() { 
        if(confirm('Clear all items from the cart?')) {
            cart = []; 
            updateCartDisplay(); 
        }
    }

    // Auto-fill logic
    function fillCustomerDetails() {
        const select = document.getElementById('customerSelect');
        const opt = select.options[select.selectedIndex];
        
        // Only fill if a valid customer is selected
        if (opt.value) {
            document.getElementById('display_phone').value = opt.dataset.phone || '';
            document.getElementById('display_address').value = opt.dataset.address || '';
        }
    }

    document.getElementById('customerSelect').addEventListener('change', fillCustomerDetails);

    function toggleType(radio) {
        // Toggle visibility of delivery fields
        const isDelivery = radio.value === 'delivery';
        document.getElementById('deliveryFields').classList.toggle('hidden', !isDelivery);
        
        if (isDelivery) {
            document.getElementById('display_phone').required = true;
            document.getElementById('display_address').required = true;
            fillCustomerDetails(); // Trigger auto-fill when switching to delivery
        } else {
            document.getElementById('display_phone').required = false;
            document.getElementById('display_address').required = false;
        }
    }

    function validateForm() {
        if (cart.length === 0) { alert('Your cart is empty! Please add items.'); return false; }
        if (!document.getElementById('customerSelect').value) { alert('Please select a customer for this order.'); return false; }
        return true;
    }

    // Category Filtering
    function filterCategory(cat) {
        // Update pills
        document.querySelectorAll('.category-pill').forEach(btn => {
            if ((cat === '' && btn.textContent.trim() === 'All Items') || 
                btn.dataset.category === cat) {
                btn.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-100');
                btn.classList.add('bg-slate-800', 'text-white', 'shadow-md');
            } else {
                btn.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-100');
                btn.classList.remove('bg-slate-800', 'text-white', 'shadow-md');
            }
        });

        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        filterItems(cat, searchTerm);
    }

    // Search Filtering
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        // Find currently active category
        const activePill = document.querySelector('.category-pill.bg-slate-800');
        const activeCat = activePill && activePill.dataset.category ? activePill.dataset.category : '';
        filterItems(activeCat, searchTerm);
    });

    function filterItems(category, search) {
        document.querySelectorAll('.menu-card').forEach(card => {
            const itemCat = card.dataset.category;
            // The name is in the h3 element inside the card
            const itemName = card.querySelector('h3').textContent.toLowerCase();
            
            const matchesCategory = !category || itemCat === category;
            const matchesSearch = !search || itemName.includes(search);

            if (matchesCategory && matchesSearch) {
                card.classList.remove('hidden');
                card.classList.add('block', 'animate-fade-in');
            } else {
                card.classList.remove('block', 'animate-fade-in');
                card.classList.add('hidden');
            }
        });
    }
</script>
</body>
</html>
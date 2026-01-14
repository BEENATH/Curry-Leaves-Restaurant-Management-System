<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
session_start();
require 'db.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_orders.php");
    exit;
}

$order_id = intval($_GET['id']);


$sql_order = "SELECT o.*, c.name as customer_name, c.email as customer_email, c.address as customer_home_address, c.phone as customer_phone
              FROM orders o 
              JOIN customers c ON o.customer_id = c.customer_id 
              WHERE o.order_id = $order_id";
$order_res = $conn->query($sql_order);

if ($order_res->num_rows == 0) {
    echo "Order not found.";
    exit;
}
$order = $order_res->fetch_assoc();


$items = [];
$sql_items = "SELECT oi.*, mi.name as item_name, mi.image_path 
              FROM order_items oi 
              JOIN menu_items mi ON oi.menu_item_id = mi.item_id 
              WHERE oi.order_id = $order_id";
$items_res = $conn->query($sql_items);
while($row = $items_res->fetch_assoc()) {
    $items[] = $row;
}

$page_title = "Order Details #$order_id | Curry Leaves";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300 min-w-0">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8 no-print fade-in">
            <a href="view_orders.php" class="flex items-center gap-2 text-slate-500 hover:text-emerald-600 font-bold transition-all text-sm md:text-base">
                <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Back to History</span><span class="sm:hidden">Back</span>
            </a>
            <div class="flex gap-2">
                <button onclick="window.print()" class="h-12 w-12 sm:w-auto sm:px-6 bg-slate-900 text-white rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-black transition-all shadow-lg shadow-slate-200">
                    <i class="fas fa-print"></i> <span class="hidden sm:inline">Print Receipt</span>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] p-6 md:p-12 shadow-sm border border-slate-100 relative overflow-hidden fade-in no-print-shadow" style="animation-delay: 0.1s;">
            <!-- Watermark -->
            <div class="absolute -top-12 -right-12 opacity-[0.02] pointer-events-none rotate-12">
                <i class="fas fa-utensils text-[20rem] text-slate-900"></i>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-start border-b border-slate-100 pb-10 mb-10 gap-6 relative z-10">
                <div>
                    <div class="text-emerald-600 font-black text-3xl mb-4 flex items-center gap-2 tracking-tighter">
                        <i class="fas fa-leaf"></i> CURRY LEAVES
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Purchase Details</p>
                        <p class="text-slate-900 font-black text-xl md:text-2xl">Order ID #<?= $order['order_id'] ?></p>
                        <p class="text-slate-400 text-xs font-bold"><?= date('F d, Y • h:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                </div>
                <div class="flex flex-col items-start md:items-end gap-3">
                    <span class="status-badge status-<?= $order['status'] ?> text-[10px] px-6 py-2 shadow-sm">
                        <?= strtoupper($order['status']) ?>
                    </span>
                    <p class="text-slate-900 font-black text-base md:text-lg uppercase tracking-widest bg-slate-50 px-4 py-1.5 rounded-xl border border-slate-100">
                        <?= str_replace('_', ' ', $order['order_type']) ?>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12 relative z-10">
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 px-1 border-l-4 border-emerald-500">Customer Intelligence</h4>
                    <div class="space-y-3">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center font-black">
                                <?= strtoupper(substr($order['customer_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-base font-black text-slate-800"><?= htmlspecialchars($order['customer_name']) ?></p>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($order['customer_email']) ?></p>
                            </div>
                        </div>
                        <p class="text-slate-500 text-sm font-bold flex items-center gap-2">
                             <i class="fas fa-phone-alt text-emerald-500 text-xs"></i>
                             <?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?>
                        </p>
                    </div>
                </div>
                
                <?php if($order['order_type'] == 'delivery'): ?>
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 px-1 border-l-4 border-slate-900">Logistics Destination</h4>
                    <div class="p-5 bg-slate-50 rounded-[1.5rem] border border-slate-100 group hover:border-emerald-200 transition-colors">
                        <p class="text-slate-600 text-xs leading-relaxed font-bold">
                            <i class="fas fa-map-marked-alt text-emerald-500 mr-2 text-lg align-middle"></i>
                            <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="mb-12 relative z-10">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8 px-1 border-l-4 border-amber-500">Inventory Provision</h4>
                <div class="space-y-6">
                    <?php foreach($items as $item): ?>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 hover:bg-slate-50 rounded-3xl transition-colors border-b border-slate-50 last:border-0 pb-6 sm:pb-4">
                        <div class="flex items-center gap-5">
                            <div class="h-16 w-16 min-w-[4rem] rounded-2xl overflow-hidden border-2 border-slate-100 shadow-sm">
                                <img src="uploads/<?= htmlspecialchars($item['image_path'] ?? 'default.jpg') ?>" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="font-black text-slate-800 text-base"><?= htmlspecialchars($item['item_name']) ?></p>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit: Rs. <?= number_format($item['price'], 0) ?></p>
                                <?php if(!empty($item['special_notes'])): ?>
                                    <div class="mt-2 text-[9px] text-amber-600 font-black italic bg-amber-50 px-2 py-1 rounded-lg inline-flex items-center gap-1">
                                        <i class="fas fa-sticky-note text-[7px]"></i> NOTE: <?= htmlspecialchars($item['special_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:text-right gap-6">
                            <div class="text-center sm:text-right">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Quantity</p>
                                <p class="text-sm font-black text-slate-700 bg-slate-100 h-8 w-12 sm:w-16 flex items-center justify-center rounded-xl mx-auto sm:ml-auto">x<?= $item['quantity'] ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Subtotal</p>
                                <p class="text-base font-black text-slate-900 tracking-tighter">Rs. <?= number_format($item['price'] * $item['quantity'], 0) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex flex-col items-end pt-8 border-t border-slate-100 relative z-10">
                <div class="w-full md:w-80 space-y-4">
                    <div class="flex justify-between items-center text-slate-500 font-bold text-xs uppercase tracking-widest">
                        <span>Cart Total</span>
                        <span class="text-slate-800">Rs. <?= number_format($order['total'], 0) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500 font-bold text-xs uppercase tracking-widest">
                        <span>Service Factor</span>
                        <span class="text-slate-800">Rs. 0</span>
                    </div>
                    <div class="flex justify-between items-center pt-6 border-t border-slate-900 mt-4">
                        <span class="text-slate-900 font-black text-xl tracking-tighter">GRAND TOTAL</span>
                        <span class="text-emerald-600 font-black text-3xl tracking-tighter">Rs. <?= number_format($order['total'], 0) ?></span>
                    </div>
                </div>
            </div>

            <?php if(!empty($order['notes'])): ?>
            <div class="mt-12 p-8 bg-slate-950 rounded-[2rem] relative z-10 shadow-xl shadow-slate-100">
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <i class="fas fa-quote-left text-emerald-500"></i> Delivery Protocol Notes
                </h4>
                <p class="text-slate-300 text-sm italic font-medium leading-relaxed leading-relaxed"><?= htmlspecialchars($order['notes']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-12 text-center no-print">
            <p class="text-slate-400 text-xs font-black uppercase tracking-[0.3em] mb-4">Verification Layer</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <button onclick="window.print()" class="bg-white text-slate-800 border-2 border-slate-100 px-8 py-4 rounded-2xl font-black text-xs hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-file-invoice"></i> Download PDF
                </button>
                <a href="view_orders.php" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-xs hover:bg-black transition-all shadow-xl shadow-slate-100 flex items-center justify-center gap-2">
                    <i class="fas fa-check-double"></i> Acknowledge View
                </a>
            </div>
        </div>
    </div>
</main>
</body>
</html>
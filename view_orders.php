<?php

require 'db.php';
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$search = '';
$status_filter = '';
$date_filter = '';
$type_filter = '';
$orders = [];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    
    $update_sql = "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id";
    if ($conn->query($update_sql)) {
        $_SESSION['success_msg'] = "Order #$order_id status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating status: " . $conn->error;
    }
    
    header("Location: view_orders.php" . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit();
}


$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);


if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['filter'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
    $status_filter = mysqli_real_escape_string($conn, $_GET['status_filter'] ?? '');
    $date_filter = mysqli_real_escape_string($conn, $_GET['date_filter'] ?? '');
    $type_filter = mysqli_real_escape_string($conn, $_GET['type_filter'] ?? '');
}


$sql = "SELECT o.order_id, o.customer_id, c.name as customer_name, o.order_type, 
               o.total, o.status, o.created_at, o.delivery_address, o.customer_phone
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE 1=1";

if (!empty($search)) { $sql .= " AND (c.name LIKE '%$search%' OR o.order_id LIKE '%$search%')"; }
if (!empty($status_filter)) { $sql .= " AND o.status = '$status_filter'"; }
if (!empty($date_filter)) { $sql .= " AND DATE(o.created_at) = '$date_filter'"; }
if (!empty($type_filter)) { $sql .= " AND o.order_type = '$type_filter'"; }

$sql .= " ORDER BY o.created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) { $orders[] = $row; }
}

$page_title = "Order History | Curry Leaves";
include 'includes/head.php';
?>


<style>
    @media print { .no-print { display: none !important; } aside { display:none !important; } main { margin-left: 0 !important; } }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300 min-w-0">
    <?php if($success_msg): ?>
        <div id="msg" class="mb-6 p-4 bg-emerald-100 text-emerald-700 rounded-2xl border border-emerald-200 no-print flex justify-between items-center shadow-sm fade-in">
            <span class="font-bold text-sm"><i class="fas fa-check-circle mr-2"></i> <?= $success_msg ?></span>
            <button onclick="this.parentElement.remove()" class="text-emerald-900 font-bold">&times;</button>
        </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 fade-in">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Order History</h1>
            <p class="text-slate-500 mt-1">Manage and track all customer orders</p>
        </div>
        <button onclick="window.print()" class="no-print w-full md:w-auto bg-white border border-slate-200 text-slate-600 px-6 py-3 rounded-2xl font-bold hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center gap-2">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-3xl md:rounded-[2.5rem] p-6 mb-8 shadow-sm no-print fade-in" style="animation-delay: 0.1s">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search ID/Name" 
                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
            </div>
            <select name="status_filter" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 appearance-none">
                <option value="">All Status</option>
                <?php foreach(['pending', 'preparing', 'ready', 'delivered', 'completed', 'cancelled'] as $status): ?>
                    <option value="<?= $status ?>" <?= $status_filter == $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type_filter" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 appearance-none">
                <option value="">All Types</option>
                <option value="dine_in" <?= $type_filter == 'dine_in' ? 'selected' : '' ?>>Dine In</option>
                <option value="takeaway" <?= $type_filter == 'takeaway' ? 'selected' : '' ?>>Takeaway</option>
                <option value="delivery" <?= $type_filter == 'delivery' ? 'selected' : '' ?>>Delivery</option>
            </select>
            <input type="date" name="date_filter" value="<?= $date_filter ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
            <button type="submit" name="filter" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-slate-200">Apply Filter</button>
        </form>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden lg:block glass-card rounded-[2.5rem] overflow-hidden shadow-sm fade-in" style="animation-delay: 0.2s">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">Order Details</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">Service Type</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">Total Amount</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">Current Status</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">Date & Time</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center no-print">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="6" class="px-8 py-12 text-center text-slate-400 italic font-bold">No orders found matching your criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr class="group transition-colors hover:bg-slate-50/50">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4 text-sm font-bold text-slate-900">
                                        <span class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black">#<?= $order['order_id'] ?></span>
                                        <div>
                                            <p class="font-black text-slate-800"><?= htmlspecialchars($order['customer_name']) ?></p>
                                            <p class="text-[10px] text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($order['customer_phone'] ?? 'Guest') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-[10px] px-3 py-1 bg-slate-100 rounded-lg font-black uppercase text-slate-500 tracking-tight">
                                        <i class="fas fa-<?= $order['order_type'] == 'delivery' ? 'truck' : ($order['order_type'] == 'takeaway' ? 'bag-shopping' : 'utensils') ?> mr-1.5 opacity-50"></i>
                                        <?= str_replace('_', ' ', $order['order_type']) ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="font-black text-emerald-600 text-sm">Rs. <?= number_format($order['total'], 0) ?></span>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-xs font-bold text-slate-600"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold"><?= date('H:i A', strtotime($order['created_at'])) ?></div>
                                </td>
                                <td class="px-8 py-5 no-print">
                                    <div class="flex justify-center gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="order_details.php?id=<?= $order['order_id'] ?>" title="View Details" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm"><i class="fas fa-eye text-xs"></i></a>
                                        <button onclick="openStatusModal(<?= $order['order_id'] ?>, '<?= $order['status'] ?>')" title="Update Status" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm"><i class="fas fa-sync-alt text-xs"></i></button>
                                        <a href="edit_order.php?id=<?= $order['order_id'] ?>" title="Edit Order" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-sm"><i class="fas fa-edit text-xs"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-4 fade-in" style="animation-delay: 0.2s">
        <?php if (empty($orders)): ?>
            <div class="bg-white p-12 rounded-3xl text-center text-slate-400 italic font-bold border border-slate-50">No orders found.</div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-sm">#<?= $order['order_id'] ?></span>
                            <div>
                                <h3 class="font-black text-slate-800 leading-none"><?= htmlspecialchars($order['customer_name']) ?></h3>
                                <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-widest"><?= date('H:i A', strtotime($order['created_at'])) ?></p>
                            </div>
                        </div>
                        <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 pt-4 border-t border-slate-50">
                        <div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-1">Type</p>
                            <p class="text-xs font-black text-slate-700 uppercase"><?= str_replace('_', ' ', $order['order_type']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total</p>
                            <p class="text-sm font-black text-emerald-600">Rs. <?= number_format($order['total'], 0) ?></p>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-slate-50">
                        <a href="order_details.php?id=<?= $order['order_id'] ?>" class="flex-1 bg-slate-50 hover:bg-emerald-600 hover:text-white text-slate-600 font-bold py-3 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button onclick="openStatusModal(<?= $order['order_id'] ?>, '<?= $order['status'] ?>')" class="flex-1 bg-slate-50 hover:bg-blue-600 hover:text-white text-slate-600 font-bold py-3 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-sync-alt"></i> Status
                        </button>
                        <a href="edit_order.php?id=<?= $order['order_id'] ?>" class="h-10 w-10 bg-slate-50 text-slate-600 flex items-center justify-center rounded-xl hover:bg-amber-500 hover:text-white transition-all">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<div id="statusModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl scale-in-center">
        <div class="flex items-center gap-3 mb-6">
            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="fas fa-sync-alt"></i>
            </div>
            <h3 class="text-xl font-black text-slate-900">Update Order Status</h3>
        </div>
        
        <form method="POST">
            <input type="hidden" name="order_id" id="modalOrderId">
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 px-1">Select Progress</label>
            <select name="new_status" id="new_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 mb-6 font-bold outline-none focus:ring-2 focus:ring-emerald-500 appearance-none">
                <option value="pending">Pending</option>
                <option value="preparing">Preparing</option>
                <option value="ready">Ready</option>
                <option value="delivered">Delivered</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeStatusModal()" class="flex-1 bg-slate-100 py-3.5 rounded-xl font-bold text-slate-600 hover:bg-slate-200 transition-all">Dismiss</button>
                <button type="submit" name="update_status" class="flex-1 bg-emerald-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all">Update Now</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStatusModal(id, status) {
        document.getElementById('modalOrderId').value = id;
        document.getElementById('new_status').value = status;
        document.getElementById('statusModal').classList.remove('hidden');
        document.getElementById('statusModal').classList.add('flex');
    }
    function closeStatusModal() { 
        document.getElementById('statusModal').classList.add('hidden'); 
        document.getElementById('statusModal').classList.remove('flex');
    }
    
    // Auto-hide messages
    setTimeout(() => { 
        const msg = document.getElementById('msg');
        if(msg) {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s ease';
            setTimeout(() => msg.remove(), 500);
        }
    }, 5000);
</script>

</body>
</html>
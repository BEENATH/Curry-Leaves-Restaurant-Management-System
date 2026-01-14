<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['employee_id'])) { 
    header("Location: login.php"); 
    exit; 
} 

require 'db.php';

$user_name = htmlspecialchars($_SESSION['employee_name'] ?? 'Admin');
$current_role = $_SESSION['role'] ?? 'Staff';

$page_title = "Curry Leaves | View Customers";
include 'includes/head.php';
?>

<style>
    .custom-table-container { border-radius: 2.5rem; overflow: hidden; }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300 min-w-0">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 md:mb-12 gap-6 fade-in">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Customer Hub</h1>
            <p class="text-slate-500 mt-1">Manage and communicate with your registered users</p>
        </div>
        <a href="add_customer.php" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3.5 rounded-2xl font-black transition-all shadow-lg shadow-emerald-100 text-sm">
            <i class="fas fa-user-plus"></i> New Customer
        </a>
    </div>

    <!-- Stats summary (Optional but attractive) -->
    <div class="bg-white p-6 rounded-[2.5rem] border border-slate-50 shadow-sm mb-8 flex items-center gap-6 no-print fade-in" style="animation-delay: 0.1s">
        <div class="h-14 w-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl">
            <i class="fas fa-users-viewfinder"></i>
        </div>
        <div>
            <?php 
            $count_res = $conn->query("SELECT COUNT(*) as total FROM customers");
            $total_cust = $count_res->fetch_assoc()['total'] ?? 0;
            ?>
            <p class="text-2xl font-black text-slate-800"><?= number_format($total_cust) ?></p>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Registered Customers</p>
        </div>
    </div>

    <div class="hidden lg:block bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden fade-in" style="animation-delay: 0.2s">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <h3 class="font-black text-slate-800 text-lg uppercase tracking-tight">Recent Registrations</h3>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1 rounded-full">Showing Last 10</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/30">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer Profile</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact Info</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php
                    $result = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC LIMIT 10");
                    while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg shadow-sm">
                                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-slate-800"><?= htmlspecialchars($row['name']) ?></p>
                                        <p class="text-[10px] text-slate-400 font-bold truncate max-w-[250px] uppercase tracking-tight mt-0.5"><i class="fas fa-location-dot mr-1 text-emerald-500/50"></i> <?= htmlspecialchars($row['address']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($row['email']) ?></p>
                                <p class="text-[10px] text-slate-400 font-bold mt-0.5 tracking-wider"><?= htmlspecialchars($row['phone']) ?></p>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-all">
                                    <a href="edit_customer.php?id=<?= $row['customer_id'] ?>" class="h-10 w-10 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <a href="delete_customer.php?id=<?= $row['customer_id'] ?>" onclick="return confirm('Are you sure you want to remove this customer?')" class="h-10 w-10 rounded-xl flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile View -->
    <div class="lg:hidden space-y-4 fade-in" style="animation-delay: 0.2s">
        <?php
        $result = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC LIMIT 10");
        while ($row = $result->fetch_assoc()): ?>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg">
                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-black text-slate-800 text-md leading-tight"><?= htmlspecialchars($row['name']) ?></h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-1"><?= htmlspecialchars($row['phone']) ?></p>
                    </div>
                    <div class="flex flex-col gap-2">
                         <a href="edit_customer.php?id=<?= $row['customer_id'] ?>" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-edit text-[10px]"></i></a>
                         <a href="delete_customer.php?id=<?= $row['customer_id'] ?>" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fas fa-trash text-[10px]"></i></a>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-50 space-y-2">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-envelope text-slate-300 w-4 text-center text-xs"></i>
                        <span class="text-xs font-bold text-slate-600 truncate"><?= htmlspecialchars($row['email']) ?></span>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-slate-300 w-4 text-center text-xs mt-1"></i>
                        <span class="text-xs font-bold text-slate-500 leading-tight"><?= htmlspecialchars($row['address']) ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <footer class="mt-24 pt-12 border-t border-slate-100 text-slate-300 text-[10px] font-black uppercase tracking-[0.2em] text-center no-print">
        <p>© 2026 Curry Leaves • Premium CRM</p>
    </footer>
</main>

</body>
</html>
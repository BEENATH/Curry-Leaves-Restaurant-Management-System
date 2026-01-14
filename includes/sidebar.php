<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($pending_orders_count)) {
    $pending_orders_count = 0;
    if (isset($conn) && isset($_SESSION['employee_id'])) {
        $res = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        if ($res) { $pending_orders_count = $res->fetch_assoc()['count']; }
    }
}

if (!isset($pending_reservations_count)) {
    $pending_reservations_count = 0;
    if (isset($conn) && isset($_SESSION['employee_id'])) {
        $res = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
        if ($res) { $pending_reservations_count = $res->fetch_assoc()['count']; }
    }
}

if (!isset($unread_contacts_count)) {
    $unread_contacts_count = 0;
    if (isset($conn) && isset($_SESSION['employee_id'])) {
        $res = $conn->query("SELECT COUNT(*) as count FROM contacts WHERE status = 'unread'");
        if ($res) { $unread_contacts_count = $res->fetch_assoc()['count']; }
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile Header -->
<header class="lg:hidden fixed top-0 left-0 right-0 h-16 nav-glass z-[60] flex items-center justify-between px-6">
    <a href="dashboard.php" class="flex items-center gap-2 text-emerald-600 font-extrabold text-xl tracking-tight">
        <i class="fas fa-leaf"></i> <span>Curry Leaves</span>
    </a>
    <button id="sidebarToggle" class="h-10 w-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-all">
        <i class="fas fa-bars-staggered"></i>
    </button>
</header>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

<aside id="sidebar" class="w-72 bg-white/95 backdrop-blur-xl border-r border-slate-200 flex flex-col fixed h-full z-50 shadow-sm transition-all duration-300 -translate-x-full lg:translate-x-0 overflow-y-auto">
    <div class="p-8 hidden lg:block">
        <a href="dashboard.php" class="flex items-center gap-3 text-emerald-600 font-extrabold text-2xl tracking-tight hover:opacity-80 transition">
            <i class="fas fa-leaf"></i> <span>Curry Leaves</span>
        </a>
    </div>
    
    <div class="lg:hidden p-8 flex items-center justify-between">
        <a href="dashboard.php" class="flex items-center gap-2 text-emerald-600 font-extrabold text-xl tracking-tight">
            <i class="fas fa-leaf text-2xl"></i> <span>Curry Leaves</span>
        </a>
        <button id="sidebarClose" class="text-slate-400 hover:text-rose-500 transition-colors">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    
    <nav class="flex-1 px-6 space-y-1 py-2 custom-scrollbar">
        <?php if(isset($_SESSION['employee_id'])): ?>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-4 mb-3">Overview</p>
            <a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt w-5 text-center"></i> Dashboard
            </a>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-4 mb-3 mt-8">Operations</p>
            
            <a href="view_orders.php" class="sidebar-link flex items-center justify-between px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'view_orders.php' ? 'active' : '' ?>">
                <div class="flex items-center gap-3"><i class="fas fa-receipt w-5 text-center"></i> Orders</div>
                <?php if($pending_orders_count > 0): ?> 
                    <span class="bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold shadow-lg shadow-rose-200"><?= $pending_orders_count ?></span> 
                <?php endif; ?>
            </a>
            
            <a href="admin_reservations.php" class="sidebar-link flex items-center justify-between px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'admin_reservations.php' ? 'active' : '' ?>">
                <div class="flex items-center gap-3"><i class="fas fa-calendar-check w-5 text-center"></i> Reservations</div>
                <?php if($pending_reservations_count > 0): ?> 
                    <span class="bg-amber-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold"><?= $pending_reservations_count ?></span> 
                <?php endif; ?>
            </a>

            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-4 mb-3 mt-8">Management</p>

            <a href="view_menu.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'view_menu.php' ? 'active' : '' ?>">
                <i class="fas fa-utensils w-5 text-center"></i> Menu Items
            </a>

            <a href="view_customers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'view_customers.php' ? 'active' : '' ?>">
                <i class="fas fa-users w-5 text-center"></i> Customers
            </a>
            
            <a href="admin_contacts.php" class="sidebar-link flex items-center justify-between px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'admin_contacts.php' ? 'active' : '' ?>">
                <div class="flex items-center gap-3"><i class="fas fa-envelope w-5 text-center"></i> Messages</div>
                <?php if($unread_contacts_count > 0): ?> 
                    <span class="bg-blue-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold"><?= $unread_contacts_count ?></span> 
                <?php endif; ?>
            </a>

            <a href="reports.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line w-5 text-center"></i> Analytics
            </a>

            <a href="settings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog w-5 text-center"></i> Settings
            </a>

        <?php else: ?>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-4 mb-3">Menu</p>
            <a href="view_menu_customers.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'view_menu_customers.php' ? 'active' : '' ?>">
                <i class="fas fa-book-open w-5 text-center"></i> Digital Menu
            </a>
            <a href="customer_orders.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'customer_orders.php' ? 'active' : '' ?>">
                <i class="fas fa-history w-5 text-center"></i> My Orders
            </a>
            <a href="add_reservation.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'add_reservation.php' ? 'active' : '' ?>">
                <i class="fas fa-chair w-5 text-center"></i> Book Table
            </a>
            <a href="contact.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-600 <?= $current_page == 'contact.php' ? 'active' : '' ?>">
                <i class="fas fa-headset w-5 text-center"></i> Contact Support
            </a>
        <?php endif; ?>
    </nav>

    <div class="p-6 border-t border-slate-100">
        <a href="<?= isset($_SESSION['employee_id']) ? 'logout.php' : 'customer_logout.php' ?>" class="flex items-center gap-3 px-6 py-4 rounded-2xl text-rose-600 hover:bg-rose-50 font-bold transition-colors">
            <i class="fas fa-power-off"></i> Sign Out
        </a>
    </div>
</aside>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const close = document.getElementById('sidebarClose');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
        setTimeout(() => overlay.classList.toggle('opacity-0'), 0);
        document.body.classList.toggle('sidebar-open');
    }

    if(toggle) toggle.addEventListener('click', toggleSidebar);
    if(close) close.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);
</script>

<div class="lg:hidden h-16 w-full"></div> <!-- Spacer for fixed header -->

<?php 
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['employee_id']) && !isset($_SESSION['customer_id'])) { 
    header("Location: login.php"); 
    exit; 
} 


require 'db.php';


$pending_orders_count = 0;
$pending_reservations_count = 0;
$unread_contacts_count = 0;
$pending_contacts_count = 0;
$activity_data = [];
$activity_labels = [];
$hourly_data = [];
$hourly_labels = [];

if (isset($_SESSION['employee_id'])) {
    $res = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    if ($res) { $pending_orders_count = $res->fetch_assoc()['count']; }
    
    $res = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
    if ($res) { $pending_reservations_count = $res->fetch_assoc()['count']; }
    
    $res = $conn->query("SELECT COUNT(*) as count FROM contacts WHERE status = 'unread'");
    if ($res) { $unread_contacts_count = $res->fetch_assoc()['count']; }
    
    $res = $conn->query("SELECT COUNT(*) as count FROM contacts WHERE status = 'pending'");
    if ($res) { $pending_contacts_count = $res->fetch_assoc()['count']; }

    
    $activity_data = [];
    $activity_labels = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $day_name = date('D', strtotime("-$i days"));
        $activity_labels[] = $day_name; 
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $res = $stmt->get_result();
        $activity_data[] = $res->fetch_assoc()['count'];
    }

    
    $today_revenue = 0;
    $today_orders_count = 0;
    $t_res = $conn->query("SELECT SUM(total) as revenue, COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    if ($t_res) {
        $t_row = $t_res->fetch_assoc();
        $today_revenue = $t_row['revenue'] ?? 0;
        $today_orders_count = $t_row['count'] ?? 0;
    }

    
    $hourly_data = array_fill(0, 24, 0);
    $hourly_labels = [];
    for($i=0; $i<24; $i++) $hourly_labels[] = sprintf("%02d:00", $i);
    
    $h_res = $conn->query("SELECT HOUR(created_at) as hr, COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE() GROUP BY HOUR(created_at)");
    if($h_res) {
        while($row = $h_res->fetch_assoc()) {
            $hourly_data[$row['hr']] = $row['count'];
        }
    }
}


function getRoleGradient($role) {
    switch (strtolower($role)) {
        case 'manager': return 'from-purple-600 to-indigo-600';
        case 'chef': return 'from-amber-600 to-orange-500';
        case 'waiter': return 'from-emerald-600 to-green-500';
        default: return 'from-slate-600 to-slate-500';
    }
}

$current_role = $_SESSION['role'] ?? 'customer';
$user_name = htmlspecialchars($_SESSION['employee_name'] ?? $_SESSION['customer_name'] ?? 'User');


$page_title = "Curry Leaves | Dashboard";


include 'includes/head.php';
?>


<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300">
    
    <div class="flex flex-col md:flex-row items-center md:items-center justify-between mb-8 md:mb-12 gap-6 fade-in text-center md:text-left">
        <div class="w-full md:w-auto">
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">System Dashboard</h1>
            <p class="text-slate-500 mt-1">Operational Overview for <span class="text-emerald-600 font-bold"><?= $user_name ?></span></p>
        </div>
        
        <div class="flex items-center gap-4 bg-white p-2 md:p-3 pr-6 md:pr-8 rounded-full shadow-sm border border-slate-100 mx-auto md:mx-0">
            <div class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-gradient-to-tr <?= getRoleGradient($current_role) ?> flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-user-circle text-xl md:text-2xl"></i>
            </div>
            <div class="text-left">
                <p class="text-[8px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= $current_role ?></p>
                <p class="text-sm md:text-md font-bold text-slate-800 leading-none"><?= $user_name ?></p>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['employee_id'])): ?>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8 md:mb-12 fade-in" style="animation-delay: 0.1s">
            <?php 
            $stats = [
                ['label' => 'Pending Orders', 'count' => $pending_orders_count, 'pulse' => true, 'icon' => 'receipt', 'color' => 'rose'],
                ['label' => 'Reservations', 'count' => $pending_reservations_count, 'pulse' => true, 'icon' => 'calendar-check', 'color' => 'amber'],
                ['label' => 'Messages', 'count' => $unread_contacts_count, 'pulse' => true, 'icon' => 'envelope', 'color' => 'blue'],
                ['label' => 'Follow-ups', 'count' => $pending_contacts_count, 'pulse' => true, 'icon' => 'indigo', 'color' => 'indigo']
            ];
            foreach ($stats as $s): ?>
            <div class="bg-white p-4 md:p-6 rounded-3xl md:rounded-[2rem] border border-slate-50 shadow-sm hover:shadow-md transition-shadow flex flex-col md:flex-row items-start md:items-center gap-4 md:gap-6 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 md:w-32 md:h-32 bg-<?= $s['color'] ?>-50 rounded-full -mr-8 -mt-8 md:-mr-16 md:-mt-16 transition-transform group-hover:scale-150"></div>
                
                <div class="h-12 w-12 md:h-16 md:w-16 rounded-2xl bg-<?= $s['color'] ?>-100 text-<?= $s['color'] ?>-600 flex items-center justify-center text-xl md:text-2xl relative z-10">
                    <i class="fas fa-<?= $s['icon'] ?>"></i>
                    <?php if($s['pulse'] && $s['count'] > 0): ?>
                        <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-<?= $s['color'] ?>-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-<?= $s['color'] ?>-500"></span>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="relative z-10">
                    <p class="text-2xl md:text-4xl font-black text-slate-800"><?= $s['count'] ?></p>
                    <p class="text-[8px] md:text-xs font-bold text-slate-400 uppercase tracking-widest"><?= $s['label'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mb-8 md:mb-12 fade-in" style="animation-delay: 0.2s">
            
            <div class="lg:col-span-2 bg-white p-6 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-slate-50">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">Weekly Activity</h3>
                        <p class="text-xs text-slate-500 font-bold mt-1">
                            Revenue: <span class="text-emerald-600 font-extrabold">Rs. <?= number_format($today_revenue) ?></span>
                        </p>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-6 md:p-8 rounded-3xl md:rounded-[2rem] text-white shadow-lg shadow-emerald-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                
                <h3 class="font-bold text-xl mb-6 relative z-10">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3 md:gap-4 relative z-10">
                    <?php 
                    $quick_actions = [
                        ['url' => 'place_order.php', 'icon' => 'plus-circle', 'label' => 'New Order'],
                        ['url' => 'add_reservation.php', 'icon' => 'calendar-plus', 'label' => 'Book Table'],
                        ['url' => 'add_menu.php', 'icon' => 'hamburger', 'label' => 'Add Item'],
                        ['url' => 'add_customer.php', 'icon' => 'user-plus', 'label' => 'Add User']
                    ];
                    foreach ($quick_actions as $qa): ?>
                    <a href="<?= $qa['url'] ?>" class="bg-white/10 hover:bg-white/20 backdrop-blur p-4 rounded-2xl flex flex-col items-center gap-2 transition-all cursor-pointer border border-white/10">
                        <i class="fas fa-<?= $qa['icon'] ?> text-2xl"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider"><?= $qa['label'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <h2 class="text-xl md:text-2xl font-black text-slate-800 mb-6 md:mb-8 flex items-center gap-3 uppercase tracking-tight fade-in" style="animation-delay: 0.3s">
            <span class="h-2 w-2 bg-emerald-500 rounded-full"></span> Control Center
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 fade-in" style="animation-delay: 0.4s">
            <?php 
            $admin_actions = [
                ['url' => 'place_order.php', 'icon' => 'plus-circle', 'title' => 'New Order', 'desc' => 'Start new service', 'color' => 'emerald'],
                ['url' => 'add_customer.php', 'icon' => 'user-plus', 'title' => 'Add Customer', 'desc' => 'Onboard new consumers', 'color' => 'blue'],
                ['url' => 'view_customers.php', 'icon' => 'users', 'title' => 'Customers', 'desc' => 'Manage accounts', 'color' => 'teal'],
                ['url' => 'view_menu.php', 'icon' => 'utensils', 'title' => 'Menu', 'desc' => 'Update items', 'color' => 'indigo'],
                ['url' => 'view_orders.php', 'icon' => 'receipt', 'title' => 'Orders', 'desc' => 'Track fulfillment', 'badge' => $pending_orders_count, 'color' => 'slate'],
                ['url' => 'admin_reservations.php', 'icon' => 'calendar-alt', 'title' => 'Bookings', 'desc' => 'Organize tables', 'badge' => $pending_reservations_count, 'color' => 'violet'],
                ['url' => 'admin_contacts.php', 'icon' => 'envelope', 'title' => 'Messages', 'desc' => 'User inquiries', 'badge' => $unread_contacts_count, 'color' => 'cyan'],
                ['url' => 'reports.php', 'icon' => 'chart-bar', 'title' => 'Reports', 'desc' => 'Sales & analytics', 'color' => 'pink'],
                ['url' => 'settings.php', 'icon' => 'cog', 'title' => 'Settings', 'desc' => 'System config', 'color' => 'gray']
            ];
            foreach ($admin_actions as $card): ?>
            <a href="<?= $card['url'] ?>" class="bg-white p-5 md:p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all group relative">
                <?php if(isset($card['badge']) && $card['badge'] > 0): ?>
                    <span class="absolute top-4 right-4 bg-rose-500 text-white text-[8px] md:text-[10px] font-black px-2 py-1 rounded-full shadow-lg"><?= $card['badge'] ?></span>
                <?php endif; ?>
                
                <div class="h-10 w-10 md:h-12 md:w-12 rounded-2xl bg-<?= $card['color'] ?>-50 text-<?= $card['color'] ?>-600 flex items-center justify-center text-lg md:text-xl mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-<?= $card['icon'] ?>"></i>
                </div>
                
                <h3 class="text-sm md:text-base font-bold text-slate-800 mb-1"><?= $card['title'] ?></h3>
                <p class="hidden md:block text-xs text-slate-400 font-medium mb-4"><?= $card['desc'] ?></p>
                
                <span class="text-[8px] md:text-[10px] font-black uppercase tracking-widest text-<?= $card['color'] ?>-600 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                    Access <i class="fas fa-arrow-right ml-1"></i>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
             <?php 
            $customer_actions = [
                ['url' => 'view_menu_customers.php', 'icon' => 'utensils', 'title' => 'Explore Menu', 'color' => 'indigo'],
                ['url' => 'place_order_customer.php', 'icon' => 'shopping-bag', 'title' => 'Order Food', 'color' => 'orange'],
                ['url' => 'customer_orders.php', 'icon' => 'history', 'title' => 'Order History', 'color' => 'pink'],
                ['url' => 'add_reservation.php', 'icon' => 'chair', 'title' => 'Book a Table', 'color' => 'purple'],
                ['url' => 'customer_profile.php', 'icon' => 'user-circle', 'title' => 'My Profile', 'color' => 'emerald'],
                ['url' => 'contact.php', 'icon' => 'headset', 'title' => 'Get Support', 'color' => 'cyan']
            ];
            foreach ($customer_actions as $c): ?>
            <a href="<?= $c['url'] ?>" class="bg-white p-8 md:p-10 rounded-3xl md:rounded-[3rem] border border-slate-50 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all flex flex-col items-center text-center group">
                <div class="h-16 w-16 md:h-20 md:w-20 rounded-full bg-<?= $c['color'] ?>-50 text-<?= $c['color'] ?>-600 flex items-center justify-center text-2xl md:text-3xl mb-6 group-hover:rotate-12 transition-transform">
                    <i class="fas fa-<?= $c['icon'] ?>"></i>
                </div>
                <h3 class="text-lg md:text-xl font-black text-slate-800 mb-2"><?= $c['title'] ?></h3>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-<?= $c['color'] ?>-600 transition-colors">Start Action</span>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <footer class="mt-24 pt-12 border-t border-slate-200 text-slate-400 text-xs font-bold uppercase tracking-widest text-center">
        <p>© <?= date('Y') ?> Curry Leaves Restaurant • Management Portal</p>
    </footer>
</main>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Weekly Chart
    const activityCanvas = document.getElementById('activityChart');
    if (activityCanvas) {
        const ctx = activityCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($activity_labels); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode($activity_data); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Today's Hourly Chart
    const hourlyCanvas = document.getElementById('hourlyChart');
    if (hourlyCanvas) {
        const ctxHourly = hourlyCanvas.getContext('2d');
        new Chart(ctxHourly, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($hourly_labels); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode($hourly_data); ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    barThickness: 12
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8, font: {size: 10} } }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});
</script>

</body>
</html>
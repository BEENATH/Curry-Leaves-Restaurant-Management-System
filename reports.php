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


$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');


function formatLKR($amount) {
    if ($amount == 0) return 'Rs. 0.00';
    return 'Rs. ' . number_format($amount, 2);
}


$orders_sql = "SELECT COUNT(*) as total_orders, 
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
               SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
               FROM orders 
               WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$orders_result = $conn->query($orders_sql);
$orders_stats = $orders_result ? $orders_result->fetch_assoc() : [];


$revenue_sql = "SELECT SUM(oi.quantity * mi.price) as total_revenue
               FROM order_items oi
               JOIN orders o ON oi.order_id = o.order_id
               JOIN menu_items mi ON oi.menu_item_id = mi.item_id
               WHERE o.status = 'completed' 
               AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
$revenue_result = $conn->query($revenue_sql);
$total_revenue = ($revenue_result && $row = $revenue_result->fetch_assoc()) ? $row['total_revenue'] : 0;


$customers_sql = "SELECT COUNT(*) as total_customers FROM customers";
$customers_result = $conn->query($customers_sql);
$total_customers = ($customers_result && $row = $customers_result->fetch_assoc()) ? $row['total_customers'] : 0;


$reservations_sql = "SELECT COUNT(*) as total_reservations,
                     SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reservations,
                     SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_reservations
                     FROM reservations 
                     WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$reservations_result = $conn->query($reservations_sql);
$reservations_stats = $reservations_result ? $reservations_result->fetch_assoc() : [];


$top_items_sql = "SELECT mi.name, COUNT(oi.order_item_id) as total_sold, SUM(oi.quantity) as total_quantity,
                         SUM(oi.quantity * mi.price) as total_revenue
                  FROM order_items oi
                  JOIN orders o ON oi.order_id = o.order_id
                  JOIN menu_items mi ON oi.menu_item_id = mi.item_id
                  WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
                  GROUP BY mi.item_id, mi.name
                  ORDER BY total_quantity DESC
                  LIMIT 5";
$top_items_result = $conn->query($top_items_sql);
$top_items = [];
if ($top_items_result) {
    while($row = $top_items_result->fetch_assoc()) {
        $top_items[] = $row;
    }
}


$daily_revenue_sql = "SELECT DATE(o.created_at) as date, SUM(oi.quantity * mi.price) as revenue
                      FROM order_items oi
                      JOIN orders o ON oi.order_id = o.order_id
                      JOIN menu_items mi ON oi.menu_item_id = mi.item_id
                      WHERE o.status = 'completed'
                      AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'
                      GROUP BY DATE(o.created_at)
                      ORDER BY date";
$daily_revenue_result = $conn->query($daily_revenue_sql);
$daily_revenue_data = [];
$daily_revenue_labels = [];

if ($daily_revenue_result) {
    while($row = $daily_revenue_result->fetch_assoc()) {
        $daily_revenue_labels[] = date('M d', strtotime($row['date']));
        $daily_revenue_data[] = $row['revenue'] ?? 0;
    }
}


if (empty($daily_revenue_labels)) {
    $daily_revenue_labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
    $daily_revenue_data = [0, 0, 0, 0];
}

$page_title = "Admin Reports | Curry Leaves";
include 'includes/head.php';
?>

<style>
    /* Page specific styles */
    .header-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        position: relative;
        overflow: hidden;
    }
    .header-gradient::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
        animation: shimmer 5s infinite;
    }
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .stat-icon {
        width: 50px; height: 50px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; color: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .progress-bar { height: 6px; border-radius: 3px; background: #f1f5f9; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 3px; transition: width 1s ease-in-out; }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300 min-w-0">
    <div class="max-w-7xl mx-auto fade-in">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 md:mb-12 gap-6">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Executive Summary</h1>
                <p class="text-slate-500 mt-1">Deep dive into your restaurant's performance indices</p>
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <button onclick="window.print()" class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-slate-900 text-white px-6 py-3.5 rounded-2xl font-black text-sm hover:bg-black transition-all shadow-xl shadow-slate-100">
                    <i class="fas fa-file-export"></i> Export Report
                </button>
            </div>
        </div>
        
        <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm mb-8 no-print">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Reporting Period Start</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3.5 font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Reporting Period End</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3.5 font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-8 py-4 rounded-2xl font-black text-sm hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-2">
                    <i class="fas fa-sync-alt"></i> Re-Calculate
                </button>
            </form>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform">
                    <i class="fas fa-vault text-[8rem] text-slate-900"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Gross Revenue</p>
                <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= formatLKR($total_revenue) ?></h3>
                <div class="mt-4 flex items-center gap-2 text-emerald-600 font-bold text-xs bg-emerald-50 px-3 py-1.5 rounded-xl w-fit">
                    <i class="fas fa-arrow-up-right-dots"></i> Targeted
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform">
                    <i class="fas fa-receipt text-[8rem] text-slate-900"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Total Conversions</p>
                <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= $orders_stats['total_orders'] ?? 0 ?></h3>
                <p class="mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= $orders_stats['completed_orders'] ?? 0 ?> Completed Orders</p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform">
                    <i class="fas fa-users text-[8rem] text-slate-900"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">User Base</p>
                <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= $total_customers ?></h3>
                <p class="mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Registered Members</p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-check text-[8rem] text-slate-900"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Reservations</p>
                <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= $reservations_stats['total_reservations'] ?? 0 ?></h3>
                <p class="mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= $reservations_stats['confirmed_reservations'] ?? 0 ?> Confirmed Seats</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-white p-8 md:p-10 rounded-[3rem] border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <i class="fas fa-chart-line text-emerald-500"></i> Revenue Forecast
                    </h3>
                    <div class="flex gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="w-2 h-2 rounded-full bg-emerald-200"></span>
                    </div>
                </div>
                <div class="h-80 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-8 md:p-10 rounded-[3rem] border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-10">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <i class="fas fa-crown text-amber-500"></i> High Performance Dishes
                    </h3>
                    <div class="px-4 py-1.5 bg-slate-50 rounded-full text-[10px] font-black text-slate-400 uppercase tracking-widest">By Volume</div>
                </div>
                
                <?php if (count($top_items) > 0): ?>
                    <div class="space-y-8">
                        <?php foreach ($top_items as $index => $item): 
                            $percentage = ($index === 0) ? 100 : floor(($item['total_quantity'] / $top_items[0]['total_quantity']) * 100);
                        ?>
                            <div>
                                <div class="flex justify-between items-end mb-3">
                                    <div class="flex items-center gap-4">
                                        <span class="h-8 w-8 rounded-xl bg-slate-50 flex items-center justify-center text-xs font-black text-slate-400">#<?= $index + 1 ?></span>
                                        <div>
                                            <h4 class="font-black text-slate-800 text-sm"><?= htmlspecialchars($item['name']) ?></h4>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5"><?= $item['total_sold'] ?> Units Sold</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-emerald-600"><?= formatLKR($item['total_revenue']) ?></p>
                                    </div>
                                </div>
                                <div class="h-2 bg-slate-50 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-600 rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-64 text-slate-400">
                        <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center mb-6">
                            <i class="fas fa-cookie-bite text-3xl opacity-20"></i>
                        </div>
                        <p class="font-black text-xs uppercase tracking-widest">Insufficient sales data</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <footer class="text-center py-12 border-t border-slate-100 no-print">
            <p class="text-slate-300 text-[10px] font-black uppercase tracking-[0.4em]">© 2026 Admin Intelligence Matrix • Curry Leaves</p>
        </footer>
    </div>
</main>

<script>
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient for chart
    const gradient = revenueCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($daily_revenue_labels) ?>,
            datasets: [{
                label: 'Revenue (LKR)',
                data: <?= json_encode($daily_revenue_data) ?>,
                borderColor: '#10b981',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#10b981',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#1e293b',
                    bodyColor: '#10b981',
                    bodyFont: { weight: 'bold', size: 14 },
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Rs. ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: '#f1f5f9', borderDash: [5, 5] },
                    ticks: {
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' },
                        color: '#94a3b8',
                        callback: function(value) { return 'Rs. ' + value/1000 + 'k'; }
                    },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' },
                        color: '#94a3b8'
                    },
                    border: { display: false }
                }
            }
        }
    });

    // Animate stats numbers if we want
</script>
</body>
</html>
<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';


if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit;
}

$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $reservation_id = $_POST['reservation_id'];
    $new_status = $_POST['status'];
    
    $sql = "UPDATE reservations SET status = ? WHERE reservation_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $reservation_id);
    $stmt->execute();
    
    header("Location: admin_reservations.php?updated=true");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_table'])) {
    $reservation_id = $_POST['reservation_id'];
    $table_id = $_POST['table_id'];
    
    
    $check_sql = "SELECT is_available FROM restaurant_tables WHERE table_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $table_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $table = $check_result->fetch_assoc();
    
    if ($table && $table['is_available']) {
        
        $assign_sql = "INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)";
        $assign_stmt = $conn->prepare($assign_sql);
        $assign_stmt->bind_param("ii", $reservation_id, $table_id);
        
        
        $update_sql = "UPDATE restaurant_tables SET is_available = 0 WHERE table_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $table_id);
        
        if ($assign_stmt->execute() && $update_stmt->execute()) {
            
            $status_sql = "UPDATE reservations SET status = 'confirmed' WHERE reservation_id = ?";
            $status_stmt = $conn->prepare($status_sql);
            $status_stmt->bind_param("i", $reservation_id);
            $status_stmt->execute();
            
            header("Location: admin_reservations.php?table_assigned=true");
            exit;
        }
    } else {
        header("Location: admin_reservations.php?table_unavailable=true");
        exit;
    }
}


if (isset($_GET['delete'])) {
    $reservation_id = $_GET['delete'];
    
    
    $tables_sql = "SELECT table_id FROM reservation_tables WHERE reservation_id = ?";
    $tables_stmt = $conn->prepare($tables_sql);
    $tables_stmt->bind_param("i", $reservation_id);
    $tables_stmt->execute();
    $tables_result = $tables_stmt->get_result();
    
    while ($table = $tables_result->fetch_assoc()) {
        $free_sql = "UPDATE restaurant_tables SET is_available = 1 WHERE table_id = ?";
        $free_stmt = $conn->prepare($free_sql);
        $free_stmt->bind_param("i", $table['table_id']);
        $free_stmt->execute();
    }
    
    
    $delete_tables_sql = "DELETE FROM reservation_tables WHERE reservation_id = ?";
    $delete_tables_stmt = $conn->prepare($delete_tables_sql);
    $delete_tables_stmt->bind_param("i", $reservation_id);
    $delete_tables_stmt->execute();
    
    
    $sql = "DELETE FROM reservations WHERE reservation_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    
    header("Location: admin_reservations.php?deleted=true");
    exit;
}


$sql = "SELECT r.*, 
               CONCAT(r.first_name, ' ', r.last_name) as full_name,
               DATE_FORMAT(r.reservation_date, '%b %d, %Y') as formatted_date,
               TIME_FORMAT(r.reservation_time, '%h:%i %p') as formatted_time,
               DATE_FORMAT(r.created_at, '%b %d, %Y %h:%i %p') as created_formatted,
               GROUP_CONCAT(rt.table_id) as assigned_tables
        FROM reservations r 
        LEFT JOIN reservation_tables rt ON r.reservation_id = rt.reservation_id
        WHERE 1=1";

$params = [];
$types = "";

if ($status_filter != 'all') {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search_term)) {
    $sql .= " AND (CONCAT(r.first_name, ' ', r.last_name) LIKE ? OR r.email LIKE ? OR r.phone LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$sql .= " GROUP BY r.reservation_id ORDER BY r.reservation_date DESC, r.reservation_time DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$reservations = $result->fetch_all(MYSQLI_ASSOC);


$tables_sql = "SELECT * FROM restaurant_tables WHERE is_available = 1 ORDER BY table_number";
$tables_result = $conn->query($tables_sql);
$available_tables = $tables_result->fetch_all(MYSQLI_ASSOC);


$counts_sql = "SELECT status, COUNT(*) as count FROM reservations GROUP BY status";
$counts_result = $conn->query($counts_sql);
$status_counts = [];
while ($row = $counts_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
$total_count = array_sum($status_counts);

$page_title = "Reservation Management | Curry Leaves";
include 'includes/head.php';
?>

<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: white;
        border-radius: 1.5rem;
        padding: 2rem;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: modalSlide 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalSlide {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-6 lg:p-10 transition-all duration-300 min-w-0">
    <div class="fade-in">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-2">
                    Reservations
                </h1>
                <p class="text-slate-500 font-medium">Manage all table reservations and bookings</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="add_reservation.php" class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-emerald-700 transition flex items-center shadow-lg shadow-emerald-200 transform hover:-translate-y-1">
                    <i class="fas fa-plus mr-2"></i> New Booking
                </a>
            </div>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="mb-8 p-4 bg-emerald-100 text-emerald-800 border-l-4 border-emerald-500 rounded-r-xl flex items-center fade-in shadow-sm">
                <i class="fas fa-check-circle text-xl mr-3"></i>
                <span class="font-bold">Reservation status updated successfully!</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-8 p-4 bg-rose-100 text-rose-800 border-l-4 border-rose-500 rounded-r-xl flex items-center fade-in shadow-sm">
                <i class="fas fa-trash-alt text-xl mr-3"></i>
                <span class="font-bold">Reservation deleted successfully!</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['table_assigned'])): ?>
            <div class="mb-8 p-4 bg-emerald-100 text-emerald-800 border-l-4 border-emerald-500 rounded-r-xl flex items-center fade-in shadow-sm">
                <i class="fas fa-check-circle text-xl mr-3"></i>
                <span class="font-bold">Table assigned successfully and reservation confirmed!</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['table_unavailable'])): ?>
            <div class="mb-8 p-4 bg-rose-100 text-rose-800 border-l-4 border-rose-500 rounded-r-xl flex items-center fade-in shadow-sm">
                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                <span class="font-bold">Selected table is no longer available. Please choose another table.</span>
            </div>
        <?php endif; ?>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Reservations</p>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2"><?php echo $total_count; ?></p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Pending</p>
                        <p class="text-3xl font-extrabold text-amber-500 mt-2"><?php echo $status_counts['pending'] ?? 0; ?></p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Confirmed</p>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-2"><?php echo $status_counts['confirmed'] ?? 0; ?></p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Today</p>
                        <p class="text-3xl font-extrabold text-blue-600 mt-2">
                            <?php 
                            $today = date('Y-m-d');
                            $today_sql = "SELECT COUNT(*) as count FROM reservations WHERE reservation_date = ?";
                            $today_stmt = $conn->prepare($today_sql);
                            $today_stmt->bind_param("s", $today);
                            $today_stmt->execute();
                            $today_result = $today_stmt->get_result();
                            $today_count = $today_result->fetch_assoc()['count'];
                            echo $today_count;
                            ?>
                        </p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="fas fa-calendar-day text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-white p-6 mb-8 rounded-[2rem] shadow-sm border border-slate-100">
            <div class="flex flex-col sm:flex-row gap-4 items-center">
                <div class="flex-1 w-full">
                    <form method="GET" class="flex flex-col sm:flex-row gap-4 w-full">
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                            <input type="text" name="search" placeholder="Search by name, email, or phone..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   class="w-full bg-slate-50 border-none rounded-xl pl-10 pr-4 py-3 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 placeholder-slate-400">
                        </div>
                        <div class="relative">
                            <i class="fas fa-filter absolute left-4 top-3.5 text-slate-400"></i>
                            <select name="status" 
                                    class="bg-slate-50 border-none rounded-xl pl-10 pr-10 py-3 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 appearance-none cursor-pointer">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-emerald-600 text-white px-6 py-3 rounded-xl hover:bg-emerald-700 transition font-bold shadow-lg shadow-emerald-200">
                                Apply
                            </button>
                            <a href="admin_reservations.php" class="bg-slate-100 text-slate-500 px-4 py-3 rounded-xl hover:bg-slate-200 transition font-bold flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
    <div class="hidden lg:block bg-white overflow-hidden rounded-[2.5rem] shadow-sm border border-slate-100 fade-in" style="animation-delay: 0.2s">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-slate-50/30 border-b border-slate-100">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Registration Info</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Schedule</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest Count</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Table Assignment</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($reservations as $reservation): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center font-black text-lg">
                                        <?= strtoupper(substr($reservation['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800"><?= htmlspecialchars($reservation['full_name']) ?></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($reservation['phone']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-slate-700"><?= $reservation['formatted_date'] ?></p>
                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest"><?= $reservation['formatted_time'] ?></p>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-black"><?= $reservation['guests'] ?></span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Persons</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-wrap gap-1">
                                    <?php if (!empty($reservation['assigned_tables'])): ?>
                                        <?php $table_ids = explode(',', $reservation['assigned_tables']);
                                        foreach ($table_ids as $table_id): ?>
                                            <span class="bg-slate-800 text-white px-3 py-1 rounded-lg text-[9px] font-black tracking-widest">T-<?= $table_id ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-300 font-bold italic">Unassigned</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="status-badge status-<?= $reservation['status'] ?>"><?= $reservation['status'] ?></span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-all">
                                    <?php if ($reservation['status'] == 'pending' && empty($reservation['assigned_tables'])): ?>
                                        <button onclick="openTableModal(<?= $reservation['reservation_id'] ?>, <?= $reservation['guests'] ?>)" class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all"><i class="fas fa-chair text-xs"></i></button>
                                    <?php endif; ?>
                                    <a href="view_reservation.php?id=<?= $reservation['reservation_id'] ?>" class="h-10 w-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all"><i class="fas fa-eye text-xs"></i></a>
                                    <a href="?delete=<?= $reservation['reservation_id'] ?>" onclick="return confirm('Delete this booking?')" class="h-10 w-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all"><i class="fas fa-trash-alt text-xs"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-4 fade-in" style="animation-delay: 0.2s">
        <?php foreach ($reservations as $reservation): ?>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center font-black">
                            <?= strtoupper(substr($reservation['full_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 leading-none"><?= htmlspecialchars($reservation['full_name']) ?></h3>
                            <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-widest"><?= $reservation['phone'] ?></p>
                        </div>
                    </div>
                    <span class="status-badge status-<?= $reservation['status'] ?>"><?= $reservation['status'] ?></span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4 py-4 border-y border-slate-50">
                    <div>
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-1">Schedule</p>
                        <p class="text-xs font-black text-slate-700"><?= $reservation['formatted_date'] ?></p>
                        <p class="text-[10px] font-bold text-emerald-600 uppercase mt-0.5"><?= $reservation['formatted_time'] ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-1">Guests</p>
                        <p class="text-sm font-black text-slate-800"><?= $reservation['guests'] ?> <span class="text-[10px] text-slate-400 font-bold ml-1">PAX</span></p>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <?php if ($reservation['status'] == 'pending' && empty($reservation['assigned_tables'])): ?>
                        <button onclick="openTableModal(<?= $reservation['reservation_id'] ?>, <?= $reservation['guests'] ?>)" class="flex-1 bg-indigo-50 text-indigo-600 h-11 rounded-2xl font-black text-xs flex items-center justify-center gap-2">
                            <i class="fas fa-chair"></i> Assign
                        </button>
                    <?php endif; ?>
                    <a href="view_reservation.php?id=<?= $reservation['reservation_id'] ?>" class="flex-1 bg-slate-50 text-slate-600 h-11 rounded-2xl font-black text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-eye"></i> Details
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
</main>


<div id="tableModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-extrabold text-slate-800">Assign Table</h3>
            <button onclick="closeTableModal()" class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form id="tableForm" method="POST">
            <input type="hidden" name="reservation_id" id="reservation_id">
            <input type="hidden" name="assign_table" value="1">
            
            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                    <i class="fas fa-chair mr-1"></i> Available Tables
                </label>
                <div id="tableOptions" class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-60 overflow-y-auto p-1 custom-scrollbar">
                    
                </div>
                
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeTableModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                    Assign Table
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function viewReservation(id) {
        window.location.href = 'view_reservation.php?id=' + id;
    }
    
    // Table Booking Modal Functions
    function openTableModal(reservationId, guests) {
        document.getElementById('reservation_id').value = reservationId;
        const tableOptions = document.getElementById('tableOptions');
        tableOptions.innerHTML = '';
        
        // Filter tables based on capacity (tables should have at least guest capacity)
        const tables = <?php echo json_encode($available_tables); ?>;
        const suitableTables = tables.filter(table => table.capacity >= guests);
        
        suitableTables.forEach(table => {
            const tableOption = document.createElement('div');
            tableOption.className = 'relative group';
            tableOption.innerHTML = `
                <input type="radio" name="table_id" value="${table.table_id}" id="table_${table.table_id}" class="peer hidden">
                <label for="table_${table.table_id}" class="block cursor-pointer bg-slate-50 border-2 border-transparent rounded-xl p-3 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 group-hover:bg-white group-hover:shadow-md">
                    <div class="font-extrabold text-slate-700 text-lg peer-checked:text-emerald-700">T-${table.table_number}</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mt-1 peer-checked:text-emerald-600">Cap: ${table.capacity}</div>
                </label>
            `;
            tableOptions.appendChild(tableOption);
        });
        
        if (suitableTables.length === 0) {
            tableOptions.innerHTML = `
                <div class="col-span-3 text-center py-8">
                    <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exclamation text-amber-500"></i>
                    </div>
                    <p class="font-bold text-slate-600 mb-1">No suitable tables.</p>
                    <p class="text-xs text-slate-400 font-bold">Try splitting the reservation.</p>
                </div>
            `;
        }
        
        document.getElementById('tableModal').style.display = 'flex';
    }
    
    function closeTableModal() {
        document.getElementById('tableModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('tableModal');
        if (event.target === modal) {
            closeTableModal();
        }
    });
    
    // Auto-refresh every 60 seconds
    setTimeout(() => {
        window.location.reload();
    }, 60000);
</script>
</body>
</html>
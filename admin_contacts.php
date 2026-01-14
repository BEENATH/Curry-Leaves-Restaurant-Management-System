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
    $contact_id = $_POST['contact_id'];
    $new_status = $_POST['status'];
    
    $sql = "UPDATE contacts SET status = ? WHERE contact_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $contact_id);
    $stmt->execute();
    
    header("Location: admin_contacts.php?updated=true");
    exit;
}


if (isset($_GET['delete'])) {
    $contact_id = $_GET['delete'];
    $sql = "DELETE FROM contacts WHERE contact_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $contact_id);
    $stmt->execute();
    
    header("Location: admin_contacts.php?deleted=true");
    exit;
}


$sql = "SELECT * FROM contacts WHERE 1=1";
$params = [];
$types = "";

if ($status_filter != 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search_term)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$contacts = $result->fetch_all(MYSQLI_ASSOC);


$counts_sql = "SELECT status, COUNT(*) as count FROM contacts GROUP BY status";
$counts_result = $conn->query($counts_sql);
$status_counts = [];
while ($row = $counts_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
$total_count = array_sum($status_counts);
$unread_count = $status_counts['unread'] ?? 0;

$page_title = "Contact Messages | Curry Leaves";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-6 lg:p-10 transition-all duration-300 min-w-0">
    <div class="fade-in">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight mb-2">
                    Message Center
                </h1>
                <p class="text-slate-500 font-medium">Manage customer inquiries and feedback</p>
            </div>
            <a href="admin_contacts.php" class="bg-white text-slate-600 px-5 py-2.5 rounded-xl hover:bg-slate-50 border border-slate-200 font-bold text-sm shadow-sm transition flex items-center gap-2">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="mb-8 p-4 bg-emerald-100 text-emerald-800 border-l-4 border-emerald-500 rounded-r-xl flex items-center fade-in shadow-sm">
                <i class="fas fa-check-circle text-xl mr-3"></i>
                <span class="font-bold">Message status updated successfully!</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-8 p-4 bg-rose-100 text-rose-800 border-l-4 border-rose-500 rounded-r-xl flex items-center fade-in shadow-sm">
                <i class="fas fa-trash-alt text-xl mr-3"></i>
                <span class="font-bold">Message deleted successfully!</span>
            </div>
        <?php endif; ?>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Messages</p>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2"><?php echo $total_count; ?></p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="fas fa-envelope-open-text text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Unread</p>
                        <p class="text-3xl font-extrabold text-amber-500 mt-2"><?php echo $unread_count; ?></p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500">
                        <i class="fas fa-bell text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Replied</p>
                        <p class="text-3xl font-extrabold text-emerald-600 mt-2"><?php echo $status_counts['replied'] ?? 0; ?></p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-reply text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Today</p>
                        <p class="text-3xl font-extrabold text-indigo-600 mt-2">
                            <?php 
                            $today = date('Y-m-d');
                            $today_sql = "SELECT COUNT(*) as count FROM contacts WHERE DATE(created_at) = ?";
                            $today_stmt = $conn->prepare($today_sql);
                            $today_stmt->bind_param("s", $today);
                            $today_stmt->execute();
                            $today_result = $today_stmt->get_result();
                            $today_count = $today_result->fetch_assoc()['count'];
                            echo $today_count;
                            ?>
                        </p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
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
                            <input type="text" name="search" placeholder="Search messages by name, email or subject..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   class="w-full bg-slate-50 border-none rounded-xl pl-10 pr-4 py-3 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 placeholder-slate-400">
                        </div>
                        <div class="relative">
                            <i class="fas fa-filter absolute left-4 top-3.5 text-slate-400"></i>
                            <select name="status" 
                                    class="bg-slate-50 border-none rounded-xl pl-10 pr-10 py-3 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 appearance-none cursor-pointer">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="unread" <?php echo $status_filter == 'unread' ? 'selected' : ''; ?>>Unread</option>
                                <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-emerald-600 text-white px-6 py-3 rounded-xl hover:bg-emerald-700 transition font-bold shadow-lg shadow-emerald-200">
                                Apply
                            </button>
                            <a href="admin_contacts.php" class="bg-slate-100 text-slate-500 px-4 py-3 rounded-xl hover:bg-slate-200 transition font-bold flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>
                <!-- <div class="text-xs font-bold text-slate-400 uppercase tracking-widest hidden lg:block">
                    Showing <?php echo count($contacts); ?> of <?php echo $total_count; ?>
                </div> -->
            </div>
        </div>

        
    <div class="hidden lg:block bg-white overflow-hidden rounded-[2.5rem] shadow-sm border border-slate-100 fade-in" style="animation-delay: 0.2s">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-slate-50/30 border-b border-slate-100">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Sender</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Inquiry Details</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Received</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($contacts as $contact): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center font-black text-lg">
                                        <?= strtoupper(substr($contact['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800"><?= htmlspecialchars($contact['name']) ?></p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($contact['email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-slate-700 truncate max-w-[200px]"><?= htmlspecialchars($contact['subject']) ?></p>
                                <p class="text-[10px] text-slate-400 font-bold mt-1 truncate max-w-[300px]"><?= htmlspecialchars($contact['message']) ?></p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-slate-700"><?= date('M d, Y', strtotime($contact['created_at'])) ?></p>
                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest"><?= date('h:i A', strtotime($contact['created_at'])) ?></p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="status-badge status-<?= $contact['status'] ?>"><?= $contact['status'] ?></span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-all">
                                    <a href="mailto:<?= urlencode($contact['email']) ?>" class="h-10 w-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all"><i class="fas fa-reply text-xs"></i></a>
                                    <a href="view_contact.php?id=<?= $contact['contact_id'] ?>" class="h-10 w-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all"><i class="fas fa-eye text-xs"></i></a>
                                    <a href="?delete=<?= $contact['contact_id'] ?>" onclick="return confirm('Delete this message?')" class="h-10 w-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all"><i class="fas fa-trash-alt text-xs"></i></a>
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
        <?php foreach ($contacts as $contact): ?>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center font-black">
                            <?= strtoupper(substr($contact['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 leading-none"><?= htmlspecialchars($contact['name']) ?></h3>
                            <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-widest"><?= $contact['email'] ?></p>
                        </div>
                    </div>
                    <span class="status-badge status-<?= $contact['status'] ?>"><?= $contact['status'] ?></span>
                </div>

                <div class="py-4 border-y border-slate-50 mb-4">
                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-1">Subject</p>
                    <p class="text-xs font-black text-slate-700 truncate"><?= htmlspecialchars($contact['subject']) ?></p>
                    <p class="text-[10px] text-slate-400 font-bold mt-2 leading-relaxed line-clamp-2"><?= htmlspecialchars($contact['message']) ?></p>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase"><?= date('M d, h:i A', strtotime($contact['created_at'])) ?></p>
                    <div class="flex gap-2">
                        <a href="mailto:<?= urlencode($contact['email']) ?>" class="h-9 w-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center"><i class="fas fa-reply text-xs"></i></a>
                        <a href="view_contact.php?id=<?= $contact['contact_id'] ?>" class="h-9 w-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center"><i class="fas fa-eye text-xs"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
</main>

<script>
    function viewMessage(id) {
        window.location.href = 'view_contact.php?id=' + id;
    }
    
    // Auto-refresh every 60 seconds
    setTimeout(() => {
        window.location.reload();
    }, 60000);
</script>
</body>
</html>
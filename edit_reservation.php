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

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';


$sql = "SELECT * FROM reservations WHERE reservation_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();

if (!$reservation) {
    header("Location: admin_reservations.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $guests = $_POST['guests'] ?? 1;
    $occasion = $_POST['occasion'] ?? 'dinner';
    $status = $_POST['status'] ?? 'pending';
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || 
        empty($reservation_date) || empty($reservation_time)) {
        $error = "Check all required fields!";
    } else {
        $update_sql = "UPDATE reservations SET 
                        first_name = ?, last_name = ?, email = ?, phone = ?, 
                        reservation_date = ?, reservation_time = ?, guests = ?, 
                        occasion = ?, status = ?, special_requests = ? 
                       WHERE reservation_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssssisssi", $first_name, $last_name, $email, $phone, 
                                 $reservation_date, $reservation_time, $guests, 
                                 $occasion, $status, $special_requests, $id);
        
        if ($update_stmt->execute()) {
            $success = "Reservation updated successfully!";
            
            $reservation['status'] = $status;
        } else {
            $error = "Update failed. Please try again.";
        }
    }
}

$page_title = "Edit Reservation #{$id} | Curry Leaves";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-6 lg:p-10 transition-all duration-300 min-w-0">
    <div class="max-w-4xl mx-auto fade-in">
        <div class="mb-8">
            <a href="view_reservation.php?id=<?= $id ?>" class="text-slate-400 hover:text-emerald-600 font-bold text-xs mb-4 inline-block transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Details
            </a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Edit Reservation</h1>
                    <p class="text-slate-500 text-xs mt-1">Modifying booking #<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-8 p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 flex items-center gap-3 font-bold shadow-sm text-sm">
                <i class="fas fa-check-circle text-lg"></i>
                <div class="flex-1"><?php echo $success; ?></div>
                <a href="admin_reservations.php" class="text-xs underline hover:text-emerald-900">Return to List</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-8 p-4 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100 flex items-center gap-3 font-bold shadow-sm text-sm">
                <i class="fas fa-exclamation-circle text-lg"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white overflow-hidden rounded-[2rem] shadow-sm border border-slate-100">
            
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8 md:p-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold mb-1"><?= htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']) ?></h2>
                        <div class="flex items-center gap-2 opacity-90 text-[10px] uppercase font-black tracking-widest">
                            <i class="far fa-calendar-alt"></i>
                            <span><?= $reservation['reservation_date'] ?></span>
                        </div>
                    </div>
                    <div class="bg-white/10 p-4 rounded-2xl backdrop-blur-sm border border-white/20">
                        <label class="block text-[9px] font-black uppercase text-white/70 tracking-widest mb-2">Reservation Status</label>
                        <select name="status" form="editForm" class="bg-white text-slate-800 text-xs font-bold rounded-lg px-4 py-2 outline-none w-full min-w-[140px]">
                            <?php 
                            $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
                            foreach($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $reservation['status'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-8 md:p-10">
                <form id="editForm" method="POST" class="space-y-10">
                    
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2 mb-6">Customer Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">First Name</label>
                                <input type="text" name="first_name" required value="<?= htmlspecialchars($reservation['first_name']) ?>"
                                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Last Name</label>
                                <input type="text" name="last_name" required value="<?= htmlspecialchars($reservation['last_name']) ?>"
                                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Email Address</label>
                                <input type="email" name="email" required value="<?= htmlspecialchars($reservation['email']) ?>"
                                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Phone Number</label>
                                <input type="tel" name="phone" required value="<?= htmlspecialchars($reservation['phone']) ?>"
                                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>

                    
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2 mb-6">Booking Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Date</label>
                                <input type="date" name="reservation_date" required value="<?= $reservation['reservation_date'] ?>"
                                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Time</label>
                                <input type="time" name="reservation_time" required value="<?= $reservation['reservation_time'] ?>"
                                       class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Guests</label>
                                <select name="guests" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500 appearance-none">
                                    <?php for($i=1; $i<=20; $i++): ?>
                                        <option value="<?= $i ?>" <?= $reservation['guests'] == $i ? 'selected' : '' ?>><?= $i ?> People</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Occasion</label>
                                <select name="occasion" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-1 focus:ring-emerald-500 appearance-none">
                                    <?php 
                                    $occasions = ['dinner', 'birthday', 'anniversary', 'business', 'celebration', 'romantic'];
                                    foreach($occasions as $occ): ?>
                                        <option value="<?= $occ ?>" <?= $reservation['occasion'] == $occ ? 'selected' : '' ?>><?= ucfirst($occ) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Special Requests</label>
                        <textarea name="special_requests" rows="3" 
                                  class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-medium text-slate-600 focus:ring-1 focus:ring-emerald-500 resize-none"><?= htmlspecialchars($reservation['special_requests']) ?></textarea>
                    </div>

                    <div class="pt-6 border-t border-slate-50 flex justify-end gap-3">
                        <button type="reset" class="px-6 py-3 rounded-xl text-xs font-bold text-slate-400 hover:bg-slate-50 transition">Discard Changes</button>
                        <button type="submit" class="bg-slate-900 text-white px-8 py-3 rounded-xl text-xs font-bold hover:bg-black transition shadow-lg shadow-slate-100 flex items-center gap-2">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <p class="text-center text-slate-400 text-[10px] mt-10 mb-10 font-bold uppercase tracking-widest">© 2026 Curry Leaves Restaurant. Edit Module.</p>
    </div>
</main>
</body>
</html>

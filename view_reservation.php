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

$sql = "SELECT r.*, 
               CONCAT(r.first_name, ' ', r.last_name) as full_name,
               DATE_FORMAT(r.reservation_date, '%b %d, %Y') as formatted_date,
               TIME_FORMAT(r.reservation_time, '%h:%i %p') as formatted_time,
               DATE_FORMAT(r.created_at, '%b %d, %Y %h:%i %p') as created_formatted,
               DATE_FORMAT(r.updated_at, '%b %d, %Y %h:%i %p') as updated_formatted
        FROM reservations r WHERE reservation_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();

if (!$reservation) {
    header("Location: admin_reservations.php");
    exit;
}
?>

<?php include 'includes/head.php'; ?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-6 lg:p-10 transition-all duration-300 min-w-0">
    <div class="max-w-4xl mx-auto fade-in">
        <div class="mb-8">
            <a href="admin_reservations.php" class="text-slate-400 hover:text-emerald-600 font-bold text-xs mb-4 inline-block transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Reservations
            </a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Reservation Details</h1>
                    <p class="text-slate-500 text-xs mt-1">ID: #<?php echo str_pad($reservation['reservation_id'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div>
                    <?php 
                        $status_colors = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'confirmed' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-rose-100 text-rose-700',
                            'completed' => 'bg-blue-100 text-blue-700'
                        ];
                        $color_class = $status_colors[$reservation['status']] ?? 'bg-slate-100 text-slate-600';
                    ?>
                    <span class="<?php echo $color_class; ?> px-4 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wide shadow-sm">
                        <?php echo $reservation['status']; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden rounded-[2rem] shadow-sm border border-slate-100">
            
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8 md:p-10">
                <h2 class="text-xl md:text-2xl font-bold mb-2"><?php echo htmlspecialchars($reservation['full_name']); ?></h2>
                <div class="flex items-center gap-2 opacity-90 text-[10px] uppercase font-black tracking-widest">
                    <i class="far fa-calendar-alt"></i>
                    <span><?php echo $reservation['formatted_date']; ?></span>
                    <span class="mx-1">•</span>
                    <i class="far fa-clock"></i>
                    <span><?php echo $reservation['formatted_time']; ?></span>
                </div>
            </div>

            
            <div class="p-8 md:p-10 space-y-10">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">
                            <i class="fas fa-user-circle mr-1"></i> Customer Information
                        </h3>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Full Name</p>
                            <p class="text-base font-bold text-slate-800"><?php echo htmlspecialchars($reservation['full_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Email Address</p>
                            <p class="text-base font-bold text-slate-800"><?php echo htmlspecialchars($reservation['email']); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Phone Number</p>
                            <p class="text-base font-bold text-slate-800"><?php echo htmlspecialchars($reservation['phone']); ?></p>
                        </div>
                    </div>

                    
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">
                            <i class="fas fa-info-circle mr-1"></i> Reservation Details
                        </h3>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Number of Guests</p>
                            <p class="text-base font-bold text-slate-800"><?php echo $reservation['guests']; ?> people</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Occasion</p>
                            <span class="inline-block bg-slate-100 px-3 py-1 rounded-lg text-[10px] font-black text-slate-600 uppercase tracking-wide">
                                <?php echo $reservation['occasion']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                
                <?php if (!empty($reservation['special_requests'])): ?>
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2 mb-6">
                        <i class="fas fa-sticky-note mr-1"></i> Special Requests
                    </h3>
                    <div class="bg-slate-50 rounded-2xl p-6 md:p-8 border border-slate-100">
                        <p class="text-slate-700 font-medium leading-relaxed text-sm italic">"<?php echo nl2br(htmlspecialchars($reservation['special_requests'])); ?>"</p>
                    </div>
                </div>
                <?php endif; ?>

                
                <div class="pt-8 border-t border-slate-50">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                        <div class="flex gap-8">
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Created</p>
                                <p class="text-xs font-bold text-slate-600"><?php echo $reservation['created_formatted']; ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Updated</p>
                                <p class="text-xs font-bold text-slate-600"><?php echo $reservation['updated_formatted']; ?></p>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <a href="mailto:<?php echo urlencode($reservation['email']); ?>?subject=Reservation #<?php echo $reservation['reservation_id']; ?>"
                               class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-emerald-700 transition flex items-center gap-2 shadow-lg shadow-emerald-100 text-xs">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <a href="edit_reservation.php?id=<?php echo $reservation['reservation_id']; ?>"
                               class="bg-amber-500 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-amber-600 transition flex items-center gap-2 shadow-lg shadow-amber-100 text-xs">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="admin_reservations.php?delete=<?php echo $reservation['reservation_id']; ?>" 
                               onclick="return confirm('Delete this reservation?')"
                               class="bg-rose-50 text-rose-600 px-5 py-2.5 rounded-xl font-bold hover:bg-rose-600 hover:text-white transition flex items-center gap-2 text-xs">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="text-center text-slate-400 text-[10px] mt-10 mb-10 font-bold uppercase tracking-widest">© 2026 Curry Leaves Restaurant. Reservation Module.</p>
    </div>
</main>
</body>
</html>
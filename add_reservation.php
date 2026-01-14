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





$success = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $guests = $_POST['guests'] ?? 1;
    $occasion = $_POST['occasion'] ?? 'dinner';
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || 
        empty($reservation_date) || empty($reservation_time) || empty($guests)) {
        $error = "All required fields must be filled!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif ($guests < 1 || $guests > 20) {
        $error = "Number of guests must be between 1 and 20!";
    } else {
        
        $selected_datetime = strtotime("$reservation_date $reservation_time");
        if ($selected_datetime < time()) {
            $error = "Reservation date and time must be in the future!";
        } else {
            
            $sql = "INSERT INTO reservations (first_name, last_name, email, phone, 
                    reservation_date, reservation_time, guests, occasion, special_requests) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssiss", $first_name, $last_name, $email, $phone, 
                             $reservation_date, $reservation_time, $guests, $occasion, $special_requests);
            
            if ($stmt->execute()) {
                $success = "Reservation submitted successfully!";
                
                $first_name = $last_name = $email = $phone = $reservation_date = $reservation_time = $special_requests = '';
                $guests = 1;
            } else {
                $error = "Failed to submit reservation. Please try again.";
            }
        }
    }
}

$page_title = "Make Reservation | Curry Leaves";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-8 lg:p-12 transition-all duration-300">
    <div class="max-w-4xl mx-auto fade-in">
        <div class="mb-8">
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Make a Reservation</h1>
            <p class="text-slate-500 mt-2">Book your table for an unforgettable dining experience.</p>
        </div>

        <?php if ($success): ?>
            <div class="mb-8 p-4 bg-emerald-100 text-emerald-700 rounded-2xl border border-emerald-200 flex items-center gap-3 font-bold shadow-sm">
                <i class="fas fa-check-circle text-xl"></i>
                <div class="flex-1"><?php echo htmlspecialchars($success); ?></div>
                <a href="admin_reservations.php" class="text-sm underline hover:text-emerald-900">View All</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-8 p-4 bg-rose-100 text-rose-700 rounded-2xl border border-rose-200 flex items-center gap-3 font-bold shadow-sm">
                <i class="fas fa-exclamation-circle text-xl"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card p-10 rounded-[2.5rem] border border-white/50 shadow-xl">
            <form method="POST" class="space-y-8">
                
                
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-6">Contact Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">First Name</label>
                            <input type="text" name="first_name" required value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Last Name</label>
                            <input type="text" name="last_name" required value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Email Address</label>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Phone Number</label>
                            <input type="tel" name="phone" required value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                    </div>
                </div>

                
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-6">Booking Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Date</label>
                            <input type="date" name="reservation_date" id="resDate" required min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo htmlspecialchars($reservation_date ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Time</label>
                            <input type="time" name="reservation_time" id="resTime" required min="10:00" max="22:00"
                                   value="<?php echo htmlspecialchars($reservation_time ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Number of Guests</label>
                            <select name="guests" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                                <?php for ($i = 1; $i <= 20; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($guests == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Person<?php echo ($i > 1) ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Occasion</label>
                            <select name="occasion" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                                <?php 
                                $occasions = ['Dinner', 'Birthday', 'Anniversary', 'Business Meeting', 'Celebration', 'Romantic'];
                                foreach ($occasions as $occ): 
                                ?>
                                    <option value="<?php echo strtolower($occ); ?>" <?php echo (($occasion ?? '') == strtolower($occ)) ? 'selected' : ''; ?>>
                                        <?php echo $occ; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                
                <div>
                     <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Special Requests</label>
                     <textarea name="special_requests" rows="3" placeholder="Allergies, high chair needed, etc."
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-medium text-slate-600 outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-none"><?php echo htmlspecialchars($special_requests ?? ''); ?></textarea>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end gap-4">
                    <button type="reset" class="bg-slate-100 text-slate-600 px-8 py-4 rounded-xl font-bold hover:bg-slate-200 transition-all">Reset</button>
                    <button type="submit" class="bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200 hover:-translate-y-1">
                        <i class="fas fa-calendar-check mr-2"></i> Book Table
                    </button>
                </div>

            </form>
        </div>
        
        <p class="text-center text-slate-400 text-xs mt-10 mb-10 font-medium">© 2026 Curry Leaves Restaurant.</p>
    </div>
</main>

<script>
    // Simple logic to default time if empty
    if(!document.getElementById('resDate').value) {
        document.getElementById('resDate').valueAsDate = new Date();
    }
</script>

</body>
</html>
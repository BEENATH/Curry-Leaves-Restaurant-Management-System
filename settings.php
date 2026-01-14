<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';


if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit;
}

$employee_id = $_SESSION['employee_id'];
$success_msg = '';
$error_msg = '';


$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        
        if (empty($name) || empty($email) || empty($username)) {
            $error_msg = "All fields are required.";
        } else {
            
            $update_stmt = $conn->prepare("UPDATE employees SET name = ?, email = ?, username = ? WHERE employee_id = ?");
            $update_stmt->bind_param("sssi", $name, $email, $username, $employee_id);
            
            if ($update_stmt->execute()) {
                $success_msg = "Profile updated successfully!";
                $_SESSION['employee_name'] = $name; 
                
                $current_user['name'] = $name;
                $current_user['email'] = $email;
                $current_user['username'] = $username;
            } else {
                $error_msg = "Error updating profile. Username or Email might be taken.";
            }
        }
    }
    
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password)) {
            $error_msg = "Please fill in all password fields.";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "New passwords do not match.";
        } elseif (!password_verify($current_password, $current_user['password'])) {
            $error_msg = "Incorrect current password.";
        } else {
            
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $pwd_stmt = $conn->prepare("UPDATE employees SET password = ? WHERE employee_id = ?");
            $pwd_stmt->bind_param("si", $new_hash, $employee_id);
            
            if ($pwd_stmt->execute()) {
                $success_msg = "Password changed successfully!";
            } else {
                $error_msg = "Error updating password.";
            }
        }
    }

    
    if (isset($_POST['update_notifications'])) {
        
        
        $success_msg = "Notification preferences updated successfully!";
    }
}

$page_title = "Curry Leaves | Settings";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300 min-w-0">
    <div class="max-w-5xl mx-auto">
        <div class="mb-12 fade-in">
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Intelligence Config</h1>
            <p class="text-slate-500 mt-2">Harmonize your account parameters and interface behavior</p>
        </div>

        <?php if ($success_msg): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 fade-in shadow-sm">
                <i class="fas fa-check-circle"></i> <span class="text-sm font-bold"><?= htmlspecialchars($success_msg) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="bg-rose-50 border border-rose-100 text-rose-700 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 fade-in shadow-sm">
                <i class="fas fa-exclamation-circle"></i> <span class="text-sm font-bold"><?= htmlspecialchars($error_msg) ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 fade-in" style="animation-delay: 0.1s">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden lg:sticky lg:top-8">
                    <nav class="flex lg:flex-col p-2 overflow-x-auto lg:overflow-x-visible no-scrollbar">
                        <a href="#profile" class="flex-none lg:flex-1 flex items-center gap-3 px-6 py-4 rounded-2xl bg-emerald-50 text-emerald-700 font-black text-[11px] uppercase tracking-widest transition-all whitespace-nowrap lg:border-l-4 lg:border-emerald-500 lg:rounded-r-none">
                            <i class="fas fa-user-circle w-5"></i> Profile
                        </a>
                        <a href="#security" class="flex-none lg:flex-1 flex items-center gap-3 px-6 py-4 rounded-2xl text-slate-400 font-black text-[11px] uppercase tracking-widest hover:bg-slate-50 hover:text-slate-800 transition-all whitespace-nowrap lg:border-l-4 lg:border-transparent lg:rounded-r-none">
                            <i class="fas fa-shield-halved w-5"></i> Security
                        </a>
                        <a href="#notifications" class="flex-none lg:flex-1 flex items-center gap-3 px-6 py-4 rounded-2xl text-slate-400 font-black text-[11px] uppercase tracking-widest hover:bg-slate-50 hover:text-slate-800 transition-all whitespace-nowrap lg:border-l-4 lg:border-transparent lg:rounded-r-none">
                            <i class="fas fa-bell w-5"></i> Alerts
                        </a>
                    </nav>
                </div>
            </div>

            <div class="lg:col-span-3 space-y-8">
                <section id="profile" class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -top-12 -right-12 opacity-[0.02] pointer-events-none group-hover:scale-110 transition-transform">
                        <i class="fas fa-id-badge text-[15rem] text-slate-900"></i>
                    </div>
                    <div class="flex items-center gap-4 mb-8">
                         <div class="h-12 w-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                             <i class="fas fa-user-pen"></i>
                         </div>
                         <h2 class="text-xl font-black text-slate-800 tracking-tight">Identity Matrix</h2>
                    </div>
                    <form method="POST" class="space-y-8 relative z-10">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Legal Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($current_user['name']) ?>" required class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Cloud Interface (Email)</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($current_user['email']) ?>" required class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Alias (Username)</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($current_user['username']) ?>" required class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">System Privilege</label>
                                <input type="text" value="<?= htmlspecialchars($current_user['role']) ?>" disabled class="w-full bg-slate-100 border-none rounded-2xl px-5 py-4 text-sm font-black text-slate-400 cursor-not-allowed uppercase tracking-widest">
                            </div>
                        </div>
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-10 rounded-2xl transition-all shadow-xl shadow-emerald-100 hover:-translate-y-1">
                                Save Configuration
                            </button>
                        </div>
                    </form>
                </section>

                 <section id="security" class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -top-12 -right-12 opacity-[0.02] pointer-events-none group-hover:scale-110 transition-transform">
                        <i class="fas fa-key text-[15rem] text-slate-900"></i>
                    </div>
                    <div class="flex items-center gap-4 mb-8">
                         <div class="h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-lg">
                             <i class="fas fa-fingerprint"></i>
                         </div>
                         <h2 class="text-xl font-black text-slate-800 tracking-tight">Access Protocol</h2>
                    </div>
                    <form method="POST" class="space-y-8 relative z-10">
                        <input type="hidden" name="change_password" value="1">
                        <div class="space-y-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Active Authentication Key</label>
                                <input type="password" name="current_password" required class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Target Key</label>
                                    <input type="password" name="new_password" required class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Confirm Target Key</label>
                                    <input type="password" name="confirm_password" required class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-slate-900 hover:bg-black text-white font-black py-4 px-10 rounded-2xl transition-all shadow-xl shadow-slate-100 hover:-translate-y-1">
                                Rotation Update
                            </button>
                        </div>
                    </form>
                </section>

                <section id="notifications" class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -top-12 -right-12 opacity-[0.02] pointer-events-none group-hover:scale-110 transition-transform">
                        <i class="fas fa-tower-broadcast text-[15rem] text-slate-900"></i>
                    </div>
                    <div class="flex items-center gap-4 mb-10">
                         <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                             <i class="fas fa-satellite-dish"></i>
                         </div>
                         <h2 class="text-xl font-black text-slate-800 tracking-tight">Signal Parameters</h2>
                    </div>
                    <form method="POST" class="relative z-10">
                        <input type="hidden" name="update_notifications" value="1">
                        <div class="space-y-4">
                            <?php 
                            $notifs = [
                                ['id' => 'notify_orders', 'title' => 'Stream Inbound Orders', 'desc' => 'Instantaneous visual and auditory feedback on new inventory requests', 'icon' => 'fa-cart-shopping'],
                                ['id' => 'notify_reservations', 'title' => 'Logistics Reservation Flux', 'desc' => 'Track table allocations and temporal seat bookings in real-time', 'icon' => 'fa-chair'],
                                ['id' => 'notify_stock', 'title' => 'Inventory Threshold Warnings', 'desc' => 'Receive critical signal when menu components reach depletion level', 'icon' => 'fa-boxes-stacked'],
                                ['id' => 'notify_reports', 'title' => 'Cyclical Intelligence Summary', 'desc' => 'Automated generation of daily performance metrics at cycle close', 'icon' => 'fa-chart-pie']
                            ];
                            foreach($notifs as $n):
                            ?>
                            <div class="flex items-center justify-between p-6 bg-slate-50 rounded-3xl border border-transparent hover:border-emerald-100 transition-all group/item">
                                <div class="flex items-center gap-5">
                                    <div class="h-10 w-10 rounded-xl bg-white text-slate-400 group-hover/item:text-emerald-500 transition-colors flex items-center justify-center shadow-sm">
                                        <i class="fas <?= $n['icon'] ?> text-xs"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-slate-800 text-sm"><?= $n['title'] ?></h3>
                                        <p class="text-[10px] text-slate-400 font-bold mt-1 leading-relaxed max-w-[250px] md:max-w-none"><?= $n['desc'] ?></p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-4">
                                    <input type="checkbox" name="<?= $n['id'] ?>" <?= $n['id'] != 'notify_stock' ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex justify-end mt-12">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-10 rounded-2xl transition-all shadow-xl shadow-emerald-100 hover:-translate-y-1">
                                Save Signal Matrix
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</main>
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</body>
</html>

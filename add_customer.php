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
$successMessage = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $phone   = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)")) {
        $stmt->bind_param("ssss", $name, $email, $phone, $address);
        if ($stmt->execute()) {
            $successMessage = "$name has been added successfully!";
        } else {
            $successMessage = "❌ Error: Could not add customer.";
        }
    }
}

$page_title = "Add Customer | Curry Leaves";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-8 lg:p-12 transition-all duration-300">
    
    <div class="max-w-3xl mx-auto fade-in">
        <div class="mb-8">
            <a href="view_customers.php" class="text-slate-400 hover:text-slate-600 font-bold text-sm mb-4 inline-block transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Customers
            </a>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Add Customer</h1>
            <p class="text-slate-500 mt-2">Register a new profile in the restaurant database.</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="mb-8 p-6 bg-emerald-50 text-emerald-800 border-l-4 border-emerald-500 rounded-3xl flex items-center gap-4 shadow-sm">
                <i class="fas fa-check-circle text-2xl"></i> 
                <span class="font-bold"><?= htmlspecialchars($successMessage) ?></span>
            </div>
        <?php endif; ?>

        <div class="glass-card p-10 rounded-[2.5rem] border border-white/50 shadow-xl">
            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Full Name</label>
                            <input type="text" name="name" required placeholder="John Doe" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Email Address</label>
                            <input type="email" name="email" required placeholder="john@example.com" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Phone Number</label>
                        <input type="text" name="phone" required placeholder="+1 234 567 890" 
                               class="w-full md:w-1/2 bg-slate-50 border border-slate-200 rounded-xl p-4 font-bold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Home Address</label>
                        <textarea name="address" rows="3" required placeholder="Enter street, city, and zip code..." 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 font-medium text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-none"></textarea>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-xl font-bold transition-all shadow-lg shadow-emerald-200 hover:-translate-y-1 flex items-center gap-2">
                        <i class="fas fa-save"></i> Save Customer Profile
                    </button>
                </div>
            </form>
        </div>
        
        <p class="text-center text-slate-400 text-xs mt-10 mb-10 font-medium">© 2026 Curry Leaves Restaurant. CRM Module.</p>
    </div>
</main>
</body>
</html>
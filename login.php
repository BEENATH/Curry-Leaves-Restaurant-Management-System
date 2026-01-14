<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (isset($_SESSION['employee_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            if (password_verify($password, $row['password'])) {
                
                session_regenerate_id(true);
                
                $_SESSION['employee_id'] = $row['employee_id'];
                $_SESSION['employee_name'] = $row['name'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['last_login'] = time();
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Curry Leaves Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #f0fdf4 100%);
            position: relative;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .food-icon {
            position: absolute;
            opacity: 0.1;
            z-index: 0;
            animation: float 8s ease-in-out infinite;
            color: #059669;
        }
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        /* Randomized positions via CSS for icons */
        .i1 { top: 10%; left: 10%; font-size: 4rem; animation-delay: 0s; }
        .i2 { top: 20%; right: 15%; font-size: 6rem; animation-delay: 1s; }
        .i3 { bottom: 15%; left: 15%; font-size: 5rem; animation-delay: 2s; }
        .i4 { bottom: 20%; right: 10%; font-size: 4rem; animation-delay: 3s; }
        .i5 { top: 50%; left: 5%; font-size: 3rem; animation-delay: 1.5s; }
        .i6 { top: 60%; right: 5%; font-size: 5rem; animation-delay: 2.5s; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 text-slate-800">
    
    
    <i class="food-icon i1 fas fa-leaf"></i>
    <i class="food-icon i2 fas fa-utensils"></i>
    <i class="food-icon i3 fas fa-pepper-hot"></i>
    <i class="food-icon i4 fas fa-wine-glass"></i>
    <i class="food-icon i5 fas fa-carrot"></i>
    <i class="food-icon i6 fas fa-lemon"></i>

    <div class="w-full max-w-md glass-card p-10 relative z-10 fade-in">
        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                <i class="fas fa-leaf text-4xl text-emerald-600"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Curry Leaves</h1>
            <p class="text-slate-500 font-bold mt-2 uppercase tracking-widest text-xs">Admin Portal</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 px-4 py-3 rounded-r-xl mb-6 flex items-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <span class="font-bold text-sm"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Username</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                    <input type="text" id="username" name="username" required placeholder="Enter your username"
                           class="w-full bg-slate-50 border-transparent focus:bg-white focus:border-emerald-500 rounded-xl pl-10 pr-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-all placeholder-slate-400">
                </div>
            </div>
            
            <div>
                <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-3.5 text-slate-400 text-sm"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••"
                           class="w-full bg-slate-50 border-transparent focus:bg-white focus:border-emerald-500 rounded-xl pl-10 pr-12 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-all placeholder-slate-400">
                    <button type="button" onclick="togglePassword()" class="absolute right-4 top-3 text-slate-400 hover:text-emerald-600 transition">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            
            <div class="pt-2">
                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-emerald-200 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transform hover:-translate-y-1 transition-all duration-200">
                    Sign In to Dashboard
                </button>
            </div>
        </form>
        
        <div class="mt-8 text-center">
            <p class="text-slate-400 text-xs font-bold">
                &copy; <?php echo date('Y'); ?> Curry Leaves Restaurant
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
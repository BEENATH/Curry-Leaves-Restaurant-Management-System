<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curry Leaves | Restaurant Management System</title>
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
        /* Randomized positions */
        .i1 { top: 10%; left: 10%; font-size: 4rem; animation-delay: 0s; }
        .i2 { top: 20%; right: 15%; font-size: 6rem; animation-delay: 1s; }
        .i3 { bottom: 15%; left: 15%; font-size: 5rem; animation-delay: 2s; }
        .i4 { bottom: 20%; right: 10%; font-size: 4rem; animation-delay: 3s; }
        .i5 { top: 50%; left: 5%; font-size: 3rem; animation-delay: 1.5s; }
        .i6 { top: 60%; right: 5%; font-size: 5rem; animation-delay: 2.5s; }
        
        .btn-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 text-slate-800">
    
    
    <i class="food-icon i1 fas fa-leaf"></i>
    <i class="food-icon i2 fas fa-utensils"></i>
    <i class="food-icon i3 fas fa-pepper-hot"></i>
    <i class="food-icon i4 fas fa-wine-glass"></i>
    <i class="food-icon i5 fas fa-carrot"></i>
    <i class="food-icon i6 fas fa-lemon"></i>

    <div class="max-w-xl w-full p-12 glass-card text-center relative z-10 fade-in">
        <div class="mb-10">
            <div class="w-24 h-24 bg-emerald-100 rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-sm transform rotate-3 hover:rotate-6 transition-transform duration-300">
                <i class="fas fa-leaf text-5xl text-emerald-600"></i>
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
                Curry Leaves
            </h1>
            <p class="text-slate-500 text-lg font-medium leading-relaxed">
                Experience the finest culinary management system. Streamline your operations with elegance and efficiency.
            </p>
        </div>

        <div class="mt-8">
            <a href="login.php" class="block w-full py-4 px-8 bg-emerald-600 text-white rounded-2xl shadow-lg shadow-emerald-200 text-lg font-bold hover:bg-emerald-700 btn-hover flex items-center justify-center gap-3">
                <i class="fas fa-user-shield"></i> Admin Login
            </a>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-100">
            <div class="flex justify-center gap-6 text-slate-400 mb-6">
                <a href="#" class="hover:text-emerald-600 transition-colors hover:scale-110 transform"><i class="fab fa-facebook-f text-xl"></i></a>
                <a href="#" class="hover:text-emerald-600 transition-colors hover:scale-110 transform"><i class="fab fa-instagram text-xl"></i></a>
                <a href="#" class="hover:text-emerald-600 transition-colors hover:scale-110 transform"><i class="fab fa-twitter text-xl"></i></a>
            </div>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">
                &copy; <?php echo date('Y'); ?> Curry Leaves Restaurant
            </p>
        </div>
    </div>
</body>
</html>
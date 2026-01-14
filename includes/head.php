<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (!isset($page_title)) {
    $page_title = "Curry Leaves | Admin";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Common Utility Classes */
        .glass-card { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        .sidebar-link { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover { background: #f0fdf4; color: #10b981; transform: translateX(4px); }
        .sidebar-link.active { background: #ecfdf5; color: #047857; font-weight: 700; border-right: 3px solid #10b981; }
        
        /* Animation utilities */
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media print {
        aside, .no-print { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        body { background: white; }
    }
        /* Status Badges */
        .status-badge { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 4px 12px; border-radius: 99px; }
        
        /* Order Status Colors */
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-preparing { background: #e0f2fe; color: #075985; }
        .status-ready { background: #fae8ff; color: #86198f; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        /* General/Contact Status Colors */
        .status-unread { background: #fef3c7; color: #92400e; }
        .status-read { background: #d1fae5; color: #065f46; }
        .status-replied { background: #dbeafe; color: #1e40af; }
        .status-archived { background: #e5e7eb; color: #374151; }
        
        /* Reservation Status Colors */
        .status-confirmed { background: #d1fae5; color: #065f46; }

        /* Mobile Adjustments */
        @media (max-width: 1024px) {
            .main-content { margin-left: 0 !important; }
            .sidebar-open { overflow: hidden; }
        }

        /* Nav Glass effect */
        .nav-glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(241, 245, 249, 1);
        }

        /* Custom Hover Effects */
        .hover-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .hover-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    },
                    borderRadius: {
                        '3xl': '1.5rem',
                        '4xl': '2rem',
                        '5xl': '2.5rem',
                    }
                }
            }
        }
    </script>
</head>
<body class="flex min-h-screen bg-slate-50 text-slate-800 relative overflow-x-hidden">
    <!-- BEE.LK Branding Watermark -->
    <div class="fixed inset-0 pointer-events-none z-[9999] overflow-hidden opacity-[0.03] select-none">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -rotate-45 text-[20vw] font-black tracking-tighter whitespace-nowrap">
            BEE.LK
        </div>
    </div>

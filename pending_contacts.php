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


$sql = "SELECT * FROM contacts WHERE status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($sql);

$pending_contacts = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pending_contacts[] = $row;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_id']) && isset($_POST['status'])) {
    $contact_id = $conn->real_escape_string($_POST['contact_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $update_sql = "UPDATE contacts SET status = '$status', updated_at = NOW() WHERE id = '$contact_id'";
    if ($conn->query($update_sql)) {
        $_SESSION['success_message'] = "Contact status updated successfully!";
        header("Location: pending_contacts.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Error updating contact: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Contacts - Restaurant Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .header-gradient {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .header-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.15) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .contact-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }
        
        .badge-pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .badge-read {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .badge-replied {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .status-btn {
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            color: white;
        }
        
        .btn-read {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .btn-read:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-replied {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
        
        .btn-replied:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.5rem;
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }
        
        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
            background: linear-gradient(135deg, #4b5563, #374151);
        }
        
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #14b8a6, #0d9488);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 6px 20px rgba(20, 184, 166, 0.3);
        }
    </style>
</head>
<body>
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="glass-card mb-8">
                <div class="header-gradient text-center">
                    <h1 class="text-3xl font-bold mb-2">Pending Contacts</h1>
                    <p class="opacity-90">Manage and respond to customer inquiries</p>
                    <div class="mt-4">
                        <a href="dashboard.php" class="back-btn">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                        <p class="text-green-700 font-medium"><?= $_SESSION['success_message'] ?></p>
                    </div>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                        <p class="text-red-700 font-medium"><?= $_SESSION['error_message'] ?></p>
                    </div>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            
            <div class="glass-card">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-comments text-teal-500 mr-2"></i>
                            Pending Messages
                        </h2>
                        <div class="text-gray-600">
                            <span class="bg-teal-100 text-teal-800 px-3 py-1 rounded-full font-semibold">
                                <?= count($pending_contacts) ?> Pending
                            </span>
                        </div>
                    </div>
                    
                    <?php if (count($pending_contacts) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($pending_contacts as $contact): ?>
                                <div class="contact-card">
                                    <div class="p-5">
                                        <div class="flex items-start space-x-4 mb-4">
                                            <div class="contact-icon">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($contact['name']) ?></h3>
                                                <p class="text-sm text-gray-600"><?= htmlspecialchars($contact['email']) ?></p>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    <i class="far fa-clock mr-1"></i>
                                                    <?= date('M d, Y h:i A', strtotime($contact['created_at'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h4 class="font-semibold text-gray-700 mb-2">Subject:</h4>
                                            <p class="text-gray-800 bg-gray-50 p-3 rounded-lg"><?= htmlspecialchars($contact['subject']) ?></p>
                                        </div>
                                        
                                        <div class="mb-6">
                                            <h4 class="font-semibold text-gray-700 mb-2">Message:</h4>
                                            <p class="text-gray-600 bg-teal-50 p-4 rounded-lg"><?= nl2br(htmlspecialchars($contact['message'])) ?></p>
                                        </div>
                                        
                                        <form method="POST" class="space-y-3">
                                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                            
                                            <div class="flex space-x-3">
                                                <button type="submit" name="status" value="read" class="status-btn btn-read flex-1">
                                                    <i class="fas fa-check mr-2"></i> Mark as Read
                                                </button>
                                                <button type="submit" name="status" value="replied" class="status-btn btn-replied flex-1">
                                                    <i class="fas fa-reply mr-2"></i> Mark as Replied
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <div class="mt-4 pt-4 border-t border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-500">Contact ID: #<?= $contact['id'] ?></span>
                                                <span class="badge-pending px-3 py-1 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3 class="text-2xl font-bold text-gray-400 mb-2">No Pending Contacts</h3>
                            <p class="text-gray-500">All customer inquiries have been addressed.</p>
                            <p class="text-gray-400 text-sm mt-2">Great job staying on top of customer communication!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            
            <div class="mt-8 text-center text-gray-500 text-sm">
                <p>
                    <i class="fas fa-heart text-rose-500 mr-1"></i>
                    © 2025 BEE_EDITZ Restaurant Management System
                </p>
            </div>
        </div>
    </div>
</body>
</html>
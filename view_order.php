<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
require 'db.php';
session_start();


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$success = '';
$error = '';

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: view_orders.php");
    exit();
}

$order_id = intval($_GET['id']);


$order = [];
$sql = "SELECT o.*, c.name as customer_name, c.phone as customer_phone_default, 
               c.address as customer_address_default, c.email as customer_email
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Order not found";
    header("Location: view_orders.php");
    exit();
}

$order = $result->fetch_assoc();


$items = [];
$sql = "SELECT oi.*, mi.name as item_name, mi.image_path as item_image
        FROM order_items oi
        JOIN menu_items mi ON oi.menu_item_id = mi.item_id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if (!empty($row['item_image'])) {
        if (!preg_match('/^http/', $row['item_image']) && !file_exists($row['item_image'])) {
            $row['item_image'] = 'uploads/' . $row['item_image'];
        }
    } else {
        $row['item_image'] = 'uploads/default-food.jpg';
    }
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Restaurant System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        .main-heading {
            background: linear-gradient(90deg, #4f46e5, #10b981);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-badge.preparing {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-badge.completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-badge.cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .order-type-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            background-color: #eef2ff;
            color: #4f46e5;
        }
        .total-amount {
            color: #10b981;
            font-weight: bold;
        }
        .item-price {
            color: #3b82f6;
            font-weight: bold;
        }
    </style>
</head>
<body class="antialiased">
    
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold main-heading">Order #<?php echo $order_id; ?></h1>
                <p class="text-gray-600 mt-1">Order details and items</p>
                <div class="mt-2">
                    <span class="status-badge <?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                    <span class="order-type-badge ml-2">
                        <?php 
                        switch($order['order_type']) {
                            case 'dine_in': echo '<i class="fas fa-utensils mr-1"></i> Dine-In'; break;
                            case 'delivery': echo '<i class="fas fa-truck mr-1"></i> Delivery'; break;
                            case 'takeaway': echo '<i class="fas fa-shopping-bag mr-1"></i> Takeaway'; break;
                        }
                        ?>
                    </span>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="view_orders.php" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-blue-700">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                <?php if ($order['status'] == 'pending'): ?>
                    <a href="edit_order.php?id=<?php echo $order_id; ?>" class="bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-yellow-700">
                        <i class="fas fa-pencil-alt"></i> Edit Order
                    </a>
                <?php endif; ?>
                <a href="print_order.php?id=<?php echo $order_id; ?>" target="_blank" class="bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-gray-700">
                    <i class="fas fa-print"></i> Print
                </a>
            </div>
        </div>

        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Information</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Customer Name</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                        </div>
                        <?php if ($order['order_type'] == 'delivery'): ?>
                            <div>
                                <p class="text-sm text-gray-500">Delivery Address</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['delivery_address'] ?? $order['customer_address_default']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Contact Phone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['customer_phone'] ?? $order['customer_phone_default']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm text-gray-500">Customer Email</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Order Information</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Order Date & Time</p>
                            <p class="font-medium"><?php echo date('M j, Y h:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Order Status</p>
                            <span class="status-badge <?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Notes</p>
                            <p class="font-medium"><?php echo !empty($order['notes']) ? htmlspecialchars($order['notes']) : 'No notes'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Order Items</h2>
            
            <?php if (empty($items)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-utensils text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No items in this order</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($items as $item): ?>
                        <div class="py-4 flex flex-col sm:flex-row">
                            <div class="flex-shrink-0 mb-3 sm:mb-0 sm:mr-4">
                                <img src="<?php echo htmlspecialchars($item['item_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                     class="w-20 h-20 object-cover rounded-lg"
                                     onerror="this.src='uploads/default-food.jpg'">
                            </div>
                            <div class="flex-grow">
                                <div class="flex justify-between">
                                    <h3 class="text-lg font-medium"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                    <p class="text-lg item-price">Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                </div>
                                <div class="flex justify-between text-sm text-gray-500 mt-1">
                                    <div>
                                        <span>Rs. <?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></span>
                                        <?php if (!empty($item['special_notes'])): ?>
                                            <p class="mt-1 text-gray-700"><i class="fas fa-sticky-note mr-1"></i> <?php echo htmlspecialchars($item['special_notes']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between text-xl font-bold">
                        <span>Total Amount:</span>
                        <span class="total-amount">Rs. <?php echo number_format($order['total'], 2); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if ($order['status'] != 'completed' && $order['status'] != 'cancelled'): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Order Actions</h2>
            <div class="flex flex-wrap gap-3">
                <?php if ($order['status'] == 'pending'): ?>
                    <form method="POST" action="update_order_status.php" class="flex-1">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="new_status" value="preparing">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                            <i class="fas fa-clock"></i> Mark as Preparing
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($order['status'] == 'preparing'): ?>
                    <form method="POST" action="update_order_status.php" class="flex-1">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="new_status" value="completed">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>
                    </form>
                <?php endif; ?>
                
                <form method="POST" action="update_order_status.php" class="flex-1">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <input type="hidden" name="new_status" value="cancelled">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this order?');" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                        <i class="fas fa-times"></i> Cancel Order
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
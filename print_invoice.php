<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
require 'db.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_orders.php");
    exit();
}

$order_id = intval($_GET['id']);


$order = [];
$sql = "SELECT o.*, c.name as customer_name, c.email as customer_email, 
               c.phone as customer_phone, c.address as customer_address,
               c.created_at as customer_since
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();


$items = [];
$sql = "SELECT oi.*, mi.name as item_name, mi.price as item_price, 
               mi.category as item_category
        FROM order_items oi
        JOIN menu_items mi ON oi.menu_item_id = mi.item_id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_id; ?> - NON Restaurant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1a3a8f;
            --dark-blue: #0d1f4d;
            --light-blue: #e6f0ff;
            --accent-green: #1e7e34;
            --dark-green: #0d3d1a;
            --light-green: #e6f7eb;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #495057;
            --text-dark: #212529;
            --black: #000000;
            
            /* Status colors */
            --status-pending-bg: #FFF3CD;
            --status-pending-text: #856404;
            --status-pending-border: #FFEEBA;
            
            --status-preparing-bg: #D1ECF1;
            --status-preparing-text: #0C5460;
            --status-preparing-border: #BEE5EB;
            
            --status-completed-bg: #D4EDDA;
            --status-completed-text: #155724;
            --status-completed-border: #C3E6CB;
            
            --status-cancelled-bg: #F8D7DA;
            --status-cancelled-text: #721C24;
            --status-cancelled-border: #F5C6CB;
        }
        
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--light-gray);
            padding: 20px;
            min-height: 100vh;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            border-radius: 10px;
            background: var(--white);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--medium-gray);
        }
        
        .invoice-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-green));
        }
        
        .restaurant-info {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .restaurant-name {
            font-size: 42px;
            font-weight: 800;
            color: var(--black);
            margin-bottom: 5px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .restaurant-details {
            color: var(--dark-gray);
            font-size: 16px;
            margin-top: 15px;
            background: var(--light-gray);
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .invoice-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            padding: 20px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--accent-green);
        }
        
        .info-box h3 {
            margin-top: 0;
            color: var(--primary-blue);
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            text-align: left;
            padding: 15px;
            background: var(--dark-blue);
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        tr:nth-child(even) td {
            background: var(--light-gray);
        }
        
        .total-row td {
            font-weight: bold;
            background: var(--light-green) !important;
            border-top: 2px solid var(--accent-green);
            border-bottom: none;
            color: var(--dark-green);
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            color: var(--dark-gray);
            font-size: 16px;
            border-top: 1px solid var(--medium-gray);
        }
        
        .thank-you {
            font-size: 20px;
            color: var(--black);
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .copyright {
            font-size: 14px;
            color: var(--dark-gray);
            opacity: 0.7;
            margin-top: 15px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 15px;
        }
        
.restaurant-name {
  font-size: 48px;
  font-weight: bold;
  background: linear-gradient(90deg, #0B1D51, #71C0BB);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}


        .btn-print {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-print:hover {
            background: var(--dark-blue);
            transform: translateY(-2px);
        }
        
        .btn-close {
            background: #ff2106;
            color: white;
        }
        
        .btn-close:hover {
            background: var(--text-dark);
            transform: translateY(-2px);
        }
        
        .btn-pdf {
            background: var(--accent-green);
            color: white;
        }
        
        .btn-pdf:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
        }
        
        /* Status badge styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid transparent;
        }
        
        .status-pending {
            background-color: var(--status-pending-bg);
            color: var(--status-pending-text);
            border-color: var(--status-pending-border);
        }
        
        .status-preparing {
            background-color: var(--status-preparing-bg);
            color: var(--status-preparing-text);
            border-color: var(--status-preparing-border);
        }
        
        .status-completed {
            background-color: var(--status-completed-bg);
            color: var(--status-completed-text);
            border-color: var(--status-completed-border);
        }
        
        .status-cancelled {
            background-color: var(--status-cancelled-bg);
            color: var(--status-cancelled-text);
            border-color: var(--status-cancelled-border);
        }
        
        /* Print styles */
        @media print {
            body {
                background: none;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .action-buttons {
                display: none !important;
            }
            
            .info-box {
                box-shadow: none;
                background: none !important;
                border-left: none;
                padding: 5px 0;
            }
            
            /* Ensure status colors print correctly */
            .status-pending {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .status-preparing {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .status-completed {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .status-cancelled {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="invoice-container">
        <div class="restaurant-info">
            <div class="restaurant-name">NON RESTAURANT</div>
            <div class="restaurant-details">
                <i class="fas fa-map-marker-alt"></i> Address: NON &nbsp;&nbsp;|&nbsp;&nbsp;
                <i class="fas fa-phone"></i> Phone: 0112224448 &nbsp;&nbsp;|&nbsp;&nbsp;
                <i class="fas fa-envelope"></i> Email: restaurant@non.com
            </div>
        </div>
        
        <div class="invoice-title">
            ORDER INVOICE #<?php echo $order['order_id']; ?>
        </div>
        <div style="text-align: center; color: var(--dark-gray); margin-bottom: 25px;">
            <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
        </div>

        <div class="invoice-info">
            <div class="info-box">
                <h3><i class="fas fa-user-tie"></i> CUSTOMER DETAILS</h3>
                <p style="font-weight:600; font-size: 16px; margin-bottom: 8px; color: var(--primary-blue);">
                    <?php echo htmlspecialchars($order['customer_name']); ?>
                </p>
                <?php if (!empty($order['customer_phone'])): ?>
                    <p><i class="fas fa-phone" style="color: var(--primary-blue); margin-right: 8px;"></i> 
                        <?php echo htmlspecialchars($order['customer_phone']); ?>
                    </p>
                <?php endif; ?>
                <?php if ($order['order_type'] == 'delivery' && !empty($order['delivery_address'])): ?>
                    <p><i class="fas fa-map-marker-alt" style="color: var(--primary-blue); margin-right: 8px;"></i> 
                        <?php echo htmlspecialchars($order['delivery_address']); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="info-box">
                <h3><i class="fas fa-receipt"></i> ORDER INFO</h3>
                <p><strong>Order Type:</strong> 
                    <span style="font-weight: 600; color: var(--primary-blue);">
                        <?php 
                        switch($order['order_type']) {
                            case 'dine_in': echo '<i class="fas fa-utensils"></i> Dine-In'; break;
                            case 'delivery': echo '<i class="fas fa-truck"></i> Delivery'; break;
                            case 'takeaway': echo '<i class="fas fa-shopping-bag"></i> Takeaway'; break;
                        }
                        ?>
                    </span>
                </p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($order['created_at'])); ?></p>
                <p>
                    <strong>Status:</strong> 
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <i class="fas fa-<?php 
                            switch($order['status']) {
                                case 'pending': echo 'clock'; break;
                                case 'preparing': echo 'utensils'; break;
                                case 'completed': echo 'check-circle'; break;
                                case 'cancelled': echo 'times-circle'; break;
                            }
                        ?>" style="margin-right: 5px;"></i>
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-utensils"></i> Item</th>
                    <th><i class="fas fa-info-circle"></i> Description</th>
                    <th><i class="fas fa-hashtag"></i> Qty</th>
                    <th><i class="fas fa-tag"></i> Unit Price</th>
                    <th><i class="fas fa-calculator"></i> Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($items)):
                    foreach($items as $item): 
                        $item_total = $item['quantity'] * $item['item_price'];
                ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--black);"><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td style="color: var(--dark-gray);">
                            <?php echo htmlspecialchars($item['item_category']); ?>
                            <?php if (!empty($item['special_notes'])): ?>
                                <div style="font-size: 12px; font-style: italic; color: var(--dark-gray);">
                                    Notes: <?php echo htmlspecialchars($item['special_notes']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td style="white-space: nowrap;">Rs.<?php echo number_format($item['item_price'], 2); ?></td>
                        <td style="font-weight: 600; white-space: nowrap;">Rs.<?php echo number_format($item_total, 2); ?></td>
                    </tr>
                <?php 
                    endforeach;
                else: 
                ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: var(--dark-gray);">
                            <i class="fas fa-clipboard-list" style="font-size: 24px; color: var(--primary-blue); display: block; margin-bottom: 10px;"></i>
                            No items found for this order
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <?php if (isset($order['tax']) && $order['tax'] > 0): ?>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Tax (<?php echo $order['tax_rate'] ?? '0'; ?>%):</strong></td>
                    <td>Rs. <?php echo number_format($order['tax'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($order['discount']) && $order['discount'] > 0): ?>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Discount:</strong></td>
                    <td>- Rs. <?php echo number_format($order['discount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="4" style="text-align: right; font-size: 18px;"><strong>TOTAL:</strong></td>
                    <td style="font-size: 18px; color: var(--accent-green);">Rs. <?php echo number_format($order['total'], 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <div class="thank-you">
                Thank you for your order!
            </div>
            <p style="font-size: 15px; color: var(--dark-gray); margin-top: 10px;">
                We hope to serve you again soon
            </p>
            <div class="copyright">
                © <?php echo date('Y'); ?> NON Restaurant. All rights reserved.
            </div>
        </div>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> Print Invoice
            </button>
            <button onclick="saveAsPDF()" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Save as PDF
            </button>
            <button onclick="window.close()" class="btn btn-close">
                <i class="fas fa-times"></i> Close Window
            </button>
        </div>
    </div>

    <script>
        // Function to handle PDF saving
        function saveAsPDF() {
            // Open print dialog where user can choose "Save as PDF" as destination
            window.print();
        }

        // Auto-print when URL has ?print=1
        if (window.location.search.includes('print=1')) {
            window.print();
        }
    </script>
</body>
</html>
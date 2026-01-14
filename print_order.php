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


$sql = "SELECT o.*, c.name as customer_name, c.phone as customer_phone_default, 
               c.address as customer_address_default
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();


$items = [];
$sql = "SELECT oi.*, mi.name as item_name, mi.price as item_price
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
    <title>Invoice #<?= $order_id ?> | Curry Leaves</title>
    
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none; border: none; width: 100%; max-width: 100%; margin: 0; padding: 0; }
            .page-break { page-break-inside: avoid; }
        }
        .pattern-grid {
            background-image: radial-gradient(#10b981 0.5px, transparent 0.5px);
            background-size: 10px 10px;
        }
    </style>
</head>
<body class="min-h-screen py-10 flex items-center justify-center">

    
    <div class="fixed bottom-8 right-8 flex gap-4 no-print z-50">
        <a href="view_orders.php" class="flex items-center gap-2 bg-slate-800 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-slate-700 transition-all">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button onclick="downloadPDF()" class="flex items-center gap-2 bg-emerald-600 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-emerald-700 transition-all">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
        <button onclick="window.print()" class="flex items-center gap-2 bg-white text-emerald-600 px-6 py-3 rounded-full font-bold shadow-lg border-2 border-emerald-600 hover:bg-emerald-50 transition-all">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>

    
    <div class="print-container w-full max-w-3xl bg-white shadow-2xl rounded-3xl overflow-hidden relative">
        
        
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-32 blur-3xl"></div>
            
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight flex items-center gap-3">
                        <i class="fas fa-leaf text-emerald-200"></i> CURRY LEAVES
                    </h1>
                    <p class="text-emerald-100 mt-1 font-medium text-sm">Authentic Indian Cuisine & Fine Dining</p>
                    <div class="mt-4 text-xs font-medium text-emerald-100/80 space-y-1">
                        <p><i class="fas fa-map-marker-alt w-4"></i> No. 123, Food Street, Colombo 03</p>
                        <p><i class="fas fa-phone w-4"></i> +94 11 222 4448</p>
                        <p><i class="fas fa-envelope w-4"></i> hello@curryleaves.com</p>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="text-xs uppercase tracking-widest text-emerald-200 font-bold mb-1">Invoice Number</p>
                    <h2 class="text-4xl font-black">#<?= str_pad($order_id, 4, '0', STR_PAD_LEFT) ?></h2>
                    <p class="mt-2 inline-flex items-center gap-2 bg-white/20 px-3 py-1 rounded-full text-xs font-bold backdrop-blur-sm">
                        <i class="fas fa-calendar"></i> <?= date('M d, Y • h:i A', strtotime($order['created_at'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="p-10">
            
            <div class="grid grid-cols-2 gap-12 mb-12">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Billed To</h3>
                    <h4 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($order['customer_name']) ?></h4>
                    <div class="text-slate-500 text-sm mt-2 space-y-1 font-medium">
                        <p class="flex items-center gap-2">
                            <i class="fas fa-phone text-slate-300 w-4"></i> 
                            <?= htmlspecialchars($order['customer_phone'] ?? $order['customer_phone_default']) ?>
                        </p>
                        <?php if($order['order_type'] == 'delivery'): ?>
                        <p class="flex items-start gap-2">
                            <i class="fas fa-map-pin text-slate-300 w-4 mt-1"></i> 
                            <?= htmlspecialchars($order['delivery_address'] ?? $order['customer_address_default']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-right">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Order Details</h3>
                    <div class="space-y-2">
                        <div class="flex justify-end items-center gap-3">
                            <span class="text-sm font-medium text-slate-500">Order Status:</span>
                            <?php 
                                $status_colors = [
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'preparing' => 'bg-blue-100 text-blue-700',
                                    'ready' => 'bg-purple-100 text-purple-700',
                                    'delivered' => 'bg-emerald-100 text-emerald-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-rose-100 text-rose-700'
                                ];
                                $st_class = $status_colors[$order['status']] ?? 'bg-slate-100 text-slate-600';
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $st_class ?>">
                                <?= $order['status'] ?>
                            </span>
                        </div>
                        <div class="flex justify-end items-center gap-3">
                            <span class="text-sm font-medium text-slate-500">Service Type:</span>
                            <span class="font-bold text-slate-700 capitalize">
                                <i class="fas fa-<?php echo ($order['order_type'] == 'delivery') ? 'motorcycle' : 'utensils'; ?> mr-1 text-slate-400"></i>
                                <?= str_replace('_', ' ', $order['order_type']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="border border-slate-100 rounded-2xl overflow-hidden mb-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-1/2">Item Description</th>
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Qty</th>
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Price</th>
                            <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($items as $item): ?>
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-6">
                                <p class="font-bold text-slate-700"><?= htmlspecialchars($item['item_name']) ?></p>
                                <?php if(!empty($item['special_notes'])): ?>
                                    <p class="text-xs text-slate-400 mt-1 italic"><i class="fas fa-info-circle mr-1"></i> <?= htmlspecialchars($item['special_notes']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-slate-600"><?= $item['quantity'] ?></td>
                            <td class="py-4 px-6 text-right text-sm font-medium text-slate-500">Rs. <?= number_format($item['item_price'], 2) ?></td>
                            <td class="py-4 px-6 text-right font-bold text-slate-800">Rs. <?= number_format($item['item_price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="flex flex-col md:flex-row justify-between items-start gap-12">
                <div class="flex-1">
                    <?php if(!empty($order['notes'])): ?>
                        <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                            <h5 class="text-amber-800 font-bold text-xs uppercase mb-2">Order Notes</h5>
                            <p class="text-amber-700 text-sm italic">"<?= htmlspecialchars($order['notes']) ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="w-full md:w-80 space-y-3">
                    <div class="flex justify-between items-center text-slate-500 text-sm">
                        <span>Subtotal</span>
                        <span class="font-medium">Rs. <?= number_format($order['total'], 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500 text-sm">
                        <span>Tax / Service</span>
                        <span class="font-medium text-xs text-slate-400">(Incl.)</span>
                    </div>
                    <div class="h-px bg-slate-100 my-2"></div>
                    <div class="flex justify-between items-center">
                        <span class="font-black text-slate-800 text-lg">Grand Total</span>
                        <span class="font-black text-emerald-600 text-2xl">Rs. <?= number_format($order['total'], 2) ?></span>
                    </div>
                </div>
            </div>
            
            
            <div class="mt-16 text-center border-t border-slate-100 pt-8">
                <p class="text-slate-800 font-bold mb-2">Thank you for dining with us!</p>
                <div class="text-slate-400 text-xs space-x-2">
                    <span>www.curryleaves.com</span>
                    <span>•</span>
                    <span>@curryleaves</span>
                </div>
            </div>

        </div>
        
        
        <div class="h-2 bg-gradient-to-r from-emerald-600 to-teal-600"></div>
    </div>

    <script>
        // Auto-print when URL has ?print=1
        if (window.location.search.includes('print=1')) {
            window.print();
        }

        // Client-side PDF Generation
        function downloadPDF() {
            // Select the container
            const element = document.querySelector('.print-container');
            
            // PDF Options
            const opt = {
                margin:       [10, 10, 10, 10], // top, left, bottom, right
                filename:     'Invoice_#<?= $order_id ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Generate
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
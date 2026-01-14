<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "restaurant_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = $_GET['order_id'];


$order_query = "SELECT o.*, c.name AS customer_name, c.phone, c.email, c.address
                FROM orders o
                JOIN customers c ON o.customer_id = c.customer_id
                WHERE o.order_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();


$items_query = "SELECT mi.name, oi.quantity, oi.price, oi.special_notes
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.item_id
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<h2>Order #<?= $order['order_id'] ?></h2>

<div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> <?= $order['customer_name'] ?></p>
        <p><strong>Phone:</strong> <?= $order['phone'] ?></p>
        <p><strong>Email:</strong> <?= $order['email'] ?></p>
        <?php if ($order['order_type'] == 'delivery'): ?>
            <p><strong>Delivery Address:</strong> <?= $order['address'] ?></p>
        <?php endif; ?>
    </div>
    
    <div>
        <h3>Order Information</h3>
        <p><strong>Date:</strong> <?= date('M j, Y g:i a', strtotime($order['order_date'])) ?></p>
        <p><strong>Type:</strong> <?= ucfirst(str_replace('_', ' ', $order['order_type'])) ?></p>
        <p><strong>Status:</strong> <span class="status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
        <p><strong>Total:</strong> $<?= number_format($order['total'], 2) ?></p>
    </div>
</div>

<h3>Order Items</h3>
<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr>
            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Item</th>
            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Qty</th>
            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Price</th>
            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Subtotal</th>
            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?= $item['name'] ?></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?= $item['quantity'] ?></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">$<?= number_format($item['price'], 2) ?></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?= $item['special_notes'] ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php if (!empty($order['notes'])): ?>
    <h3>Order Notes</h3>
    <p><?= $order['notes'] ?></p>
<?php endif; ?>

<h3>Update Status</h3>
<form method="post" action="view_orders.php" style="margin-top: 20px;">
    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
    <select name="status" style="padding: 8px; margin-right: 10px;">
        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
    <button type="submit" name="update_status">Update Status</button>
</form>
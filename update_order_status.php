<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if ($order['status'] != 'completed' && $order['status'] != 'cancelled'): ?>
<div class="bg-white rounded-lg shadow-md p-6 mt-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Order Actions</h2>
    <div class="flex flex-wrap gap-3">
        <?php if ($order['status'] == 'pending'): ?>
            <form method="POST" action="update_order_status.php" class="flex-1">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="new_status" value="preparing">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                    <i class="fas fa-clock"></i> Mark as Preparing
                </button>
            </form>
        <?php endif; ?>
        
        <?php if ($order['status'] == 'preparing'): ?>
            <form method="POST" action="update_order_status.php" class="flex-1">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="new_status" value="completed">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                    <i class="fas fa-check"></i> Mark as Completed
                </button>
            </form>
        <?php endif; ?>
        
        <form method="POST" action="update_order_status.php" class="flex-1">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            <input type="hidden" name="new_status" value="cancelled">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" 
                    onclick="return confirm('Are you sure you want to cancel this order?');"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2">
                <i class="fas fa-times"></i> Cancel Order
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
<?php
include 'db.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM customers WHERE customer_id = $id");
$customer = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Customer - Restaurant CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
<div class="max-w-2xl mx-auto p-8 bg-white rounded-xl shadow-xl mt-10">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
        <i class="fas fa-user-edit text-blue-600 mr-2"></i> Edit Customer
    </h2>

    <form method="POST" class="space-y-5">
        
        <div class="relative">
            <input type="text" name="name" value="<?= htmlspecialchars($customer['name']) ?>" placeholder="Full Name" required 
                   class="peer w-full border border-gray-300 rounded-xl p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-transparent" />
            <label class="absolute top-4 left-4 text-gray-500 text-sm floating-label"></label>
        </div>

        
        <div class="relative">
            <input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" placeholder="Email Address" required 
                   class="peer w-full border border-gray-300 rounded-xl p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-transparent" />
            <label class="absolute top-4 left-4 text-gray-500 text-sm floating-label"></label>
        </div>

        
        <div class="relative">
            <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" placeholder="Phone Number" required 
                   class="peer w-full border border-gray-300 rounded-xl p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-transparent" />
            <label class="absolute top-4 left-4 text-gray-500 text-sm floating-label"></label>
        </div>

        
        <div class="relative">
            <textarea name="address" rows="3" placeholder="Full Address" required
                      class="peer w-full border border-gray-300 rounded-xl p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-transparent resize-none"><?= htmlspecialchars($customer['address']) ?></textarea>
            <label class="absolute top-4 left-4 text-gray-500 text-sm floating-label"></label>
        </div>

        
        <button type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-all">
            <i class="fas fa-save mr-2"></i> Update Customer
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="view_customers.php" class="text-blue-500 hover:underline">Back to Customer List</a>
    </div>
</div>

<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $stmt = $conn->prepare("UPDATE customers SET name=?, email=?, phone=?, address=? WHERE customer_id=?");
    $stmt->bind_param("ssssi", $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['address'], $id);
    if ($stmt->execute()) {
        echo "<script>alert('Customer updated successfully!'); window.location='view_customers.php';</script>";
    } else {
        echo "<p class='text-red-500 text-center mt-4'>❌ Error updating customer.</p>";
    }
}
?>
</body>
</html>
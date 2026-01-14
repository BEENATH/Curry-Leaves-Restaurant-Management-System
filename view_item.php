<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
require 'db.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_menu.php?error=Invalid+menu+item+ID");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);


$sql = "SELECT * FROM menu_items WHERE id='$id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: view_menu.php?error=Menu+item+not+found");
    exit();
}

$item = $result->fetch_assoc();


$category_colors = [
    'Appetizers' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
    'Main Course' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200'],
    'Desserts' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-200'],
    'Drinks' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200'],
    'Salads' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200'],
    'Specials' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'border' => 'border-indigo-200'],
    'Soups' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-200'],
    'Breakfast' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-800', 'border' => 'border-pink-200'],
];

$default_color = ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200'];
$color = isset($category_colors[$item['category']]) ? $category_colors[$item['category']] : $default_color;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['name']); ?> - Restaurant Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        
        .detail-card {
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .btn-back {
            background-color: #3b82f6;
            color: white;
            transition: all 0.2s ease;
        }
        
        .btn-back:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3), 0 2px 4px -1px rgba(59, 130, 246, 0.2);
        }
        
        .btn-order {
            background-color: #10b981;
            color: white;
            transition: all 0.2s ease;
        }
        
        .btn-order:hover {
            background-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3), 0 2px 4px -1px rgba(16, 185, 129, 0.2);
        }
        
        .menu-image-container {
            height: 400px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .menu-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .category-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .price-tag {
            font-size: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="antialiased">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="view_menu.php" class="btn-back font-semibold py-2 px-4 rounded-lg inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Menu
            </a>
        </div>
        
        <div class="detail-card border <?php echo $color['border']; ?>">
            <div class="menu-image-container">
                <?php if (!empty($item['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-image">
                <?php else: ?>
                    <div class="w-full h-full <?php echo $color['bg']; ?> flex items-center justify-center">
                        <i class="fas fa-utensils text-6xl <?php echo $color['text']; ?>"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h1>
                    <span class="price-tag <?php echo $color['text']; ?>">Rs. <?php echo number_format($item['price'], 2); ?></span>
                </div>
                
                <div class="mb-6">
                    <span class="category-badge <?php echo $color['bg'] . ' ' . $color['text']; ?>">
                        <?php echo htmlspecialchars($item['category']); ?>
                    </span>
                </div>
                
                <?php if (!empty($item['description'])): ?>
                    <div class="prose max-w-none mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Description</h3>
                        <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="flex gap-4">
                    <a href="place_order.php?item_id=<?php echo $item['id']; ?>" class="flex-1 btn-order font-semibold py-3 px-4 rounded-lg flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-cart"></i> Order Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
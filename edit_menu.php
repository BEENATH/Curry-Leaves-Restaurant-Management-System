<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
session_start();

require 'db.php';


$is_admin = isset($_SESSION['employee_id']);
if (!$is_admin) {
    header("Location: view_menu.php?error=Unauthorized+access");
    exit();
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_menu.php?error=No+item+ID+provided");
    exit();
}

$item_id = intval($_GET['id']);


$name = $description = $price = $category = $is_available = $image_path = '';
$error = '';


$sql = "SELECT * FROM menu_items WHERE item_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_menu.php?error=Menu+item+not+found");
    exit();
}

$menu_item = $result->fetch_assoc();


$name = $menu_item['name'];
$description = $menu_item['description'];
$price = $menu_item['price'];
$category = $menu_item['category'];
$is_available = $menu_item['is_available'];
$image_path = $menu_item['image_path'];


$absolute_upload_dir = 'C:/xampp/htdocs/restaurant_admin/uploads/';
$relative_upload_dir = 'uploads/';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $price = trim($_POST["price"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);
    $is_available = isset($_POST["is_available"]) ? 1 : 0;
    
    
    if (empty($name) || empty($price) || empty($category)) {
        $error = "Name, Price and Category are required.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number.";
    } else {
        
        $upload_ok = true;
        $new_image_path = $image_path; 
        
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {
            if (!file_exists($absolute_upload_dir)) { mkdir($absolute_upload_dir, 0777, true); }
            
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.' . $file_extension;
            $target_file = $absolute_upload_dir . $new_filename;
            
            
            if ($_FILES["image"]["size"] > 5000000) {
                $error = "File is too large (max 5MB).";
                $upload_ok = false;
            } elseif (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $error = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
                $upload_ok = false;
            }
            
            if ($upload_ok) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    
                    if (!empty($image_path) && strpos($image_path, 'uploads/') !== false) {
                         $old_path = str_replace('uploads/', $absolute_upload_dir, $image_path); 
                         
                         if(file_exists($old_path)) { @unlink($old_path); }
                    }
                    $new_image_path = $relative_upload_dir . $new_filename;
                } else {
                    $error = "Failed to upload image.";
                    $upload_ok = false;
                }
            }
        }
        
        if ($upload_ok && empty($error)) {
            $sql = "UPDATE menu_items SET name=?, description=?, price=?, category=?, is_available=?, image_path=? WHERE item_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsisi", $name, $description, $price, $category, $is_available, $new_image_path, $item_id);
            
            if ($stmt->execute()) {
                header("Location: view_menu.php?success=Menu+item+updated+successfully");
                exit();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}


$available_categories = ['Appetizers', 'Main Course', 'Desserts', 'Drinks', 'Salads', 'Specials', 'Soups', 'Breakfast', 'Sides'];

$page_title = "Edit Item: $name | Curry Leaves";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300 min-w-0">
    <div class="max-w-4xl mx-auto fade-in">
        
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 md:mb-12 gap-6">
            <div>
                <a href="view_menu.php" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-emerald-600 transition-colors mb-4">
                    <i class="fas fa-arrow-left text-[8px]"></i> Back to Inventory
                </a>
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Refine Delicacy</h1>
                <p class="text-slate-500 mt-1">Adjust parameters for <span class="text-emerald-600">"<?= htmlspecialchars($name) ?>"</span></p>
            </div>
            <div class="h-16 w-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-sm">
                <i class="fas fa-pen-nib"></i>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-8 p-6 bg-rose-50 text-rose-700 rounded-3xl border border-rose-100 flex items-center gap-4 fade-in shadow-sm">
                <div class="h-10 w-10 rounded-2xl bg-white flex items-center justify-center text-rose-500 shadow-sm">
                    <i class="fas fa-exclamation"></i>
                </div>
                <p class="text-sm font-black"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-12 opacity-[0.02] pointer-events-none">
                <i class="fas fa-sliders text-[16rem]"></i>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-10 relative z-10">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Entity Label</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-black text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Valuation (Rs.)</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rs.</span>
                                    <input type="number" name="price" value="<?= htmlspecialchars($price) ?>" step="0.01" required class="w-full bg-slate-50 border-none rounded-2xl pl-12 pr-6 py-4 text-sm font-black text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Classification</label>
                                <select name="category" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-black text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all appearance-none text-center">
                                    <option value="">Select Segment</option>
                                    <?php foreach ($available_categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Narrative Profile</label>
                            <textarea name="description" rows="4" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-medium text-slate-600 outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-none leading-relaxed"><?= htmlspecialchars($description) ?></textarea>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Visual Identity</label>
                            <div class="relative group h-[216px]">
                                <input type="file" name="image" id="fileIn" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div id="dropZone" class="w-full h-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:border-emerald-200 transition-all p-8 text-center overflow-hidden">
                                     <?php if(!empty($image_path)): ?>
                                        <div id="previewContainer" class="absolute inset-0">
                                            <img id="imagePreview" src="<?= htmlspecialchars($image_path) ?>" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <p class="text-white text-[10px] font-black uppercase tracking-widest">Update Signature Frame</p>
                                            </div>
                                        </div>
                                     <?php else: ?>
                                        <div id="uploadIcon" class="mb-4">
                                            <div class="h-16 w-16 rounded-2xl bg-white text-slate-300 flex items-center justify-center text-2xl shadow-sm group-hover:text-emerald-500 transition-colors">
                                                <i class="fas fa-camera"></i>
                                            </div>
                                        </div>
                                        <p class="font-black text-xs uppercase tracking-widest mb-1">Click to Upload</p>
                                     <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Availability Protocol</label>
                            <label class="flex items-center justify-between p-6 bg-slate-50 rounded-3xl border border-transparent hover:border-emerald-100 transition-all group/item cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-white text-emerald-500 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-bolt text-xs"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-slate-800 text-sm">Active in Loop</h3>
                                        <p class="text-[10px] text-slate-400 font-bold mt-0.5">Visibile for procurement</p>
                                    </div>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" name="is_available" <?= ($is_available == 1) ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-50 flex flex-col md:flex-row items-center justify-end gap-4">
                    <a href="view_menu.php" class="w-full md:w-auto text-center px-10 py-5 rounded-2xl font-black text-sm text-slate-400 hover:text-slate-600 transition-all uppercase tracking-widest">Abort Changes</a>
                    <button type="submit" class="w-full md:w-auto bg-emerald-600 text-white px-12 py-5 rounded-2xl font-black text-sm hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-100 hover:-translate-y-1 flex items-center justify-center gap-3">
                        <i class="fas fa-cloud-arrow-up"></i> Synchronize Item
                    </button>
                </div>

            </form>
        </div>
    </div>
</main>

<script>
    const fileIn = document.getElementById('fileIn');
    const imagePreview = document.getElementById('imagePreview');

    fileIn.onchange = function(e) {
        if(this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ex) {
                if (imagePreview) {
                    imagePreview.src = ex.target.result;
                } else {
                    location.reload(); // Quickest way to handle no preview img tag
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    };
</script>
</body>
</html>

</body>
</html>
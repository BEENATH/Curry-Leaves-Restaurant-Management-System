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

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        
        if ($_FILES["image"]["size"] > 5000000) {
            $error = "File is too large (max 5MB).";
        } elseif (!in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'webp'])) {
            $error = "Only JPG, JPEG, PNG & WEBP files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO menu_items (name, description, price, category, image_path, is_available) 
                VALUES ('$name', '$description', $price, '$category', '$image_path', $is_available)";
        
        if ($conn->query($sql) === TRUE) {
            $success = "Menu item added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

$available_categories = ['Appetizers', 'Main Course', 'Desserts', 'Drinks', 'Salads', 'Specials', 'Soups', 'Breakfast', 'Sides'];

$page_title = "Add Menu Item | Curry Leaves";
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
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Expand Menu</h1>
                <p class="text-slate-500 mt-1">Introduce a new culinary masterpiece to your collection</p>
            </div>
            <div class="h-16 w-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-sm">
                <i class="fas fa-utensils"></i>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-8 p-6 bg-emerald-50 text-emerald-700 rounded-3xl border border-emerald-100 flex items-center gap-4 fade-in shadow-sm">
                <div class="h-10 w-10 rounded-2xl bg-white flex items-center justify-center text-emerald-500 shadow-sm">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-black"><?= $success ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-8 p-6 bg-rose-50 text-rose-700 rounded-3xl border border-rose-100 flex items-center gap-4 fade-in shadow-sm">
                <div class="h-10 w-10 rounded-2xl bg-white flex items-center justify-center text-rose-500 shadow-sm">
                    <i class="fas fa-exclamation"></i>
                </div>
                <p class="text-sm font-black"><?= $error ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-12 opacity-[0.02] pointer-events-none">
                <i class="fas fa-plus text-[16rem]"></i>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-10 relative z-10">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Delicacy Identity</label>
                            <input type="text" name="name" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-black text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all placeholder-slate-300" placeholder="e.g. Saffron Infused Sea Bass">
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Market Price</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-xs">Rs.</span>
                                    <input type="number" name="price" step="0.01" required class="w-full bg-slate-50 border-none rounded-2xl pl-12 pr-6 py-4 text-sm font-black text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all" placeholder="0.00">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Category Flux</label>
                                <select name="category" required class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-black text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500 transition-all appearance-none">
                                    <option value="" disabled selected>Select Segment</option>
                                    <?php foreach ($available_categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Sensory Narrative (Description)</label>
                            <textarea name="description" rows="4" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-medium text-slate-600 outline-none focus:ring-2 focus:ring-emerald-500 transition-all placeholder-slate-300 leading-relaxed" placeholder="Describe the ingredients, preparation, and soul of the dish..."></textarea>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Visual Representation</label>
                            <div class="relative group h-[216px]">
                                <input type="file" name="image" id="fileIn" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div id="dropZone" class="w-full h-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:border-emerald-200 transition-all p-8 text-center overflow-hidden">
                                    <div id="uploadIcon" class="mb-4">
                                        <div class="h-16 w-16 rounded-2xl bg-white text-slate-300 flex items-center justify-center text-2xl shadow-sm group-hover:text-emerald-500 transition-colors">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                    </div>
                                    <p class="font-black text-xs uppercase tracking-widest mb-1 group-hover:text-emerald-700 transition-colors">Capture or Select</p>
                                    <p class="text-[10px] font-bold opacity-60">High Resolution Recommended</p>
                                    <div id="previewContainer" class="absolute inset-0 hidden">
                                        <img id="imagePreview" src="#" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                            <p class="text-white text-[10px] font-black uppercase tracking-widest">Change Image</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Operational State</label>
                            <label class="flex items-center justify-between p-6 bg-slate-50 rounded-3xl border border-transparent hover:border-emerald-100 transition-all group/item cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-white text-emerald-500 flex items-center justify-center shadow-sm">
                                        <i class="fas fa-bolt text-xs"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-slate-800 text-sm">Active in Loop</h3>
                                        <p class="text-[10px] text-slate-400 font-bold mt-0.5">Visible to customers immediately</p>
                                    </div>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" name="is_available" checked class="sr-only peer">
                                    <div class="w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-600"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-50 flex flex-col md:flex-row items-center justify-between gap-6">
                    <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em] text-center md:text-left">Ensure all data aligns with premium standards before deployment</p>
                    <button type="submit" class="w-full md:w-auto bg-emerald-600 text-white px-12 py-5 rounded-2xl font-black text-sm hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-100 hover:-translate-y-1 flex items-center justify-center gap-3">
                        <i class="fas fa-sparkles"></i> Deploy to Menu
                    </button>
                </div>

            </form>
        </div>
    </div>
</main>

<script>
    const fileIn = document.getElementById('fileIn');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const uploadIcon = document.getElementById('uploadIcon');

    fileIn.onchange = function(e) {
        if(this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ex) {
                imagePreview.src = ex.target.result;
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(this.files[0]);
        }
    };
    
    // Auto remove alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.bg-emerald-50, .bg-rose-50');
        alerts.forEach(a => {
            a.style.opacity = '0';
            a.style.transform = 'translateY(-10px)';
            a.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            setTimeout(() => a.remove(), 500);
        });
    }, 4000);
</script>

</body>
</html>
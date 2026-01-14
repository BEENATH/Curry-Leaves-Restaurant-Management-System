<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';


if (!isset($_SESSION['employee_id']) && !isset($_SESSION['customer_id'])) { 
    header("Location: login.php"); 
    exit; 
} 

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;


if (isset($_GET['delete_id']) && isset($_SESSION['employee_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_stmt = $conn->prepare("DELETE FROM menu_items WHERE item_id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        header("Location: view_menu.php?success=Item+deleted+successfully");
        exit();
    }
}


$result = $conn->query("SELECT * FROM menu_items ORDER BY category, name");
$grouped_items = [];
while($row = $result->fetch_assoc()) {
    $grouped_items[$row['category']][] = $row;
}

$current_role = $_SESSION['role'] ?? 'customer';
$user_name = htmlspecialchars($_SESSION['employee_name'] ?? $_SESSION['customer_name'] ?? 'User');

function getRoleGradient($role) {
    switch (strtolower($role)) {
        case 'manager': return 'from-purple-600 to-indigo-600';
        case 'chef': return 'from-amber-600 to-orange-500';
        case 'waiter': return 'from-emerald-600 to-green-500';
        default: return 'from-slate-600 to-slate-500';
    }
}

$page_title = "Curry Leaves | Digital Menu";
include 'includes/head.php';
?>


<style>
    .menu-item { 
        transition: all 0.3s ease;
        border: 1px solid rgba(241, 245, 249, 1);
    }
    .menu-item:hover { 
        transform: translateY(-4px);
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-8 lg:p-12 transition-all duration-300 min-w-0">
    <?php if ($success): ?>
        <div class="mb-6 p-4 bg-emerald-100 text-emerald-700 rounded-2xl border border-emerald-200 flex items-center gap-3 fade-in shadow-sm">
            <i class="fas fa-check-circle translate-y-[1px]"></i> 
            <span class="font-bold text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 md:mb-12 gap-6 fade-in">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Digital Menu</h1>
            <p class="text-slate-500 mt-1">Manage and explore your restaurant offerings</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <?php if(isset($_SESSION['employee_id'])): ?>
                <a href="add_menu.php" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-2xl font-black text-sm flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-100">
                    <i class="fas fa-plus"></i> Add Item
                </a>
            <?php endif; ?>
            <div class="hidden sm:flex items-center gap-3 bg-white p-1.5 rounded-2xl shadow-sm border border-slate-100 pr-4">
                <div class="h-8 w-8 rounded-xl bg-gradient-to-tr <?= getRoleGradient($current_role) ?> flex items-center justify-center text-white text-[10px]">
                    <i class="fas fa-user-circle"></i>
                </div>
                <p class="text-[10px] font-black text-slate-800 uppercase tracking-wider"><?= $user_name ?></p>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white p-3 rounded-3xl md:rounded-[2rem] shadow-sm border border-slate-50 mb-8 flex flex-col md:flex-row gap-3 items-center fade-in no-print" style="animation-delay: 0.1s">
        <div class="flex-1 w-full relative group">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm transition-colors group-focus-within:text-emerald-500"></i>
            <input type="text" id="searchInput" placeholder="Search for dishes..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <select id="categoryFilter" class="flex-1 md:flex-none bg-slate-50 border-none rounded-2xl px-5 py-3 text-[11px] font-black uppercase text-slate-600 focus:ring-2 focus:ring-emerald-500 cursor-pointer appearance-none tracking-widest">
                <option value="">All Categories</option>
                <?php foreach(array_keys($grouped_items) as $cat): ?>
                    <option value="<?= $cat ?>"><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
            <select id="sortFilter" class="flex-1 md:flex-none bg-slate-50 border-none rounded-2xl px-5 py-3 text-[11px] font-black uppercase text-slate-600 focus:ring-2 focus:ring-emerald-500 cursor-pointer appearance-none tracking-widest">
                <option value="name_asc">Name A-Z</option>
                <option value="name_desc">Name Z-A</option>
                <option value="price_asc">Price Low</option>
                <option value="price_desc">Price High</option>
            </select>
        </div>
    </div>

    <div id="menuContainer" class="space-y-12 fade-in" style="animation-delay: 0.2s">
        <?php foreach ($grouped_items as $category => $items): ?>
        <section class="category-section" data-category="<?= $category ?>">
            <div class="flex items-center gap-4 mb-8">
                <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight"><?= $category ?></h2>
                <div class="h-[1px] flex-1 bg-slate-100 rounded-full"></div>
                <span class="text-[10px] font-black text-slate-400 bg-slate-50 px-3 py-1 rounded-full uppercase tracking-widest"><?= count($items) ?> Items</span>
            </div>

            <div class="menu-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
                <?php foreach ($items as $item): ?>
                <div class="menu-item bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all group overflow-hidden" 
                     data-name="<?= strtolower($item['name']) ?>" 
                     data-price="<?= $item['price'] ?>">
                    
                    <div class="relative h-56 w-full overflow-hidden">
                        <img src="uploads/<?= htmlspecialchars($item['image_path'] ?? 'default.jpg') ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute top-4 left-4">
                            <span class="px-4 py-1.5 bg-white/90 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest text-slate-800 shadow-sm">
                                <?= $category ?>
                            </span>
                        </div>
                        <?php if(isset($_SESSION['employee_id'])): ?>
                            <div class="absolute top-4 right-4 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all translate-x-4 group-hover:translate-x-0">
                                <a href="edit_menu.php?id=<?= $item['item_id'] ?>" class="h-9 w-9 rounded-xl bg-white text-blue-600 flex items-center justify-center shadow-lg hover:bg-blue-600 hover:text-white transition-all"><i class="fas fa-edit text-xs"></i></a>
                                <a href="view_menu.php?delete_id=<?= $item['item_id'] ?>" onclick="return confirm('Delete this delicacy?')" class="h-9 w-9 rounded-xl bg-white text-rose-600 flex items-center justify-center shadow-lg hover:bg-rose-600 hover:text-white transition-all"><i class="fas fa-trash-alt text-xs"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6 md:p-8">
                        <div class="flex justify-between items-start mb-3 gap-2">
                            <h3 class="text-lg font-black text-slate-800 leading-tight"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-emerald-600 font-black text-lg whitespace-nowrap">Rs. <?= number_format($item['price'], 0) ?></p>
                        </div>
                        <p class="text-xs text-slate-400 font-bold leading-relaxed mb-6 line-clamp-2"><?= htmlspecialchars($item['description']) ?></p>
                        
                        <?php if(!isset($_SESSION['employee_id'])): ?>
                            <a href="place_order.php?item_id=<?= $item['item_id'] ?>" class="w-full bg-slate-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-slate-100">
                                <i class="fas fa-plus"></i> Add to Order
                            </a>
                        <?php else: ?>
                            <div class="flex items-center gap-3">
                                <div class="h-1.5 flex-1 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 w-full rounded-full"></div>
                                </div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Available</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </div>
</main>

<script>
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.menu-item').forEach(card => {
            card.style.display = card.dataset.name.includes(term) ? 'flex' : 'none';
        });
    });

    document.getElementById('categoryFilter').addEventListener('change', function(e) {
        const cat = e.target.value;
        document.querySelectorAll('.category-section').forEach(section => {
            section.style.display = (!cat || section.dataset.category === cat) ? 'block' : 'none';
        });
    });

    document.getElementById('sortFilter').addEventListener('change', function(e) {
        const type = e.target.value;
        document.querySelectorAll('.category-section').forEach(section => {
            const grid = section.querySelector('.menu-grid');
            const items = Array.from(grid.children);
            
            items.sort((a, b) => {
                if(type === 'name_asc') return a.dataset.name.localeCompare(b.dataset.name);
                if(type === 'name_desc') return b.dataset.name.localeCompare(a.dataset.name);
                if(type === 'price_asc') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                if(type === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            });
            
            items.forEach(item => grid.appendChild(item));
        });
    });

    function confirmDelete(id) {
        if(confirm('Delete this item?')) {
            window.location.href = `view_menu.php?delete_id=${id}`;
        }
    }
</script>

</body>
</html>
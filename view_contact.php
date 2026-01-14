<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
session_start();
include 'db.php';


if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;


$sql = "SELECT *,
               DATE_FORMAT(created_at, '%b %d, %Y at %h:%i %p') as formatted_date
        FROM contacts WHERE contact_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$contact = $result->fetch_assoc();

if (!$contact) {
    header("Location: admin_contacts.php");
    exit;
}


if ($contact['status'] == 'unread') {
    $update_sql = "UPDATE contacts SET status = 'read' WHERE contact_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $id);
    $update_stmt->execute();
    $contact['status'] = 'read';
}

$page_title = "Message #{$contact['contact_id']} | Curry Leaves";
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 lg:ml-72 p-6 lg:p-10 transition-all duration-300 min-w-0">
    <div class="max-w-4xl mx-auto fade-in">
        <div class="mb-8">
            <a href="admin_contacts.php" class="text-slate-400 hover:text-emerald-600 font-bold text-xs mb-4 inline-block transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Messages
            </a>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Contact Message</h1>
                    <p class="text-slate-500 text-xs mt-1">Details for message #<?php echo str_pad($contact['contact_id'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div>
                    <?php 
                        $status_colors = [
                            'unread' => 'bg-amber-100 text-amber-700',
                            'read' => 'bg-blue-100 text-blue-700',
                            'replied' => 'bg-emerald-100 text-emerald-700',
                            'archived' => 'bg-slate-100 text-slate-600'
                        ];
                        $color_class = $status_colors[$contact['status']] ?? 'bg-slate-100 text-slate-600';
                    ?>
                    <span class="<?php echo $color_class; ?> px-4 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wide shadow-sm">
                        <?php echo $contact['status']; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden rounded-[2rem] shadow-sm border border-slate-100">
            
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8 md:p-10">
                <h2 class="text-xl md:text-2xl font-bold mb-2"><?php echo htmlspecialchars($contact['subject']); ?></h2>
                <div class="flex items-center gap-2 opacity-90 text-[10px] uppercase font-black tracking-widest">
                    <i class="far fa-clock"></i>
                    <span><?php echo $contact['formatted_date']; ?></span>
                </div>
            </div>

            
            <div class="p-8 md:p-10 space-y-10">
                
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">Sender Details</h3>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Name</p>
                            <p class="text-base font-bold text-slate-800"><?php echo htmlspecialchars($contact['name']); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Email</p>
                            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-base font-bold text-emerald-600 hover:text-emerald-700 transition">
                                <?php echo htmlspecialchars($contact['email']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2">Quick Actions</h3>
                        <div class="flex flex-col gap-3">
                             <a href="mailto:<?php echo urlencode($contact['email']); ?>?subject=Re: <?php echo urlencode($contact['subject']); ?>" 
                                class="bg-emerald-600 text-white px-5 py-3 rounded-xl font-bold hover:bg-emerald-700 transition flex items-center justify-center gap-2 shadow-lg shadow-emerald-100 text-sm">
                                <i class="fas fa-reply"></i> Reply via Email
                            </a>
                            <form method="POST" action="admin_contacts.php" class="w-full">
                                <input type="hidden" name="contact_id" value="<?php echo $contact['contact_id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <div class="flex gap-2">
                                    <select name="status" class="flex-1 bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold text-slate-600 focus:ring-1 focus:ring-emerald-500 appearance-none cursor-pointer">
                                        <option value="read" <?php echo $contact['status'] == 'read' ? 'selected' : ''; ?>>Mark as Read</option>
                                        <option value="replied" <?php echo $contact['status'] == 'replied' ? 'selected' : ''; ?>>Mark as Replied</option>
                                        <option value="archived" <?php echo $contact['status'] == 'archived' ? 'selected' : ''; ?>>Archive</option>
                                        <option value="unread" <?php echo $contact['status'] == 'unread' ? 'selected' : ''; ?>>Mark as Unread</option>
                                    </select>
                                    <button type="submit" class="bg-slate-900 text-white px-5 py-3 rounded-xl font-bold hover:bg-black transition text-xs">
                                        Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 pb-2 mb-6">Message Content</h3>
                    <div class="bg-slate-50 rounded-2xl p-6 md:p-8 border border-slate-100">
                        <div class="text-slate-700 font-medium leading-relaxed whitespace-pre-wrap text-sm message-content"><?php echo htmlspecialchars($contact['message']); ?></div>
                    </div>
                </div>

                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-8 border-t border-slate-50 text-center">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Words</p>
                        <p class="text-lg font-black text-slate-800"><?php echo str_word_count($contact['message']); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Chars</p>
                        <p class="text-lg font-black text-slate-800"><?php echo strlen($contact['message']); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Verification</p>
                        <p class="text-[10px] font-black <?php echo filter_var($contact['email'], FILTER_VALIDATE_EMAIL) ? 'text-emerald-600' : 'text-rose-600'; ?> mt-1 uppercase tracking-wider">
                            <?php echo filter_var($contact['email'], FILTER_VALIDATE_EMAIL) ? 'Email Valid' : 'Email Invalid'; ?>
                        </p>
                    </div>
                    <div class="flex items-center justify-center">
                        <a href="admin_contacts.php?delete=<?php echo $contact['contact_id']; ?>" 
                           onclick="return confirm('Delete this message?')"
                           class="text-rose-500 hover:text-rose-700 font-bold text-xs transition-colors py-2 px-4 rounded-xl hover:bg-rose-50 flex items-center gap-2">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </div>
                </div>

            </div>
        </div>
        
        <p class="text-center text-slate-400 text-[10px] mt-10 mb-10 font-bold uppercase tracking-widest">© 2026 Curry Leaves Restaurant. CRM Module.</p>
    </div>
</main>

<script>
    // Highlight phone numbers
    document.addEventListener('DOMContentLoaded', () => {
        const messageDiv = document.querySelector('.message-content');
        if(messageDiv) {
            const text = messageDiv.innerHTML;
            const phoneRegex = /(\+\d{1,3}[-.]?)?\(?\d{3}\)?[-.]?\d{3}[-.]?\d{4}/g;
            messageDiv.innerHTML = text.replace(phoneRegex, '<span class="bg-yellow-100 text-yellow-800 px-1 rounded font-bold cursor-pointer" onclick="window.location.href=\'tel:$&\'+this.textContent.replace(/[^0-9+]/g,\'\')">$&</span>');
        }
    });
</script>
</body>
</html>
<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN CLIENT TESTIMONIALS
// ==========================================
require_once 'admin_header.php';

$success_msg = "";
$error_msg = "";

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// 1. Process Form Submission (Add or Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_testimonial'])) {
    $client_name = trim($_POST['client_name'] ?? '');
    $client_designation = trim($_POST['client_designation'] ?? '');
    $project_name = trim($_POST['project_name'] ?? '');
    $quote = trim($_POST['quote'] ?? '');
    $color = trim($_POST['color'] ?? 'blue');
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if (!empty($client_name) && !empty($client_designation) && !empty($project_name) && !empty($quote)) {
        if ($edit_id > 0) {
            // UPDATE
            try {
                $stmt = $pdo->prepare("UPDATE testimonials SET client_name = ?, client_designation = ?, project_name = ?, quote = ?, color = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$client_name, $client_designation, $project_name, $quote, $color, $sort_order, $edit_id]);
                $success_msg = "Testimonial updated successfully.";
                $action = 'list';
            } catch (PDOException $e) {
                $error_msg = "Failed to update testimonial: " . $e->getMessage();
            }
        } else {
            // INSERT
            try {
                $stmt = $pdo->prepare("INSERT INTO testimonials (client_name, client_designation, project_name, quote, color, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_name, $client_designation, $project_name, $quote, $color, $sort_order]);
                $success_msg = "New testimonial added successfully.";
                $action = 'list';
            } catch (PDOException $e) {
                $error_msg = "Failed to save testimonial: " . $e->getMessage();
            }
        }
    } else {
        $error_msg = "Please fill in all required fields (Client Name, Designation, Project, and Quote).";
    }
}

// 2. Process Delete Action
if ($action === 'delete' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$edit_id]);
        $success_msg = "Testimonial deleted successfully.";
        $action = 'list';
    } catch (PDOException $e) {
        $error_msg = "Failed to delete testimonial: " . $e->getMessage();
    }
}

// 3. Fetch Edit Item
$edit_testimonial = null;
if ($action === 'edit' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_testimonial = $stmt->fetch();
        if (!$edit_testimonial) {
            $error_msg = "Testimonial not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_msg = "Database query error: " . $e->getMessage();
    }
}

// 4. Fetch all Testimonials for Listing
$testimonials = [];
if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, id ASC");
        $testimonials = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_msg = "Failed to fetch testimonials: " . $e->getMessage();
    }
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Client Testimonials</h1>
            <p class="text-sm text-slate-500 mt-1">Manage quotes, ratings, and commissions highlighted on the homepage testimonials block.</p>
        </div>
        <?php if ($action === 'list'): ?>
            <a href="testimonials.php?action=add" class="inline-flex items-center px-5 py-2.5 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                + Add Testimonial
            </a>
        <?php else: ?>
            <a href="testimonials.php" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                &larr; Back to List
            </a>
        <?php endif; ?>
    </div>

    <!-- Alerts -->
    <?php if (!empty($success_msg)): ?>
        <div class="p-4 bg-green-50 border border-green-200 text-green-600 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span><?php echo htmlspecialchars($success_msg); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span><?php echo htmlspecialchars($error_msg); ?></span>
        </div>
    <?php endif; ?>

    <!-- LIST MODE -->
    <?php if ($action === 'list'): ?>
        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-150 text-left">
                    <thead class="bg-slate-50/20 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Client Initials</th>
                            <th class="px-6 py-4">Client Name</th>
                            <th class="px-6 py-4">Designation / Role</th>
                            <th class="px-6 py-4">Project Name</th>
                            <th class="px-6 py-4">Theme Color</th>
                            <th class="px-6 py-4">Display Order</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php if (count($testimonials) > 0): ?>
                            <?php foreach ($testimonials as $t): ?>
                                <tr class="hover:bg-slate-50/20 transition-all duration-150 border-t border-slate-100">
                                    <!-- Initials -->
                                    <td class="px-6 py-4">
                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-xs">
                                            <?php echo get_initials($t['client_name']); ?>
                                        </div>
                                    </td>
                                    <!-- Client Name -->
                                    <td class="px-6 py-4 font-bold text-[#0f172a] text-sm">
                                        <?php echo htmlspecialchars($t['client_name']); ?>
                                    </td>
                                    <!-- Designation -->
                                    <td class="px-6 py-4 text-slate-500 max-w-xs truncate">
                                        <?php echo htmlspecialchars($t['client_designation']); ?>
                                    </td>
                                    <!-- Project -->
                                    <td class="px-6 py-4 font-semibold text-slate-700">
                                        <?php echo htmlspecialchars($t['project_name']); ?>
                                    </td>
                                    <!-- Theme Color -->
                                    <td class="px-6 py-4">
                                        <?php if ($t['color'] === 'red'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-800 border border-red-200">Red</span>
                                        <?php elseif ($t['color'] === 'purple'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">Purple</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">Blue</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Sort Order -->
                                    <td class="px-6 py-4 font-mono text-slate-400">
                                        <?php echo $t['sort_order']; ?>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="testimonials.php?action=edit&id=<?php echo $t['id']; ?>" class="p-2 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Edit testimonial">
                                                ✏️
                                            </a>
                                            <a href="testimonials.php?action=delete&id=<?php echo $t['id']; ?>" onclick="return confirm('Are you sure you want to delete this client testimonial?')" class="p-2 border border-red-100 hover:bg-red-50 text-red-500 rounded-xl transition-all duration-300 bg-white" title="Delete testimonial">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Quote snippet preview row -->
                                <tr class="bg-slate-50/5 text-[11px] text-slate-500 font-normal">
                                    <td colspan="7" class="px-6 pb-4 pt-1 border-t-0 max-w-lg">
                                        <div class="italic border-l-2 border-slate-200 pl-4 py-1">
                                            "<?php echo htmlspecialchars(mb_strimwidth($t['quote'], 0, 150, "...")); ?>"
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-normal">
                                    <span class="text-3xl block mb-2">💬</span>
                                    No testimonials found in database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <?php if ($action === 'add' || $action === 'edit'): 
        $client_val = $edit_testimonial ? $edit_testimonial['client_name'] : '';
        $designation_val = $edit_testimonial ? $edit_testimonial['client_designation'] : '';
        $project_val = $edit_testimonial ? $edit_testimonial['project_name'] : '';
        $quote_val = $edit_testimonial ? $edit_testimonial['quote'] : '';
        $color_val = $edit_testimonial ? $edit_testimonial['color'] : 'blue';
        $order_val = $edit_testimonial ? $edit_testimonial['sort_order'] : '0';
        ?>
        <div class="max-w-2xl bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-display font-bold text-lg text-slate-900">
                    <?php echo $action === 'edit' ? 'Edit Testimonial Details' : 'Add New Client Testimonial'; ?>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Define who the client is, their project name, the quote text, and select theme styling colors.</p>
            </div>

            <form action="testimonials.php?id=<?php echo $edit_id; ?>" method="POST" class="p-6 md:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Client Name -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Client Name *</label>
                        <input type="text" name="client_name" value="<?php echo htmlspecialchars($client_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Isadora R. Sterling">
                    </div>

                    <!-- Client Designation -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Designation / Role *</label>
                        <input type="text" name="client_designation" value="<?php echo htmlspecialchars($designation_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Philanthropist & Art Collector">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Project Name -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Project Name *</label>
                        <input type="text" name="project_name" value="<?php echo htmlspecialchars($project_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. The Obsidian Villa">
                    </div>

                    <!-- Theme Color Selection -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Theme Accent Color</label>
                        <select name="color" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                            <option value="blue" <?php echo $color_val === 'blue' ? 'selected' : ''; ?>>Blue (#00aff0)</option>
                            <option value="red" <?php echo $color_val === 'red' ? 'selected' : ''; ?>>Red (#ec3237)</option>
                            <option value="purple" <?php echo $color_val === 'purple' ? 'selected' : ''; ?>>Purple (#9333ea)</option>
                        </select>
                    </div>
                </div>

                <!-- Testimonial Quote -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Testimonial Quote *</label>
                    <textarea name="quote" rows="5" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-4 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none" placeholder="Provide the client's detailed review statement here..."><?php echo htmlspecialchars($quote_val); ?></textarea>
                </div>

                <!-- Sort Order -->
                <div class="space-y-1.5 max-w-xs">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Display Order Index</label>
                    <input type="number" name="sort_order" value="<?php echo htmlspecialchars($order_val); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                </div>

                <!-- Form Buttons -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-2">
                    <a href="testimonials.php" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                        Cancel
                    </a>
                    <button type="submit" name="save_testimonial" class="px-6 py-2 bg-[#00aff0] hover:bg-[#009ece] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        <?php echo $action === 'edit' ? 'Update Testimonial' : 'Save Testimonial'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'admin_footer.php';
?>

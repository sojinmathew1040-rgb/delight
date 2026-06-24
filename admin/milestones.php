<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN TIMELINE MILESTONES
// ==========================================
require_once 'admin_header.php';

$success_msg = "";
$error_msg = "";

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// 1. Process Form Submission (Add or Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_milestone'])) {
    $year = trim($_POST['year'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if (!empty($year) && !empty($title) && !empty($description)) {
        if ($edit_id > 0) {
            // UPDATE
            try {
                $stmt = $pdo->prepare("UPDATE milestones SET year = ?, title = ?, description = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$year, $title, $description, $sort_order, $edit_id]);
                $success_msg = "Milestone timeline item updated successfully.";
                $action = 'list';
            } catch (PDOException $e) {
                $error_msg = "Failed to update milestone: " . $e->getMessage();
            }
        } else {
            // INSERT
            try {
                $stmt = $pdo->prepare("INSERT INTO milestones (year, title, description, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$year, $title, $description, $sort_order]);
                $success_msg = "New milestone timeline item added successfully.";
                $action = 'list';
            } catch (PDOException $e) {
                $error_msg = "Failed to save milestone: " . $e->getMessage();
            }
        }
    } else {
        $error_msg = "Please fill in all fields (Year, Title, and Description).";
    }
}

// 2. Process Delete Action
if ($action === 'delete' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM milestones WHERE id = ?");
        $stmt->execute([$edit_id]);
        $success_msg = "Milestone deleted successfully.";
        $action = 'list';
    } catch (PDOException $e) {
        $error_msg = "Failed to delete milestone: " . $e->getMessage();
    }
}

// 3. Fetch Edit Item
$edit_milestone = null;
if ($action === 'edit' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM milestones WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_milestone = $stmt->fetch();
        if (!$edit_milestone) {
            $error_msg = "Milestone not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_msg = "Database query error: " . $e->getMessage();
    }
}

// 4. Fetch all Milestones for Listing
$milestones = [];
if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT * FROM milestones ORDER BY sort_order ASC, year ASC");
        $milestones = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_msg = "Failed to fetch milestones: " . $e->getMessage();
    }
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Timeline Milestones</h1>
            <p class="text-sm text-slate-500 mt-1">Manage historical nodes and coordinates highlighted on the corporate legacy timeline.</p>
        </div>
        <?php if ($action === 'list'): ?>
            <a href="milestones.php?action=add" class="inline-flex items-center px-5 py-2.5 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                + Add Milestone
            </a>
        <?php else: ?>
            <a href="milestones.php" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
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
                            <th class="px-6 py-4">Year Node</th>
                            <th class="px-6 py-4">Milestone Title</th>
                            <th class="px-6 py-4">Milestone Description</th>
                            <th class="px-6 py-4">Order Index</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php if (count($milestones) > 0): ?>
                            <?php foreach ($milestones as $m): ?>
                                <tr class="hover:bg-slate-50/20 transition-all duration-150">
                                    <!-- Year -->
                                    <td class="px-6 py-4 font-display font-black text-slate-900 text-lg">
                                        <?php echo htmlspecialchars($m['year']); ?>
                                    </td>
                                    <!-- Title -->
                                    <td class="px-6 py-4 font-bold text-slate-950 max-w-xs text-sm">
                                        <?php echo htmlspecialchars($m['title']); ?>
                                    </td>
                                    <!-- Description -->
                                    <td class="px-6 py-4 text-slate-500 max-w-sm">
                                        <p class="line-clamp-2 leading-relaxed font-normal"><?php echo htmlspecialchars($m['description']); ?></p>
                                    </td>
                                    <!-- Order -->
                                    <td class="px-6 py-4 font-mono text-slate-400">
                                        <?php echo $m['sort_order']; ?>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="milestones.php?action=edit&id=<?php echo $m['id']; ?>" class="p-2 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Edit milestone">
                                                ✏️
                                            </a>
                                            <a href="milestones.php?action=delete&id=<?php echo $m['id']; ?>" onclick="return confirm('Are you sure you want to delete this timeline milestone?')" class="p-2 border border-red-100 hover:bg-red-50 text-red-500 rounded-xl transition-all duration-300 bg-white" title="Delete milestone">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-normal">
                                    <span class="text-3xl block mb-2">📅</span>
                                    No historical milestones found in database.
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
        $year_val = $edit_milestone ? $edit_milestone['year'] : '';
        $title_val = $edit_milestone ? $edit_milestone['title'] : '';
        $desc_val = $edit_milestone ? $edit_milestone['description'] : '';
        $order_val = $edit_milestone ? $edit_milestone['sort_order'] : '0';
        ?>
        <div class="max-w-xl bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-display font-bold text-lg text-slate-900">
                    <?php echo $action === 'edit' ? 'Edit Milestone Details' : 'Add Milestone Node'; ?>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Specify chronological year nodes, titles, description copy, and list index ordering.</p>
            </div>

            <form action="milestones.php?id=<?php echo $edit_id; ?>" method="POST" class="p-6 md:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Year Node -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Year / Node Name *</label>
                        <input type="text" name="year" value="<?php echo htmlspecialchars($year_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. 2006">
                    </div>

                    <!-- Milestone Title -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Milestone Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($title_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. The Initial Foundation">
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Timeline Description *</label>
                    <textarea name="description" rows="4" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-4 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none" placeholder="Provide description detailing materials, offices, and achievements..."><?php echo htmlspecialchars($desc_val); ?></textarea>
                </div>

                <!-- Sort Order -->
                <div class="space-y-1.5 max-w-xs">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Sort Order Index</label>
                    <input type="number" name="sort_order" value="<?php echo htmlspecialchars($order_val); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                </div>

                <!-- Form Buttons -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-2">
                    <a href="milestones.php" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                        Cancel
                    </a>
                    <button type="submit" name="save_milestone" class="px-6 py-2 bg-[#00aff0] hover:bg-[#009ece] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        <?php echo $action === 'edit' ? 'Update Milestone' : 'Save Milestone'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'admin_footer.php';
?>

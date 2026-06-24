<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN KEY METRICS (STATS)
// ==========================================
require_once 'admin_header.php';

$success_msg = "";
$error_msg = "";

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// 1. Process Form Submission (Add or Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_stat'])) {
    $number = trim($_POST['number'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $icon = trim($_POST['icon'] ?? 'house');
    $sort_order = intval($_POST['sort_order'] ?? 0);

    if (!empty($number) && !empty($label)) {
        if ($edit_id > 0) {
            // UPDATE
            try {
                $stmt = $pdo->prepare("UPDATE stats SET number = ?, label = ?, icon = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$number, $label, $icon, $sort_order, $edit_id]);
                $success_msg = "Metric updated successfully.";
                $action = 'list';
            } catch (PDOException $e) {
                $error_msg = "Failed to update metric: " . $e->getMessage();
            }
        } else {
            // INSERT
            try {
                $stmt = $pdo->prepare("INSERT INTO stats (number, label, icon, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$number, $label, $icon, $sort_order]);
                $success_msg = "New metric added successfully.";
                $action = 'list';
            } catch (PDOException $e) {
                $error_msg = "Failed to save new metric: " . $e->getMessage();
            }
        }
    } else {
        $error_msg = "Please fill in both Number and Label fields.";
    }
}

// 2. Process Delete Action
if ($action === 'delete' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM stats WHERE id = ?");
        $stmt->execute([$edit_id]);
        $success_msg = "Key metric deleted successfully.";
        $action = 'list';
    } catch (PDOException $e) {
        $error_msg = "Failed to delete metric: " . $e->getMessage();
    }
}

// 3. Fetch Edit Item
$edit_stat = null;
if ($action === 'edit' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM stats WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_stat = $stmt->fetch();
        if (!$edit_stat) {
            $error_msg = "Metric not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_msg = "Database query error: " . $e->getMessage();
    }
}

// 4. Fetch all Stats for Listing
$stats = [];
if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC, id ASC");
        $stats = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_msg = "Failed to fetch metrics: " . $e->getMessage();
    }
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Key Metrics (Statistics)</h1>
            <p class="text-sm text-slate-500 mt-1">Manage numbers and counters highlighted on the homepage stats ticker.</p>
        </div>
        <?php if ($action === 'list'): ?>
            <a href="stats.php?action=add" class="inline-flex items-center px-5 py-2.5 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                + Add Metric
            </a>
        <?php else: ?>
            <a href="stats.php" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
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
                            <th class="px-6 py-4">Icon Type</th>
                            <th class="px-6 py-4">Display Number</th>
                            <th class="px-6 py-4">Label Text</th>
                            <th class="px-6 py-4">Display Order</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php if (count($stats) > 0): ?>
                            <?php foreach ($stats as $s): ?>
                                <tr class="hover:bg-slate-50/20 transition-all duration-150">
                                    <!-- Icon -->
                                    <td class="px-6 py-4">
                                        <div class="inline-flex items-center px-3 py-1 bg-slate-100 border border-slate-200 text-slate-700 font-semibold rounded-lg capitalize">
                                            <?php echo htmlspecialchars($s['icon']); ?>
                                        </div>
                                    </td>
                                    <!-- Number -->
                                    <td class="px-6 py-4 font-display font-black text-[#0f172a] text-lg">
                                        <?php echo htmlspecialchars($s['number']); ?>
                                    </td>
                                    <!-- Label -->
                                    <td class="px-6 py-4 text-slate-650 max-w-xs">
                                        <?php echo htmlspecialchars($s['label']); ?>
                                    </td>
                                    <!-- Order -->
                                    <td class="px-6 py-4 font-mono text-slate-400">
                                        <?php echo $s['sort_order']; ?>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="stats.php?action=edit&id=<?php echo $s['id']; ?>" class="p-2 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Edit metric">
                                                ✏️
                                            </a>
                                            <a href="stats.php?action=delete&id=<?php echo $s['id']; ?>" onclick="return confirm('Are you sure you want to delete this key statistic?')" class="p-2 border border-red-100 hover:bg-red-50 text-red-500 rounded-xl transition-all duration-300 bg-white" title="Delete metric">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-normal">
                                    <span class="text-3xl block mb-2">📊</span>
                                    No metrics found in database.
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
        $num_val = $edit_stat ? $edit_stat['number'] : '';
        $label_val = $edit_stat ? $edit_stat['label'] : '';
        $icon_val = $edit_stat ? $edit_stat['icon'] : 'house';
        $order_val = $edit_stat ? $edit_stat['sort_order'] : '0';
        ?>
        <div class="max-w-xl bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-display font-bold text-lg text-slate-900">
                    <?php echo $action === 'edit' ? 'Edit Metric details' : 'Add New Stat Metric'; ?>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Define number counters, select vector SVGs, and order sorting parameters.</p>
            </div>

            <form action="stats.php?id=<?php echo $edit_id; ?>" method="POST" class="p-6 md:p-8 space-y-6">
                <!-- Display Number -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Display Number *</label>
                    <input type="text" name="number" value="<?php echo htmlspecialchars($num_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. 250+ or 14">
                </div>

                <!-- Label -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Label Description *</label>
                    <input type="text" name="label" value="<?php echo htmlspecialchars($label_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. COMPLETED PROJECTS">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Icon type -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Vector Icon Category</label>
                        <select name="icon" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                            <option value="helmet" <?php echo $icon_val === 'helmet' ? 'selected' : ''; ?>>Helmet (Experience)</option>
                            <option value="house" <?php echo $icon_val === 'house' ? 'selected' : ''; ?>>House (Projects)</option>
                            <option value="map-pin" <?php echo $icon_val === 'map-pin' ? 'selected' : ''; ?>>Map Pin (Locations/Districts)</option>
                            <option value="family" <?php echo $icon_val === 'family' ? 'selected' : ''; ?>>Family (Happy Clients)</option>
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Display Order Index</label>
                        <input type="number" name="sort_order" value="<?php echo htmlspecialchars($order_val); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-2">
                    <a href="stats.php" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                        Cancel
                    </a>
                    <button type="submit" name="save_stat" class="px-6 py-2 bg-[#00aff0] hover:bg-[#009ece] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        <?php echo $action === 'edit' ? 'Update Metric' : 'Save Metric'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'admin_footer.php';
?>

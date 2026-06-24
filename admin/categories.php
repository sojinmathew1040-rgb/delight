<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN PORTFOLIO CATEGORIES
// ==========================================
require_once 'admin_header.php';

$success_msg = "";
$error_msg = "";

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// 1. Process Form Submission (Add or Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_category'])) {
    $name = trim($_POST['name'] ?? '');

    if (!empty($name)) {
        if ($edit_id > 0) {
            // UPDATE
            try {
                // Get the old category name first to cascade update projects using it
                $stmt = $pdo->prepare("SELECT name FROM portfolio_categories WHERE id = ?");
                $stmt->execute([$edit_id]);
                $old_name = $stmt->fetchColumn();

                if ($old_name !== false) {
                    $pdo->beginTransaction();

                    // Update category name
                    $stmt_update_cat = $pdo->prepare("UPDATE portfolio_categories SET name = ? WHERE id = ?");
                    $stmt_update_cat->execute([$name, $edit_id]);

                    // Cascade update to portfolio table string reference
                    if ($old_name !== $name) {
                        $stmt_cascade = $pdo->prepare("UPDATE portfolio SET category = ? WHERE category = ?");
                        $stmt_cascade->execute([$name, $old_name]);
                    }

                    $pdo->commit();
                    $success_msg = "Category name updated successfully and cascaded to all corresponding projects.";
                    $action = 'list';
                } else {
                    $error_msg = "Category not found.";
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($e->getCode() == 23000) {
                    $error_msg = "A category with this name already exists.";
                } else {
                    $error_msg = "Failed to update category: " . $e->getMessage();
                }
            }
        } else {
            // INSERT
            try {
                $stmt = $pdo->prepare("INSERT INTO portfolio_categories (name) VALUES (?)");
                $stmt->execute([$name]);
                $success_msg = "New category added successfully.";
                $action = 'list';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error_msg = "A category with this name already exists.";
                } else {
                    $error_msg = "Failed to save category: " . $e->getMessage();
                }
            }
        }
    } else {
        $error_msg = "Please enter a valid category name.";
    }
}

// 2. Process Delete Action
if ($action === 'delete' && $edit_id > 0) {
    try {
        // Fetch category name
        $stmt_name = $pdo->prepare("SELECT name FROM portfolio_categories WHERE id = ?");
        $stmt_name->execute([$edit_id]);
        $cat_name = $stmt_name->fetchColumn();

        if ($cat_name !== false) {
            // Check if any portfolio projects are using this category
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM portfolio WHERE category = ?");
            $stmt_count->execute([$cat_name]);
            $proj_count = intval($stmt_count->fetchColumn());

            if ($proj_count > 0) {
                $error_msg = "Cannot delete category \"$cat_name\" because it is currently assigned to $proj_count project(s). Please reassign those projects first.";
            } else {
                $stmt_del = $pdo->prepare("DELETE FROM portfolio_categories WHERE id = ?");
                $stmt_del->execute([$edit_id]);
                $success_msg = "Category deleted successfully.";
            }
        } else {
            $error_msg = "Category not found.";
        }
        $action = 'list';
    } catch (PDOException $e) {
        $error_msg = "Failed to delete category: " . $e->getMessage();
    }
}

// 3. Fetch Edit Item
$edit_category = null;
if ($action === 'edit' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM portfolio_categories WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_category = $stmt->fetch();
        if (!$edit_category) {
            $error_msg = "Category not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_msg = "Database query error: " . $e->getMessage();
    }
}

// 4. Fetch all Categories for Listing
$categories = [];
if ($action === 'list') {
    try {
        // Fetch categories and join count of projects in portfolio table using category string match
        $stmt = $pdo->query("SELECT c.*, COUNT(p.id) as project_count FROM portfolio_categories c LEFT JOIN portfolio p ON c.name = p.category GROUP BY c.id ORDER BY c.name ASC");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_msg = "Failed to fetch categories: " . $e->getMessage();
    }
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Portfolio Categories</h1>
            <p class="text-sm text-slate-500 mt-1">Manage sectors and tags that organize your portfolio works archive.</p>
        </div>
        <?php if ($action === 'list'): ?>
            <a href="categories.php?action=add" class="inline-flex items-center px-5 py-2.5 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                + Add Category
            </a>
        <?php else: ?>
            <a href="categories.php" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
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
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Category Name</th>
                            <th class="px-6 py-4">Associated Projects</th>
                            <th class="px-6 py-4">Date Created</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-slate-50/20 transition-all duration-150">
                                    <!-- ID -->
                                    <td class="px-6 py-4 font-mono text-slate-400">
                                        #<?php echo $cat['id']; ?>
                                    </td>
                                    <!-- Name -->
                                    <td class="px-6 py-4 font-bold text-slate-900 text-sm">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </td>
                                    <!-- Associated Projects count -->
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold <?php echo $cat['project_count'] > 0 ? 'bg-[#00aff0]/10 text-[#00aff0]' : 'bg-slate-100 text-slate-400'; ?>">
                                            <?php echo $cat['project_count']; ?> projects
                                        </span>
                                    </td>
                                    <!-- Created At -->
                                    <td class="px-6 py-4 text-slate-400 font-normal">
                                        <?php echo date('M d, Y • H:i', strtotime($cat['created_at'])); ?>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="p-2 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Edit Category">
                                                ✏️
                                            </a>
                                            <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" onclick="return confirm('Are you sure you want to delete the category &quot;<?php echo htmlspecialchars($cat['name']); ?>&quot;?')" class="p-2 border border-red-100 hover:bg-red-50 text-red-500 rounded-xl transition-all duration-300 bg-white" title="Delete Category">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-normal">
                                    <span class="text-3xl block mb-2">📁</span>
                                    No categories found in database.
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
        $name_val = $edit_category ? $edit_category['name'] : '';
        ?>
        <div class="max-w-xl bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-display font-bold text-lg text-slate-900">
                    <?php echo $action === 'edit' ? 'Edit Category' : 'Add New Category'; ?>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Define a distinct design sector or project type label.</p>
            </div>

            <form action="categories.php?id=<?php echo $edit_id; ?>" method="POST" class="p-6 md:p-8 space-y-6">
                <!-- Category Name -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Category Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Industrial Architecture">
                </div>

                <!-- Form Buttons -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-2">
                    <a href="categories.php" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                        Cancel
                    </a>
                    <button type="submit" name="save_category" class="px-6 py-2 bg-[#00aff0] hover:bg-[#009ece] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        <?php echo $action === 'edit' ? 'Update Category' : 'Save Category'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'admin_footer.php';
?>

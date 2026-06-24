<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN TEAM MEMBERS
// ==========================================
require_once 'admin_header.php';

$success_msg = "";
$error_msg = "";

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// Helper function to handle team member image uploads
function handle_image_upload($file_field) {
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'error' => 'No file uploaded or upload error.'];
    }

    $file = $_FILES[$file_field];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $file_info = pathinfo($file['name']);
    $ext = strtolower($file_info['extension'] ?? '');

    if (!in_array($ext, $allowed_exts)) {
        return ['status' => false, 'error' => 'Invalid image format. Allowed: ' . implode(', ', $allowed_exts)];
    }

    $upload_dir = '../asset/images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $clean_name = time() . '_team_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file_info['filename']) . '.' . $ext;
    $target_path = $upload_dir . $clean_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['status' => true, 'path' => 'asset/images/' . $clean_name];
    } else {
        return ['status' => false, 'error' => 'Failed to write file to assets directory.'];
    }
}

// 1. Process Form Submission (Add or Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_member'])) {
    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $avatar_text = trim($_POST['avatar_text'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if (!empty($name) && !empty($role) && !empty($description)) {
        if (empty($avatar_text)) {
            // Auto generate avatar initials from name
            $words = explode(" ", $name);
            $avatar_text = "";
            foreach ($words as $w) {
                $avatar_text .= strtoupper(substr($w, 0, 1));
            }
            $avatar_text = substr($avatar_text, 0, 2);
        }

        // Handle image upload
        $image_path = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_res = handle_image_upload('image');
            if ($upload_res['status']) {
                $image_path = $upload_res['path'];
            } else {
                $error_msg = $upload_res['error'];
            }
        }

        if (empty($error_msg)) {
            if ($edit_id > 0) {
                // UPDATE
                try {
                    if (!empty($image_path)) {
                        // Fetch old image to delete
                        $stmt = $pdo->prepare("SELECT image FROM team_members WHERE id = ?");
                        $stmt->execute([$edit_id]);
                        $old_img = $stmt->fetchColumn();

                        $stmt = $pdo->prepare("UPDATE team_members SET name = ?, role = ?, avatar_text = ?, description = ?, sort_order = ?, parent_id = ?, image = ? WHERE id = ?");
                        $stmt->execute([$name, $role, $avatar_text, $description, $sort_order, $parent_id, $image_path, $edit_id]);

                        if ($old_img && file_exists("../" . $old_img)) {
                            @unlink("../" . $old_img);
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE team_members SET name = ?, role = ?, avatar_text = ?, description = ?, sort_order = ?, parent_id = ? WHERE id = ?");
                        $stmt->execute([$name, $role, $avatar_text, $description, $sort_order, $parent_id, $edit_id]);
                    }
                    $success_msg = "Team member details updated successfully.";
                    $action = 'list';
                } catch (PDOException $e) {
                    $error_msg = "Failed to update member: " . $e->getMessage();
                }
            } else {
                // INSERT
                try {
                    $stmt = $pdo->prepare("INSERT INTO team_members (name, role, avatar_text, description, sort_order, parent_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $role, $avatar_text, $description, $sort_order, $parent_id, !empty($image_path) ? $image_path : null]);
                    $success_msg = "New team member added successfully.";
                    $action = 'list';
                } catch (PDOException $e) {
                    $error_msg = "Failed to save member: " . $e->getMessage();
                }
            }
        }
    } else {
        $error_msg = "Please fill in all required fields (Name, Role, and Description).";
    }
}

// 2. Process Delete Action
if ($action === 'delete' && $edit_id > 0) {
    try {
        // Fetch old image to delete
        $stmt = $pdo->prepare("SELECT image FROM team_members WHERE id = ?");
        $stmt->execute([$edit_id]);
        $old_img = $stmt->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$edit_id]);

        if ($old_img && file_exists("../" . $old_img)) {
            @unlink("../" . $old_img);
        }
        $success_msg = "Team member deleted successfully.";
        $action = 'list';
    } catch (PDOException $e) {
        $error_msg = "Failed to delete team member: " . $e->getMessage();
    }
}

// 3. Fetch Edit Item
$edit_member = null;
if ($action === 'edit' && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_member = $stmt->fetch();
        if (!$edit_member) {
            $error_msg = "Team member not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error_msg = "Database query error: " . $e->getMessage();
    }
}

// 4. Fetch all Members for Listing
$members = [];
if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC");
        $members = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_msg = "Failed to fetch team members: " . $e->getMessage();
    }
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Executive Team</h1>
            <p class="text-sm text-slate-500 mt-1">Manage team members, roles, and profiles shown in the organizational legacy structure.</p>
        </div>
        <?php if ($action === 'list'): ?>
            <a href="team.php?action=add" class="inline-flex items-center px-5 py-2.5 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                + Add Member
            </a>
        <?php else: ?>
            <a href="team.php" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
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
        <?php
        // Refetch members with parent names for the table view
        try {
            $stmt = $pdo->query("SELECT t1.*, t2.name as parent_name FROM team_members t1 LEFT JOIN team_members t2 ON t1.parent_id = t2.id ORDER BY t1.sort_order ASC, t1.id ASC");
            $members = $stmt->fetchAll();
        } catch (PDOException $e) {
            $members = [];
        }
        ?>
        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-150 text-left">
                    <thead class="bg-slate-50/20 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Profile Photo</th>
                            <th class="px-6 py-4">Full Name</th>
                            <th class="px-6 py-4">Role Position</th>
                            <th class="px-6 py-4">Reports To</th>
                            <th class="px-6 py-4">Description Bio</th>
                            <th class="px-6 py-4">Order</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php if (count($members) > 0): ?>
                            <?php foreach ($members as $m): ?>
                                <tr class="hover:bg-slate-50/20 transition-all duration-150">
                                    <!-- Photo/Avatar initials -->
                                    <td class="px-6 py-4">
                                        <?php if (!empty($m['image'])): ?>
                                            <div class="w-10 h-10 rounded-full border border-slate-200 overflow-hidden shadow-sm">
                                                <img src="../<?php echo htmlspecialchars($m['image']); ?>" class="w-full h-full object-cover">
                                            </div>
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-display text-xs font-black text-slate-900 shadow-inner">
                                                <?php echo htmlspecialchars($m['avatar_text']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Name -->
                                    <td class="px-6 py-4 font-bold text-slate-900 text-sm">
                                        <?php echo htmlspecialchars($m['name']); ?>
                                    </td>
                                    <!-- Role -->
                                    <td class="px-6 py-4 text-slate-650">
                                        <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($m['role']); ?></span>
                                    </td>
                                    <!-- Reports To -->
                                    <td class="px-6 py-4">
                                        <?php if (!empty($m['parent_name'])): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase border bg-slate-50 border-slate-150 text-slate-600">
                                                👤 <?php echo htmlspecialchars($m['parent_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">None (Top Level)</span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Description -->
                                    <td class="px-6 py-4 text-slate-500 max-w-xs">
                                        <p class="line-clamp-2 leading-relaxed font-normal"><?php echo htmlspecialchars($m['description']); ?></p>
                                    </td>
                                    <!-- Order -->
                                    <td class="px-6 py-4 font-mono text-slate-400">
                                        <?php echo $m['sort_order']; ?>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="team.php?action=edit&id=<?php echo $m['id']; ?>" class="p-2 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Edit member">
                                                ✏️
                                            </a>
                                            <a href="team.php?action=delete&id=<?php echo $m['id']; ?>" onclick="return confirm('Are you sure you want to delete this team member?')" class="p-2 border border-red-100 hover:bg-red-50 text-red-500 rounded-xl transition-all duration-300 bg-white" title="Delete member">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-normal">
                                    <span class="text-3xl block mb-2">👥</span>
                                    No executive team members found in database.
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
        $name_val = $edit_member ? $edit_member['name'] : '';
        $role_val = $edit_member ? $edit_member['role'] : '';
        $avatar_val = $edit_member ? $edit_member['avatar_text'] : '';
        $desc_val = $edit_member ? $edit_member['description'] : '';
        $order_val = $edit_member ? $edit_member['sort_order'] : '0';
        $parent_id_val = $edit_member ? $edit_member['parent_id'] : null;

        // Fetch other members for the Reports To dropdown
        $parent_members = [];
        try {
            if ($edit_id > 0) {
                $stmt_p = $pdo->prepare("SELECT id, name, role FROM team_members WHERE id != ? ORDER BY name ASC");
                $stmt_p->execute([$edit_id]);
            } else {
                $stmt_p = $pdo->query("SELECT id, name, role FROM team_members ORDER BY name ASC");
            }
            $parent_members = $stmt_p->fetchAll();
        } catch (PDOException $e) {
            $parent_members = [];
        }
        ?>
        <div class="max-w-xl bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-display font-bold text-lg text-slate-900">
                    <?php echo $action === 'edit' ? 'Edit Member Details' : 'Add Team Member'; ?>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Specify personal names, professional roles, avatar text, profile bio, and display order.</p>
            </div>

            <form action="team.php?id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
                <!-- Name -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Full Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Sterling H. Croft">
                </div>

                <!-- Role -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Role / Position *</label>
                    <input type="text" name="role" value="<?php echo htmlspecialchars($role_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Principal Architect & Founder">
                </div>

                <!-- Reports To / Parent dropdown -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Reports To (Organizational Parent)</label>
                    <select name="parent_id" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                        <option value="">-- None (Top Level / Founder) --</option>
                        <?php foreach ($parent_members as $pm): ?>
                            <option value="<?php echo $pm['id']; ?>" <?php echo $parent_id_val == $pm['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pm['name']); ?> (<?php echo htmlspecialchars($pm['role']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Avatar Text -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Avatar Initials (max 2 chars)</label>
                        <input type="text" name="avatar_text" value="<?php echo htmlspecialchars($avatar_val); ?>" maxlength="2" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. SC (Leave blank to auto-generate)">
                    </div>

                    <!-- Sort Order -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Sort Order Index</label>
                        <input type="number" name="sort_order" value="<?php echo htmlspecialchars($order_val); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Profile Bio / Description *</label>
                    <textarea name="description" rows="4" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-4 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none" placeholder="Explain structural focus, coordinates, experience summary, and metrics..."><?php echo htmlspecialchars($desc_val); ?></textarea>
                </div>

                <!-- Profile Photo -->
                <div class="space-y-3 pt-2">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Profile Photo (Optional)</label>
                    
                    <?php if ($edit_member && !empty($edit_member['image'])): ?>
                        <div class="flex items-center gap-4 p-4 border border-slate-200 rounded-2xl bg-slate-50/50">
                            <img src="../<?php echo $edit_member['image']; ?>" alt="Current photo" class="w-16 h-16 object-cover rounded-full border border-slate-200">
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Current Photo Path</span>
                                <span class="text-xs text-slate-650 font-mono block truncate"><?php echo $edit_member['image']; ?></span>
                                <span class="text-[10px] text-slate-400 font-medium block mt-1">Upload a new photo below if you wish to overwrite this file.</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="file" name="image" class="w-full border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-[#00aff0]/10 file:text-[#00aff0] hover:file:bg-[#00aff0]/20 transition-all duration-300" accept="image/*">
                    <p class="text-[10px] text-slate-400">Accepted formats: JPG, JPEG, PNG, WEBP, GIF. Max file size: 5MB.</p>
                </div>

                <!-- Form Buttons -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-2">
                    <a href="team.php" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                        Cancel
                    </a>
                    <button type="submit" name="save_member" class="px-6 py-2 bg-[#00aff0] hover:bg-[#009ece] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        <?php echo $action === 'edit' ? 'Update Member' : 'Save Member'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'admin_footer.php';
?>

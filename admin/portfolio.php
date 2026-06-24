<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN PORTFOLIO
// ==========================================
require_once 'db_connection.php';

// AJAX Toggle Gallery Visibility
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['action']) && $_GET['action'] === 'toggle_gallery_visibility') {
    header('Content-Type: application/json');
    $img_id = intval($_POST['img_id'] ?? 0);
    $show_in_gallery = intval($_POST['show_in_gallery'] ?? 1);

    if ($img_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE portfolio_gallery SET show_in_gallery = ? WHERE id = ?");
            $stmt->execute([$show_in_gallery, $img_id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid image ID.']);
    }
    exit;
}

require_once 'admin_header.php';

$success_msg = "";
$error_msg = "";

$action = $_GET['action'] ?? 'list';
$edit_id = intval($_GET['id'] ?? 0);

// Helper function to handle image uploads
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

    // Target upload directory
    $upload_dir = '../asset/images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Clean filename
    $clean_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file_info['filename']) . '.' . $ext;
    $target_path = $upload_dir . $clean_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['status' => true, 'path' => 'asset/images/' . $clean_name];
    } else {
        return ['status' => false, 'error' => 'Failed to write file to assets directory. check folder permissions.'];
    }
}

// Multiple gallery uploads are managed inside portfolio.php

// Helper function to handle multiple gallery image uploads
function handle_multiple_gallery_uploads_custom($file_array, $target_project_id, $pdo, $single_title = '', $single_desc = '', $single_stage = '', $single_materiality = '', $show_in_gallery = 0) {
    if (!isset($file_array) || !is_array($file_array['name']) || empty($file_array['name'][0])) {
        return ['status' => false, 'error' => 'No files uploaded or upload error.'];
    }
    
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $upload_dir = '../asset/images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $uploaded_count = 0;
    $errors = [];
    $num_files = count($file_array['name']);

    foreach ($file_array['name'] as $index => $name) {
        if ($file_array['error'][$index] !== UPLOAD_ERR_OK) {
            $errors[] = "File \"" . htmlspecialchars($name) . "\" encountered an upload error (Code: " . $file_array['error'][$index] . ").";
            continue; 
        }
        
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) {
            $errors[] = "File \"" . htmlspecialchars($name) . "\" has an invalid format. Allowed: " . implode(', ', $allowed_exts);
            continue;
        }
        
        // Clean filename
        $clean_name = time() . '_gallery_' . $index . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($name, PATHINFO_FILENAME)) . '.' . $ext;
        $target_path = $upload_dir . $clean_name;
        
        if (move_uploaded_file($file_array['tmp_name'][$index], $target_path)) {
            // Determine title, description, stage, materiality
            if ($num_files === 1) {
                $title = !empty($single_title) ? $single_title : ucwords(str_replace(['_', '-'], ' ', pathinfo($name, PATHINFO_FILENAME)));
                $desc = $single_desc;
                $stage = !empty($single_stage) ? $single_stage : null;
                $materiality = !empty($single_materiality) ? $single_materiality : null;
            } else {
                $title = ucwords(str_replace(['_', '-'], ' ', pathinfo($name, PATHINFO_FILENAME)));
                $desc = '';
                $stage = null;
                $materiality = null;
            }
            
            // Insert into portfolio_gallery
            $stmt = $pdo->prepare("INSERT INTO portfolio_gallery (portfolio_id, src, title, desc_text, stage, materiality, show_in_gallery) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$target_project_id, 'asset/images/' . $clean_name, $title, $desc, $stage, $materiality, $show_in_gallery]);
            $uploaded_count++;
        } else {
            $errors[] = "Failed to save file \"" . htmlspecialchars($name) . "\".";
        }
    }
    
    return [
        'status' => empty($errors),
        'count' => $uploaded_count,
        'errors' => $errors
    ];
}

// Process Gallery Image Detail Updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_gallery_image'])) {
    $img_id = intval($_POST['img_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['desc_text'] ?? '');
    $stage = trim($_POST['stage'] ?? '');
    $materiality = trim($_POST['materiality'] ?? '');
    $show_in_gallery = isset($_POST['show_in_gallery']) ? 1 : 0;

    if ($img_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE portfolio_gallery SET title = ?, desc_text = ?, stage = ?, materiality = ?, show_in_gallery = ? WHERE id = ?");
            $stmt->execute([
                $title,
                $desc,
                !empty($stage) ? $stage : null,
                !empty($materiality) ? $materiality : null,
                $show_in_gallery,
                $img_id
            ]);
            $success_msg = "Blueprint photo details successfully updated.";
        } catch (PDOException $e) {
            $error_msg = "Failed to update blueprint: " . $e->getMessage();
        }
    }
}

// Process Adding Images to Gallery
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_gallery_image'])) {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['desc_text'] ?? '');
    $stage = trim($_POST['stage'] ?? '');
    $materiality = trim($_POST['materiality'] ?? '');
    $show_in_gallery = 0; // Default new uploads to hidden from public gallery until explicitly selected

    if ($edit_id <= 0) {
        $error_msg = "Please select a valid project.";
    } else {
        // Fetch target project's title
        try {
            $stmt = $pdo->prepare("SELECT title FROM portfolio WHERE id = ?");
            $stmt->execute([$edit_id]);
            $target_project_title = $stmt->fetchColumn();
        } catch (PDOException $e) {
            $target_project_title = "Selected Project";
        }

        if (isset($_FILES['gallery_files']) && !empty($_FILES['gallery_files']['name'][0])) {
            $upload_res = handle_multiple_gallery_uploads_custom($_FILES['gallery_files'], $edit_id, $pdo, $title, $desc, $stage, $materiality, $show_in_gallery);
            if ($upload_res['status']) {
                $success_msg = "Successfully uploaded " . $upload_res['count'] . " blueprint photo(s) to project \"" . htmlspecialchars($target_project_title) . "\".";
            } else {
                if ($upload_res['count'] > 0) {
                    $success_msg = "Successfully uploaded " . $upload_res['count'] . " blueprint photo(s) to project \"" . htmlspecialchars($target_project_title) . "\".";
                }
                $error_msg = implode("<br>", $upload_res['errors']);
            }
        } else {
            $error_msg = "Please choose at least one blueprint image to upload.";
        }
    }
}

// Process Deleting Image from Gallery
if (($action === 'gallery' || $action === 'edit') && $edit_id > 0 && isset($_GET['subaction']) && $_GET['subaction'] === 'delete' && isset($_GET['img_id'])) {
    $img_id = intval($_GET['img_id']);
    try {
        $stmt = $pdo->prepare("SELECT src FROM portfolio_gallery WHERE id = ? AND portfolio_id = ?");
        $stmt->execute([$img_id, $edit_id]);
        $img_src = $stmt->fetchColumn();

        if ($img_src) {
            $stmt = $pdo->prepare("DELETE FROM portfolio_gallery WHERE id = ?");
            $stmt->execute([$img_id]);

            if (file_exists("../" . $img_src)) {
                @unlink("../" . $img_src);
            }
            $success_msg = "Blueprint photo successfully deleted.";
        }
    } catch (PDOException $e) {
        $error_msg = "Failed to delete photo: " . $e->getMessage();
    }
}

// 1. Process Post Submissions (Add or Edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_project'])) {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stage = trim($_POST['stage'] ?? '');
    $materiality = trim($_POST['materiality'] ?? '');
    
    if (!empty($title) && !empty($category)) {
        $image_path = "";
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_res = handle_image_upload('image');
            if ($upload_res['status']) {
                $image_path = $upload_res['path'];
            } else {
                $error_msg = $upload_res['error'];
            }
        }

        // Only proceed if upload didn't fail (or wasn't requested)
        if (empty($error_msg)) {
            if ($edit_id > 0) {
                // UPDATE
                try {
                    if (!empty($image_path)) {
                        // Get old image to delete later (optional)
                        $stmt = $pdo->prepare("SELECT image FROM portfolio WHERE id = ?");
                        $stmt->execute([$edit_id]);
                        $old_img = $stmt->fetchColumn();
                        
                        $stmt = $pdo->prepare("UPDATE portfolio SET title = ?, category = ?, description = ?, stage = ?, materiality = ?, image = ? WHERE id = ?");
                        $stmt->execute([$title, $category, $description, !empty($stage) ? $stage : null, !empty($materiality) ? $materiality : null, $image_path, $edit_id]);
                        
                        if ($old_img && file_exists("../" . $old_img) && strpos($old_img, 'default') === false) {
                            @unlink("../" . $old_img);
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE portfolio SET title = ?, category = ?, description = ?, stage = ?, materiality = ? WHERE id = ?");
                        $stmt->execute([$title, $category, $description, !empty($stage) ? $stage : null, !empty($materiality) ? $materiality : null, $edit_id]);
                    }


                    if (empty($error_msg)) {
                        $success_msg = "Project details successfully updated.";
                        $action = 'list';
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database update error: " . $e->getMessage();
                }
            } else {
                // INSERT
                if (empty($image_path)) {
                    $error_msg = "Please upload a primary image for the new project.";
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO portfolio (title, category, description, stage, materiality, image) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $category, $description, !empty($stage) ? $stage : null, !empty($materiality) ? $materiality : null, $image_path]);
                        $new_project_id = $pdo->lastInsertId();


                        if (empty($error_msg)) {
                            $success_msg = "New project successfully added to portfolio archive.";
                            $action = 'list';
                        }
                    } catch (PDOException $e) {
                        $error_msg = "Database insert error: " . $e->getMessage();
                    }
                }
            }
        }
    } else {
        $error_msg = "Please fill in all required fields (Title and Category).";
    }
}

// 2. Process Delete Action
if ($action === 'delete' && $edit_id > 0) {
    try {
        // Fetch project image and gallery images to delete files
        $stmt = $pdo->prepare("SELECT image FROM portfolio WHERE id = ?");
        $stmt->execute([$edit_id]);
        $proj_img = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT src FROM portfolio_gallery WHERE portfolio_id = ?");
        $stmt->execute([$edit_id]);
        $gallery_imgs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Delete from database (foreign key will cascade delete gallery entries!)
        $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
        $stmt->execute([$edit_id]);

        // Unlink files
        if ($proj_img && file_exists("../" . $proj_img)) {
            @unlink("../" . $proj_img);
        }
        foreach ($gallery_imgs as $g_img) {
            if ($g_img && file_exists("../" . $g_img)) {
                @unlink("../" . $g_img);
            }
        }

        $success_msg = "Project and all corresponding gallery blueprints deleted.";
        $action = 'list';
    } catch (PDOException $e) {
        $error_msg = "Failed to delete project: " . $e->getMessage();
    }
}

// 3. Fetch details for editing / gallery view
$edit_project = null;
$gallery_photos = [];
$edit_image_data = null;

if (($action === 'edit' || $action === 'gallery') && $edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_project = $stmt->fetch();
        if (!$edit_project) {
            $error_msg = "Requested project was not found.";
            $action = 'list';
        }
        
        // Fetch gallery photos for this project (for both gallery and edit action)
        if ($edit_project) {
            $stmt = $pdo->prepare("SELECT * FROM portfolio_gallery WHERE portfolio_id = ? ORDER BY id ASC");
            $stmt->execute([$edit_id]);
            $gallery_photos = $stmt->fetchAll();

            if ($action === 'gallery') {
                // Fetch edit image details if edit_img_id is set
                $edit_img_id = intval($_GET['edit_img_id'] ?? 0);
                if ($edit_img_id > 0) {
                    $stmt = $pdo->prepare("SELECT * FROM portfolio_gallery WHERE id = ? AND portfolio_id = ?");
                    $stmt->execute([$edit_img_id, $edit_id]);
                    $edit_image_data = $stmt->fetch();
                    if (!$edit_image_data) {
                        $error_msg = "Requested blueprint photo not found.";
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $error_msg = "Failed to fetch details: " . $e->getMessage();
    }
}

// 4. Fetch all projects for listing
$projects = [];
if ($action === 'list') {
    try {
        // Fetch project list with gallery image counts
        $stmt = $pdo->query("SELECT p.*, COUNT(g.id) as gallery_count FROM portfolio p LEFT JOIN portfolio_gallery g ON p.id = g.portfolio_id GROUP BY p.id ORDER BY p.created_at DESC");
        $projects = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_msg = "Failed to load projects list: " . $e->getMessage();
    }
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Portfolio Works</h1>
            <p class="text-sm text-slate-500 mt-1">Manage architectural projects and blueprint galleries displayed on the website.</p>
        </div>
        <?php if ($action === 'list'): ?>
            <a href="portfolio.php?action=add" class="inline-flex items-center px-5 py-2.5 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                + Add New Project
            </a>
        <?php else: ?>
            <a href="portfolio.php" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
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
                            <th class="px-6 py-4">Preview</th>
                            <th class="px-6 py-4">Project Info</th>
                            <th class="px-6 py-4">Sector Category</th>
                            <th class="px-6 py-4">Blueprints count</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        <?php if (count($projects) > 0): ?>
                            <?php foreach ($projects as $proj): 
                                $image_url = "../" . $proj['image'];
                                ?>
                                <tr class="hover:bg-slate-50/30 transition-all duration-150">
                                    <!-- Image Preview -->
                                    <td class="px-6 py-4 flex-shrink-0">
                                        <div class="w-20 h-14 rounded-lg overflow-hidden border border-slate-200 bg-slate-100">
                                            <img src="<?php echo $image_url; ?>" alt="Project thumbnail" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <!-- Title & Description -->
                                    <td class="px-6 py-4 max-w-sm">
                                        <div class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($proj['title']); ?></div>
                                        <div class="text-slate-400 mt-1 line-clamp-2 leading-relaxed font-normal"><?php echo htmlspecialchars($proj['description']); ?></div>
                                    </td>
                                    <!-- Category -->
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase border bg-slate-50 border-slate-150">
                                            <?php echo htmlspecialchars($proj['category']); ?>
                                        </span>
                                    </td>
                                    <!-- Blueprint Count -->
                                    <td class="px-6 py-4 text-slate-650 font-bold">
                                        <div class="flex items-center gap-1.5">
                                            <span class="bg-[#00aff0]/10 text-[#00aff0] px-2 py-0.5 rounded text-[10px]">
                                                <?php echo $proj['gallery_count']; ?>
                                            </span>
                                            <a href="portfolio.php?action=gallery&id=<?php echo $proj['id']; ?>" class="text-[10px] text-slate-400 hover:text-[#00aff0] hover:underline font-semibold">Manage Blueprints</a>
                                        </div>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="portfolio.php?action=gallery&id=<?php echo $proj['id']; ?>" class="px-3 py-1.5 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Manage Blueprint Gallery">
                                                🖼️ Manage Gallery
                                            </a>
                                            <a href="portfolio.php?action=edit&id=<?php echo $proj['id']; ?>" class="p-2 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Edit details">
                                                ✏️
                                            </a>
                                            <a href="portfolio.php?action=delete&id=<?php echo $proj['id']; ?>" onclick="return confirm('Are you sure you want to delete this project? All corresponding gallery blueprint photos will be permanently deleted.')" class="p-2 border border-red-100 hover:bg-red-50 text-red-500 rounded-xl transition-all duration-300 bg-white" title="Delete Project">
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
                                    No projects in portfolio archive.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- ADD / EDIT MODE -->
    <?php if ($action === 'add' || $action === 'edit'): 
        $title_val = $edit_project ? $edit_project['title'] : '';
        $category_val = $edit_project ? $edit_project['category'] : '';
        $desc_val = $edit_project ? $edit_project['description'] : '';
        $stage_val = $edit_project ? $edit_project['stage'] : '';
        $materiality_val = $edit_project ? $edit_project['materiality'] : '';
        $img_val = $edit_project ? $edit_project['image'] : '';

        // Fetch categories dynamically
        try {
            $stmt_cats = $pdo->query("SELECT name FROM portfolio_categories ORDER BY name ASC");
            $db_categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $db_categories = ['Luxury Residential', 'Commercial Frameworks', 'Sustainable Fits'];
        }
        ?>
        <div class="max-w-2xl bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-display font-bold text-lg text-slate-900">
                    <?php echo $action === 'edit' ? 'Edit Project Details' : 'Add New Project'; ?>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Specify structural geometry parameters, categories, and upload primary asset file.</p>
            </div>

            <form action="portfolio.php?id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
                <!-- Title -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Project Title *</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($title_val); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. The Obsidian Villa">
                </div>

                <!-- Category Sector dropdown -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Category Sector *</label>
                    <select name="category" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                        <option value="" disabled <?php echo empty($category_val) ? 'selected' : ''; ?>>-- Select a Category --</option>
                        <?php foreach ($db_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category_val === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="text-right">
                        <a href="categories.php" class="text-[10px] text-[#00aff0] hover:underline font-semibold mt-1 inline-block">+ Manage / Add Categories</a>
                    </div>
                </div>

                <!-- Project Stage -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Project Stage *</label>
                    <select name="stage" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                        <option value="Consultation" <?php echo $stage_val === 'Consultation' ? 'selected' : ''; ?>>1. Consultation</option>
                        <option value="Design & Planning" <?php echo $stage_val === 'Design & Planning' ? 'selected' : ''; ?>>2. Design & Planning</option>
                        <option value="Construction" <?php echo ($stage_val === 'Construction' || empty($stage_val)) ? 'selected' : ''; ?>>3. Construction</option>
                        <option value="Handover" <?php echo $stage_val === 'Handover' ? 'selected' : ''; ?>>4. Handover</option>
                    </select>
                </div>

                <!-- Materiality -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Materiality</label>
                    <input type="text" name="materiality" value="<?php echo htmlspecialchars($materiality_val); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Premium Curated">
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Description / Concept Summary</label>
                    <textarea name="description" rows="4" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-4 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none" placeholder="Describe brutalist weight, glass integrations, structural frames, and dimensions..."><?php echo htmlspecialchars($desc_val); ?></textarea>
                </div>

                <!-- Image upload -->
                <div class="space-y-3 pt-2">
                    <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Primary Project Image Thumbnail *</label>
                    
                    <?php if ($action === 'edit' && !empty($img_val)): ?>
                        <div class="flex items-center gap-4 p-4 border border-slate-200 rounded-2xl bg-slate-50/50">
                            <img src="../<?php echo $img_val; ?>" alt="Current project thumbnail" class="w-24 h-16 object-cover rounded-lg border border-slate-200">
                            <div>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Current File Path</span>
                                <span class="text-xs text-slate-650 font-mono block truncate"><?php echo $img_val; ?></span>
                                <span class="text-[10px] text-slate-400 font-medium block mt-1">Upload a new image below if you wish to overwrite this file.</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="file" name="image" class="w-full border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-[#00aff0]/10 file:text-[#00aff0] hover:file:bg-[#00aff0]/20 transition-all duration-300">
                    <p class="text-[10px] text-slate-400">Accepted formats: JPG, JPEG, PNG, WEBP, GIF. Max file size: 5MB.</p>
                </div>
                <!-- Form Buttons -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-2">
                    <a href="portfolio.php" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                        Cancel
                    </a>
                    <button type="submit" name="save_project" class="px-6 py-2 bg-[#00aff0] hover:bg-[#009ece] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        <?php echo $action === 'edit' ? 'Update Project' : 'Save Project'; ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if ($action === 'edit' && $edit_id > 0): ?>
            <!-- Inline Gallery Upload & Management Section -->
            <div class="mt-12 space-y-6">
                <div class="border-t border-slate-200 pt-8">
                    <h3 class="font-display font-bold text-2xl text-slate-900 tracking-tight">Project Blueprint & Gallery Photos</h3>
                    <p class="text-sm text-slate-500 mt-1">Upload and manage project photos across the entire timeline process directly from here.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Gallery upload form -->
                    <div class="lg:col-span-4 bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h4 class="font-display font-bold text-base text-slate-900">Upload Photos</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Add progress images to this project.</p>
                        </div>
                        
                        <form action="portfolio.php?action=edit&id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Image Files * (Multiple Supported)</label>
                                <input type="file" name="gallery_files[]" multiple required class="w-full border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs text-slate-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] file:mr-4 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-[9px] file:font-bold file:uppercase file:bg-[#00aff0]/10 file:text-[#00aff0] hover:file:bg-[#00aff0]/20 transition-all duration-300" accept="image/*">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Blueprint Label / Title (Single Upload Only)</label>
                                <input type="text" name="title" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none" placeholder="e.g. Foundation Pouring">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Description (Single Upload Only)</label>
                                <textarea name="desc_text" rows="3" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-3 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none resize-none" placeholder="Describe progress details..."></textarea>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Process Timeline Stage</label>
                                <select name="stage" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                                    <option value="Consultation">1. Consultation</option>
                                    <option value="Design & Planning">2. Design & Planning</option>
                                    <option value="Construction" selected>3. Construction</option>
                                    <option value="Handover">4. Handover</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Materiality</label>
                                <input type="text" name="materiality" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none" placeholder="e.g. Raw Laminated Timber">
                            </div>



                            <button type="submit" name="add_gallery_image" class="w-full py-3 bg-[#0f172a] hover:bg-[#00aff0] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                                Upload Photo Assets
                            </button>
                        </form>
                    </div>

                    <!-- Current gallery photos list -->
                    <div class="lg:col-span-8 space-y-4 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                        <h4 class="font-display font-bold text-lg text-slate-900 border-b border-slate-100 pb-3">Current Gallery Photos (<?php echo count($gallery_photos); ?>)</h4>
                        
                        <?php if (count($gallery_photos) > 0): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <?php foreach ($gallery_photos as $img): 
                                    $resolved_src = "../" . $img['src'];
                                    ?>
                                    <div class="border border-slate-200 rounded-2xl overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow duration-300 bg-slate-50/20">
                                        <div class="aspect-[16/10] bg-slate-950 overflow-hidden relative group">
                                            <img src="<?php echo $resolved_src; ?>" class="w-full h-full object-cover">
                                        </div>
                                        <div class="p-4 space-y-2 flex-grow flex flex-col justify-between">
                                            <div class="space-y-1">
                                                <h5 class="font-bold text-slate-800 text-xs truncate leading-snug">
                                                    <?php echo !empty($img['title']) ? htmlspecialchars($img['title']) : 'Untitled Detail'; ?>
                                                </h5>
                                                <p class="text-[11px] text-slate-500 leading-normal font-normal line-clamp-2">
                                                    <?php echo !empty($img['desc_text']) ? htmlspecialchars($img['desc_text']) : 'No description provided.'; ?>
                                                </p>
                                                <div class="flex flex-wrap gap-2 text-[9px] text-[#00aff0] font-semibold uppercase tracking-wider pt-1">
                                                    <?php if (!empty($img['stage'])): ?>
                                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">🛠️ <?php echo htmlspecialchars($img['stage']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($img['materiality'])): ?>
                                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">💎 <?php echo htmlspecialchars($img['materiality']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (intval($img['show_in_gallery'] ?? 1) === 1): ?>
                                                        <label class="inline-flex items-center gap-1 bg-green-50 text-green-600 px-1.5 py-0.5 rounded border border-green-200 cursor-pointer select-none transition-all duration-300">
                                                            <input type="checkbox" class="show-in-gallery-toggle w-3 h-3 text-green-600 border-green-300 rounded focus:ring-green-500 cursor-pointer" data-img-id="<?php echo $img['id']; ?>" checked>
                                                            <span class="badge-text font-bold">🌐 Public Gallery</span>
                                                        </label>
                                                    <?php else: ?>
                                                        <label class="inline-flex items-center gap-1 bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded border border-amber-200 cursor-pointer select-none transition-all duration-300">
                                                            <input type="checkbox" class="show-in-gallery-toggle w-3 h-3 text-amber-600 border-amber-300 rounded focus:ring-amber-500 cursor-pointer" data-img-id="<?php echo $img['id']; ?>">
                                                            <span class="badge-text font-bold">🔒 Project Only</span>
                                                        </label>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-2">
                                                <span class="text-[9px] text-slate-400 font-mono select-all truncate max-w-[120px]"><?php echo $img['src']; ?></span>
                                                <div class="flex gap-2">
                                                    <a href="portfolio.php?action=gallery&id=<?php echo $edit_id; ?>&edit_img_id=<?php echo $img['id']; ?>" class="text-[10px] font-bold text-[#00aff0] hover:underline uppercase tracking-wider">
                                                        Edit
                                                    </a>
                                                    <span class="text-slate-350">|</span>
                                                    <a href="portfolio.php?action=edit&id=<?php echo $edit_id; ?>&subaction=delete&img_id=<?php echo $img['id']; ?>" onclick="return confirm('Are you sure you want to delete this blueprint photo from slide?')" class="text-[10px] font-bold text-red-500 hover:text-red-700 hover:underline uppercase tracking-wider">
                                                        Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="py-16 text-center text-slate-400 font-normal">
                                <span class="text-3xl block mb-2">📸</span>
                                No gallery photos added to this project's timeline yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- GALLERY VIEW MODE -->
    <?php if ($action === 'gallery' && $edit_project): ?>
        <div class="space-y-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="text-xs text-[#00aff0] tracking-wider uppercase font-bold block">PROJECT BLUEPRINT STORAGE</span>
                    <h1 class="font-display font-bold text-2xl text-slate-900 tracking-tight mt-0.5">
                        Manage Gallery: <span class="text-[#0f172a] underline decoration-[#00aff0] decoration-2"><?php echo htmlspecialchars($edit_project['title']); ?></span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">Add, label, or remove blueprints and details for the project slideshow.</p>
                </div>
                <a href="portfolio.php" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                    &larr; Back to Portfolio list
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- LEFT COLUMN: Upload/Edit Form -->
                <?php if (isset($edit_image_data) && $edit_image_data): ?>
                    <div class="lg:col-span-4 bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-display font-bold text-base text-slate-900">Edit Blueprint Details</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Modify the metadata details for this blueprint photo.</p>
                        </div>

                        <form action="portfolio.php?action=gallery&id=<?php echo $edit_id; ?>" method="POST" class="p-6 space-y-5">
                            <input type="hidden" name="img_id" value="<?php echo $edit_image_data['id']; ?>">

                            <!-- Preview -->
                            <div class="space-y-1.5">
                                <span class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Preview</span>
                                <div class="w-full aspect-[16/10] rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                                    <img src="../<?php echo htmlspecialchars($edit_image_data['src']); ?>" class="w-full h-full object-cover">
                                </div>
                            </div>

                            <!-- Title -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Blueprint Label / Title</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($edit_image_data['title'] ?? ''); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Master Wing Atrium">
                            </div>

                            <!-- Description -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Blueprint Description</label>
                                <textarea name="desc_text" rows="3" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-3 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none" placeholder="Describe layout details..."><?php echo htmlspecialchars($edit_image_data['desc_text'] ?? ''); ?></textarea>
                            </div>

                            <!-- Stage -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Project Stage</label>
                                <select name="stage" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                                    <option value="Consultation" <?php echo ($edit_image_data['stage'] ?? '') === 'Consultation' ? 'selected' : ''; ?>>1. Consultation</option>
                                    <option value="Design & Planning" <?php echo ($edit_image_data['stage'] ?? '') === 'Design & Planning' ? 'selected' : ''; ?>>2. Design & Planning</option>
                                    <option value="Construction" <?php echo (($edit_image_data['stage'] ?? '') === 'Construction' || empty($edit_image_data['stage'])) ? 'selected' : ''; ?>>3. Construction</option>
                                    <option value="Handover" <?php echo ($edit_image_data['stage'] ?? '') === 'Handover' ? 'selected' : ''; ?>>4. Handover</option>
                                </select>
                            </div>

                            <!-- Materiality -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Materiality</label>
                                <input type="text" name="materiality" value="<?php echo htmlspecialchars($edit_image_data['materiality'] ?? ''); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Premium Curated">
                            </div>

                            <div class="flex items-center gap-2 py-1">
                                <input type="checkbox" id="show_in_gallery_edit_detail" name="show_in_gallery" value="1" <?php echo (intval($edit_image_data['show_in_gallery'] ?? 1) === 1) ? 'checked' : ''; ?> class="w-4.5 h-4.5 text-[#00aff0] border-slate-350 rounded focus:ring-[#00aff0] cursor-pointer">
                                <label for="show_in_gallery_edit_detail" class="text-xs text-slate-700 font-bold cursor-pointer select-none">Show in Main Public Gallery</label>
                            </div>

                            <!-- Buttons -->
                            <div class="pt-2 flex gap-2">
                                <a href="portfolio.php?action=gallery&id=<?php echo $edit_id; ?>" class="w-1/2 text-center py-3 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                                    Cancel
                                </a>
                                <button type="submit" name="edit_gallery_image" class="w-1/2 py-3 bg-[#00aff0] hover:bg-[#009ece] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="lg:col-span-4 bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-display font-bold text-base text-slate-900">Upload Blueprint Photos</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Add one or multiple slides to this project's gallery.</p>
                        </div>

                        <form action="portfolio.php?action=gallery&id=<?php echo $edit_id; ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                            <!-- File Upload -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Image Files * (Multiple Supported)</label>
                                <input type="file" name="gallery_files[]" multiple required class="w-full border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs text-slate-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] file:mr-4 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-[9px] file:font-bold file:uppercase file:bg-[#00aff0]/10 file:text-[#00aff0] hover:file:bg-[#00aff0]/20 transition-all duration-300" accept="image/*">
                            </div>

                            <!-- Title -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Blueprint Label / Title (Single Upload Only)</label>
                                <input type="text" name="title" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Master Wing Atrium">
                                <p class="text-[9px] text-slate-400">If multiple files are selected, titles will be auto-generated from file names.</p>
                            </div>

                            <!-- Description -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Blueprint Description (Single Upload Only)</label>
                                <textarea name="desc_text" rows="3" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-3 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none" placeholder="Describe layout details, steel beams, passive thermal filters, etc..."></textarea>
                            </div>

                            <!-- Stage -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Project Stage (Single Upload Only)</label>
                                <select name="stage" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                                    <option value="Consultation">1. Consultation</option>
                                    <option value="Design & Planning">2. Design & Planning</option>
                                    <option value="Construction" selected>3. Construction</option>
                                    <option value="Handover">4. Handover</option>
                                </select>
                            </div>

                            <!-- Materiality -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Materiality (Single Upload Only)</label>
                                <input type="text" name="materiality" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="e.g. Premium Curated">
                            </div>



                            <!-- Submit -->
                            <div class="pt-2">
                                <button type="submit" name="add_gallery_image" class="w-full py-3 bg-[#0f172a] hover:bg-[#00aff0] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                                    Upload Assets
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- RIGHT COLUMN: Grid of Current Blueprints -->
                <div class="lg:col-span-8 space-y-4 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <h3 class="font-display font-bold text-lg text-slate-900 border-b border-slate-100 pb-3">Current Blueprint Gallery (<?php echo count($gallery_photos); ?>)</h3>

                    <?php if (count($gallery_photos) > 0): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($gallery_photos as $img): 
                                $resolved_src = "../" . $img['src'];
                                ?>
                                <div class="border border-slate-200 rounded-2xl overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow duration-300 bg-slate-50/20">
                                    <!-- Image Preview -->
                                    <div class="aspect-[16/10] bg-slate-950 overflow-hidden relative group">
                                        <img src="<?php echo $resolved_src; ?>" alt="<?php echo htmlspecialchars($img['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                            <span class="text-[9px] font-bold text-white uppercase tracking-wider py-1 px-3 border border-white/20 rounded-full backdrop-blur-sm">Slide Active</span>
                                        </div>
                                    </div>

                                    <!-- Image Caption Info -->
                                    <div class="p-4 space-y-2 flex-grow flex flex-col justify-between">
                                        <div class="space-y-1">
                                            <h4 class="font-bold text-slate-800 text-xs truncate leading-snug">
                                                <?php echo !empty($img['title']) ? htmlspecialchars($img['title']) : 'Untitled Detail'; ?>
                                            </h4>
                                            <p class="text-[11px] text-slate-500 leading-normal font-normal line-clamp-2">
                                                <?php echo !empty($img['desc_text']) ? htmlspecialchars($img['desc_text']) : 'No description provided.'; ?>
                                            </p>
                                            <div class="flex flex-wrap gap-2 text-[9px] text-[#00aff0] font-semibold uppercase tracking-wider pt-1">
                                                <?php if (!empty($img['stage'])): ?>
                                                    <span class="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">🛠️ <?php echo htmlspecialchars($img['stage']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($img['materiality'])): ?>
                                                    <span class="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">💎 <?php echo htmlspecialchars($img['materiality']); ?></span>
                                                <?php endif; ?>
                                                <?php if (intval($img['show_in_gallery'] ?? 1) === 1): ?>
                                                    <label class="inline-flex items-center gap-1 bg-green-50 text-green-600 px-1.5 py-0.5 rounded border border-green-200 cursor-pointer select-none transition-all duration-300">
                                                        <input type="checkbox" class="show-in-gallery-toggle w-3 h-3 text-green-600 border-green-300 rounded focus:ring-green-500 cursor-pointer" data-img-id="<?php echo $img['id']; ?>" checked>
                                                        <span class="badge-text font-bold">🌐 Public Gallery</span>
                                                    </label>
                                                <?php else: ?>
                                                    <label class="inline-flex items-center gap-1 bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded border border-amber-200 cursor-pointer select-none transition-all duration-300">
                                                        <input type="checkbox" class="show-in-gallery-toggle w-3 h-3 text-amber-600 border-amber-300 rounded focus:ring-amber-500 cursor-pointer" data-img-id="<?php echo $img['id']; ?>">
                                                        <span class="badge-text font-bold">🔒 Project Only</span>
                                                    </label>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-2">
                                            <span class="text-[9px] text-slate-400 font-mono select-all truncate max-w-[120px]"><?php echo $img['src']; ?></span>
                                            <div class="flex gap-2">
                                                <a href="portfolio.php?action=gallery&id=<?php echo $edit_id; ?>&edit_img_id=<?php echo $img['id']; ?>" class="text-[10px] font-bold text-[#00aff0] hover:text-[#009ece] hover:underline uppercase tracking-wider">
                                                    Edit
                                                </a>
                                                <span class="text-slate-350">|</span>
                                                <a href="portfolio.php?action=gallery&id=<?php echo $edit_id; ?>&subaction=delete&img_id=<?php echo $img['id']; ?>" onclick="return confirm('Are you sure you want to delete this blueprint photo from slide?')" class="text-[10px] font-bold text-red-500 hover:text-red-700 hover:underline uppercase tracking-wider">
                                                    Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="py-16 text-center text-slate-400 font-normal">
                            <span class="text-3xl block mb-2">🖼️</span>
                            No blueprint slides added to this project's gallery yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.show-in-gallery-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const imgId = this.getAttribute('data-img-id');
            const show = this.checked ? 1 : 0;
            const label = this.closest('label');
            const badgeText = label.querySelector('.badge-text');

            this.disabled = true;

            const params = new URLSearchParams();
            params.append('img_id', imgId);
            params.append('show_in_gallery', show);

            fetch('portfolio.php?action=toggle_gallery_visibility', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    if (show === 1) {
                        label.className = "inline-flex items-center gap-1 bg-green-50 text-green-600 px-1.5 py-0.5 rounded border border-green-200 cursor-pointer select-none transition-all duration-300";
                        if (badgeText) badgeText.innerText = "🌐 Public Gallery";
                    } else {
                        label.className = "inline-flex items-center gap-1 bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded border border-amber-200 cursor-pointer select-none transition-all duration-300";
                        if (badgeText) badgeText.innerText = "🔒 Project Only";
                    }
                } else {
                    this.checked = !this.checked;
                    alert('Error: ' + (data.error || 'Failed to update visibility.'));
                }
            })
            .catch(error => {
                this.disabled = false;
                this.checked = !this.checked;
                alert('Connection error. Failed to save visibility state.');
            });
        });
    });
});
</script>

<?php
require_once 'admin_footer.php';
?>

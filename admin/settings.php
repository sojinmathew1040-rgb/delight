<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN SETTINGS
// ==========================================
require_once 'admin_header.php';

$success_msg = "";
$error_msg = "";

// 1. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if it's the settings update or password update
    if (isset($_POST['update_settings'])) {
        $keys = [
            'site_title', 'established_year', 'coordinates', 'contact_email', 
            'contact_phone', 'business_hours', 'office_address', 'google_maps_iframe',
            'hero_subtitle', 'philosophy_text_1', 'philosophy_text_2', 'about_hero_desc'
        ];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
            
            foreach ($keys as $key) {
                $val = trim($_POST[$key] ?? '');
                $stmt->execute([$key, $val, $val]);
            }
            
            $pdo->commit();
            $success_msg = "Global site settings successfully saved.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Failed to update global settings: " . $e->getMessage();
        }
    } 
    elseif (isset($_POST['change_password'])) {
        $username = trim($_POST['admin_username'] ?? '');
        $new_pass = trim($_POST['new_password'] ?? '');
        $conf_pass = trim($_POST['confirm_password'] ?? '');
        $full_name = trim($_POST['admin_fullname'] ?? '');

        if (!empty($new_pass) || !empty($full_name)) {
            if (!empty($new_pass) && $new_pass !== $conf_pass) {
                $error_msg = "New password and confirmation password do not match.";
            } else {
                try {
                    $admin_id = $_SESSION['admin_id'];
                    if (!empty($new_pass)) {
                        // Change both username/fullname and password
                        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ?, full_name = ? WHERE id = ?");
                        $stmt->execute([$username, $hashed_pass, $full_name, $admin_id]);
                    } else {
                        // Change username/fullname only
                        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, full_name = ? WHERE id = ?");
                        $stmt->execute([$username, $full_name, $admin_id]);
                    }

                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_fullname'] = $full_name;

                    $success_msg = "Administrator credentials successfully updated.";
                } catch (PDOException $e) {
                    $error_msg = "Failed to update credentials: " . $e->getMessage();
                }
            }
        } else {
            $error_msg = "Please fill in username or password fields.";
        }
    }
}

// 2. Fetch current settings keys
$site_title = get_setting('site_title');
$established_year = get_setting('established_year');
$coordinates = get_setting('coordinates');
$contact_email = get_setting('contact_email');
$contact_phone = get_setting('contact_phone');
$business_hours = get_setting('business_hours');
$office_address = get_setting('office_address');
$google_maps_iframe = get_setting('google_maps_iframe');
$hero_subtitle = get_setting('hero_subtitle');
$philosophy_text_1 = get_setting('philosophy_text_1');
$philosophy_text_2 = get_setting('philosophy_text_2');
$about_hero_desc = get_setting('about_hero_desc');

// Fetch current admin user info
try {
    $stmt = $pdo->prepare("SELECT username, full_name FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin_data = $stmt->fetch();
} catch (PDOException $e) {
    $admin_data = ['username' => $_SESSION['admin_username'], 'full_name' => $_SESSION['admin_fullname']];
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">System & Site Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Configure global elements, branding copy, location maps, and manage secure credentials.</p>
    </div>

    <!-- Alert Banners -->
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left 8 Columns: Main Website Content settings -->
        <div class="lg:col-span-8">
            <form action="settings.php" method="POST" class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-display font-bold text-lg text-slate-900">Website Configuration</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Control typography copy, corporate numbers, and structural metrics.</p>
                </div>

                <div class="p-6 md:p-8 space-y-8">
                    <!-- General Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Site Title -->
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">SEO Site Title</label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars($site_title); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                        </div>

                        <!-- Est Year -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Established Year</label>
                            <input type="text" name="established_year" value="<?php echo htmlspecialchars($established_year); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                        </div>
                    </div>

                    <!-- Coordinates & Hero -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Coordinates -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">HQ Coordinates</label>
                            <input type="text" name="coordinates" value="<?php echo htmlspecialchars($coordinates); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                        </div>

                        <!-- Hero Subtitle -->
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Homepage Hero Subtitle</label>
                            <input type="text" name="hero_subtitle" value="<?php echo htmlspecialchars($hero_subtitle); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                        </div>
                    </div>

                    <!-- Philosophy texts -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Conceptual Philosophy Copy</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Philosophy Paragraph 1</label>
                                <textarea name="philosophy_text_1" rows="4" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-4 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none"><?php echo htmlspecialchars($philosophy_text_1); ?></textarea>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Philosophy Paragraph 2</label>
                                <textarea name="philosophy_text_2" rows="4" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-4 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none"><?php echo htmlspecialchars($philosophy_text_2); ?></textarea>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">About Page Legacy Intro Description</label>
                            <textarea name="about_hero_desc" rows="3" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-4 text-slate-800 text-xs font-normal leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-none"><?php echo htmlspecialchars($about_hero_desc); ?></textarea>
                        </div>
                    </div>

                    <!-- Contacts & Address -->
                    <div class="space-y-6 pt-6 border-t border-slate-100">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Contact & Location Mapping</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Contact Email</label>
                                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Contact Phone</label>
                                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Business Hours</label>
                                <input type="text" name="business_hours" value="<?php echo htmlspecialchars($business_hours); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Office Address (Kerala HQ)</label>
                            <input type="text" name="office_address" value="<?php echo htmlspecialchars($office_address); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Google Maps Embed URL (src attribute only)</label>
                            <textarea name="google_maps_iframe" rows="2" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] p-3.5 text-slate-800 text-xs font-normal rounded-xl focus:outline-none transition-all duration-300 resize-none"><?php echo htmlspecialchars($google_maps_iframe); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5 border-t border-slate-100 flex justify-end bg-slate-50/20">
                    <button type="submit" name="update_settings" class="px-6 py-3 bg-[#0f172a] hover:bg-[#00aff0] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>

        <!-- Right 4 Columns: Admin Security configuration -->
        <div class="lg:col-span-4 space-y-6">
            <form action="settings.php" method="POST" class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-display font-bold text-base text-slate-900">Admin Credentials</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Manage control panel security credentials and identifiers.</p>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Name -->
                    <div class="space-y-1">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Display Name</label>
                        <input type="text" name="admin_fullname" value="<?php echo htmlspecialchars($admin_data['full_name']); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                    </div>

                    <!-- Username -->
                    <div class="space-y-1">
                        <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Admin Username</label>
                        <input type="text" name="admin_username" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-800 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                    </div>

                    <div class="border-t border-slate-150 pt-4 mt-4 space-y-4">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Update Secure Password</h4>
                        <p class="text-[10px] text-slate-500 leading-normal">Leave blank if you do not wish to change the password.</p>
                        
                        <!-- Password -->
                        <div class="space-y-1">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">New Password</label>
                            <input type="password" name="new_password" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-850 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-1">
                            <label class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Confirm Password</label>
                            <input type="password" name="confirm_password" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 text-slate-850 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end bg-slate-50/20">
                    <button type="submit" name="change_password" class="w-full py-2.5 bg-slate-900 hover:bg-[#00aff0] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                        Update Credentials
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'admin_footer.php';
?>

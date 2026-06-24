<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN INQUIRIES
// ==========================================
require_once 'admin_header.php';

$message_info = "";
$error_info = "";

// 1. Process Actions (Status Updates or Deletion)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
            $stmt->execute([$id]);
            $message_info = "Inquiry successfully removed from database.";
        } catch (PDOException $e) {
            $error_info = "Failed to delete inquiry: " . $e->getMessage();
        }
    } elseif (in_array($action, ['read', 'replied', 'unread'])) {
        try {
            $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
            $stmt->execute([$action, $id]);
            $message_info = "Inquiry status updated to " . strtoupper($action) . ".";
        } catch (PDOException $e) {
            $error_info = "Failed to update status: " . $e->getMessage();
        }
    }
}

// 2. Fetch Selected Inquiry for Detail View
$selected_inq = null;
if (isset($_GET['id']) && (!isset($_GET['action']) || $_GET['action'] !== 'delete')) {
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
        $stmt->execute([$id]);
        $selected_inq = $stmt->fetch();
        
        // Auto-mark as read when opened
        if ($selected_inq && $selected_inq['status'] === 'unread') {
            $update = $pdo->prepare("UPDATE inquiries SET status = 'read' WHERE id = ?");
            $update->execute([$id]);
            $selected_inq['status'] = 'read';
        }
    } catch (PDOException $e) {
        $error_info = "Enquiry retrieval failed: " . $e->getMessage();
    }
}

// Fetch all categories for filter dropdown
try {
    $stmt_cats = $pdo->query("SELECT name FROM portfolio_categories ORDER BY name ASC");
    $db_categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $db_categories = [];
}

// 3. Build Filter/Search Parameters
$search = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$filter_category = trim($_GET['category'] ?? '');

$query = "SELECT * FROM inquiries WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR whatsapp LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

if ($filter_status !== '') {
    $query .= " AND status = ?";
    $params[] = $filter_status;
}

if ($filter_category !== '') {
    $query .= " AND category = ?";
    $params[] = $filter_category;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $inquiries = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_info = "Failed to search database: " . $e->getMessage();
    $inquiries = [];
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Customer Inquiries</h1>
            <p class="text-sm text-slate-500 mt-1">Review, track, and manage client commission inquiries submitted through the website.</p>
        </div>
        <div>
            <a href="print_inquiries.php?status=<?php echo urlencode($filter_status); ?>&category=<?php echo urlencode($filter_category); ?>&search=<?php echo urlencode($search); ?>" target="_blank" class="px-5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm flex items-center gap-2">
                <span>🖨️ Export PDF / Print</span>
            </a>
        </div>
    </div>

    <!-- Alert Banners -->
    <?php if (!empty($message_info)): ?>
        <div class="p-4 bg-green-50 border border-green-200 text-green-600 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span><?php echo htmlspecialchars($message_info); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_info)): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span><?php echo htmlspecialchars($error_info); ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- LEFT PANEL: Search, Filters & Inquiries List -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Search and Filter Panel -->
            <form action="inquiries.php" method="GET" class="bg-white border border-slate-200 p-5 rounded-3xl shadow-sm space-y-4">
                <div class="relative">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-4 py-2.5 pl-10 text-slate-800 placeholder-slate-400 text-xs font-semibold rounded-xl focus:outline-none transition-all duration-300" placeholder="Search by name, email, or content...">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        🔍
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Status Filter -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Status</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                            <option value="">All Statuses</option>
                            <option value="unread" <?php echo $filter_status === 'unread' ? 'selected' : ''; ?>>Unread</option>
                            <option value="read" <?php echo $filter_status === 'read' ? 'selected' : ''; ?>>Read</option>
                            <option value="replied" <?php echo $filter_status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Sector Interest</label>
                        <select name="category" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-2.5 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0]">
                            <option value="">All Sectors</option>
                            <?php if (empty($db_categories)): ?>
                                <option value="Luxury Residential" <?php echo $filter_category === 'Luxury Residential' ? 'selected' : ''; ?>>Luxury Residential</option>
                                <option value="Commercial Frameworks" <?php echo $filter_category === 'Commercial Frameworks' ? 'selected' : ''; ?>>Commercial Frameworks</option>
                                <option value="Sustainable Fits" <?php echo $filter_category === 'Sustainable Fits' ? 'selected' : ''; ?>>Sustainable Fits</option>
                            <?php else: ?>
                                <?php foreach ($db_categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filter_category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="Other" <?php echo $filter_category === 'Other' ? 'selected' : ''; ?>>Other / Custom</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="inquiries.php" class="px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                        Clear
                    </a>
                    <button type="submit" class="px-5 py-2 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                        Filter
                    </button>
                </div>
            </form>

            <!-- Inquiries List Container -->
            <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
                <div class="divide-y divide-slate-100">
                    <?php if (count($inquiries) > 0): ?>
                        <?php foreach ($inquiries as $inq): 
                            $status_color = "bg-red-50 text-red-600 border border-red-100";
                            if ($inq['status'] === 'read') {
                                $status_color = "bg-green-50 text-green-600 border border-green-100";
                            } elseif ($inq['status'] === 'replied') {
                                $status_color = "bg-blue-50 text-blue-600 border border-blue-100";
                            }
                            
                            $is_active_row = ($selected_inq && $selected_inq['id'] == $inq['id']) ? 'bg-slate-50/80 border-l-4 border-l-[#00aff0]' : '';
                            $unread_bold = ($inq['status'] === 'unread') ? 'font-bold text-slate-900' : 'text-slate-700';
                            ?>
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50/50 transition-all duration-200 <?php echo $is_active_row; ?>">
                                <a href="inquiries.php?id=<?php echo $inq['id']; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&category=<?php echo urlencode($filter_category); ?>" class="flex-1 min-w-0 pr-4">
                                    <div class="flex items-center gap-2.5">
                                        <h4 class="text-sm <?php echo $unread_bold; ?> truncate"><?php echo htmlspecialchars($inq['name']); ?></h4>
                                        <span id="list-status-badge-<?php echo $inq['id']; ?>" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?php echo $status_color; ?>">
                                            <?php echo $inq['status']; ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 truncate mt-1"><?php echo htmlspecialchars($inq['message']); ?></p>
                                    <div class="flex items-center gap-4 mt-2 text-[10px] text-slate-400 font-semibold uppercase">
                                        <span><?php echo htmlspecialchars($inq['category'] ?? 'General'); ?></span>
                                        <span>•</span>
                                        <span><?php echo date('M d, Y', strtotime($inq['created_at'])); ?></span>
                                    </div>
                                </a>
                                <div class="flex-shrink-0 flex items-center gap-2">
                                    <a href="inquiries.php?id=<?php echo $inq['id']; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&category=<?php echo urlencode($filter_category); ?>" class="p-2 border border-slate-200 hover:border-[#00aff0] hover:text-[#00aff0] rounded-xl transition-all duration-300 bg-white" title="Open Details">
                                        👁️
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-12 text-center text-slate-400 font-normal">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            No matching customer inquiries found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Inquiry Details -->
        <div class="lg:col-span-5">
            <?php if ($selected_inq): ?>
                <!-- Detail View Card -->
                <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden sticky top-6">
                    <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Inquiry details</span>
                            <span class="text-[10px] text-slate-400 font-semibold uppercase mt-0.5 block">ID: #<?php echo $selected_inq['id']; ?> &bull; Received <?php echo date('M d, Y h:i A', strtotime($selected_inq['created_at'])); ?></span>
                        </div>
                        
                        <div class="flex items-center gap-1.5">
                            <!-- Delete action -->
                            <a href="inquiries.php?action=delete&id=<?php echo $selected_inq['id']; ?>" onclick="return confirm('Are you sure you want to delete this customer inquiry? This cannot be undone.')" class="p-2 border border-red-150 hover:bg-red-50 text-red-500 rounded-xl transition-all duration-300 bg-white" title="Delete Inquiry">
                                🗑️
                            </a>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <!-- Client Contact Details -->
                        <div class="space-y-4">
                            <div class="border-l-2 border-[#00aff0] pl-4 space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Client Name</span>
                                <h3 class="font-display font-bold text-base text-slate-900 leading-snug"><?php echo htmlspecialchars($selected_inq['name']); ?></h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                                <div class="space-y-1">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Secure Email</span>
                                    <a href="mailto:<?php echo htmlspecialchars($selected_inq['email']); ?>" class="text-xs text-[#00aff0] hover:underline font-semibold block truncate" title="<?php echo htmlspecialchars($selected_inq['email']); ?>"><?php echo htmlspecialchars($selected_inq['email']); ?></a>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Secure Phone</span>
                                    <span class="text-xs text-slate-800 font-semibold block truncate"><?php echo htmlspecialchars($selected_inq['phone'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">WhatsApp</span>
                                    <?php if (!empty($selected_inq['whatsapp'])): 
                                        $clean_wa = preg_replace('/[^0-9]/', '', $selected_inq['whatsapp']);
                                    ?>
                                        <a href="https://wa.me/<?php echo $clean_wa; ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-[#25d366] hover:underline font-semibold flex items-center gap-1 block truncate" title="Chat on WhatsApp">
                                            <span class="text-sm">💬</span> <span class="truncate"><?php echo htmlspecialchars($selected_inq['whatsapp']); ?></span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 block font-semibold">N/A</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Sector of Interest</span>
                                    <span class="font-semibold text-slate-700 block mt-0.5"><?php echo htmlspecialchars($selected_inq['category'] ?? 'General'); ?></span>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Status Badge</span>
                                    <span id="detail-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border mt-0.5 inline-block
                                        <?php 
                                            if ($selected_inq['status'] === 'unread') echo 'bg-red-50 text-red-600 border-red-100';
                                            elseif ($selected_inq['status'] === 'read') echo 'bg-green-50 text-green-600 border-green-100';
                                            else echo 'bg-blue-50 text-blue-600 border-blue-100';
                                        ?>">
                                        <?php echo $selected_inq['status']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Content -->
                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Project Goals & Description</span>
                            <div class="p-4 bg-slate-50 border border-slate-200/60 rounded-2xl text-xs text-slate-700 leading-relaxed font-normal whitespace-pre-wrap">
                                <?php echo htmlspecialchars($selected_inq['message']); ?>
                            </div>
                        </div>

                        <!-- WhatsApp Communication Hub -->
                        <div class="space-y-3 pt-4 border-t border-slate-100 bg-[#25d366]/5 p-4 rounded-2xl border border-[#25d366]/10">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-bold text-[#128c7e] uppercase tracking-widest block flex items-center gap-1">
                                    <span>💬</span> WhatsApp Communication Hub
                                </span>
                                <div class="flex items-center gap-2">
                                    <img src="../asset/images/logo.png" alt="Delight Builders" class="h-6 w-auto object-contain border border-slate-200 rounded p-0.5 bg-white" title="Delight Builders Official Logo">
                                    <?php if (!empty($selected_inq['whatsapp'])): ?>
                                        <span class="text-[9px] bg-[#25d366]/20 text-[#128c7e] px-2 py-0.5 rounded font-bold uppercase">Ready to dispatch</span>
                                    <?php else: ?>
                                        <span class="text-[9px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-bold uppercase">No WhatsApp number</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($selected_inq['whatsapp'])): 
                                // Resolve absolute logo URL
                                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                                $script_dir = str_replace('admin/inquiries.php', '', $_SERVER['SCRIPT_NAME']);
                                $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $script_dir;
                                $full_logo_url = $base_url . "asset/images/logo.png";
                                
                                // Clean phone number for link
                                $clean_wa_number = preg_replace('/[^0-9]/', '', $selected_inq['whatsapp']);
                                
                                // Message Template
                                $salutation = "Hello " . htmlspecialchars($selected_inq['name']) . ",";
                                $intro = "Thank you for reaching out to Delight Builders. We have received your inquiry regarding the '" . htmlspecialchars($selected_inq['category'] ?? 'General') . "' sector.";
                                $body = "Our architectural and design team is reviewing your project details. We will be in touch shortly to schedule a consultation.";
                                $signoff = "Best regards,\nDelight Builders Team\n" . $full_logo_url;
                                
                                $whatsapp_text = "$salutation\n\n$intro\n\n$body\n\n$signoff";
                            ?>
                                <div class="space-y-2">
                                    <label for="whatsapp-custom-msg" class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Edit Response Message</label>
                                    <textarea id="whatsapp-custom-msg" rows="6" 
                                        class="w-full bg-white border border-slate-200 focus:ring-2 focus:ring-[#25d366] focus:border-[#25d366] p-3 text-xs text-slate-700 font-sans leading-relaxed rounded-xl focus:outline-none transition-all duration-300 resize-y"
                                    ><?php echo htmlspecialchars($whatsapp_text); ?></textarea>
                                    <div class="flex flex-wrap justify-between items-center pt-1 gap-2">
                                        <p class="text-[10px] text-slate-400">Copy the logo first, then click Send and paste (Ctrl+V) in WhatsApp.</p>
                                        <div class="flex items-center gap-2">
                                            <button onclick="copyLogoToClipboard(this)" 
                                                class="inline-flex items-center gap-1.5 bg-white hover:bg-slate-50 text-slate-700 px-3.5 py-2 text-[10px] font-bold uppercase tracking-wider rounded-xl transition-all duration-300 border border-slate-200 cursor-pointer">
                                                <span id="copy-btn-text">📋 Copy Logo Image</span>
                                            </button>
                                            <button onclick="sendWhatsAppMessage()" 
                                                class="inline-flex items-center gap-1.5 bg-[#25d366] text-white hover:bg-[#20ba5a] px-3.5 py-2 text-[10px] font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer">
                                                <span>Send via WhatsApp</span>
                                                <span>🚀</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                function sendWhatsAppMessage() {
                                    const message = document.getElementById('whatsapp-custom-msg').value;
                                    const encodedMsg = encodeURIComponent(message);
                                    const phone = "<?php echo $clean_wa_number; ?>";
                                    const id = "<?php echo $selected_inq['id']; ?>";
                                    const url = `https://wa.me/${phone}?text=${encodedMsg}`;
                                    
                                    // Open WhatsApp window/tab
                                    window.open(url, '_blank');
                                    
                                    // Auto-mark the inquiry as replied in the database
                                    fetch(`inquiries.php?action=replied&id=${id}`)
                                        .then(response => {
                                            if (response.ok) {
                                                // Update dynamic status badges
                                                const detailBadge = document.getElementById('detail-status-badge');
                                                if (detailBadge) {
                                                    detailBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold uppercase border mt-0.5 inline-block bg-blue-50 text-blue-600 border-blue-100";
                                                    detailBadge.innerHTML = "replied";
                                                }
                                                const listBadge = document.getElementById(`list-status-badge-${id}`);
                                                if (listBadge) {
                                                    listBadge.className = "px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-blue-50 text-blue-600 border-blue-100";
                                                    listBadge.innerHTML = "replied";
                                                }
                                            }
                                        })
                                        .catch(err => console.error('Failed to mark inquiry as replied:', err));
                                }

                                async function copyLogoToClipboard(btn) {
                                    const btnText = document.getElementById('copy-btn-text');
                                    const originalText = btnText.innerHTML;
                                    btnText.innerHTML = "⏳ Copying...";
                                    btn.disabled = true;
                                    
                                    try {
                                        // Path to the logo image
                                        const logoUrl = '../asset/images/logo.png';
                                        const response = await fetch(logoUrl);
                                        if (!response.ok) throw new Error('Failed to fetch image file.');
                                        const blob = await response.blob();
                                        
                                        // Write the image to the clipboard
                                        const item = new ClipboardItem({ [blob.type]: blob });
                                        await navigator.clipboard.write([item]);
                                        
                                        btnText.innerHTML = "✅ Logo Copied!";
                                        btn.classList.add('bg-green-50', 'border-green-300', 'text-green-700');
                                        
                                        setTimeout(() => {
                                            btnText.innerHTML = originalText;
                                            btn.classList.remove('bg-green-50', 'border-green-300', 'text-green-700');
                                            btn.disabled = false;
                                        }, 3000);
                                    } catch (err) {
                                        console.error('Clipboard copy failed:', err);
                                        btnText.innerHTML = "❌ Failed to Copy";
                                        setTimeout(() => {
                                            btnText.innerHTML = originalText;
                                            btn.disabled = false;
                                        }, 3000);
                                    }
                                }
                                </script>
                            <?php else: ?>
                                <p class="text-xs text-slate-500">To dispatch a notification, please verify that a valid WhatsApp number was provided in the inquiry submission.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Status Action Controls -->
                        <div class="pt-6 border-t border-slate-100 flex flex-wrap gap-2 justify-end">
                            <a href="inquiries.php?action=unread&id=<?php echo $selected_inq['id']; ?>" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-[10px] font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                                Mark Unread
                            </a>
                            <a href="inquiries.php?action=replied&id=<?php echo $selected_inq['id']; ?>" class="px-5 py-2 bg-slate-900 text-white hover:bg-slate-800 text-[10px] font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm">
                                Mark Replied
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty Placeholder -->
                <div class="bg-slate-50 border border-slate-200 border-dashed rounded-3xl p-8 text-center text-slate-400 font-normal">
                    <div class="text-3xl mb-3">📬</div>
                    <h4 class="font-display font-semibold text-sm text-slate-700">No inquiry selected</h4>
                    <p class="text-xs text-slate-500 mt-1">Select an inquiry from the left-hand column list to read client message specifications and take actions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'admin_footer.php';
?>

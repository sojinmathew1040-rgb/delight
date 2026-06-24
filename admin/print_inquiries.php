<?php
// ==========================================
// DELIGHT BUILDERS - PRINT/EXPORT INQUIRIES
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'db_connection.php';

// Fetch all categories for filter dropdown
try {
    $stmt_cats = $pdo->query("SELECT name FROM portfolio_categories ORDER BY name ASC");
    $db_categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $db_categories = [];
}

// Get filter inputs
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$category = trim($_GET['category'] ?? '');

// Build search query
$query = "SELECT * FROM inquiries WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR whatsapp LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

if ($status !== '') {
    $query .= " AND status = ?";
    $params[] = $status;
}

if ($category !== '') {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $inquiries = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Query Failure: " . $e->getMessage());
}

$logo_path = "../" . get_setting('logo_path', 'asset/images/logo.png');
?>
<!DOCTYPE html>
<html lang="en" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Inquiry Report - Delight Builders</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="../asset/css/admin.css">
</head>
<body class="font-sans text-slate-800 antialiased p-0 md:p-8">

    <!-- NO-PRINT FILTER CONTROLS BAR -->
    <div class="no-print max-w-6xl mx-auto mb-8 bg-white border border-slate-200 p-6 rounded-3xl shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h1 class="font-display font-bold text-xl text-slate-900 tracking-tight">Report Generator & Print Center</h1>
                <p class="text-xs text-slate-500 mt-0.5">Filter, preview, and print secure reports to PDF with dynamic branding layouts.</p>
            </div>
            <div class="flex gap-2">
                <a href="inquiries.php" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-300">
                    Back to Inquiries
                </a>
                <button onclick="window.print()" class="px-5 py-2 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm flex items-center gap-1.5">
                    <span>🖨️ Print / Save as PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Form -->
        <form action="" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <!-- Search field -->
            <div class="space-y-1">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Search Keywords</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                    class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] px-3 py-2 text-xs font-semibold rounded-xl focus:outline-none transition-all" 
                    placeholder="Search details...">
            </div>

            <!-- Status filter -->
            <div class="space-y-1">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Status State</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0]">
                    <option value="">All Inquiries</option>
                    <option value="unread" <?php echo $status === 'unread' ? 'selected' : ''; ?>>Unread</option>
                    <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>
            </div>

            <!-- Category filter -->
            <div class="space-y-1">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Sector Interest</label>
                <select name="category" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00aff0]">
                    <option value="">All Sectors</option>
                    <?php foreach ($db_categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                    <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>Other / Custom</option>
                </select>
            </div>

            <!-- Submit buttons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm">
                    Apply Filters
                </button>
                <a href="print_inquiries.php" class="px-4 py-2.5 border border-slate-200 text-slate-500 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- PRINT CONTAINER (THE DYNAMIC TEMPLATE) -->
    <div class="print-container max-w-6xl mx-auto bg-white border border-slate-200 p-8 md:p-12 shadow-sm rounded-3xl space-y-8">
        
        <!-- Branded Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 border-b-2 border-slate-800 pb-6">
            <div class="flex items-center gap-4">
                <img src="<?php echo $logo_path; ?>" alt="Delight Builders Logo" class="h-16 w-auto object-contain">
                <div>
                    <h2 class="font-display font-extrabold text-lg text-slate-900 tracking-wider">DELIGHT BUILDERS</h2>
                    <p class="text-[9px] font-bold tracking-widest text-[#64748b] uppercase">Premium Architectural Abstractions</p>
                </div>
            </div>
            <div class="text-left sm:text-right text-[10px] text-slate-500 space-y-1 font-semibold">
                <p>Website: www.delightbuilders.com</p>
                <p>Email: admin@delightbuilders.com</p>
                <p>Generated: <?php echo date('M d, Y h:i A'); ?></p>
            </div>
        </div>

        <!-- Document Title & Meta -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <div>
                <h3 class="font-display font-bold text-sm text-slate-800 tracking-wide uppercase">Customer Inquiry Summary Report</h3>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-[10px] text-slate-500 font-semibold uppercase">
                    <span>Scope: <strong><?php echo $status ? htmlspecialchars($status) : 'All Statuses'; ?></strong></span>
                    <span>•</span>
                    <span>Sector: <strong><?php echo $category ? htmlspecialchars($category) : 'All Sectors'; ?></strong></span>
                    <?php if ($search): ?>
                        <span>•</span>
                        <span>Query: <strong>"<?php echo htmlspecialchars($search); ?>"</strong></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[9px] text-slate-400 font-bold uppercase block tracking-wider">Total Records</span>
                <span class="font-display font-extrabold text-xl text-slate-800"><?php echo count($inquiries); ?> inquiries</span>
            </div>
        </div>

        <!-- Inquiry Records Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                        <th class="py-3 pr-4 w-28">Date</th>
                        <th class="py-3 pr-4 w-48">Client Details</th>
                        <th class="py-3 pr-4 w-36">Sector</th>
                        <th class="py-3 pr-4">Message / Scope</th>
                        <th class="py-3 w-20 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($inquiries) > 0): ?>
                        <?php foreach ($inquiries as $inq): 
                            $badge_style = 'bg-red-50 text-red-600 border border-red-100';
                            if ($inq['status'] === 'read') {
                                $badge_style = 'bg-green-50 text-green-600 border border-green-100';
                            } elseif ($inq['status'] === 'replied') {
                                $badge_style = 'bg-blue-50 text-blue-600 border border-blue-100';
                            }
                        ?>
                            <tr class="inquiry-card align-top">
                                <td class="py-4 pr-4 font-semibold text-slate-500 leading-normal">
                                    <?php echo date('M d, Y', strtotime($inq['created_at'])); ?><br>
                                    <span class="text-[9px] font-normal text-slate-400"><?php echo date('h:i A', strtotime($inq['created_at'])); ?></span>
                                </td>
                                <td class="py-4 pr-4 leading-relaxed font-semibold text-slate-900 space-y-1">
                                    <div class="text-sm font-bold"><?php echo htmlspecialchars($inq['name']); ?></div>
                                    <div class="text-[10px] text-[#00aff0] font-normal lowercase"><?php echo htmlspecialchars($inq['email']); ?></div>
                                    <div class="text-[10px] text-slate-500 font-normal">P: <?php echo htmlspecialchars($inq['phone'] ?? 'N/A'); ?></div>
                                    <?php if (!empty($inq['whatsapp'])): ?>
                                        <div class="text-[10px] text-[#25d366] font-normal">WA: <?php echo htmlspecialchars($inq['whatsapp']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 pr-4 font-bold text-slate-700 leading-normal">
                                    <?php echo htmlspecialchars($inq['category'] ?? 'General'); ?>
                                </td>
                                <td class="py-4 pr-4 text-slate-600 font-normal leading-relaxed whitespace-pre-wrap">
                                    <?php echo htmlspecialchars($inq['message']); ?>
                                </td>
                                <td class="py-4 text-right">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase inline-block <?php echo $badge_style; ?>">
                                        <?php echo $inq['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 font-normal">
                                No inquiries match the selected criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer Coordinates -->
        <div class="border-t border-slate-200 pt-6 text-center text-[10px] text-slate-400 font-semibold uppercase tracking-wider space-y-1">
            <p>© <?php echo date("Y"); ?> Delight Builders Inc. All report coordinates verified.</p>
            <p class="font-normal normal-case text-slate-400">Confidential. Internal administrative distribution only.</p>
        </div>
    </div>

</body>
</html>

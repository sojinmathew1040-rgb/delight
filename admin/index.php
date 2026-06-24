<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN DASHBOARD
// ==========================================
require_once 'admin_header.php';

// Fetch stats counts
try {
    // 1. Projects Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio");
    $total_projects = $stmt->fetchColumn();

    // 2. Gallery Blueprints Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_gallery");
    $total_blueprints = $stmt->fetchColumn();

    // 3. Unread Inquiries Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'unread'");
    $unread_inquiries = $stmt->fetchColumn();

    // 4. Team Members Count
    $stmt = $pdo->query("SELECT COUNT(*) FROM team_members");
    $total_team = $stmt->fetchColumn();

    // 5. Recent Inquiries (Limit 5)
    $stmt = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5");
    $recent_inquiries = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "<div class='p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl mb-6'>Error loading dashboard statistics: " . $e->getMessage() . "</div>";
    $total_projects = $total_blueprints = $unread_inquiries = $total_team = 0;
    $recent_inquiries = [];
}
?>

<div class="space-y-8">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight">Dashboard Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Welcome back, <strong class="text-slate-800"><?php echo htmlspecialchars($_SESSION['admin_fullname']); ?></strong>. Delight Builders administration metrics at a glance.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="portfolio.php?action=add" class="inline-flex items-center px-4 py-2.5 bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 shadow-sm hover:shadow">
                + Add Project
            </a>
            <a href="settings.php" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300">
                Site Settings
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Projects -->
        <div class="bg-white border border-slate-200/80 p-6 rounded-2xl flex items-center justify-between shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Projects</span>
                <span class="font-display font-black text-3xl text-slate-900 block"><?php echo $total_projects; ?></span>
                <span class="text-[10px] text-slate-400 font-medium">In portfolio archive</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
        </div>

        <!-- Card 2: Blueprints -->
        <div class="bg-white border border-slate-200/80 p-6 rounded-2xl flex items-center justify-between shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Blueprints</span>
                <span class="font-display font-black text-3xl text-slate-900 block"><?php echo $total_blueprints; ?></span>
                <span class="text-[10px] text-slate-400 font-medium">Blueprint gallery items</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Card 3: Unread Inquiries -->
        <div class="bg-white border border-slate-200/80 p-6 rounded-2xl flex items-center justify-between shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">New Inquiries</span>
                <span class="font-display font-black text-3xl <?php echo $unread_inquiries > 0 ? 'text-red-500' : 'text-slate-900'; ?> block">
                    <?php echo $unread_inquiries; ?>
                </span>
                <span class="text-[10px] text-slate-400 font-medium">Requires partner review</span>
            </div>
            <div class="w-12 h-12 rounded-2xl <?php echo $unread_inquiries > 0 ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-600'; ?> flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Card 4: Team Orchestrators -->
        <div class="bg-white border border-slate-200/80 p-6 rounded-2xl flex items-center justify-between shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Team Size</span>
                <span class="font-display font-black text-3xl text-slate-900 block"><?php echo $total_team; ?></span>
                <span class="text-[10px] text-slate-400 font-medium">Active orchestrators</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Recent Inquiries Section -->
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="font-display font-bold text-lg text-slate-900">Recent Customer Inquiries</h3>
                <p class="text-xs text-slate-500 mt-0.5">Most recent design and construction requests sent via frontend forms.</p>
            </div>
            <a href="inquiries.php" class="text-xs font-bold text-[#00aff0] hover:text-[#009ece] hover:underline uppercase tracking-wider">
                View All
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-150 text-left">
                <thead class="bg-slate-50/20 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Client</th>
                        <th class="px-6 py-4">Contacts</th>
                        <th class="px-6 py-4">Interest Sector</th>
                        <th class="px-6 py-4">Submitted Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (count($recent_inquiries) > 0): ?>
                        <?php foreach ($recent_inquiries as $inq): 
                            $status_class = "bg-red-50 text-red-600 border border-red-100";
                            if ($inq['status'] === 'read') {
                                $status_class = "bg-green-50 text-green-600 border border-green-100";
                            } elseif ($inq['status'] === 'replied') {
                                $status_class = "bg-blue-50 text-blue-600 border border-blue-100";
                            }
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($inq['name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-500"><?php echo htmlspecialchars($inq['email']); ?></div>
                                    <div class="text-slate-400 mt-0.5"><?php echo htmlspecialchars($inq['phone'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border bg-slate-50 border-slate-150">
                                        <?php echo htmlspecialchars($inq['category'] ?? 'General Inquiry'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    <?php echo date('M d, Y h:i A', strtotime($inq['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase <?php echo $status_class; ?>">
                                        <?php echo $inq['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="inquiries.php?id=<?php echo $inq['id']; ?>" class="inline-flex items-center text-[10px] font-bold text-[#00aff0] hover:text-[#009ece] uppercase tracking-wider py-1 px-3 bg-[#00aff0]/5 hover:bg-[#00aff0]/10 border border-[#00aff0]/10 rounded-lg transition-colors">
                                        Open Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-normal">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                No inquiries logged in database yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'admin_footer.php';
?>

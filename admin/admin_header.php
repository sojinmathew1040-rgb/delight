<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN HEADER & AUTHMENU
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

// Active menu helper
$current_page = basename($_SERVER['PHP_SELF']);
function is_active($page) {
    global $current_page;
    return $current_page === $page ? 'bg-[#00aff0]/10 text-[#00aff0]' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0f172a]';
}

$logo_path_resolved = "../" . get_setting('logo_path', 'asset/images/logo.png');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delight Builders Admin Control</title>
    
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
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full font-sans text-slate-800 antialiased flex">

    <!-- DESKTOP SIDEBAR -->
    <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 border-r border-slate-200 bg-white">
        <div class="flex flex-col flex-grow pt-5 overflow-y-auto">
            <!-- Branding -->
            <div class="flex items-center flex-shrink-0 px-6 mb-8">
                <img src="<?php echo $logo_path_resolved; ?>" alt="Delight Builders Logo" class="h-14 w-auto object-contain">
                <div class="ml-3">
                    <span class="font-display font-bold text-xs tracking-wider text-[#0f172a] block">DELIGHT</span>
                    <span class="text-[9px] font-semibold tracking-widest text-[#64748b] block uppercase">CONTROL PANEL</span>
                </div>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex-1 px-4 space-y-1 bg-white">
                <a href="index.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 <?php echo is_active('index.php'); ?>">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>

                <a href="inquiries.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 <?php echo is_active('inquiries.php'); ?>">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Inquiries
                </a>

                <a href="portfolio.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 <?php echo is_active('portfolio.php'); ?>">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Portfolio Works
                </a>

                <a href="categories.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 <?php echo is_active('categories.php'); ?>">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    Portfolio Categories
                </a>

                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Frontend Sections</span>
                </div>

                <a href="stats.php" class="flex items-center px-4 py-2 text-xs font-medium rounded-lg transition-all duration-300 <?php echo is_active('stats.php'); ?>">
                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2zm9 0v-9a2 2 0 00-2-2h-2a2 2 0 00-2 2v9a2 2 0 002 2h2a2 2 0 002-2z"></path>
                    </svg>
                    Key Metrics (Stats)
                </a>

                <a href="pillars.php" class="flex items-center px-4 py-2 text-xs font-medium rounded-lg transition-all duration-300 <?php echo is_active('pillars.php'); ?>">
                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Design Pillars
                </a>

                <a href="milestones.php" class="flex items-center px-4 py-2 text-xs font-medium rounded-lg transition-all duration-300 <?php echo is_active('milestones.php'); ?>">
                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Journey Timeline
                </a>

                <a href="team.php" class="flex items-center px-4 py-2 text-xs font-medium rounded-lg transition-all duration-300 <?php echo is_active('team.php'); ?>">
                    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Executive Team
                </a>

                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest block">System Settings</span>
                </div>

                <a href="settings.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-300 <?php echo is_active('settings.php'); ?>">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </a>
            </nav>
            
            <!-- User Profile & Logout -->
            <div class="flex-shrink-0 flex border-t border-slate-100 p-4 bg-slate-50/50">
                <div class="flex items-center">
                    <div class="inline-block h-9 w-9 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-display font-bold text-xs uppercase shadow-sm border border-white">
                        <?php echo substr($_SESSION['admin_fullname'] ?? 'A', 0, 2); ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-semibold text-slate-800 leading-tight">
                            <?php echo htmlspecialchars($_SESSION['admin_fullname'] ?? 'Administrator'); ?>
                        </p>
                        <a href="logout.php" class="text-[10px] font-bold text-red-500 hover:text-red-700 transition-colors uppercase tracking-wider block mt-1">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN RIGHT CONTAINER -->
    <div class="md:pl-64 flex flex-col flex-1 w-full min-h-screen">
        <!-- Top bar (Mobile Header) -->
        <header class="sticky top-0 z-30 flex-shrink-0 flex h-16 bg-white border-b border-slate-200 md:hidden justify-between items-center px-6">
            <div class="flex items-center">
                <img src="<?php echo $logo_path_resolved; ?>" alt="Delight Builders Logo" class="h-10 w-auto object-contain">
                <span class="ml-2 font-display font-extrabold text-[11px] tracking-widest text-[#0f172a] uppercase">DELIGHT ADMIN</span>
            </div>
            
            <!-- Mobile Menu Dropdown Toggle -->
            <div class="flex items-center space-x-4">
                <a href="logout.php" class="text-xs font-bold text-red-500 hover:underline uppercase tracking-wide">Logout</a>
            </div>
        </header>

        <!-- Main Content Wrapper -->
        <main class="flex-grow p-6 md:p-8">

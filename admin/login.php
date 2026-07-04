<?php
// ==========================================
// DELIGHT BUILDERS - ADMIN LOGIN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful Authentication
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_fullname'] = $user['full_name'] ?? 'Principal Admin';
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid username or password credentials.";
            }
        } catch (PDOException $e) {
            $error = "Database error encountered: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all security fields.";
    }
}

$logo_path_resolved = "../" . get_setting('logo_path', 'asset/images/logo.png');
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login | Delight Builders Control Center</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logo_path_resolved); ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans bg-slate-50 relative overflow-hidden flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

    <!-- BLUEPRINT GRID LINES LAYER -->
    <div class="absolute inset-0 grid grid-cols-4 pointer-events-none z-0">
        <div class="border-r border-slate-200/50 h-full"></div>
        <div class="border-r border-slate-200/50 h-full"></div>
        <div class="border-r border-slate-200/50 h-full"></div>
        <div class="h-full"></div>
    </div>
    
    <div class="max-w-md w-full space-y-8 relative z-10">
        <!-- Logo Header -->
        <div class="text-center">
            <img class="mx-auto h-24 w-auto object-contain" src="<?php echo $logo_path_resolved; ?>" alt="Delight Builders Logo">
            <h2 class="mt-4 font-display text-2xl font-bold tracking-tight text-[#0f172a] uppercase">
                CONTROL PANEL LOGIN
            </h2>
            <p class="mt-1 text-xs text-[#64748b] tracking-wider font-semibold uppercase">
                DELIGHT BUILDERS PORTAL
            </p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white/80 backdrop-blur-md border border-slate-200 p-8 rounded-3xl shadow-xl space-y-6">
            <?php if (!empty($error)): ?>
                <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="login.php" method="POST">
                <!-- Username -->
                <div class="space-y-2">
                    <label for="username" class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input id="username" name="username" type="text" required class="appearance-none rounded-xl relative block w-full pl-10 pr-3 py-3 border border-slate-200 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] text-sm font-medium transition-all duration-300 bg-slate-50/50 focus:bg-white" placeholder="Enter username">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label for="password" class="block text-[10px] tracking-wider uppercase text-slate-500 font-bold">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" required class="appearance-none rounded-xl relative block w-full pl-10 pr-3 py-3 border border-slate-200 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#00aff0] focus:border-[#00aff0] text-sm font-medium transition-all duration-300 bg-slate-50/50 focus:bg-white" placeholder="••••••••">
                    </div>
                </div>

                <!-- Sign In Button -->
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold uppercase tracking-wider rounded-xl text-white bg-[#0f172a] hover:bg-[#00aff0] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00aff0] transition-all duration-300 hover:shadow-lg">
                        Sign In Securely
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center text-[10px] text-slate-400 font-medium">
            Authorized Personnel Only. Actions logged dynamically.
        </div>
    </div>
    
</body>
</html>

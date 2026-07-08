<?php
// ==========================================
// DELIGHT BUILDERS - PORTFOLIO DATA CONFIG
// ==========================================
require_once 'admin/db_connection.php';

// Fallback resolver for asset paths in case directory name is "assets" or "asset"
function resolve_asset_path($path)
{
    if (file_exists($path)) {
        return $path;
    }
    if (strpos($path, 'assets/') === 0) {
        $alt = str_replace('assets/', 'asset/', $path);
        if (file_exists($alt))
            return $alt;
    }
    if (strpos($path, 'asset/') === 0) {
        $alt = str_replace('asset/', 'assets/', $path);
        if (file_exists($alt))
            return $alt;
    }
    return $path;
}

// Fetch settings
$site_title = get_setting('site_title', 'Delight Builders | Architects of Permanence & Luxury Construction');
$established_year = get_setting('established_year', '2006');
$coordinates = get_setting('coordinates', '40.7128° N, 74.0060° W');
$logo_path = resolve_asset_path(get_setting('logo_path', 'asset/images/logo.png'));
$hero_subtitle = get_setting('hero_subtitle', 'Sculpting structural blueprints into monolithic icons of stone, glass, and timber. We build custom architectural poetry.');
$philosophy_text_1 = get_setting('philosophy_text_1', 'We do not merely construct spaces; we synthesize permanent environments. By uniting the tactile gravity of custom-cast concrete with the weightless fluidity of structural glass, Delight Builders challenges the ephemeral nature of modern housing.');
$philosophy_text_2 = get_setting('philosophy_text_2', 'Every commission is executed with absolute structural precision. From deep soil diagnostics to custom seismic load profiles, our engineering team constructs structural poetry that guarantees durability across centuries.');

// Fetch stats
try {
    $stmt = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC, id ASC");
    $stats = $stmt->fetchAll();
} catch (PDOException $e) {
    $stats = [];
}

// Fetch portfolio projects
try {
    $stmt = $pdo->query("SELECT * FROM portfolio ORDER BY id DESC");
    $portfolio = $stmt->fetchAll();
    
    // Add gallery items dynamically for each project to match the format of frontend
    foreach ($portfolio as $key => $project) {
        $stmt_gal = $pdo->prepare("SELECT src, title, desc_text as `desc` FROM portfolio_gallery WHERE portfolio_id = ? ORDER BY id ASC");
        $stmt_gal->execute([$project['id']]);
        $portfolio[$key]['gallery'] = $stmt_gal->fetchAll();
    }
} catch (PDOException $e) {
    $portfolio = [];
}

// Pick 8 representative photos for the home page gallery preview from ALL blueprints
$preview_photos = [];
$total_blueprints = 0;
try {
    $stmt_prev = $pdo->query("SELECT src, title, desc_text as `desc` FROM portfolio_gallery WHERE (show_in_gallery = 1 OR show_in_gallery IS NULL) ORDER BY id ASC LIMIT 8");
    $preview_photos = $stmt_prev->fetchAll();
    $total_blueprints = $pdo->query("SELECT COUNT(*) FROM portfolio_gallery WHERE (show_in_gallery = 1 OR show_in_gallery IS NULL)")->fetchColumn();
} catch (PDOException $e) {
    $preview_photos = [];
    $total_blueprints = 0;
}

// Fetch categories for inquiry form
try {
    $stmt_cats = $pdo->query("SELECT name FROM portfolio_categories ORDER BY name ASC");
    $db_categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $db_categories = [];
}

// Fetch testimonials
try {
    $stmt_test = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, id ASC");
    $db_testimonials = $stmt_test->fetchAll();
} catch (PDOException $e) {
    $db_testimonials = [];
}

// Contact form processing (Stateful validation)
$inquiry_sent = false;
$inquiry_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inquiry'])) {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $whatsapp = htmlspecialchars(trim($_POST['whatsapp'] ?? ''));
    $category = htmlspecialchars(trim($_POST['category'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if ($name && $email && $message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, whatsapp, category, message, status) VALUES (?, ?, ?, ?, ?, ?, 'unread')");
            $stmt->execute([$name, $email, $phone, $whatsapp, $category, $message]);
            $inquiry_sent = true;
        } catch (PDOException $e) {
            $inquiry_error = "Failed to submit inquiry: " . $e->getMessage();
        }
    } else {
        $inquiry_error = "Please fill in all fields with valid information.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-[#f8fafc]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logo_path); ?>">

    <!-- Meta tags for SEO -->
    <meta name="description"
        content="Delight Builders curates elite luxury residential, commercial frameworks, and sustainable construction projects. Explore our premium scrollytelling experience.">
    <meta name="keywords"
        content="Luxury construction, premium residential, architectural frameworks, sustainable fits, Delight Builders">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit (premium $100k-site typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            cyan: '#00aff0',
                            crimson: '#ec3237',
                            dark: '#f8fafc',
                            black: '#0f172a',
                            border: '#e2e8f0'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS for luxury scrollbar, overlays, and animations -->
    <link rel="stylesheet" href="asset/css/style.css?v=<?php echo filemtime('asset/css/style.css'); ?>">
</head>

<body
    class="font-sans text-[#0f172a]/85 relative overflow-x-hidden min-h-screen selection:bg-[#00aff0]/10 selection:text-[#00aff0]">

    <!-- ELITE LUXURY PRELOADER OVERLAY -->
    <div id="preloader"
        class="fixed inset-0 z-50 bg-[#f8fafc] flex flex-col justify-center items-center transition-all duration-1000 ease-in-out">
        <div class="text-center space-y-5 max-w-md px-6 flex flex-col items-center">
            <!-- Dynamic Logo -->
            <img src="<?php echo $logo_path; ?>" alt="Delight Builders Logo" class="h-32 w-auto object-contain mb-2">

            <h2
                class="font-display font-bold text-[#0f172a] tracking-wider text-2xl uppercase transition-opacity duration-700">
                DELIGHT BUILDERS
            </h2>
            <p class="text-[10px] tracking-[0.25em] text-[#00aff0] uppercase font-semibold">
                Architects of Permanence
            </p>
            <div class="relative w-64 h-[3px] bg-[#e2e8f0] mx-auto overflow-hidden mt-2 rounded-full">
                <!-- Solid gold preloader bar -->
                <div id="preloader-bar"
                    class="absolute top-0 left-0 h-full bg-[#00aff0] w-0 transition-all duration-100 rounded-full">
                </div>
            </div>
            <div id="preloader-percentage" class="font-display text-xs font-semibold text-[#64748b] tracking-normal">
                0%
            </div>
            <p class="text-xs text-[#64748b] italic font-light tracking-wide pt-1">
                Assembling structural geometries...
            </p>
        </div>
    </div>

    <!-- BACKGROUND CANVAS SCROLLYTELLING ENGINE -->
    <canvas id="scrolly-canvas"
        class="fixed top-0 left-0 w-full h-full -z-20 pointer-events-none transition-opacity duration-1000 opacity-0"></canvas>

    <!-- LIGHT PASTEL BLENDING OVERLAY FOR SEQUENCE VISIBILITY -->
    <div class="fixed inset-0 -z-10 bg-white/80 pointer-events-none"></div>

    <!-- BLUEPRINT GRID LINES LAYER -->
    <div class="fixed inset-0 grid grid-cols-4 pointer-events-none z-0">
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="h-full"></div>
    </div>

    <!-- MAIN APP WRAPPER -->
    <div id="main-content" class="relative z-10 opacity-0 transition-opacity duration-1000 ease-in">

        <!-- HEADER NAVIGATION BAR -->
        <header id="main-header"
            class="fixed top-0 left-0 w-full z-40 h-28 border-b border-[#e2e8f0]/40 bg-white/72 backdrop-blur-md transition-all duration-500">
            <div class="max-w-7xl mx-auto px-6 h-full flex justify-between items-center">
                <!-- Return Home link -->
                <a href="index.php" class="flex items-center group">
                    <img id="header-logo" src="<?php echo $logo_path; ?>" alt="Delight Builders Logo"
                        class="h-36 w-auto object-contain transition-all duration-500 group-hover:scale-105">
                </a>

                <!-- Nav Menu -->
                <nav
                    class="hidden md:flex items-center space-x-10 text-[13px] tracking-tight font-medium text-[#0f172a]/80">
                    <a href="#vision" class="hover:text-[#00aff0] transition-colors duration-305">Vision</a>
                    <a href="#timeline" class="hover:text-[#00aff0] transition-colors duration-305">Timeline</a>
                    <a href="portfolio.php" class="hover:text-[#00aff0] transition-colors duration-305">Portfolio</a>
                    <a href="#contact" class="hover:text-[#00aff0] transition-colors duration-305">Contact Us</a>
                </nav>

                <!-- CTA Buttons -->
                <div class="flex items-center space-x-3">
                    <a href="contact.php"
                        class="font-display bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold px-5 py-2.5 rounded-full transition-all duration-300 shadow-sm hover:shadow">
                        Contact Us
                    </a>
                </div>
            </div>
            <!-- Brand Scroll Progress Bar -->
            <div id="scroll-progress-bar"
                class="absolute bottom-0 left-0 h-[2.5px] bg-gradient-to-r from-[#00aff0] to-[#ec3237] transition-all duration-75"
                style="width: 0%;"></div>
        </header>

        <!-- HERO TITLE SECTION -->
        <section id="hero"
            class="relative min-h-screen flex flex-col justify-center px-6 md:px-16 lg:px-24 z-10 border-b border-[#e2e8f0]/60">
            <div class="max-w-5xl mt-12">
                <!-- Metadata line -->
                <div
                    class="flex items-center space-x-4 mb-6 text-xs text-[#00aff0] tracking-normal uppercase font-semibold">
                    <span>EST. <?php echo htmlspecialchars($established_year); ?></span>
                    <span class="w-1.5 h-1.5 bg-[#00aff0] rounded-full"></span>
                    <span>COORDINATES <?php echo htmlspecialchars($coordinates); ?></span>
                </div>

                <!-- High-Impact Bold Minimal Title -->
                <h1
                    class="font-display text-5xl md:text-7xl lg:text-8xl text-[#0f172a] font-extrabold tracking-tighter leading-[1.05] select-none">
                    DELIGHT<br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-[#0f172a] via-[#00aff0] to-[#ec3237] font-extrabold">BUILDERS</span>
                </h1>

                <!-- Subtitle -->
                <p
                    class="mt-8 text-[#0f172a]/70 font-normal text-base md:text-xl max-w-xl tracking-tight leading-relaxed">
                    <?php echo htmlspecialchars($hero_subtitle); ?>
                </p>

                <!-- Action button -->
                <div class="mt-12 flex flex-col sm:flex-row gap-4">
                    <a href="#portfolio"
                        class="bg-[#0f172a] text-white hover:bg-black px-8 py-4 text-xs font-semibold tracking-tight rounded-full transition-all duration-300 text-center shadow-sm hover:shadow-md">
                        Explore Portfolio
                    </a>
                    <a href="#vision"
                        class="border border-[#94a3b8] hover:border-[#0f172a] text-[#0f172a] px-8 py-4 text-xs font-semibold tracking-tight rounded-full transition-all duration-300 text-center glass-panel">
                        The Philosophy
                    </a>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div
                class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center space-y-2 pointer-events-none">
                <span class="text-[9px] tracking-normal text-[#64748b] uppercase font-semibold">SCROLL TO BUILD</span>
                <div class="w-[1px] h-12 bg-gradient-to-b from-[#e2e8f0] to-transparent relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1/2 bg-[#00aff0] animate-bounce"></div>
                </div>
            </div>
        </section>

        <!-- SECTION: PHILOSOPHY / VISION -->
        <section id="vision" class="relative py-32 px-6 md:px-16 lg:px-24 border-b border-[#e2e8f0]/60 glass-panel">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12">
                <!-- Left Title Panel -->
                <div class="lg:col-span-5 space-y-6">
                    <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">01 /
                        Conceptual Vision</span>
                    <h2
                        class="font-display text-3xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight leading-tight">
                        BRUTALIST ELEGANCE.<br>INTELLIGENT FORM.
                    </h2>
                    <div class="w-16 h-[2px] bg-[#00aff0]"></div>
                </div>

                <!-- Right Content Panel -->
                <div
                    class="lg:col-span-7 space-y-8 text-[#0f172a]/75 font-normal text-base md:text-lg leading-relaxed pt-2">
                    <p align="justify">
                        <?php echo htmlspecialchars($philosophy_text_1); ?>
                    </p>
                    <p align="justify">
                        <?php echo htmlspecialchars($philosophy_text_2); ?>
                    </p>
                    <div class="grid grid-cols-2 gap-6 pt-4 text-[#0f172a]">
                        <div class="border-l-2 border-[#00aff0] pl-4 space-y-1">
                            <div class="font-display text-3xl text-[#0f172a] font-extrabold">100%</div>
                            <div class="text-[10px] tracking-[0.15em] uppercase text-[#64748b] font-semibold">Bespoke
                                Architectural
                                Geometry</div>
                        </div>
                        <div class="border-l-2 border-[#00aff0] pl-4 space-y-1">
                            <div class="font-display text-3xl text-[#0f172a] font-extrabold">A++</div>
                            <div class="text-[10px] tracking-[0.15em] uppercase text-[#64748b] font-semibold">
                                Environmental Efficiency
                                Scale</div>
                        </div>
                    </div>

                    <!-- View More Button linking to about.php -->
                    <div class="pt-8">
                        <a href="about.php"
                            class="inline-block bg-[#0f172a] text-white hover:bg-[#009ece] px-6 py-3.5 text-xs font-semibold tracking-wide rounded-full transition-all duration-300 hover:shadow-[0_10px_25px_rgba(0,175,240,0.08)]">
                            View More
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION: KEY METRICS / STATS -->
        <section id="stats" class="relative py-20 px-6 md:px-16 lg:px-24 border-b border-[#e2e8f0]/60 bg-[#f8fafc]/50">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($stats as $stat): ?>
                        <div
                            class="bg-white border border-[#e2e8f0]/80 rounded-2xl p-6 flex items-center gap-5 apple-card opacity-0 translate-y-8 transition-all duration-700 ease-out">
                            <div
                                class="w-14 h-14 rounded-full bg-[#ec3237]/10 text-[#ec3237] flex items-center justify-center flex-shrink-0">
                                <?php if ($stat['icon'] === 'helmet'): ?>
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 4C7.58 4 4 7.58 4 12h16c0-4.42-3.58-8-8-8zm-1 2h2v4h-2V6zm-9 7h20c.55 0 1 .45 1 1s-.45 1-1 1H2c-.55 0-1-.45-1-1s.45-1 1-1z" />
                                    </svg>
                                <?php elseif ($stat['icon'] === 'house'): ?>
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                                    </svg>
                                <?php elseif ($stat['icon'] === 'map-pin'): ?>
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <!-- Folded map folds -->
                                        <path
                                            d="M15 19l-6-2.11V5l6 2.11V19zM20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5z"
                                            opacity="0.45" />
                                        <!-- Pin on top -->
                                        <path
                                            d="M12 2C8.69 2 6 4.69 6 8c0 4.5 6 11 6 11s6-6.5 6-11c0-3.31-2.69-6-6-6zm0 8.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                    </svg>
                                <?php elseif ($stat['icon'] === 'family'): ?>
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 6a3 3 0 100-6 3 3 0 000 6zm-4.3 1.25a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm8.6 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM12 8.2c-2.7 0-8 1.35-8 4v2.8h16V12.2c0-2.65-5.3-4-8-4zm-5.7 4c0-.9.5-1.7 1.3-2.2-.8-.2-1.8-.3-2.6-.3-2 0-6 1-6 3v2.3h7.3v-2.8zm11.4 0v2.8H25V13c0-2-4-3-6-3-.8 0-1.8.1-2.6.3.8.5 1.3 1.3 1.3 2.2z" />
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="font-display text-3xl text-[#0f172a] font-extrabold tracking-tight mb-1 stat-number"
                                    data-target="<?php echo (int) $stat['number']; ?>"
                                    data-suffix="<?php echo strpos($stat['number'], '+') !== false ? '+' : ''; ?>">
                                    <?php echo $stat['number']; ?>
                                </div>
                                <div
                                    class="text-[10px] tracking-wider uppercase text-[#64748b] font-semibold leading-tight">
                                    <?php echo $stat['label']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- SECTION: ENGINEERING TIMELINE -->
        <section id="timeline" class="relative border-b border-[#e2e8f0]/60 bg-[#f8fafc]/30">
            <div class="h-[250vh]" id="timeline-scroll-track">
                <div
                    class="sticky top-0 h-screen w-full overflow-hidden flex flex-col justify-between py-10 px-0">

                    <!-- Section Header -->
                    <div class="text-center space-y-4 max-w-xl mx-auto z-20 px-6">
                        <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">02 / Workflow
                            Timeline</span>
                        <h2 class="font-display text-3xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">Our
                            Structural Metric</h2>
                    </div>

                    <!-- Sticky Canvas Area for Scrollytelling Path -->
                    <div class="relative w-full flex-grow my-4" id="timeline-canvas-container">
                        <!-- SVG path container -->
                        <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" id="timeline-svg">
                            <defs>
                                <linearGradient id="timeline-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#00aff0" />
                                    <stop offset="50%" stop-color="#a855f7" />
                                    <stop offset="100%" stop-color="#ec3237" />
                                </linearGradient>
                                <linearGradient id="timeline-grad-dots" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#00aff0" />
                                    <stop offset="50%" stop-color="#a855f7" />
                                    <stop offset="100%" stop-color="#ec3237" />
                                </linearGradient>
                                <mask id="timeline-path-mask">
                                    <path id="timeline-mask-path" fill="none" stroke="white" stroke-width="12"
                                        stroke-linecap="round" />
                                </mask>
                            </defs>
                            <!-- Background dotted path (Perfect circular dots spacing) -->
                            <path id="timeline-bg-path" fill="none" stroke="#e2e8f0" stroke-width="6"
                                stroke-dasharray="1 14" stroke-linecap="round" />
                            <!-- Foreground animated dotted path (masked for dynamic reveal with gradient stroke) -->
                            <path id="timeline-fg-path" fill="none" stroke="url(#timeline-grad)" stroke-width="6"
                                stroke-dasharray="1 14" stroke-linecap="round" mask="url(#timeline-path-mask)" />
                        </svg>

                        <!-- Circle 1 (Left Top) -->
                        <div id="circle-1"
                            class="timeline-circle w-28 h-28 md:w-36 md:h-36 flex flex-col items-center justify-center text-center absolute cursor-pointer transition-all duration-500 apple-card z-10 group">
                            <!-- House SVG Background -->
                            <svg class="absolute inset-0 w-full h-full text-slate-200 fill-white/90 backdrop-blur filter drop-shadow-[0_8px_30px_rgba(0,0,0,0.03)] transition-all duration-500 house-bg z-0" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M 50 2 L 98 35 L 98 98 L 2 98 L 2 35 Z" stroke="currentColor" stroke-width="3" fill="inherit" stroke-linejoin="round" />
                            </svg>
                            <!-- Step Number Badge -->
                            <div class="absolute left-1/2 -translate-x-1/2 -top-2.5 md:-top-3.5 bg-slate-50 border border-slate-200 text-[9px] font-bold text-slate-300 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center circle-num font-mono shadow-sm transition-all duration-500 z-20">01</div>
                            <!-- Content -->
                            <div class="relative z-10 flex flex-col items-center justify-center p-3 md:p-4 pt-5 md:pt-6">
                                <!-- Consultation Icon: Chat / Discussion -->
                                <div class="text-[#00aff0] mb-1 opacity-90 transition-opacity">
                                    <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div class="text-[8px] md:text-xs tracking-wider uppercase text-slate-650 font-bold circle-label leading-tight mt-1">
                                    Consultation</div>
                            </div>
                        </div>

                        <!-- Circle 2 (Right Top) -->
                        <div id="circle-2"
                            class="timeline-circle w-28 h-28 md:w-36 md:h-36 flex flex-col items-center justify-center text-center absolute cursor-pointer transition-all duration-500 apple-card z-10 group">
                            <!-- House SVG Background -->
                            <svg class="absolute inset-0 w-full h-full text-slate-200 fill-white/90 backdrop-blur filter drop-shadow-[0_8px_30px_rgba(0,0,0,0.03)] transition-all duration-500 house-bg z-0" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M 50 2 L 98 35 L 98 98 L 2 98 L 2 35 Z" stroke="currentColor" stroke-width="3" fill="inherit" stroke-linejoin="round" />
                            </svg>
                            <!-- Step Number Badge -->
                            <div class="absolute left-1/2 -translate-x-1/2 -top-2.5 md:-top-3.5 bg-slate-50 border border-slate-200 text-[9px] font-bold text-slate-300 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center circle-num font-mono shadow-sm transition-all duration-500 z-20">02</div>
                            <!-- Content -->
                            <div class="relative z-10 flex flex-col items-center justify-center p-3 md:p-4 pt-5 md:pt-6">
                                <!-- Design & Planning Icon: Ruler & Pencil Map -->
                                <div class="text-[#00aff0] mb-1 opacity-90 transition-opacity">
                                    <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 01.553-.894L9 2l6 3 5.447-2.724A1 1 0 0121 3.176v10.764a1 1 0 01-.553.894L15 18l-6 2z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 2v18M15 4v18"></path>
                                    </svg>
                                </div>
                                <div class="text-[8px] md:text-xs tracking-wider uppercase text-slate-650 font-bold circle-label leading-tight mt-1">
                                    Design & Planning</div>
                            </div>
                        </div>

                        <!-- Circle 3 (Left Bottom) -->
                        <div id="circle-3"
                            class="timeline-circle w-28 h-28 md:w-36 md:h-36 flex flex-col items-center justify-center text-center absolute cursor-pointer transition-all duration-500 apple-card z-10 group">
                            <!-- House SVG Background -->
                            <svg class="absolute inset-0 w-full h-full text-slate-200 fill-white/90 backdrop-blur filter drop-shadow-[0_8px_30px_rgba(0,0,0,0.03)] transition-all duration-500 house-bg z-0" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M 50 2 L 98 35 L 98 98 L 2 98 L 2 35 Z" stroke="currentColor" stroke-width="3" fill="inherit" stroke-linejoin="round" />
                            </svg>
                            <!-- Step Number Badge -->
                            <div class="absolute left-1/2 -translate-x-1/2 -top-2.5 md:-top-3.5 bg-slate-50 border border-slate-200 text-[9px] font-bold text-slate-300 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center circle-num font-mono shadow-sm transition-all duration-500 z-20">03</div>
                            <!-- Content -->
                            <div class="relative z-10 flex flex-col items-center justify-center p-3 md:p-4 pt-5 md:pt-6">
                                <!-- Construction Icon: Building Blocks / Wall -->
                                <div class="text-[#00aff0] mb-1 opacity-90 transition-opacity">
                                    <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="text-[8px] md:text-xs tracking-wider uppercase text-slate-650 font-bold circle-label leading-tight mt-1">
                                    Construction</div>
                            </div>
                        </div>

                        <!-- Circle 4 (Right Bottom) -->
                        <div id="circle-4"
                            class="timeline-circle w-28 h-28 md:w-36 md:h-36 flex flex-col items-center justify-center text-center absolute cursor-pointer transition-all duration-500 apple-card z-10 group">
                            <!-- House SVG Background -->
                            <svg class="absolute inset-0 w-full h-full text-slate-200 fill-white/90 backdrop-blur filter drop-shadow-[0_8px_30px_rgba(0,0,0,0.03)] transition-all duration-500 house-bg z-0" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M 50 2 L 98 35 L 98 98 L 2 98 L 2 35 Z" stroke="currentColor" stroke-width="3" fill="inherit" stroke-linejoin="round" />
                            </svg>
                            <!-- Step Number Badge -->
                            <div class="absolute left-1/2 -translate-x-1/2 -top-2.5 md:-top-3.5 bg-slate-50 border border-slate-200 text-[9px] font-bold text-slate-300 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center circle-num font-mono shadow-sm transition-all duration-500 z-20">04</div>
                            <!-- Content -->
                            <div class="relative z-10 flex flex-col items-center justify-center p-3 md:p-4 pt-5 md:pt-6">
                                <!-- Handover Icon: Home -->
                                <div class="text-[#00aff0] mb-1 opacity-90 transition-opacity">
                                    <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                </div>
                                <div class="text-[8px] md:text-xs tracking-wider uppercase text-slate-650 font-bold circle-label leading-tight mt-1">
                                    Handover</div>
                            </div>
                        </div>
                    </div>

                    <!-- Unified Detail Description Card -->
                    <div id="timeline-detail-panel"
                        class="max-w-xl mx-6 sm:mx-auto glass-panel border border-[#e2e8f0]/80 rounded-2xl p-4 md:p-5 shadow-[0_8px_30px_rgba(0,0,0,0.03)] transition-all duration-300 z-20 min-h-[110px] flex flex-col justify-center text-center">
                        <span id="timeline-detail-subtitle"
                            class="text-[10px] tracking-widest text-[#00aff0] uppercase font-bold mb-1">Diagnostic
                            Geometries</span>
                        <h4 id="timeline-detail-title" class="font-display text-[#0f172a] text-lg font-extrabold mb-2">1
                            / Consultation</h4>
                        <p id="timeline-detail-desc"
                            class="text-[#0f172a]/70 text-xs md:text-sm leading-relaxed max-w-lg mx-auto">
                            Complete laser terrain mapping and geologic core diagnostics. We simulate natural light
                            profiles and solar tracking metrics across seasons before structural drafts commence.
                        </p>
                    </div>

                    <!-- Scroll Instruction -->
                    <div class="text-center z-20 mt-4">
                        <span id="timeline-scroll-tip"
                            class="text-[9px] tracking-[0.2em] text-[#64748b] uppercase font-bold animate-pulse">Scroll
                            to navigate workflow</span>
                    </div>

                </div>
            </div>
        </section>

        <!-- SECTION: DYNAMIC PORTFOLIO GRID PANEL -->
        <section id="portfolio" class="relative py-32 px-6 md:px-16 lg:px-24 border-b border-[#e2e8f0]/60">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-20 gap-6">
                    <div class="space-y-4">
                        <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">03 /
                            Architectural Archives</span>
                        <h2 class="font-display text-3xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">
                            Selected Portfolio
                        </h2>
                    </div>

                    <!-- Right container with description and CTA button -->
                    <div class="flex flex-col items-start md:items-end gap-5 max-w-md">
                        <p class="text-[#0f172a]/60 tracking-normal font-normal text-sm leading-relaxed md:text-right">
                            Each architectural project is custom configured. Select any project to view its full
                            structural blueprints and details on our gallery page.
                        </p>
                        <a href="portfolio.php"
                            class="inline-block bg-[#0f172a] text-white hover:bg-black px-6 py-3.5 text-xs font-semibold tracking-wide rounded-full transition-all duration-300 hover:shadow-[0_10px_25px_rgba(0,0,0,0.1)]">
                            View All Projects
                        </a>
                    </div>
                </div>

                <!-- Dynamic Grid using PHP foreach -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($portfolio as $index => $project):
                        $image_path = resolve_asset_path($project['image']);

                        // Select badge color based on category sector
                        $badge_class = "border-[#00aff0]/20 text-[#00aff0] bg-[#00aff0]/5";
                        if ($project['category'] === 'Luxury Residential') {
                            $badge_class = "border-[#ec3237]/20 text-[#ec3237] bg-[#ec3237]/5";
                        }
                        ?>
                        <a href="project.php?id=<?php echo $project['id']; ?>"
                            class="portfolio-card-link group relative bg-white border border-[#e2e8f0] overflow-hidden flex flex-col justify-between transition-all duration-500 rounded-2xl block shadow-[0_8px_30px_rgba(0,0,0,0.03)] apple-card">
                            <!-- Image Container -->
                            <div class="relative overflow-hidden aspect-[4/3] bg-[#f8fafc]">
                                <img src="<?php echo $image_path; ?>" alt="<?php echo $project['title']; ?>" loading="lazy"
                                    class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-in-out filter brightness-[0.98] group-hover:brightness-100">

                                <!-- Subtle image grid layer -->
                                <div class="absolute inset-0 pointer-events-none border border-white/10 z-10"></div>
                            </div>

                            <!-- Content -->
                            <div class="p-8 space-y-4 flex-grow flex flex-col justify-between bg-white">
                                <div class="space-y-2">
                                    <span
                                        class="text-[9px] tracking-wider px-2.5 py-1 border rounded-full inline-block font-semibold uppercase <?php echo $badge_class; ?>">
                                        <?php echo $project['category']; ?>
                                    </span>
                                    <h3 class="font-display text-xl text-[#0f172a] font-extrabold tracking-tight pt-2">
                                        <?php echo $project['title']; ?>
                                    </h3>
                                    <p class="text-[#0f172a]/70 text-sm leading-relaxed font-normal mt-2" align="justify">
                                        <?php echo $project['description']; ?>
                                    </p>
                                </div>

                                <div class="pt-6 border-t border-[#e2e8f0] mt-4">
                                    <span
                                        class="inline-flex items-center text-[10px] tracking-wider text-[#0f172a]/80 group-hover:text-[#00aff0] font-bold uppercase transition-colors duration-300">
                                        Launch Blueprint Gallery
                                        <svg class="w-3.5 h-3.5 ml-2 group-hover:translate-x-1 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>

        <!-- SECTION: GALLERY PREVIEW -->
        <section id="gallery" class="relative py-32 px-6 md:px-16 lg:px-24 border-b border-[#e2e8f0]/60">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-20 gap-6">
                    <div class="space-y-4">
                        <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">04 /
                            Architectural Archives</span>
                        <h2 class="font-display text-3xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">
                            Blueprint Gallery Preview
                        </h2>
                    </div>

                    <!-- Right container with description and CTA button -->
                    <div class="flex flex-col items-start md:items-end gap-5 max-w-md">
                        <p class="text-[#0f172a]/60 tracking-normal font-normal text-sm leading-relaxed md:text-right">
                            A view-only showcase of select detailing, timber structures, and cast layouts. Click the
                            button to browse the complete index of <?php echo $total_blueprints; ?> blueprints.
                        </p>
                        <a href="gallery.php"
                            class="inline-block bg-[#0f172a] text-white hover:bg-black px-6 py-3.5 text-xs font-semibold tracking-wide rounded-full transition-all duration-300 hover:shadow-[0_10px_25px_rgba(0,0,0,0.1)]">
                            Enter Full Blueprint Gallery
                        </a>
                    </div>
                </div>

                <!-- Preview Grid (8 Items, View-Only) -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="gallery-preview-grid">
                    <?php foreach ($preview_photos as $photo):
                        $resolved_src = resolve_asset_path($photo['src']);
                        ?>
                        <div
                            class="group relative overflow-hidden aspect-square border border-[#e2e8f0] rounded-2xl transition-all duration-500 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-md">
                            <!-- Image -->
                            <img src="<?php echo $resolved_src; ?>" alt="<?php echo $photo['title']; ?>" loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 filter brightness-95 group-hover:brightness-100 ease-in-out">

                            <!-- Simple Text Overlay on Hover - text highlights in white -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-4 flex flex-col justify-end rounded-2xl">
                                <h4
                                    class="font-display text-xs text-stone-100 group-hover:text-white uppercase tracking-wider font-semibold transition-colors duration-300">
                                    <?php echo $photo['title']; ?>
                                </h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>

        <?php if (!empty($db_testimonials)): 
            // Triplicate the testimonials so that it creates a seamless infinite marquee scroll
            $marquee_testimonials = array_merge($db_testimonials, $db_testimonials, $db_testimonials);
        ?>
        <!-- SECTION: TESTIMONIALS -->
        <section id="testimonials"
            class="relative py-32 overflow-hidden border-b border-[#e2e8f0]/60 bg-[#f8fafc]/40">
            <div class="px-6 md:px-16 lg:px-24 max-w-7xl mx-auto mb-20">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                    <div class="space-y-4">
                        <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">05 /
                            Conceptual Trust</span>
                        <h2 class="font-display text-3xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">
                            Client Testimonials
                        </h2>
                    </div>
                    <div class="max-w-md">
                        <p class="text-[#0f172a]/60 tracking-normal font-normal text-sm leading-relaxed md:text-right">
                            True permanence is reflected in the relationships we forge with our clients. Explore their
                            feedback on our architectural commissions.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Horizontal Scroll Marquee Container -->
            <div class="relative w-full overflow-hidden">
                <!-- Premium edge-fades to blend cards dynamically (placed outside/above the scroll container) -->
                <div class="absolute inset-y-0 left-0 w-16 md:w-32 bg-gradient-to-r from-[#f8fafc] via-[#f8fafc]/80 to-transparent z-20 pointer-events-none"></div>
                <div class="absolute inset-y-0 right-0 w-16 md:w-32 bg-gradient-to-l from-[#f8fafc] via-[#f8fafc]/80 to-transparent z-20 pointer-events-none"></div>

                <div id="testimonials-scroll-container" class="w-full overflow-x-auto scrollbar-none flex gap-8 py-4 px-4 select-none cursor-grab active:cursor-grabbing relative z-10">
                    <?php foreach ($marquee_testimonials as $t): 
                        $initials = get_initials($t['client_name']);
                        
                        $quote_icon_color = 'text-[#00aff0]';
                        $project_tag_color = 'text-[#00aff0]';
                        
                        if ($t['color'] === 'red') {
                            $quote_icon_color = 'text-[#ec3237]';
                            $project_tag_color = 'text-[#ec3237]';
                        } elseif ($t['color'] === 'purple') {
                            $quote_icon_color = 'text-purple-600';
                            $project_tag_color = 'text-purple-600';
                        }
                    ?>
                    <!-- Testimonial Card -->
                    <div
                        class="w-[300px] md:w-[420px] shrink-0 glass-panel border border-[#e2e8f0] rounded-3xl p-8 flex flex-col justify-between space-y-8 apple-card shadow-[0_8px_30px_rgba(0,0,0,0.02)] whitespace-normal">
                        <!-- Quote Icon -->
                        <div class="<?php echo $quote_icon_color; ?> opacity-35">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                            </svg>
                        </div>

                        <!-- Testimonial text -->
                        <p class="text-xs md:text-sm text-[#0f172a]/80 leading-relaxed font-normal flex-grow italic">
                            "<?php echo htmlspecialchars($t['quote']); ?>"
                        </p>

                        <!-- Client info -->
                        <div class="border-t border-[#e2e8f0] pt-6 flex items-center space-x-4">
                            <div
                                class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-display text-xs font-bold text-[#0f172a] shrink-0">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-display text-xs text-[#0f172a] font-bold truncate"><?php echo htmlspecialchars($t['client_name']); ?></h4>
                                <p class="text-[9px] tracking-wider uppercase text-[#64748b] font-semibold truncate">
                                    <?php echo htmlspecialchars($t['client_designation']); ?></p>
                                <span class="text-[8px] <?php echo $project_tag_color; ?> font-bold block uppercase mt-0.5 truncate"><?php echo htmlspecialchars($t['project_name']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- SECTION: INQUIRY FORM / CONTACT -->
        <section id="contact" class="relative py-32 px-6 md:px-16 lg:px-24 border-b border-[#e2e8f0]/60">
            <div class="max-w-4xl mx-auto">
                <div class="text-center space-y-4 mb-16">
                    <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">06 / Project
                        Inquiry</span>
                    <h2 class="font-display text-3xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">
                        Initiate Commission
                    </h2>
                    <p class="text-sm text-[#0f172a]/60 tracking-normal font-normal max-w-md mx-auto">
                        Inquire regarding custom estate designs, commercial frameworks, or structural consulting.
                    </p>
                </div>

                <!-- Glassmorphism Form container -->
                <div
                    class="glass-panel border border-[#e2e8f0]/80 rounded-3xl p-8 md:p-12 shadow-[0_8px_30px_rgba(0,0,0,0.04)] relative">

                    <?php if ($inquiry_sent): ?>
                        <!-- Success Banner -->
                        <div class="mb-6 p-4 bg-green-50 border border-green-250 text-green-700 rounded-2xl text-xs font-semibold flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Commission Request Registered successfully! An architectural partner will coordinate files within 24 standard hours.</span>
                        </div>
                    <?php endif; ?>
                        <!-- Form View -->
                        <?php if (!empty($inquiry_error)): ?>
                            <div
                                class="mb-6 p-4 bg-[#ec3237]/10 border border-[#ec3237]/20 text-[#ec3237] rounded-xl text-sm font-medium tracking-wide">
                                <?php echo $inquiry_error; ?>
                            </div>
                        <?php endif; ?>

                        <form action="#contact" method="POST" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Name Input -->
                                <div class="space-y-2">
                                    <label for="name"
                                        class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Client
                                        Name</label>
                                    <input type="text" id="name" name="name" required
                                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                        placeholder="e.g. Sterling H. Croft">
                                </div>

                                <!-- Email Input -->
                                <div class="space-y-2">
                                    <label for="email"
                                        class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Secure
                                        Contact Email</label>
                                    <input type="email" id="email" name="email" required
                                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                        placeholder="e.g. shc@croftenterprises.com">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Phone Input -->
                                <div class="space-y-2">
                                    <label for="phone"
                                        class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Secure
                                        Phone Number</label>
                                    <input type="tel" id="phone" name="phone" required
                                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                        placeholder="e.g. +91 98765 43210">
                                </div>

                                <!-- WhatsApp Input -->
                                <div class="space-y-2">
                                    <label for="whatsapp"
                                        class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">WhatsApp Number *</label>
                                    <input type="tel" id="whatsapp" name="whatsapp" required
                                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                        placeholder="e.g. +91 98765 43210">
                                </div>
                            </div>

                            <!-- Category Sector selection (full width) -->
                            <div class="space-y-2">
                                <label for="category"
                                    class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Category
                                    Sector</label>
                                <div class="relative">
                                    <select id="category" name="category"
                                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300 appearance-none">
                                        <?php if (empty($db_categories)): ?>
                                            <option value="Luxury Residential">Luxury Residential Sector</option>
                                            <option value="Commercial Frameworks">Commercial Framework Sector</option>
                                            <option value="Sustainable Fits">Sustainable Fits Sector</option>
                                        <?php else: ?>
                                            <?php foreach ($db_categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?> Sector</option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <option value="Other">Custom Consulting / Structural Audit</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        ▼
                                    </div>
                                </div>
                            </div>

                            <!-- Narrative inquiry -->
                            <div class="space-y-2">
                                <label for="message"
                                    class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Structural
                                    Requirements / Scope</label>
                                <textarea id="message" name="message" rows="5" required
                                    class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300 resize-none"
                                    placeholder="Outline your spatial dimensions, programmatic desires, or geologic site conditions..."></textarea>
                            </div>

                            <!-- Submit button - Clean Blue Pill/Rounded -->
                            <div class="pt-4">
                                <button type="submit" name="submit_inquiry"
                                    class="w-full bg-[#00aff0] text-white hover:bg-[#009ece] px-8 py-4 text-xs font-semibold tracking-wide rounded-full transition-all duration-300 hover:shadow-md">
                                    Submit Official Commission Package
                                </button>
                            </div>
                        </form>
                </div>
            </div>
        </section>

        <!-- FOOTER SECTION -->
        <footer class="bg-[#f8fafc]/95 py-16 px-6 md:px-16 border-t border-[#e2e8f0]/60 z-20 relative">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 relative">
                <!-- Branding -->
                <div class="flex flex-col items-center md:items-start space-y-3">
                    <img src="<?php echo $logo_path; ?>" alt="Delight Builders Logo" loading="lazy" class="h-16 w-auto object-contain">
                    <span class="font-display text-[#0f172a] font-extrabold tracking-widest text-base uppercase">DELIGHT
                        BUILDERS</span>
                    <span class="text-[10px] tracking-widest text-[#64748b] font-semibold uppercase">STRUCTURAL POETRY •
                        SINCE 2006</span>
                </div>

                <!-- Copyright & Scroll-to-Top Column -->
                <div class="flex flex-col items-center md:items-end space-y-5 w-full md:w-auto">
                    <div class="text-[11px] tracking-normal text-[#64748b] text-center md:text-right space-y-1 font-normal pb-8 md:pb-0">
                        <p>© <?php echo date("Y"); ?> Delight Builders Inc. All architectural frameworks reserved.</p>
                        <p>Designed for premium longevity. Engineered to absolute structural coordinates.</p>
                    </div>
                    
                    <!-- Scroll to Top Button -->
                    <div class="absolute bottom-[-16px] right-0 md:relative md:bottom-auto md:right-auto">
                        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                            class="group p-3 bg-gradient-to-r from-[#00aff0] to-[#ec3237] text-white hover:shadow-lg rounded-full transition-all duration-300 hover:scale-105 focus:outline-none flex items-center justify-center cursor-pointer border-none shadow-sm"
                            aria-label="Scroll to top"
                            title="Scroll to Top">
                            <svg class="w-4 h-4 transform group-hover:-translate-y-0.5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </footer>

    </div>

    <!-- CENTERED INQUIRY FORM MODAL ON LOAD -->
    <div id="load-inquiry-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-500">
        <div
            class="relative w-full max-w-lg mx-4 bg-white border border-[#e2e8f0] p-8 md:p-10 shadow-3xl rounded-3xl transform scale-95 transition-all duration-500 max-h-[90vh] overflow-y-auto scrollbar-thin">
            <!-- Close Button -->
            <button id="close-load-inquiry-btn"
                class="absolute top-4 right-4 text-[#64748b] hover:text-[#0f172a] text-xl focus:outline-none transition-colors p-2"
                aria-label="Close modal">
                ✕
            </button>

            <div class="text-center space-y-3 mb-6">
                <span class="text-[9px] text-[#00aff0] tracking-wider uppercase font-bold block">Exclusive
                    Invitation</span>
                <h2 class="font-display text-2xl md:text-3xl text-[#0f172a] font-extrabold tracking-tight">Initiate
                    Inquiry</h2>
                <div class="w-12 h-[1px] bg-[#00aff0] mx-auto"></div>
                <p class="text-xs text-[#0f172a]/60 tracking-normal font-normal max-w-md mx-auto">
                    Inquire regarding custom estate designs, commercial frameworks, or structural consulting.
                </p>
            </div>

            <?php if ($inquiry_sent): ?>
                <!-- Success Banner inside Modal -->
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-600 rounded-xl text-xs font-semibold text-center">
                    Commission Request Registered successfully! An architectural partner will coordinate files within 24 standard hours.
                </div>
            <?php endif; ?>

            <?php if (!empty($inquiry_error)): ?>
                <div class="mb-4 p-3 bg-[#ec3237]/10 border border-[#ec3237]/20 text-[#ec3237] rounded-xl text-xs font-medium text-center">
                    <?php echo $inquiry_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div class="space-y-1">
                    <label for="modal-name"
                        class="block text-[9px] tracking-wider uppercase text-[#64748b] font-bold">Client Name</label>
                    <input type="text" id="modal-name" name="name" required
                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-2.5 text-[#0f172a] placeholder-[#64748b]/70 text-xs tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                        placeholder="e.g. Sterling H. Croft">
                </div>

                <div class="space-y-1">
                    <label for="modal-email"
                        class="block text-[9px] tracking-wider uppercase text-[#64748b] font-bold">Secure Contact
                        Email</label>
                    <input type="email" id="modal-email" name="email" required
                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-2.5 text-[#0f172a] placeholder-[#64748b]/70 text-xs tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                        placeholder="e.g. shc@croftenterprises.com">
                </div>

                <!-- Phone and WhatsApp Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="modal-phone"
                            class="block text-[9px] tracking-wider uppercase text-[#64748b] font-bold">Secure Phone Number</label>
                        <input type="tel" id="modal-phone" name="phone" required
                            class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-2.5 text-[#0f172a] placeholder-[#64748b]/70 text-xs tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                            placeholder="e.g. +91 98765 43210">
                    </div>
                    <div class="space-y-1">
                        <label for="modal-whatsapp"
                            class="block text-[9px] tracking-wider uppercase text-[#64748b] font-bold">WhatsApp Number *</label>
                        <input type="tel" id="modal-whatsapp" name="whatsapp" required
                            class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-2.5 text-[#0f172a] placeholder-[#64748b]/70 text-xs tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                            placeholder="e.g. +91 98765 43210">
                    </div>
                </div>


                <!-- Category Sector Input (full width) -->
                <div class="space-y-1">
                    <label for="modal-category"
                        class="block text-[9px] tracking-wider uppercase text-[#64748b] font-bold">Category
                        Sector</label>
                    <div class="relative">
                        <select id="modal-category" name="category"
                            class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-3 py-2.5 text-[#0f172a] text-xs tracking-wide rounded-xl focus:outline-none transition-all duration-300 appearance-none">
                            <?php if (empty($db_categories)): ?>
                                <option value="Luxury Residential">Luxury Residential Sector</option>
                                <option value="Commercial Frameworks">Commercial Framework Sector</option>
                                <option value="Sustainable Fits">Sustainable Fits Sector</option>
                            <?php else: ?>
                                <?php foreach ($db_categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?> Sector</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="Other">Custom Consulting / Audit</option>
                        </select>
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                            ▼
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="modal-message"
                        class="block text-[9px] tracking-wider uppercase text-[#64748b] font-bold">Structural
                        Requirements / Scope</label>
                    <textarea id="modal-message" name="message" rows="3" required
                        class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-2.5 text-[#0f172a] placeholder-[#64748b]/70 text-xs tracking-wide rounded-xl focus:outline-none transition-all duration-300 resize-none"
                        placeholder="Outline your spatial dimensions..."></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" name="submit_inquiry"
                        class="w-full bg-[#00aff0] text-white hover:bg-[#009ece] px-6 py-3.5 text-[10px] font-semibold tracking-wide rounded-full transition-all duration-300 hover:shadow-md">
                        Submit Official Commission Package
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT FOR LOADING & SCROLLYTELLING CANVAS ENGINE -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('scrolly-canvas');
            const ctx = canvas.getContext('2d');
            const preloader = document.getElementById('preloader');
            const preloaderBar = document.getElementById('preloader-bar');
            const preloaderPercent = document.getElementById('preloader-percentage');
            const mainContent = document.getElementById('main-content');

            const frameCount = 151; // 151 images from 000 to 150
            const images = [];

            // Generate sequence frame file path based on zero-padded index
            const pad = (num, size) => num.toString().padStart(size, '0');
            const sequenceFolder = '<?php echo file_exists("assets/sequence") ? "assets/sequence" : "asset/sequence"; ?>';
            const getFramePath = (index) => `${sequenceFolder}/frame_${pad(index, 3)}_delay-0.066s.webp`;

            // State management
            let currentFrameIndex = 0;
            let targetFrameIndex = 0;
            let isAnimating = false;
            let imagesLoadedCount = 0;
            const hasLoadedBefore = sessionStorage.getItem('site_loaded') === 'true';

            // Instant preloader bypass for repeat visits
            if (hasLoadedBefore && preloader) {
                preloader.style.display = 'none';
                preloader.classList.add('opacity-0', 'pointer-events-none');
            }

            // Viewport Cover Scaling Logic (mimics object-fit: cover on canvas context)
            function drawFrame(index) {
                const img = images[Math.floor(index)];
                if (!img || !img.complete) return;

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                const imgWidth = img.naturalWidth || 1920;
                const imgHeight = img.naturalHeight || 1080;

                const canvasRatio = canvas.width / canvas.height;
                const imgRatio = imgWidth / imgHeight;

                let drawWidth, drawHeight, offsetX, offsetY;

                if (canvasRatio > imgRatio) {
                    // Canvas is wider than image aspect ratio
                    drawWidth = canvas.width;
                    drawHeight = canvas.width / imgRatio;
                    offsetX = 0;
                    offsetY = (canvas.height - drawHeight) / 2;
                } else {
                    // Canvas is taller than image aspect ratio
                    drawWidth = canvas.height * imgRatio;
                    drawHeight = canvas.height;
                    offsetX = (canvas.width - drawWidth) / 2;
                    offsetY = 0;
                }

                ctx.drawImage(img, offsetX, offsetY, drawWidth, drawHeight);
            }

            // Canvas size synchronization with aspect ratio logic
            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;

                // Redraw current frame to avoid white flash on resizing
                if (images.length === frameCount) {
                    drawFrame(currentFrameIndex);
                }
            }

            // Scroll calculation & throttle logic
            function handleScroll() {
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrollPercent = scrollHeight <= 0 ? 0 : scrollTop / scrollHeight;

                // Map percentage to target frame
                targetFrameIndex = Math.min(frameCount - 1, Math.max(0, scrollPercent * (frameCount - 1)));

                // Trigger smooth linear interpolation loop if not running
                if (!isAnimating) {
                    isAnimating = true;
                    requestAnimationFrame(smoothRenderLoop);
                }

                // Canvas rendering takes place automatically here
            }

            // Smooth linear interpolation (lerp) loop for background scrolling
            function smoothRenderLoop() {
                const diff = targetFrameIndex - currentFrameIndex;

                // If difference is non-trivial, lerp towards it
                if (Math.abs(diff) > 0.02) {
                    currentFrameIndex += diff * 0.12; // Smoothing factor (lower is slower/smoother)
                    drawFrame(currentFrameIndex);
                    requestAnimationFrame(smoothRenderLoop);
                } else {
                    currentFrameIndex = targetFrameIndex;
                    drawFrame(currentFrameIndex);
                    isAnimating = false; // Halt requestAnimationFrame when idle
                }
            }

            // Image preloader & caching sequence
            function preloadSequence() {
                // Determine critical frames (first 5 frames) to get the page loaded and visible immediately
                const criticalFrames = [0, 1, 2, 3, 4];
                const criticalPromises = [];

                // Pre-populate images array with placeholder Image objects to maintain index alignment
                for (let i = 0; i < frameCount; i++) {
                    images[i] = new Image();
                }

                // Helper to load a single frame
                function loadFrame(index) {
                    return new Promise((resolve) => {
                        const img = images[index];
                        img.onload = () => {
                            imagesLoadedCount++;
                            if (Math.floor(currentFrameIndex) === index) {
                                drawFrame(currentFrameIndex);
                            }
                            resolve(index);
                        };
                        img.onerror = () => {
                            imagesLoadedCount++;
                            resolve(index);
                        };
                        img.src = getFramePath(index);
                    });
                }

                // Start loading critical frames
                criticalFrames.forEach(idx => {
                    criticalPromises.push(loadFrame(idx));
                });

                // Smooth progress animation for preloader percentage
                let currentPercent = 0;
                let preloaderInterval = null;

                function updatePreloaderProgress() {
                    if (hasLoadedBefore) return;

                    const criticalLoadedCount = criticalFrames.filter(idx => images[idx].complete).length;
                    const criticalPercent = (criticalLoadedCount / criticalFrames.length) * 100;

                    let targetPercent = Math.min(99, Math.floor(criticalPercent));
                    if (criticalLoadedCount === criticalFrames.length) {
                        targetPercent = 100;
                    }

                    if (currentPercent < targetPercent) {
                        currentPercent += Math.ceil((targetPercent - currentPercent) * 0.1);
                        if (currentPercent > targetPercent) currentPercent = targetPercent;
                        preloaderBar.style.width = `${currentPercent}%`;
                        preloaderPercent.innerText = `${currentPercent}%`;
                    }

                    if (currentPercent >= 100) {
                        clearInterval(preloaderInterval);
                        onPreloaderComplete();
                    }
                }

                if (!hasLoadedBefore) {
                    preloaderInterval = setInterval(updatePreloaderProgress, 50);
                }

                function onPreloaderComplete() {
                    setTimeout(() => {
                        if (preloader) {
                            preloader.classList.add('opacity-0', 'pointer-events-none');
                        }

                        canvas.classList.remove('opacity-0');
                        mainContent.classList.remove('opacity-0');

                        resizeCanvas();
                        drawFrame(0);

                        generatePath();
                        updateTimelineScroll();

                        window.addEventListener('scroll', handleScroll, { passive: true });

                        sessionStorage.setItem('site_loaded', 'true');

                        setTimeout(openInquiryModal, 400);
                    }, 300);
                }

                if (hasLoadedBefore) {
                    canvas.classList.remove('opacity-0');
                    mainContent.classList.remove('opacity-0');
                    resizeCanvas();
                    drawFrame(0);
                    generatePath();
                    updateTimelineScroll();
                    window.addEventListener('scroll', handleScroll, { passive: true });

                    if (!sessionStorage.getItem('inquiry_modal_dismissed')) {
                        setTimeout(openInquiryModal, 400);
                    }
                }

                // Wait for critical frames, then load the remaining sequence frames in the background
                Promise.all(criticalPromises).then(() => {
                    // Force complete preloader progress bar if still running
                    if (!hasLoadedBefore && currentPercent < 100) {
                        clearInterval(preloaderInterval);
                        let finishPercent = currentPercent;
                        const finishInterval = setInterval(() => {
                            finishPercent += 5;
                            if (finishPercent >= 100) {
                                finishPercent = 100;
                                clearInterval(finishInterval);
                                preloaderBar.style.width = '100%';
                                preloaderPercent.innerText = '100%';
                                onPreloaderComplete();
                            } else {
                                preloaderBar.style.width = `${finishPercent}%`;
                                preloaderPercent.innerText = `${finishPercent}%`;
                            }
                        }, 30);
                    }

                    // Background load all remaining frames with concurrency limit of 3
                    const queue = [];
                    for (let i = 0; i < frameCount; i++) {
                        if (!criticalFrames.includes(i)) {
                            queue.push(i);
                        }
                    }

                    const CONCURRENCY_LIMIT = 3;
                    let activeConnections = 0;

                    function processQueue() {
                        while (queue.length > 0 && activeConnections < CONCURRENCY_LIMIT) {
                            const nextFrame = queue.shift();
                            activeConnections++;
                            loadFrame(nextFrame).then(() => {
                                activeConnections--;
                                processQueue();
                            });
                        }
                    }

                    processQueue();
                });
            }

            // Inquiry Modal Controls
            const inquiryModal = document.getElementById('load-inquiry-modal');
            const closeInquiryBtn = document.getElementById('close-load-inquiry-btn');
            const continueToSiteBtn = document.getElementById('continue-to-site-btn');

            function openInquiryModal() {
                if (!inquiryModal) return;
                inquiryModal.classList.remove('pointer-events-none', 'opacity-0');
                const innerContainer = inquiryModal.querySelector('.transform');
                if (innerContainer) {
                    innerContainer.classList.remove('scale-95');
                    innerContainer.classList.add('scale-100');
                }
                document.body.classList.add('overflow-hidden');
            }

            function closeInquiryModal() {
                if (!inquiryModal) return;
                inquiryModal.classList.add('pointer-events-none', 'opacity-0');
                const innerContainer = inquiryModal.querySelector('.transform');
                if (innerContainer) {
                    innerContainer.classList.remove('scale-100');
                    innerContainer.classList.add('scale-95');
                }
                document.body.classList.remove('overflow-hidden');
                sessionStorage.setItem('inquiry_modal_dismissed', 'true');
            }

            if (closeInquiryBtn) {
                closeInquiryBtn.addEventListener('click', closeInquiryModal);
            }
            if (continueToSiteBtn) {
                continueToSiteBtn.addEventListener('click', closeInquiryModal);
            }

            // Close modal when clicking outside the modal content container
            if (inquiryModal) {
                inquiryModal.addEventListener('click', (e) => {
                    if (e.target === inquiryModal) {
                        closeInquiryModal();
                    }
                });
            }

            // Accessibility: Escape key closes the modal
            document.addEventListener('keydown', (e) => {
                if (inquiryModal && !inquiryModal.classList.contains('opacity-0') && e.key === 'Escape') {
                    closeInquiryModal();
                }
            });

            // Initialize Event listeners
            window.addEventListener('resize', resizeCanvas);

            // Dynamic Header Styling & Scrollspy on Scroll
            const mainHeader = document.getElementById('main-header');
            const headerLogo = document.getElementById('header-logo');
            const progress = document.getElementById('scroll-progress-bar');
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('#main-header nav a[href^="#"]');

            function updateHeaderScroll() {
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;

                if (scrollTop > 80) {
                    mainHeader.classList.add('bg-white/85', 'h-24', 'border-[#e2e8f0]', 'shadow-sm');
                    mainHeader.classList.remove('bg-white/72', 'h-28', 'border-[#e2e8f0]/40');
                    if (headerLogo) {
                        headerLogo.classList.add('h-28');
                        headerLogo.classList.remove('h-36');
                    }
                } else {
                    mainHeader.classList.add('bg-white/72', 'h-28', 'border-[#e2e8f0]/40');
                    mainHeader.classList.remove('bg-white/85', 'h-24', 'border-[#e2e8f0]', 'shadow-sm');
                    if (headerLogo) {
                        headerLogo.classList.add('h-36');
                        headerLogo.classList.remove('h-28');
                    }
                }

                if (progress && scrollHeight > 0) {
                    const percent = (scrollTop / scrollHeight) * 100;
                    progress.style.width = percent + '%';
                }

                // Scrollspy active section highlighting
                sections.forEach(current => {
                    const sectionHeight = current.offsetHeight;
                    const sectionTop = current.offsetTop - 150; // Offset for smooth matching
                    const sectionId = current.getAttribute('id');

                    if (scrollTop > sectionTop && scrollTop <= sectionTop + sectionHeight) {
                        navLinks.forEach(link => {
                            if (link.getAttribute('href') === '#' + sectionId) {
                                link.classList.add('text-[#00aff0]', 'font-semibold');
                                link.classList.remove('text-[#0f172a]/80');
                            } else {
                                link.classList.remove('text-[#00aff0]', 'font-semibold');
                                link.classList.add('text-[#0f172a]/80');
                            }
                        });
                    }
                });
            }
            window.addEventListener('scroll', () => {
                updateHeaderScroll();
                updateTimelineScroll();
            }, { passive: true });

            // Timeline Data definition
            const timelineData = [
                {
                    title: "Consultation",
                    subtitle: "Diagnostic Geometries",
                    desc: "Complete laser terrain mapping and geologic core diagnostics. We simulate natural light profiles and solar tracking metrics across seasons before structural drafts commence."
                },
                {
                    title: "Design & Planning",
                    subtitle: "Heavy Framework",
                    desc: "Laying massive reinforced foundational grids and architectural steel support frameworks. Concrete is mixed with high-strength binders and poured into custom hand-crafted timber forms for tactile texturing."
                },
                {
                    title: "Construction",
                    subtitle: "Structural Glass & Textures",
                    desc: "Installation of oversized, low-iron structural glass paneling and outfitting interiors with hand-brushed metals, local travertine stone, and reclaimed wood frameworks."
                },
                {
                    title: "Handover",
                    subtitle: "Commissioning & Delivery",
                    desc: "Every mechanical system undergoes comprehensive thermal efficiency runs prior to commissioning, ensuring a seamless structural and thermodynamic delivery to our clients."
                }
            ];

            const timelineScrollTrack = document.getElementById('timeline-scroll-track');
            const timelineCanvas = document.getElementById('timeline-canvas-container');
            const bgPath = document.getElementById('timeline-bg-path');
            const fgPath = document.getElementById('timeline-fg-path');
            const maskPath = document.getElementById('timeline-mask-path');

            function generatePath() {
                if (!timelineCanvas || !bgPath || !fgPath || !maskPath) return;

                const c1 = document.getElementById('circle-1');
                const c2 = document.getElementById('circle-2');
                const c3 = document.getElementById('circle-3');
                const c4 = document.getElementById('circle-4');

                if (!c1 || !c2 || !c3 || !c4) return;

                const getCenter = (el) => {
                    return {
                        x: el.offsetLeft + el.offsetWidth / 2,
                        y: el.offsetTop + el.offsetHeight / 2
                    };
                };

                const p1 = getCenter(c1);
                const p2 = getCenter(c2);
                const p3 = getCenter(c3);
                const p4 = getCenter(c4);

                // Custom wavy curve calculations matching the hand-drawn sketch layout (Z-shape)
                const waveAmp = Math.min(window.innerWidth * 0.08, 80);
                const dx12 = p2.x - p1.x;
                const dx23 = p3.x - p2.x;
                const dy23 = p3.y - p2.y;
                const dx34 = p4.x - p3.x;

                // Midpoint between Circle 1 (top left) and Circle 2 (top right) - offset up
                const pm12 = {
                    x: (p1.x + p2.x) / 2,
                    y: (p1.y + p2.y) / 2 - waveAmp * 0.7
                };

                // Midpoint between Circle 3 (bottom left) and Circle 4 (bottom right) - offset up
                const pm34 = {
                    x: (p3.x + p4.x) / 2,
                    y: (p3.y + p4.y) / 2 - waveAmp * 0.7
                };

                const d = `M ${p1.x} ${p1.y} 
                           C ${p1.x + dx12 * 0.15} ${p1.y - waveAmp * 0.4}, ${pm12.x - dx12 * 0.15} ${pm12.y}, ${pm12.x} ${pm12.y}
                           C ${pm12.x + dx12 * 0.15} ${pm12.y}, ${p2.x - dx12 * 0.15} ${p2.y + waveAmp * 0.4}, ${p2.x} ${p2.y}
                           C ${p2.x + dx23 * 0.35} ${p2.y + dy23 * 0.15}, ${p3.x - dx23 * 0.35} ${p3.y - dy23 * 0.15}, ${p3.x} ${p3.y}
                           C ${p3.x + dx34 * 0.15} ${p3.y - waveAmp * 0.4}, ${pm34.x - dx34 * 0.15} ${pm34.y}, ${pm34.x} ${pm34.y}
                           C ${pm34.x + dx34 * 0.15} ${pm34.y}, ${p4.x - dx34 * 0.15} ${p4.y + waveAmp * 0.4}, ${p4.x} ${p4.y}`;

                bgPath.setAttribute('d', d);
                fgPath.setAttribute('d', d);
                maskPath.setAttribute('d', d);

                const length = maskPath.getTotalLength();
                maskPath.style.strokeDasharray = length;
            }

            function updateTimelineScroll() {
                if (!timelineScrollTrack) return;

                const rect = timelineScrollTrack.getBoundingClientRect();
                const viewHeight = window.innerHeight;

                const totalScrollable = rect.height - viewHeight;
                const scrolled = -rect.top;

                let progress = scrolled / totalScrollable;
                progress = Math.min(1, Math.max(0, progress));

                if (maskPath) {
                    const length = maskPath.getTotalLength();
                    maskPath.style.strokeDashoffset = length * (1 - progress);
                }

                // Determine active index based on progress thresholds
                let activeIndex = 0;
                if (progress >= 0.9) {
                    activeIndex = 3;
                } else if (progress >= 0.6) {
                    activeIndex = 2;
                } else if (progress >= 0.3) {
                    activeIndex = 1;
                } else {
                    activeIndex = 0;
                }

                const circles = [
                    { el: document.getElementById('circle-1'), threshold: 0.0 },
                    { el: document.getElementById('circle-2'), threshold: 0.3 },
                    { el: document.getElementById('circle-3'), threshold: 0.6 },
                    { el: document.getElementById('circle-4'), threshold: 0.9 }
                ];

                circles.forEach((circle, index) => {
                    if (!circle.el) return;
                    const num = circle.el.querySelector('.circle-num');

                    if (progress >= circle.threshold) {
                        circle.el.classList.add('border-[#00aff0]', 'bg-white', 'shadow-[0_0_30px_rgba(0,175,240,0.15)]', 'scale-105', 'z-20');
                        circle.el.classList.remove('border-slate-200', 'bg-white/90', 'scale-100', 'z-10');
                        if (num) {
                            num.classList.add('text-[#00aff0]');
                            num.classList.remove('text-slate-300');
                        }
                    } else {
                        circle.el.classList.remove('border-[#00aff0]', 'shadow-[0_0_30px_rgba(0,175,240,0.15)]', 'scale-105', 'z-20');
                        circle.el.classList.add('border-slate-200', 'bg-white/90', 'scale-100', 'z-10');
                        if (num) {
                            num.classList.remove('text-[#00aff0]');
                            num.classList.add('text-slate-300');
                        }
                    }
                });

                // Update details text panel dynamically
                const detailTitle = document.getElementById('timeline-detail-title');
                const detailSubtitle = document.getElementById('timeline-detail-subtitle');
                const detailDesc = document.getElementById('timeline-detail-desc');
                const detailPanel = document.getElementById('timeline-detail-panel');

                if (detailPanel && detailPanel.dataset.active !== String(activeIndex)) {
                    detailPanel.dataset.active = activeIndex;
                    detailPanel.classList.add('opacity-0', 'translate-y-2');

                    setTimeout(() => {
                        const data = timelineData[activeIndex];
                        if (detailTitle) detailTitle.innerText = `${activeIndex + 1} / ${data.title}`;
                        if (detailSubtitle) detailSubtitle.innerText = data.subtitle;
                        if (detailDesc) detailDesc.innerText = data.desc;
                        detailPanel.classList.remove('opacity-0', 'translate-y-2');
                    }, 150);
                }
            }

            // Sync layout on resize
            window.addEventListener('resize', () => {
                resizeCanvas();
                generatePath();
                updateTimelineScroll();
            });

            updateHeaderScroll();
            updateTimelineScroll();

            // Stats count-up animation with IntersectionObserver
            const statNumbers = document.querySelectorAll('.stat-number');
            const statsSection = document.getElementById('stats');

            if (statsSection && statNumbers.length > 0) {
                const animateStats = () => {
                    statNumbers.forEach((stat, index) => {
                        const card = stat.closest('.apple-card');
                        const target = parseInt(stat.getAttribute('data-target'), 10);
                        const suffix = stat.getAttribute('data-suffix') || '';

                        // Set starting state
                        stat.innerText = '0' + suffix;

                        // Stagger the activation of each card's entrance and counter
                        setTimeout(() => {
                            if (card) {
                                card.classList.remove('opacity-0', 'translate-y-8');
                                card.classList.add('opacity-100', 'translate-y-0', 'card-animating');
                            }

                            const duration = 1600; // 1.6 seconds count-up duration
                            const startTime = performance.now();

                            const updateCount = (timestamp) => {
                                const elapsed = timestamp - startTime;
                                const progress = Math.min(elapsed / duration, 1);

                                // Ease-out quad formula
                                const easeProgress = progress * (2 - progress);
                                const currentVal = Math.floor(easeProgress * target);

                                stat.innerText = currentVal + suffix;

                                if (progress < 1) {
                                    requestAnimationFrame(updateCount);
                                } else {
                                    stat.innerText = target + suffix;
                                    // Settle card styling after animation completes
                                    if (card) {
                                        card.classList.remove('card-animating');
                                    }
                                }
                            };

                            requestAnimationFrame(updateCount);
                        }, index * 200); // 200ms stagger between card load-ins
                    });
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            animateStats();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15 });

                observer.observe(statsSection);
            }


            // Client Testimonials Scroll and Drag Controller
            const testimonialsContainer = document.getElementById('testimonials-scroll-container');
            if (testimonialsContainer) {
                const cards = testimonialsContainer.children;
                const N = <?php echo count($db_testimonials); ?>;
                
                if (cards.length >= N * 3 && N > 0) {
                    let W = 0;
                    function calculateWidth() {
                        W = cards[N].getBoundingClientRect().left - cards[0].getBoundingClientRect().left;
                    }
                    calculateWidth();
                    window.addEventListener('resize', calculateWidth);

                    // Initial scroll position in the middle to support infinite scrolling in both directions
                    testimonialsContainer.scrollLeft = W;

                    let isDragging = false;
                    let startX = 0;
                    let scrollLeftStart = 0;
                    let autoScrollSpeed = 0.6; // pixels per frame
                    let currentDirection = 1; // 1 = scroll right (content left), -1 = scroll left
                    let autoScrollPaused = false;
                    let lastX = 0;
                    let lastTime = 0;
                    let velocity = 0;

                    // Wrapping scroll position infinitely
                    function checkWrap() {
                        if (testimonialsContainer.scrollLeft >= W * 2) {
                            testimonialsContainer.scrollLeft -= W;
                        } else if (testimonialsContainer.scrollLeft <= 0) {
                            testimonialsContainer.scrollLeft += W;
                        }
                    }

                    // Auto-scroll loop
                    function animate() {
                        if (!isDragging && !autoScrollPaused) {
                            testimonialsContainer.scrollLeft += autoScrollSpeed * currentDirection;
                            checkWrap();
                        }
                        requestAnimationFrame(animate);
                    }

                    // Wrap detection during manual scrolling (touch/trackpad/etc.)
                    testimonialsContainer.addEventListener('scroll', () => {
                        if (!isDragging) {
                            checkWrap();
                        }
                    });

                    // Mouse down - Start Dragging
                    testimonialsContainer.addEventListener('mousedown', (e) => {
                        isDragging = true;
                        testimonialsContainer.classList.add('cursor-grabbing');
                        testimonialsContainer.classList.remove('cursor-grab');
                        startX = e.pageX - testimonialsContainer.offsetLeft;
                        scrollLeftStart = testimonialsContainer.scrollLeft;
                        lastX = e.pageX;
                        lastTime = performance.now();
                        velocity = 0;
                    });

                    // Mouse move - Drag
                    window.addEventListener('mousemove', (e) => {
                        if (!isDragging) return;
                        const x = e.pageX - testimonialsContainer.offsetLeft;
                        const walk = (x - startX) * 1.2; // Drag multiplier
                        testimonialsContainer.scrollLeft = scrollLeftStart - walk;

                        // Wrap and adjust scroll start dynamically to prevent snap bugs
                        if (testimonialsContainer.scrollLeft >= W * 2) {
                            testimonialsContainer.scrollLeft -= W;
                            scrollLeftStart -= W;
                        } else if (testimonialsContainer.scrollLeft <= 0) {
                            testimonialsContainer.scrollLeft += W;
                            scrollLeftStart += W;
                        }

                        // Calculate drag speed for velocity
                        const now = performance.now();
                        const dt = now - lastTime;
                        if (dt > 0) {
                            const dx = e.pageX - lastX;
                            velocity = dx / dt;
                        }
                        lastX = e.pageX;
                        lastTime = now;
                    });

                    // Mouse up - End Dragging
                    window.addEventListener('mouseup', () => {
                        if (!isDragging) return;
                        isDragging = false;
                        testimonialsContainer.classList.remove('cursor-grabbing');
                        testimonialsContainer.classList.add('cursor-grab');

                        // Set direction based on last drag velocity direction
                        if (velocity < -0.1) {
                            currentDirection = 1; // Dragged left -> scroll right (content left)
                        } else if (velocity > 0.1) {
                            currentDirection = -1; // Dragged right -> scroll left (content right)
                        }
                    });

                    // Hover States
                    testimonialsContainer.addEventListener('mouseenter', () => {
                        autoScrollPaused = true;
                    });

                    testimonialsContainer.addEventListener('mouseleave', () => {
                        autoScrollPaused = false;
                        if (isDragging) {
                            isDragging = false;
                            testimonialsContainer.classList.remove('cursor-grabbing');
                            testimonialsContainer.classList.add('cursor-grab');
                        }
                    });

                    // Touch events for mobile compatibility
                    testimonialsContainer.addEventListener('touchstart', (e) => {
                        isDragging = true;
                        startX = e.touches[0].pageX - testimonialsContainer.offsetLeft;
                        scrollLeftStart = testimonialsContainer.scrollLeft;
                        lastX = e.touches[0].pageX;
                        lastTime = performance.now();
                        velocity = 0;
                    }, { passive: true });

                    testimonialsContainer.addEventListener('touchmove', (e) => {
                        if (!isDragging) return;
                        const x = e.touches[0].pageX - testimonialsContainer.offsetLeft;
                        const walk = (x - startX) * 1.2;
                        testimonialsContainer.scrollLeft = scrollLeftStart - walk;

                        if (testimonialsContainer.scrollLeft >= W * 2) {
                            testimonialsContainer.scrollLeft -= W;
                            scrollLeftStart -= W;
                        } else if (testimonialsContainer.scrollLeft <= 0) {
                            testimonialsContainer.scrollLeft += W;
                            scrollLeftStart += W;
                        }

                        const now = performance.now();
                        const dt = now - lastTime;
                        if (dt > 0) {
                            const dx = e.touches[0].pageX - lastX;
                            velocity = dx / dt;
                        }
                        lastX = e.touches[0].pageX;
                        lastTime = now;
                    }, { passive: true });

                    testimonialsContainer.addEventListener('touchend', () => {
                        isDragging = false;
                        if (velocity < -0.1) {
                            currentDirection = 1;
                        } else if (velocity > 0.1) {
                            currentDirection = -1;
                        }
                    });

                    // Start loop
                    animate();
                }
            }

            // Kick off preloading sequence
            preloadSequence();
        });
    </script>
</body>

</html>
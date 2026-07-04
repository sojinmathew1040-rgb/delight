<?php
// ==========================================
// DELIGHT BUILDERS - PORTFOLIO COMPREHENSIVE
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
$site_title = get_setting('site_title', 'Delight Builders | Comprehensive Portfolio of Works');
$logo_path = resolve_asset_path(get_setting('logo_path', 'asset/images/logo.png'));
$is_gallery = true; // Use the simple header configuration
$title_indicator = "PORTFOLIO";
$back_to_section = "#portfolio";

// Fetch portfolio projects
try {
    $stmt = $pdo->query("SELECT * FROM portfolio ORDER BY id DESC");
    $portfolio = $stmt->fetchAll();
} catch (PDOException $e) {
    $portfolio = [];
}

// Fetch categories for filtering
try {
    $stmt_cats = $pdo->query("SELECT name FROM portfolio_categories ORDER BY name ASC");
    $categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = ['Luxury Residential', 'Commercial Frameworks', 'Sustainable Fits'];
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-[#f8fafc]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delight Builders | Comprehensive Portfolio of Works</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logo_path); ?>">

    <!-- Meta tags for SEO -->
    <meta name="description"
        content="Explore the complete collection of luxury estates, commercial frameworks, and sustainable custom structures by Delight Builders.">
    <meta name="keywords"
        content="Luxury construction, premium residential, architectural frameworks, sustainable fits, complete portfolio">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit (premium $100k-site typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
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

    <!-- Custom styling -->
    <link rel="stylesheet" href="asset/css/style.css?v=<?php echo time(); ?>">
</head>

<body
    class="font-sans text-[#0f172a]/85 relative overflow-x-hidden min-h-screen bg-[#f8fafc] selection:bg-[#00aff0]/10 selection:text-[#00aff0]">

    <!-- LIGHT PASTEL BLENDING OVERLAY -->
    <div class="fixed inset-0 -z-10 bg-white/80 pointer-events-none"></div>

    <!-- STATIC GRID OVERLAY -->
    <div class="fixed inset-0 grid grid-cols-4 pointer-events-none z-0">
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="h-full"></div>
    </div>

    <!-- MAIN WRAPPER -->
    <div class="relative z-10 flex flex-col min-h-screen justify-between">

        <!-- HEADER NAVIGATION BAR -->
        <?php include 'header.php'; ?>

        <!-- MAIN PORTFOLIO CONTAINER -->
        <main class="flex-grow py-20 px-6 md:px-16 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="text-center space-y-4 mb-16 mt-12">
                    <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">03 / Works Archive</span>
                    <h1 class="font-display text-4xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">
                        Our Comprehensive Portfolio
                    </h1>
                    <p class="text-sm text-[#0f172a]/60 max-w-xl mx-auto tracking-normal font-normal">
                        Explore our structural milestones, from brutalist estates to sustainable timber designs. Filter by sector or click to launch the blueprint galleries.
                    </p>
                </div>

                <!-- Category Filters - styled with classy Apple active tags -->
                <div
                    class="flex flex-wrap justify-center items-center gap-3 mb-16 text-xs tracking-tight font-semibold">
                    <button type="button" data-filter="all"
                        class="portfolio-filter-btn px-5 py-2.5 bg-[#0f172a] text-white rounded-full transition-all duration-300 focus:outline-none">
                        All Works (<?php echo count($portfolio); ?>)
                    </button>
                    <?php foreach ($categories as $cat): ?>
                        <button type="button" data-filter="<?php echo htmlspecialchars($cat); ?>"
                            class="portfolio-filter-btn px-5 py-2.5 bg-[#f8fafc] border border-[#e2e8f0] text-[#0f172a]/80 hover:bg-[#e2e8f0] rounded-full transition-all duration-300 focus:outline-none">
                            <?php echo htmlspecialchars($cat); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Comprehensive Works Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="portfolio-grid">
                    <?php foreach ($portfolio as $index => $project):
                        $image_path = resolve_asset_path($project['image']);

                        // Select badge color based on category sector
                        $badge_class = "border-[#00aff0]/20 text-[#00aff0] bg-[#00aff0]/5";
                        if ($project['category'] === 'Luxury Residential') {
                            $badge_class = "border-[#ec3237]/20 text-[#ec3237] bg-[#ec3237]/5";
                        }
                        ?>
                        <a href="project.php?id=<?php echo $project['id']; ?>" class="portfolio-item-card group relative bg-white border border-[#e2e8f0] overflow-hidden flex flex-col justify-between transition-all duration-500 rounded-2xl block shadow-[0_8px_30px_rgba(0,0,0,0.03)] apple-card"
                            data-category="<?php echo $project['category']; ?>">
                            
                            <!-- Image Container -->
                            <div class="relative overflow-hidden aspect-[4/3] bg-[#f8fafc]">
                                <img src="<?php echo $image_path; ?>" alt="<?php echo $project['title']; ?>"
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
                                    <p class="text-[#0f172a]/70 text-sm leading-relaxed font-normal mt-2">
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
        </main>

        <!-- FOOTER SECTION -->
        <footer class="bg-[#f8fafc]/95 py-16 px-6 md:px-16 border-t border-[#e2e8f0]/60 z-20 relative">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
                <!-- Branding -->
                <div class="flex flex-col items-center md:items-start space-y-3">
                    <img src="<?php echo $logo_path; ?>" alt="Delight Builders Logo" class="h-16 w-auto object-contain">
                    <span class="font-display text-[#0f172a] font-extrabold tracking-widest text-base uppercase">DELIGHT BUILDERS</span>
                    <span class="text-[10px] tracking-widest text-[#64748b] font-semibold uppercase">STRUCTURAL POETRY • SINCE 2006</span>
                </div>

                <!-- Copyright -->
                <div class="text-[11px] tracking-normal text-[#64748b] text-center md:text-right space-y-1 font-normal">
                    <p>© <?php echo date("Y"); ?> Delight Builders Inc. All architectural frameworks reserved.</p>
                    <p>Designed for premium longevity. Engineered to absolute structural coordinates.</p>
                </div>
            </div>
        </footer>

    </div>

    <!-- SCRIPT FOR FILTERING WORKS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterBtns = document.querySelectorAll('.portfolio-filter-btn');
            const portfolioItems = document.querySelectorAll('.portfolio-item-card');

            filterBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const filterValue = btn.getAttribute('data-filter');

                    filterBtns.forEach((b) => {
                        b.classList.remove('bg-[#0f172a]', 'text-white');
                        b.classList.add('bg-[#f8fafc]', 'border', 'border-[#e2e8f0]', 'text-[#0f172a]/80');
                    });

                    btn.classList.remove('bg-[#f8fafc]', 'border', 'border-[#e2e8f0]', 'text-[#0f172a]/80');
                    btn.classList.add('bg-[#0f172a]', 'text-white');

                    portfolioItems.forEach((item) => {
                        const itemCategory = item.getAttribute('data-category');

                        if (filterValue === 'all' || itemCategory === filterValue) {
                            item.classList.remove('hidden');
                            setTimeout(() => {
                                item.style.opacity = '1';
                                item.style.transform = 'scale(1)';
                            }, 50);
                        } else {
                            item.style.opacity = '0';
                            item.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                item.classList.add('hidden');
                            }, 300);
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>

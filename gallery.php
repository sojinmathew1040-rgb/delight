<?php
// ==========================================
// DELIGHT BUILDERS - PORTFOLIO DATA CONFIG
// Mapping all photos dynamically from database
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

// Fetch logo path from settings
$logo_path = resolve_asset_path(get_setting('logo_path', 'asset/images/logo.png'));
$is_gallery = true;
$back_to_section = "#gallery";
$title_indicator = "ARCHIVES";

// Fetch portfolio projects and their gallery images
$portfolio = [];
try {
    $stmt = $pdo->query("SELECT * FROM portfolio ORDER BY id ASC");
    $db_portfolio = $stmt->fetchAll();
    
    foreach ($db_portfolio as $proj) {
        $stmt_gal = $pdo->prepare("SELECT src, title, desc_text as `desc`, stage, materiality FROM portfolio_gallery WHERE portfolio_id = ? AND (show_in_gallery = 1 OR show_in_gallery IS NULL) ORDER BY id ASC");
        $stmt_gal->execute([$proj['id']]);
        $db_gallery = $stmt_gal->fetchAll();
        
        $portfolio[] = [
            "id" => $proj['id'],
            "title" => $proj['title'],
            "category" => $proj['category'],
            "description" => $proj['description'],
            "image" => $proj['image'],
            "stage" => $proj['stage'] ?? null,
            "materiality" => $proj['materiality'] ?? null,
            "gallery" => $db_gallery
        ];
    }
} catch (PDOException $e) {
    $portfolio = [];
}

// Fetch categories for filtering
try {
    $stmt_cats = $pdo->query("SELECT name FROM portfolio_categories ORDER BY name ASC");
    $categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}

// Flat list of all photos for the grid Gallery Page
$all_gallery_photos = [];
foreach ($portfolio as $project_idx => $project) {
    foreach ($project['gallery'] as $img_idx => $img) {
        $all_gallery_photos[] = [
            "project_index" => $project_idx,
            "image_index" => $img_idx,
            "src" => $img['src'],
            "title" => $img['title'],
            "desc" => $img['desc'],
            "category" => $project['category']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-[#f8fafc]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delight Builders | Architectural Blueprints Archive</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logo_path); ?>">

    <!-- Meta tags for SEO -->
    <meta name="description"
        content="Browse Delight Builders' visual archive of luxury residential, commercial frameworks, and sustainable design components.">
    <meta name="keywords"
        content="Luxury construction, premium residential, architectural frameworks, sustainable fits, blueprint gallery">

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
    <link rel="stylesheet" href="asset/css/style.css">
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

        <!-- MAIN GALLERY CONTAINER -->
        <main class="flex-grow py-20 px-6 md:px-16 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="text-center space-y-4 mb-16">
                    <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">04 / Blueprint Record</span>
                    <h1 class="font-display text-4xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">
                        Architectural Imagery
                    </h1>
                    <p class="text-sm text-[#0f172a]/60 max-w-xl mx-auto tracking-normal font-normal">
                        A comprehensive catalog of all <?php echo count($all_gallery_photos); ?> construction frameworks. Select any card to open the
                        fullscreen blueprints detailed description.
                    </p>
                </div>

                <!-- Category Filters - styled with classy Apple active tags -->
                <div
                    class="flex flex-wrap justify-center items-center gap-3 mb-16 text-xs tracking-tight font-semibold">
                    <button type="button" data-filter="all"
                        class="gallery-filter-btn px-5 py-2.5 bg-[#0f172a] text-white rounded-full transition-all duration-300 focus:outline-none">
                        All Blueprints (<?php echo count($all_gallery_photos); ?>)
                    </button>
                    <?php foreach ($categories as $cat): 
                        $display_name = str_ireplace(['Luxury ', ' Frameworks', ' Fits', ' Sector'], '', $cat);
                    ?>
                        <button type="button" data-filter="<?php echo htmlspecialchars($cat); ?>"
                            class="gallery-filter-btn px-5 py-2.5 bg-[#f8fafc] border border-[#e2e8f0] text-[#0f172a]/80 hover:bg-[#e2e8f0] rounded-full transition-all duration-300 focus:outline-none">
                            <?php echo htmlspecialchars($display_name); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- 28-Photo Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="gallery-grid">
                    <?php foreach ($all_gallery_photos as $photo):
                        $resolved_src = resolve_asset_path($photo['src']);
                        ?>
                        <div class="gallery-item cursor-pointer group relative overflow-hidden aspect-square border border-[#e2e8f0] rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-md transition-all duration-500 bg-[#f8fafc]"
                            data-project="<?php echo $photo['project_index']; ?>"
                            data-image="<?php echo $photo['image_index']; ?>"
                            data-category="<?php echo $photo['category']; ?>">

                            <!-- Gallery Image -->
                            <img src="<?php echo $resolved_src; ?>" alt="<?php echo $photo['title']; ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 filter brightness-95 group-hover:brightness-100 ease-in-out">

                            <!-- Dynamic Overlay details on hover -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-4 flex flex-col justify-end rounded-2xl">
                                <span class="text-[9px] tracking-wider text-[#00aff0] uppercase font-bold mb-1">
                                    <?php echo $photo['category']; ?>
                                </span>
                                <h4
                                    class="font-display text-xs text-white uppercase tracking-normal font-bold leading-tight mb-1 transition-colors">
                                    <?php echo $photo['title']; ?>
                                </h4>
                                <p class="text-[10px] text-[#f8fafc]/80 font-normal truncate">
                                    <?php echo $photo['desc']; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <!-- GALLERY DETAIL VIEW MODAL (SEPARATE VIEW) -->
        <div id="gallery-modal"
            class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-lg flex items-center justify-center hidden transition-opacity duration-500 opacity-0">
            
            <!-- Close Button -->
            <button id="close-modal-btn" aria-label="Close Lightbox"
                class="absolute top-6 right-6 z-50 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 transition-all duration-300 w-11 h-11 rounded-full flex items-center justify-center font-semibold text-lg border border-white/10 focus:outline-none">
                ✕
            </button>

            <!-- Navigation Arrow Left -->
            <button id="prev-image-btn" aria-label="Previous Image"
                class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 text-white/70 hover:text-white flex items-center justify-center transition-all duration-300 shadow-lg focus:outline-none z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7">
                    </path>
                </svg>
            </button>

            <!-- Active Image Container -->
            <div class="max-w-[90vw] max-h-[80vh] flex justify-center items-center">
                <img id="modal-active-image" src="" alt="Active Space"
                    class="max-h-[75vh] w-auto max-w-full object-contain transition-opacity duration-300 rounded-xl shadow-2xl border border-white/5 bg-slate-900/50">
            </div>

            <!-- Navigation Arrow Right -->
            <button id="next-image-btn" aria-label="Next Image"
                class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full border border-white/10 bg-white/5 hover:bg-white/10 text-white/70 hover:text-white flex items-center justify-center transition-all duration-300 shadow-lg focus:outline-none z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7">
                    </path>
                </svg>
            </button>

            <!-- Bottom Caption & Counter -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-center text-white/95 z-20 space-y-1.5 w-full max-w-xl px-6">
                <span id="modal-image-counter"
                    class="text-[10px] tracking-[0.15em] text-[#00aff0] uppercase font-bold block">IMAGE 1 OF 10</span>
                <h3 id="modal-image-title"
                    class="font-display text-base md:text-lg font-bold tracking-tight leading-snug">Image Title</h3>
                <p id="modal-image-desc" class="text-xs text-white/60 font-normal leading-relaxed truncate max-w-md mx-auto">
                    Image description goes here.
                </p>
            </div>
        </div>

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

    <!-- SCRIPT FOR DYNAMIC FILTER GRID & GALLERY MODAL INTERACTION -->
    <script>
        const portfolioData = <?php echo json_encode($portfolio); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('gallery-modal');
            const modalActiveImg = document.getElementById('modal-active-image');
            const modalImgCounter = document.getElementById('modal-image-counter');
            const modalImgTitle = document.getElementById('modal-image-title');
            const modalImgDesc = document.getElementById('modal-image-desc');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const prevImgBtn = document.getElementById('prev-image-btn');
            const nextImgBtn = document.getElementById('next-image-btn');

            let activeProjectIdx = 0;
            let activeImgIdx = 0;

            function openGalleryModal(projectIdx, imageIdx = 0) {
                activeProjectIdx = projectIdx;
                activeImgIdx = imageIdx;

                updateModalImage();

                // Show modal with animation
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                }, 50);

                document.body.classList.add('overflow-hidden');
            }

            function closeGalleryModal() {
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 500);

                document.body.classList.remove('overflow-hidden');
            }

            function updateModalImage() {
                const project = portfolioData[activeProjectIdx];
                const image = project.gallery[activeImgIdx];

                modalActiveImg.style.opacity = '0';

                setTimeout(() => {
                    modalActiveImg.src = image.src;
                    modalActiveImg.alt = image.title;

                    modalImgCounter.innerText = `IMAGE ${activeImgIdx + 1} OF ${project.gallery.length}`;
                    modalImgTitle.innerText = image.title || "Untitled Detail";
                    modalImgDesc.innerText = image.desc || "";

                    modalActiveImg.style.opacity = '1';
                }, 180);
            }

            // Click listener for grid items
            document.querySelectorAll('.gallery-item').forEach((item) => {
                item.addEventListener('click', () => {
                    const pIdx = parseInt(item.getAttribute('data-project'));
                    const iIdx = parseInt(item.getAttribute('data-image'));
                    openGalleryModal(pIdx, iIdx);
                });
            });

            closeModalBtn.addEventListener('click', closeGalleryModal);

            prevImgBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const project = portfolioData[activeProjectIdx];
                activeImgIdx = (activeImgIdx - 1 + project.gallery.length) % project.gallery.length;
                updateModalImage();
            });

            nextImgBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const project = portfolioData[activeProjectIdx];
                activeImgIdx = (activeImgIdx + 1) % project.gallery.length;
                updateModalImage();
            });

            // Accessibility: Keyboard Navigation
            document.addEventListener('keydown', (e) => {
                if (modal.classList.contains('hidden')) return;

                if (e.key === 'Escape') {
                    closeGalleryModal();
                } else if (e.key === 'ArrowLeft') {
                    const project = portfolioData[activeProjectIdx];
                    activeImgIdx = (activeImgIdx - 1 + project.gallery.length) % project.gallery.length;
                    updateModalImage();
                } else if (e.key === 'ArrowRight') {
                    const project = portfolioData[activeProjectIdx];
                    activeImgIdx = (activeImgIdx + 1) % project.gallery.length;
                    updateModalImage();
                }
            });

            // Filter Grid system - active styling changed to charcoal/white active pill
            const filterBtns = document.querySelectorAll('.gallery-filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');

            filterBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const filterValue = btn.getAttribute('data-filter');

                    filterBtns.forEach((b) => {
                        b.classList.remove('bg-[#0f172a]', 'text-white');
                        b.classList.add('bg-[#f8fafc]', 'border', 'border-[#e2e8f0]', 'text-[#0f172a]/80');
                    });

                    btn.classList.remove('bg-[#f8fafc]', 'border', 'border-[#e2e8f0]', 'text-[#0f172a]/80');
                    btn.classList.add('bg-[#0f172a]', 'text-white');

                    galleryItems.forEach((item) => {
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


            // Check URL Search Params to auto-launch modal (project-specific link support)
            const urlParams = new URLSearchParams(window.location.search);
            const projectParam = urlParams.get('project');
            if (projectParam !== null) {
                const projectIdx = parseInt(projectParam);
                if (projectIdx >= 0 && projectIdx < portfolioData.length) {
                    setTimeout(() => {
                        openGalleryModal(projectIdx, 0);
                    }, 150);
                }
            }
        });
    </script>
</body>

</html>
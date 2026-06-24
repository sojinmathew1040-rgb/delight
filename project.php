<?php
// ==========================================
// DELIGHT BUILDERS - PORTFOLIO PROJECT DETAIL
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

$project_id = intval($_GET['id'] ?? 0);
if ($project_id <= 0) {
    header("Location: portfolio.php");
    exit;
}

// Fetch specific project details
try {
    $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    if (!$project) {
        header("Location: portfolio.php");
        exit;
    }

    // Fetch its gallery blueprint photos
    $stmt_gal = $pdo->prepare("SELECT src, title, desc_text as `desc`, stage, materiality FROM portfolio_gallery WHERE portfolio_id = ? ORDER BY id ASC");
    $stmt_gal->execute([$project_id]);
    $gallery = $stmt_gal->fetchAll();

    // Fallback if no gallery photos exist: use primary project image as the single slide
    if (empty($gallery)) {
        $gallery[] = [
            "src" => $project['image'],
            "title" => $project['title'] . " Exterior Showcase",
            "desc" => $project['description'],
            "stage" => "Completed",
            "materiality" => "Premium Curated"
        ];
    }

    // Resolve asset path for all gallery items
    foreach ($gallery as &$item) {
        $item['src'] = resolve_asset_path($item['src']);
    }
    unset($item);
} catch (PDOException $e) {
    header("Location: portfolio.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-[#f8fafc]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> | Delight Builders Project details</title>

    <!-- Meta tags for SEO -->
    <meta name="description"
        content="View architectural layouts, blueprint details, structures, and stage progress for <?php echo htmlspecialchars($project['title']); ?> by Delight Builders.">
    <meta name="keywords"
        content="Luxury construction, premium residential, architectural frameworks, blueprint archive, details">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit (premium typography) -->
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
    class="font-sans text-[#0f172a]/85 relative overflow-x-hidden min-h-screen bg-[#f8fafc] selection:bg-[#00aff0]/10 selection:text-[#00aff0] flex flex-col justify-between">

    <!-- LIGHT PASTEL BLENDING OVERLAY -->
    <div class="fixed inset-0 -z-10 bg-white/80 pointer-events-none"></div>

    <!-- STATIC GRID OVERLAY -->
    <div class="fixed inset-0 grid grid-cols-4 pointer-events-none z-0">
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="h-full"></div>
    </div>

    <!-- MAIN GRID CONTAINER -->
    <div class="relative z-10 flex flex-col min-h-screen justify-between">

        <!-- HEADER SECTION -->
        <header class="border-b border-[#e2e8f0]/80 px-6 py-5 flex justify-between items-center bg-white/72 backdrop-blur-md">
            <div>
                <span class="text-[10px] tracking-widest text-[#00aff0] uppercase font-bold block mb-0.5">
                    <?php echo htmlspecialchars($project['category']); ?>
                </span>
                <h1 class="font-display text-[#0f172a] text-2xl md:text-3xl font-extrabold tracking-tight">
                    <?php echo htmlspecialchars($project['title']); ?>
                </h1>
            </div>
            <a href="portfolio.php"
                class="font-display text-[#0f172a] hover:text-[#00aff0] text-xs font-semibold flex items-center space-x-2 focus:outline-none py-2.5 px-5 rounded-full border border-[#e2e8f0] bg-white/40 hover:border-[#00aff0] transition-all duration-300">
                <span>✕</span> <span>Close</span>
            </a>
        </header>

        <!-- BODY SHOWCASE PANEL -->
        <div class="flex-grow grid grid-cols-1 lg:grid-cols-12 overflow-hidden">
            
            <!-- Left Section: Large Image Showcase -->
            <div class="lg:col-span-8 p-6 flex flex-col justify-center items-center bg-[#f8fafc]/40 border-b lg:border-b-0 lg:border-r border-[#e2e8f0]/80 relative overflow-hidden group min-h-[40vh] lg:min-h-[75vh]">
                
                <!-- Active Image view wrapper -->
                <div class="w-full h-full flex justify-center items-center relative z-10">
                    <img id="active-image" src="" alt="Showcase Active Detail"
                        class="max-h-[50vh] lg:max-h-[70vh] w-auto max-w-full object-contain transition-all duration-350 ease-in-out rounded-2xl shadow-md border border-[#e2e8f0]/50 bg-white">
                </div>

                <!-- Navigation arrows overlaid on hover -->
                <button id="prev-image-btn" type="button"
                    class="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full border border-[#e2e8f0] bg-white/72 backdrop-blur-md text-[#0f172a]/80 hover:text-[#00aff0] hover:border-[#00aff0] flex items-center justify-center transition-all duration-300 shadow-sm focus:outline-none z-20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>
                <button id="next-image-btn" type="button"
                    class="absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full border border-[#e2e8f0] bg-white/72 backdrop-blur-md text-[#0f172a]/80 hover:text-[#00aff0] hover:border-[#00aff0] flex items-center justify-center transition-all duration-300 shadow-sm focus:outline-none z-20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Right Section: Details, Meta Information & Thumbnails -->
            <div class="lg:col-span-4 p-8 flex flex-col justify-between overflow-y-auto bg-white/60 backdrop-blur-md min-h-[40vh]">
                
                <!-- Description text & metadata -->
                <div class="space-y-6">
                    <div>
                        <span id="image-counter"
                            class="text-[10px] tracking-[0.15em] text-[#64748b] font-bold block mb-1">IMAGE 1 OF Y</span>
                        <h3 id="image-title"
                            class="font-display text-[#0f172a] text-xl md:text-2xl font-extrabold tracking-tight leading-snug">
                            Image Title
                        </h3>
                    </div>
                    
                    <!-- Cyan underline block -->
                    <div class="w-10 h-[3px] bg-[#00aff0] rounded-full"></div>
                    
                    <!-- PROCESS TIMELINE STEPPER -->
                    <div class="py-4 border-y border-[#e2e8f0]/80">
                        <span class="text-[9px] tracking-widest text-[#64748b] uppercase font-bold block mb-3">Project lifecycle progress</span>
                        <div class="flex items-center justify-between relative px-2">
                            <!-- Connecting Line -->
                            <div class="absolute left-6 right-6 top-4 h-[2px] bg-slate-200 z-0"></div>
                            <div id="timeline-progress-bar" class="absolute left-6 top-4 h-[2px] bg-[#00aff0] z-0 transition-all duration-500" style="width: 0%;"></div>
                            
                            <!-- Stage 1 -->
                            <button type="button" data-stage="Consultation" class="stage-node-btn relative z-10 flex flex-col items-center group focus:outline-none">
                                <span id="node-1" class="w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-400 transition-all duration-300">1</span>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-slate-650 transition-all duration-300 mt-1">Consult</span>
                            </button>
                            
                            <!-- Stage 2 -->
                            <button type="button" data-stage="Design & Planning" class="stage-node-btn relative z-10 flex flex-col items-center group focus:outline-none">
                                <span id="node-2" class="w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-400 transition-all duration-300">2</span>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-slate-650 transition-all duration-300 mt-1">Design</span>
                            </button>
                            
                            <!-- Stage 3 -->
                            <button type="button" data-stage="Construction" class="stage-node-btn relative z-10 flex flex-col items-center group focus:outline-none">
                                <span id="node-3" class="w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-400 transition-all duration-300">3</span>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-slate-650 transition-all duration-300 mt-1">Build</span>
                            </button>
                            
                            <!-- Stage 4 -->
                            <button type="button" data-stage="Handover" class="stage-node-btn relative z-10 flex flex-col items-center group focus:outline-none">
                                <span id="node-4" class="w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-400 transition-all duration-300">4</span>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-slate-650 transition-all duration-300 mt-1">Handover</span>
                            </button>
                        </div>
                        <div class="text-[9px] text-[#00aff0] font-bold text-center mt-3 cursor-pointer hover:underline hidden" id="clear-stage-filter">
                            Showing filtered stage. Click here to show all stages.
                        </div>
                    </div>
                    
                    <p id="image-desc" class="text-[#0f172a]/75 text-sm md:text-base leading-relaxed font-normal">
                        Active image description goes here.
                    </p>
                    
                    <!-- Metadata metrics -->
                    <div class="grid grid-cols-2 gap-4 pt-6 border-t border-[#e2e8f0] text-xs">
                        <div class="space-y-1">
                            <span class="text-[#64748b] uppercase tracking-wider text-[9px] font-bold block">Project Stage</span>
                            <span id="detail-stage" class="text-[#0f172a] font-extrabold text-sm">Conceptual Render</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[#64748b] uppercase tracking-wider text-[9px] font-bold block">Materiality</span>
                            <span id="detail-materiality" class="text-[#0f172a] font-extrabold text-sm">Premium Curated</span>
                        </div>
                    </div>
                </div>

                <!-- Thumbnails frame grid -->
                <div class="mt-12 pt-6 border-t border-[#e2e8f0]">
                    <span class="text-[10px] tracking-widest text-[#64748b] uppercase font-bold block mb-4">
                        BLUEPRINT FRAME ARCHIVE
                    </span>
                    <div id="thumbnails-container"
                        class="grid grid-cols-5 gap-2 overflow-y-auto max-h-48 scrollbar-thin pr-1">
                        <!-- Thumbnails populated dynamically via JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER BAR -->
        <footer class="bg-[#f8fafc]/95 py-8 px-6 md:px-16 border-t border-[#e2e8f0]/60 z-20 relative">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center space-x-3">
                    <img src="<?php echo $logo_path; ?>" alt="Delight Builders" class="h-10 w-auto object-contain">
                    <span class="font-display text-[#0f172a] font-bold tracking-widest text-xs uppercase">DELIGHT BUILDERS</span>
                </div>
                <div class="text-[10px] tracking-normal text-[#64748b] text-center sm:text-right font-medium">
                    <p>© <?php echo date("Y"); ?> Delight Builders Inc. All architectural rights reserved.</p>
                </div>
            </div>
        </footer>

    </div>

    <!-- DYNAMIC CONTROLLER SCRIPT -->
    <script>
        const galleryData = <?php echo json_encode($gallery); ?>;
        const projectStage = <?php echo json_encode($project['stage'] ?? null); ?>;
        const projectMateriality = <?php echo json_encode($project['materiality'] ?? null); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const activeImg = document.getElementById('active-image');
            const imgCounter = document.getElementById('image-counter');
            const imgTitle = document.getElementById('image-title');
            const imgDesc = document.getElementById('image-desc');
            const detailStage = document.getElementById('detail-stage');
            const detailMateriality = document.getElementById('detail-materiality');
            const thumbnailsContainer = document.getElementById('thumbnails-container');
            const prevBtn = document.getElementById('prev-image-btn');
            const nextBtn = document.getElementById('next-image-btn');
            const clearFilterBtn = document.getElementById('clear-stage-filter');

            let filteredData = [...galleryData];
            let activeIdx = 0;
            let currentFilterStage = null;

            // Group images by stage to see which stages have photos
            const stagesWithPhotos = new Set();
            galleryData.forEach(img => {
                // Normalize stage names to map to timeline
                let imgStage = img.stage ? img.stage : (projectStage ? projectStage : "Construction");
                // Map custom stages if any to main 4 stages
                if (imgStage.toLowerCase().includes('consult')) imgStage = 'Consultation';
                else if (imgStage.toLowerCase().includes('design') || imgStage.toLowerCase().includes('plan')) imgStage = 'Design & Planning';
                else if (imgStage.toLowerCase().includes('construct') || imgStage.toLowerCase().includes('build')) imgStage = 'Construction';
                else if (imgStage.toLowerCase().includes('handover') || imgStage.toLowerCase().includes('complete')) imgStage = 'Handover';
                
                img.normalizedStage = imgStage;
                stagesWithPhotos.add(imgStage);
            });

            const stageMap = {
                "Consultation": 1,
                "Design & Planning": 2,
                "Construction": 3,
                "Handover": 4
            };

            function updateTimeline(stageName) {
                const stageNum = stageMap[stageName] || 3;
                
                // Update progress bar width
                const pct = ((stageNum - 1) / 3) * 100;
                const progressBar = document.getElementById('timeline-progress-bar');
                if (progressBar) {
                    progressBar.style.width = pct + '%';
                }

                // Update node classes
                for (let i = 1; i <= 4; i++) {
                    const node = document.getElementById(`node-${i}`);
                    if (!node) continue;
                    
                    if (i < stageNum) {
                        // Completed stage
                        node.className = "w-8 h-8 rounded-full bg-[#00aff0]/10 border-2 border-[#00aff0] text-[#00aff0] flex items-center justify-center text-[10px] font-bold transition-all duration-300";
                    } else if (i === stageNum) {
                        // Current active stage
                        node.className = "w-8 h-8 rounded-full bg-[#00aff0] border-2 border-[#00aff0] text-white flex items-center justify-center text-[10px] font-bold ring-4 ring-[#00aff0]/20 transition-all duration-300 scale-110";
                    } else {
                        // Future stage
                        node.className = "w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 text-slate-400 flex items-center justify-center text-[10px] font-bold transition-all duration-300";
                    }
                }
            }

            function initTimelineLockStates() {
                document.querySelectorAll('.stage-node-btn').forEach(btn => {
                    const stage = btn.getAttribute('data-stage');
                    const hasImg = stagesWithPhotos.has(stage);
                    const nodeNum = btn.querySelector('span');
                    
                    if (!hasImg) {
                        btn.classList.add('cursor-not-allowed');
                        btn.title = `No photos uploaded for the ${stage} stage yet`;
                        if (nodeNum) {
                            nodeNum.className = "w-8 h-8 rounded-full bg-slate-50 border-2 border-dashed border-slate-200 text-slate-300 flex items-center justify-center text-[10px] font-bold";
                        }
                    } else {
                        btn.classList.remove('cursor-not-allowed');
                        btn.title = `Click to filter photos by the ${stage} stage`;
                        if (nodeNum) {
                            nodeNum.className = "w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 text-slate-500 flex items-center justify-center text-[10px] font-bold hover:border-[#00aff0] hover:text-[#00aff0] cursor-pointer transition-all duration-300";
                        }
                    }
                });
            }

            function updateImage() {
                if (filteredData.length === 0) return;
                const image = filteredData[activeIdx];
                
                activeImg.style.opacity = '0';
                
                setTimeout(() => {
                    activeImg.src = image.src;
                    activeImg.alt = image.title;
                    
                    imgCounter.innerText = `IMAGE ${activeIdx + 1} OF ${filteredData.length}`;
                    imgTitle.innerText = image.title ? image.title : "Untitled Detail";
                    imgDesc.innerText = image.desc ? image.desc : "No description provided.";
                    
                    // Fallbacks for Stage and Materiality
                    const rawStage = image.stage ? image.stage : (projectStage ? projectStage : "Construction");
                    const materialityVal = image.materiality ? image.materiality : (projectMateriality ? projectMateriality : "Premium Curated");
                    
                    detailStage.innerText = rawStage;
                    detailMateriality.innerText = materialityVal;
                    
                    activeImg.style.opacity = '1';

                    // Update timeline to match this image's stage
                    updateTimeline(image.normalizedStage || "Construction");
                }, 180);

                // Update active state in thumbnail grid
                document.querySelectorAll('.thumbnail-btn').forEach((thumb, idx) => {
                    if (idx === activeIdx) {
                        thumb.classList.add('border-[#00aff0]', 'opacity-100');
                        thumb.classList.remove('border-[#e2e8f0]', 'opacity-40');
                    } else {
                        thumb.classList.remove('border-[#00aff0]', 'opacity-100');
                        thumb.classList.add('border-[#e2e8f0]', 'opacity-40');
                    }
                });
            }

            function buildThumbnails() {
                thumbnailsContainer.innerHTML = '';
                filteredData.forEach((image, idx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `thumbnail-btn aspect-square border-2 border-[#e2e8f0] opacity-40 hover:opacity-100 focus:outline-none transition-all duration-300 relative overflow-hidden rounded-xl bg-slate-900`;
                    btn.innerHTML = `<img src="${image.src}" alt="${image.title}" class="w-full h-full object-cover">`;
                    
                    btn.addEventListener('click', () => {
                        activeIdx = idx;
                        updateImage();
                    });
                    
                    thumbnailsContainer.appendChild(btn);
                });
            }

            function applyStageFilter(stageName) {
                if (stageName) {
                    filteredData = galleryData.filter(img => img.normalizedStage === stageName);
                    currentFilterStage = stageName;
                    clearFilterBtn.innerText = `Showing ${stageName} Photos. Click here to show all stages.`;
                    clearFilterBtn.classList.remove('hidden');
                } else {
                    filteredData = [...galleryData];
                    currentFilterStage = null;
                    clearFilterBtn.classList.add('hidden');
                }
                activeIdx = 0;
                buildThumbnails();
                updateImage();
            }

            // Click stage buttons to filter
            document.querySelectorAll('.stage-node-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const stage = btn.getAttribute('data-stage');
                    if (stagesWithPhotos.has(stage)) {
                        applyStageFilter(stage);
                    } else {
                        // Flash warning
                        const alertBox = document.createElement('div');
                        alertBox.className = "fixed bottom-10 right-10 bg-slate-900 text-white text-xs px-4 py-3 rounded-xl shadow-xl z-50 border border-slate-800 transition-all duration-300 opacity-0 translate-y-4";
                        alertBox.innerText = `No photos have been uploaded for the "${stage}" stage yet.`;
                        document.body.appendChild(alertBox);
                        setTimeout(() => {
                            alertBox.classList.remove('opacity-0', 'translate-y-4');
                        }, 50);
                        setTimeout(() => {
                            alertBox.classList.add('opacity-0', 'translate-y-4');
                            setTimeout(() => alertBox.remove(), 300);
                        }, 3000);
                    }
                });
            });

            clearFilterBtn.addEventListener('click', () => {
                applyStageFilter(null);
            });

            // Initialize Everything
            initTimelineLockStates();
            buildThumbnails();
            updateImage();

            // Arrows
            prevBtn.addEventListener('click', (e) => {
                if (filteredData.length === 0) return;
                activeIdx = (activeIdx - 1 + filteredData.length) % filteredData.length;
                updateImage();
            });

            nextBtn.addEventListener('click', (e) => {
                if (filteredData.length === 0) return;
                activeIdx = (activeIdx + 1) % filteredData.length;
                updateImage();
            });

            // Keyboard Navigation
            document.addEventListener('keydown', (e) => {
                if (filteredData.length === 0) return;
                if (e.key === 'ArrowLeft') {
                    activeIdx = (activeIdx - 1 + filteredData.length) % filteredData.length;
                    updateImage();
                } else if (e.key === 'ArrowRight') {
                    activeIdx = (activeIdx + 1) % filteredData.length;
                    updateImage();
                }
            });
        });
    </script>
</body>

</html>

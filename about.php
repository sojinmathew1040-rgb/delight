<?php
// ==========================================================================
// DELIGHT BUILDERS - ABOUT US / CONCEPTUAL LEGACY
// ==========================================================================
require_once 'admin/db_connection.php';

$is_gallery = true;
$is_dark = false; // Light-mode integration
$title_indicator = "ABOUT";
$back_to_section = "#vision";

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

function render_team_branch($member, $all_members_by_parent, $depth = 0)
{
    global $established_year;
    
    // Determine card sizing based on depth
    if ($depth === 0) {
        $card_classes = "w-72 p-8 shadow-[0_8px_30px_rgba(0,0,0,0.02)]";
        $avatar_classes = "w-20 h-20 text-xl";
        $name_classes = "text-lg";
        $role_classes = "text-[10px]";
        $desc_classes = "text-xs";
    } elseif ($depth === 1) {
        $card_classes = "w-64 p-6 shadow-[0_6px_24px_rgba(0,0,0,0.02)]";
        $avatar_classes = "w-16 h-16 text-lg";
        $name_classes = "text-base";
        $role_classes = "text-[9px]";
        $desc_classes = "text-[11px]";
    } else {
        $card_classes = "w-56 p-5 shadow-[0_4px_16px_rgba(0,0,0,0.01)]";
        $avatar_classes = "w-12 h-12 text-sm";
        $name_classes = "text-sm";
        $role_classes = "text-[8px]";
        $desc_classes = "text-[10px]";
    }
    
    $member_id = $member['id'];
    $children = isset($all_members_by_parent[$member_id]) ? $all_members_by_parent[$member_id] : [];
    
    $image_path = $member['image'];
    $has_image = !empty($image_path) && file_exists($image_path);
    
    $blueprint_svg = '';
    switch (($depth + $member['id']) % 3) {
        case 0:
            $blueprint_svg = '<svg class="absolute inset-0 w-full h-full text-slate-100/60" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="0.5" stroke-dasharray="2 2"/>
                <line x1="10" y1="50" x2="90" y2="50" stroke="currentColor" stroke-width="0.5"/>
                <line x1="50" y1="10" x2="50" y2="90" stroke="currentColor" stroke-width="0.5"/>
            </svg>';
            break;
        case 1:
            $blueprint_svg = '<svg class="absolute inset-0 w-full h-full text-slate-100/60" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 80 L50 20 L80 80 Z" stroke="currentColor" stroke-width="0.5" stroke-dasharray="2 2"/>
                <line x1="50" y1="20" x2="50" y2="80" stroke="currentColor" stroke-width="0.5"/>
            </svg>';
            break;
        case 2:
            $blueprint_svg = '<svg class="absolute inset-0 w-full h-full text-slate-100/60" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 50 Q50 10 90 50 Q50 90 10 50 Z" stroke="currentColor" stroke-width="0.5" stroke-dasharray="2 2"/>
            </svg>';
            break;
    }
    ?>
    <div class="flex flex-col items-center relative select-none">
        <!-- Card -->
        <div class="glass-panel border border-[#e2e8f0] rounded-3xl flex flex-col items-center text-center space-y-4 <?php echo $card_classes; ?> apple-card bg-white/85 backdrop-blur-md relative z-10 hover:shadow-[0_12px_45px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-all duration-300">
            <!-- Avatar / Photo -->
            <div class="relative <?php echo $avatar_classes; ?> rounded-full border border-slate-200 flex items-center justify-center overflow-hidden shadow-inner bg-slate-50">
                <?php if ($has_image): ?>
                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-full h-full object-cover text-xs">
                <?php else: ?>
                    <?php echo $blueprint_svg; ?>
                    <span class="font-display font-black text-[#0f172a] relative z-10"><?php echo htmlspecialchars($member['avatar_text']); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Details -->
            <div class="space-y-1">
                <h3 class="font-display <?php echo $name_classes; ?> text-[#0f172a] font-extrabold tracking-tight"><?php echo htmlspecialchars($member['name']); ?></h3>
                <p class="<?php echo $role_classes; ?> tracking-wider uppercase text-[#00aff0] font-bold"><?php echo htmlspecialchars($member['role']); ?></p>
                <?php if ($depth === 0): ?>
                    <span class="text-[9px] text-[#64748b] font-mono tracking-widest block uppercase">Kerala Principal &bull; Est. <?php echo htmlspecialchars($established_year); ?></span>
                <?php endif; ?>
            </div>
            
            <p class="<?php echo $desc_classes; ?> text-[#0f172a]/70 leading-relaxed font-normal">
                <?php echo htmlspecialchars($member['description']); ?>
            </p>
        </div>
        
        <!-- Connector down to children -->
        <?php if (!empty($children)): ?>
            <div class="w-0.5 h-8 bg-slate-200"></div>
            
            <div class="flex flex-row justify-center relative items-start">
                <?php foreach ($children as $index => $child): ?>
                    <div class="flex flex-col items-center relative px-4">
                        <!-- Horizontal connectors -->
                        <?php if (count($children) > 1): ?>
                            <?php if ($index > 0): ?>
                                <div class="absolute top-0 left-0 w-1/2 h-0.5 bg-slate-200"></div>
                            <?php endif; ?>
                            <?php if ($index < count($children) - 1): ?>
                                <div class="absolute top-0 right-0 w-1/2 h-0.5 bg-slate-200"></div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Top vertical connector -->
                        <div class="w-0.5 h-8 bg-slate-200"></div>
                        
                        <!-- Recursive call -->
                        <?php render_team_branch($child, $all_members_by_parent, $depth + 1); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Fetch settings
$site_title = get_setting('site_title', 'Delight Builders | Professional Architectural Legacy & Principles');
$established_year = get_setting('established_year', '2006');
$logo_path = resolve_asset_path(get_setting('logo_path', 'asset/images/logo.png'));
$about_hero_desc = get_setting('about_hero_desc', 'Creating spaces with purpose and precision. Established in 2006, Delight Builders challenges transient architectural trends, synthesizing volumetric concrete gravity and biophilic structural systems to deliver custom residences and corporate structures calculated to endure for generations.');

// Fetch pillars
try {
    $stmt = $pdo->query("SELECT * FROM pillars ORDER BY sort_order ASC, id ASC");
    $pillars = $stmt->fetchAll();
} catch (PDOException $e) {
    $pillars = [];
}

// Fetch milestones
try {
    $stmt = $pdo->query("SELECT * FROM milestones ORDER BY sort_order ASC, id ASC");
    $milestones = $stmt->fetchAll();
} catch (PDOException $e) {
    $milestones = [];
}

// Fetch team members
try {
    $stmt = $pdo->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC");
    $team = $stmt->fetchAll();
} catch (PDOException $e) {
    $team = [];
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-[#f8fafc]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>

    <!-- Meta tags for SEO -->
    <meta name="description"
        content="Discover the architectural legacy of Delight Builders. Established in 2006, we combine brutalist concrete weight with biophilic timber arches.">
    <meta name="keywords"
        content="About Delight Builders, architectural principles, brutalist legacy, sustainable construction, structural timeline, residential expertise, commercial construction">

    <!-- Google Fonts -->
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

    <!-- Custom CSS for luxury overlay grids and scrollbars -->
    <link rel="stylesheet" href="asset/css/style.css?v=<?php echo time(); ?>">
</head>

<body
    class="font-sans text-[#0f172a]/85 relative overflow-x-hidden min-h-screen selection:bg-[#00aff0]/10 selection:text-[#00aff0]">

    <!-- LIGHT PASTEL BLENDING OVERLAY -->
    <div class="fixed inset-0 -z-10 bg-white/80 pointer-events-none"></div>

    <!-- BLUEPRINT GRID LINES LAYER -->
    <div class="fixed inset-0 grid grid-cols-4 pointer-events-none z-0">
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="border-r border-[#e2e8f0]/60 h-full"></div>
        <div class="h-full"></div>
    </div>

    <!-- MAIN APP WRAPPER -->
    <div class="relative z-10 flex flex-col min-h-screen justify-between pt-28">

        <!-- HEADER NAVIGATION BAR -->
        <?php include 'header.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-grow">

            <!-- HERO INTRO -->
            <section class="py-24 px-6 md:px-16 lg:px-24">
                <div class="max-w-4xl mx-auto space-y-6 text-center md:text-left mt-6">
                    <div class="space-y-2">
                        <span class="text-xs text-[#00aff0] tracking-[0.1em] uppercase font-semibold block">01 / Corporate Profile</span>
                        <span class="text-[11px] tracking-[0.15em] text-[#64748b] font-bold uppercase block">Construction Company &bull; Est. <?php echo htmlspecialchars($established_year); ?></span>
                    </div>
                    
                    <h1 class="font-display text-4xl md:text-6xl text-[#0f172a] font-black tracking-tight leading-none">
                        ARCHITECTS OF<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#0f172a] via-[#00aff0] to-[#ec3237]">PERMANENCE.</span>
                    </h1>

                    <div class="border-y border-[#e2e8f0]/80 py-4 max-w-2xl text-[13px] font-bold tracking-tight text-[#0f172a]/90 space-y-2">
                        <div class="text-[#00aff0] font-display text-sm tracking-wide uppercase">Architecture &bull; Construction &bull; Interior &bull; Landscape</div>
                        <div class="text-[#64748b] text-xs font-semibold uppercase tracking-wider">Residential & Commercial Architectural Excellence</div>
                    </div>

                    <p class="text-[#0f172a]/70 font-normal text-base md:text-lg leading-relaxed max-w-2xl pt-2">
                        <?php echo htmlspecialchars($about_hero_desc); ?>
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-8 pt-8 max-w-lg">
                        <div class="space-y-1">
                            <div class="text-xs text-[#64748b] font-bold tracking-wider uppercase">ESTABLISHED</div>
                            <div class="font-display text-3xl font-black text-[#0f172a]"><?php echo htmlspecialchars($established_year); ?></div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-xs text-[#64748b] font-bold tracking-wider uppercase">STRUCTURAL PROJECTS</div>
                            <div class="font-display text-3xl font-black text-[#0f172a]">150+</div>
                        </div>
                        <div class="space-y-1 col-span-2 sm:col-span-1">
                            <div class="text-xs text-[#64748b] font-bold tracking-wider uppercase">SEISMIC RATING</div>
                            <div class="font-display text-3xl font-black text-[#0f172a]">A++</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- DESIGN PILLARS / PHILOSOPHY -->
            <section class="py-20 px-6 md:px-16 lg:px-24 bg-[#f8fafc]/40 border-y border-[#e2e8f0]/60">
                <div class="max-w-7xl mx-auto">
                    <div class="space-y-4 mb-16 text-center md:text-left">
                        <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">02 / Core Philosophy</span>
                        <h2 class="font-display text-3xl md:text-4xl text-[#0f172a] font-extrabold tracking-tight">
                            Foundational Pillars
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <?php foreach ($pillars as $p): 
                            $badge_color = "bg-[#00aff0]/10 text-[#00aff0]";
                            if ($p['icon'] === 'fluidity') {
                                $badge_color = "bg-[#ec3237]/10 text-[#ec3237]";
                            } elseif ($p['icon'] === 'harmony') {
                                $badge_color = "bg-purple-500/10 text-purple-600";
                            }
                            ?>
                            <div class="glass-panel border border-[#e2e8f0] rounded-3xl p-8 space-y-6 apple-card shadow-[0_8px_30px_rgba(0,0,0,0.02)]">
                                <div class="w-12 h-12 rounded-2xl <?php echo $badge_color; ?> flex items-center justify-center">
                                    <?php if ($p['icon'] === 'gravity'): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    <?php elseif ($p['icon'] === 'fluidity'): ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-11.314l.707.707m11.314 11.314l.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <h3 class="font-display text-xl text-[#0f172a] font-extrabold tracking-tight"><?php echo htmlspecialchars($p['title']); ?></h3>
                                <p class="text-xs md:text-sm text-[#0f172a]/70 leading-relaxed font-normal">
                                    <?php echo htmlspecialchars($p['description']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- TIMELINE / JOURNEY SECTION -->
            <section class="py-20 px-6 md:px-16 lg:px-24">
                <div class="max-w-5xl mx-auto">
                    <div class="space-y-4 mb-16 text-center">
                        <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">03 / Historical Journey</span>
                        <h2 class="font-display text-3xl md:text-4xl text-[#0f172a] font-extrabold tracking-tight">
                            Milestones of Construction
                        </h2>
                    </div>

                    <!-- Journey Timeline Grid -->
                    <div class="relative border-l-2 border-[#e2e8f0] ml-4 md:ml-32 space-y-12 py-4">
                        <?php if (count($milestones) > 0): ?>
                            <?php foreach ($milestones as $m): ?>
                                <!-- Node: <?php echo htmlspecialchars($m['year']); ?> -->
                                <div class="relative pl-8 md:pl-12">
                                    <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full bg-white border-4 border-[#00aff0] shadow-sm"></div>
                                    
                                    <!-- Date overlay for larger viewports -->
                                    <div class="hidden md:block absolute -left-32 top-0 w-24 text-right font-display text-2xl text-[#0f172a] font-black"><?php echo htmlspecialchars($m['year']); ?></div>
                                    
                                    <div class="space-y-2">
                                        <span class="md:hidden font-display text-lg text-[#00aff0] font-black block"><?php echo htmlspecialchars($m['year']); ?></span>
                                        <h3 class="font-display text-lg text-[#0f172a] font-bold"><?php echo htmlspecialchars($m['title']); ?></h3>
                                        <p class="text-xs md:text-sm text-[#0f172a]/70 leading-relaxed max-w-2xl font-normal">
                                            <?php echo htmlspecialchars($m['description']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-8 text-center text-slate-400">No milestones registered yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- LEADERSHIP / EXECUTIVE TEAM SECTION -->
            <section class="py-20 px-6 md:px-16 lg:px-24 bg-[#f8fafc]/40 border-t border-[#e2e8f0]/60">
                <div class="max-w-7xl mx-auto">
                    <div class="space-y-4 mb-12 text-center">
                        <span class="text-xs text-[#00aff0] tracking-normal uppercase font-semibold block">04 / Organizational Hierarchy</span>
                        <h2 class="font-display text-3xl md:text-4xl text-[#0f172a] font-extrabold tracking-tight">
                            Structural Orchestrators
                        </h2>
                        <p class="text-sm text-[#64748b] max-w-md mx-auto font-normal">
                            Our executive architecture represents a clear engineering workflow, from initial design concepts down to material physics and sustainable structures.
                        </p>
                    </div>

                    <!-- Hierarchical Leadership Tree Layout -->
                    <?php
                    // Group members and determine roots
                    $all_members_by_parent = [];
                    $members_by_id = [];
                    foreach ($team as $member) {
                        $members_by_id[$member['id']] = $member;
                    }
                    foreach ($team as $member) {
                        $p_id = $member['parent_id'] ? intval($member['parent_id']) : 0;
                        $all_members_by_parent[$p_id][] = $member;
                    }
                    $roots = [];
                    foreach ($team as $member) {
                        $p_id = $member['parent_id'];
                        if (empty($p_id) || !isset($members_by_id[$p_id])) {
                            $roots[] = $member;
                        }
                    }
                    ?>
                    <div class="relative w-full overflow-x-auto mt-16 pb-12 scrollbar-thin select-none">
                        <div class="inline-block min-w-full p-4 align-middle">
                            <?php if (count($roots) > 0): ?>
                                <div class="flex flex-row justify-center items-start gap-12">
                                    <?php foreach ($roots as $root): ?>
                                        <?php render_team_branch($root, $all_members_by_parent); ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-8 text-center text-slate-400">No team members registered yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

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

</body>

</html>

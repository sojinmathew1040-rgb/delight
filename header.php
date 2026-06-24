<?php
// ==========================================================================
// DELIGHT BUILDERS - COMMON HEADER COMPONENT
// ==========================================================================
$is_gallery = isset($is_gallery) && $is_gallery;

if (!isset($logo_path)) {
    if (function_exists('get_setting')) {
        $logo_path = get_setting('logo_path', 'asset/images/logo.png');
    } else {
        $logo_path = 'asset/images/logo.png';
    }
    if (function_exists('resolve_asset_path')) {
        $logo_path = resolve_asset_path($logo_path);
    }
}
?>
<header id="main-header"
    class="fixed top-0 left-0 w-full z-40 h-28 border-b border-[#e2e8f0]/40 bg-white/72 backdrop-blur-md transition-all duration-500">
    <div class="max-w-7xl mx-auto px-6 h-full flex justify-between items-center">
        <!-- Return Home link -->
        <a href="index.php" class="flex items-center group">
            <img id="header-logo" src="<?php echo $logo_path; ?>" alt="Delight Builders Logo"
                class="h-36 w-auto object-contain transition-all duration-500 group-hover:scale-105">
        </a>

        <?php if ($is_gallery):
            $header_title = isset($title_indicator) ? $title_indicator : "ARCHIVES";
            $back_url = "index.php" . (isset($back_to_section) ? $back_to_section : "");
            ?>
            <!-- Title indicator -->
            <span
                class="font-display text-xs text-[#64748b] tracking-[0.15em] font-bold uppercase hidden sm:block"><?php echo $header_title; ?></span>

            <!-- Home Back Button -->
            <div>
                <a href="<?php echo $back_url; ?>"
                    class="font-display border border-[#e2e8f0] text-[#0f172a] hover:border-[#00aff0] hover:text-[#00aff0] px-5 py-2 rounded-full text-xs font-bold tracking-tight transition-all duration-300 glass-panel">
                    Back to Home
                </a>
            </div>
        <?php else: ?>
            <!-- Nav Menu -->
            <nav
                class="font-display hidden md:flex items-center space-x-10 text-[13px] tracking-tight font-semibold text-[#0f172a]/80">
                <a href="index.php#vision" class="hover:text-[#00aff0] transition-colors duration-305">Vision</a>
                <a href="index.php#timeline" class="hover:text-[#00aff0] transition-colors duration-305">Timeline</a>
                <a href="portfolio.php" class="hover:text-[#00aff0] transition-colors duration-305">Portfolio</a>
                <a href="index.php#contact" class="hover:text-[#00aff0] transition-colors duration-305">Contact Us</a>
            </nav>

            <!-- CTA Buttons -->
            <div class="flex items-center space-x-3">
                <a href="contact.php"
                    class="font-display bg-[#00aff0] text-white hover:bg-[#009ece] text-xs font-bold px-5 py-2.5 rounded-full transition-all duration-300 shadow-sm hover:shadow">
                    Contact Us
                </a>
            </div>
        <?php endif; ?>
    </div>
    <!-- Brand Scroll Progress Bar -->
    <div id="scroll-progress-bar"
        class="absolute bottom-0 left-0 h-[2.5px] bg-gradient-to-r from-[#00aff0] to-[#ec3237] transition-all duration-75"
        style="width: 0%;"></div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const header = document.getElementById('main-header');
        const logo = document.getElementById('header-logo');
        const progress = document.getElementById('scroll-progress-bar');

        function updateHeaderScroll() {
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;

            if (scrollTop > 80) {
                header.classList.add('bg-white/85', 'h-24', 'border-[#e2e8f0]', 'shadow-sm');
                header.classList.remove('bg-white/72', 'h-28', 'border-[#e2e8f0]/40');
                if (logo) {
                    logo.classList.add('h-28');
                    logo.classList.remove('h-36');
                }
            } else {
                header.classList.add('bg-white/72', 'h-28', 'border-[#e2e8f0]/40');
                header.classList.remove('bg-white/85', 'h-24', 'border-[#e2e8f0]', 'shadow-sm');
                if (logo) {
                    logo.classList.add('h-36');
                    logo.classList.remove('h-28');
                }
            }

            if (progress && scrollHeight > 0) {
                const percent = (scrollTop / scrollHeight) * 100;
                progress.style.width = percent + '%';
            }
        }

        window.addEventListener('scroll', updateHeaderScroll, { passive: true });
        // Initial run to check position on load
        updateHeaderScroll();
    });
</script>
<?php
// ==========================================================================
// DELIGHT BUILDERS - CONTACT US / INQUIRY PAGE
// ==========================================================================
require_once 'admin/db_connection.php';

$is_gallery = true;
$title_indicator = "CONTACT";
$back_to_section = "";

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

$logo_path = resolve_asset_path(get_setting('logo_path', 'asset/images/logo.png'));

// Fetch categories for inquiry form
try {
    $stmt_cats = $pdo->query("SELECT name FROM portfolio_categories ORDER BY name ASC");
    $db_categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $db_categories = [];
}

$contact_sent = false;
$contact_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
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
            $contact_sent = true;
        } catch (PDOException $e) {
            $contact_error = "Database Error: Failed to log your inquiry. Please try again later.";
        }
    } else {
        $contact_error = "Please fill in all fields with valid information.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth bg-[#f8fafc]">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delight Builders | Contact Us & Project Inquiry</title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logo_path); ?>">

    <!-- Meta tags for SEO -->
    <meta name="description"
        content="Securely contact Delight Builders regarding custom residential estates, commercial frameworks, or sustainable biophilic consulting.">
    <meta name="keywords"
        content="Contact Delight Builders, project inquiry, secure construction form, architectural consultation, Kerala builders">

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
    <link rel="stylesheet" href="asset/css/style.css?v=<?php echo filemtime('asset/css/style.css'); ?>">
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
        <main class="flex-grow py-20 px-6 md:px-16 lg:px-24">
            <div class="max-w-7xl mx-auto mt-6">

                <!-- Page Title -->
                <div class="text-center md:text-left space-y-4 mb-16">
                    <span class="text-xs text-[#00aff0] tracking-widest uppercase font-semibold block">01 / Secure
                        Channel</span>
                    <h1 class="font-display text-4xl md:text-5xl text-[#0f172a] font-extrabold tracking-tight">
                        Initiate Commission
                    </h1>
                    <div class="w-16 h-[2px] bg-[#00aff0] mx-auto md:mx-0"></div>
                </div>

                <!-- Content Columns -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">

                    <!-- Left Column: Coordinates & Blueprint Schematic Map -->
                    <div class="lg:col-span-5 space-y-10">
                        <div class="space-y-6">
                            <span class="text-[10px] uppercase font-bold tracking-widest text-[#ec3237] block">
                                Corporate Details
                            </span>
                            <h2
                                class="font-display text-2xl text-[#0f172a] font-extrabold tracking-tight leading-tight">
                                Direct Office Coordinates
                            </h2>
                            <p class="text-[#0f172a]/70 text-sm leading-relaxed font-normal">
                                Connect directly with our Kerala HQ or arrange a private structural consultation. We
                                coordinate files and blueprints securely.
                            </p>
                        </div>

                        <!-- Info details grid -->
                        <div class="space-y-6 text-sm">
                            <div class="space-y-1">
                                <div class="text-[10px] tracking-wider uppercase text-[#64748b] font-bold">KERALA HQ
                                    LOCATION</div>
                                <p class="text-[#0f172a] font-medium leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars(get_setting('office_address', "First floor, 449/A4, Delight Builders Cherakkalayil Complex,\nKakkanad Pallikara Rd, Kakkanad, Kerala • Pin: 683565"))); ?>
                                </p>
                            </div>

                            <div class="space-y-1">
                                <div class="text-[10px] tracking-wider uppercase text-[#64748b] font-bold">BUSINESS
                                    HOURS</div>
                                <p class="text-[#0f172a] font-medium"><?php echo htmlspecialchars(get_setting('business_hours', 'Monday — Saturday: 9:00 AM — 6:00 PM IST')); ?></p>
                            </div>

                            <div class="space-y-1">
                                <div class="text-[10px] tracking-wider uppercase text-[#64748b] font-bold">SECURE
                                    COMMUNICATIONS</div>
                                <p class="text-[#0f172a] font-semibold flex flex-col space-y-1">
                                    <span
                                        class="text-[#00aff0] hover:underline cursor-pointer"><?php echo htmlspecialchars(get_setting('contact_email', 'inquire@delightbuilders.com')); ?></span>
                                    <span class="text-[#0f172a]/70 font-medium"><?php echo htmlspecialchars(get_setting('contact_phone', '+91 484 234 5678')); ?></span>
                                </p>
                            </div>
                        </div>



                        <!-- Google Maps Embed Container -->
                        <div
                            class="relative w-full h-64 border border-[#e2e8f0] rounded-3xl overflow-hidden shadow-[0_8px_30px_rgba(0,0,0,0.02)] apple-card mt-8">
                            <iframe
                                src="<?php echo htmlspecialchars(get_setting('google_maps_iframe', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.9064267811978!2d76.40098747479394!3d10.024580290082096!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b07e2f4640c474d%3A0xb28071796b18b1a7!2sDelight%20Builders%20Kakkanad!5e0!3m2!1sen!2sin!4v1782197484219!5m2!1sen!2sin')); ?>"
                                class="w-full h-full border-0" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>

                    <!-- Right Column: Glassmorphic Contact Form -->
                    <div class="lg:col-span-7">
                        <div
                            class="glass-panel border border-[#e2e8f0]/80 rounded-3xl p-8 md:p-12 shadow-[0_8px_30px_rgba(0,0,0,0.04)] relative">

                            <?php if ($contact_sent): ?>
                                <!-- Success Panel -->
                                <div class="text-center py-16 space-y-6">
                                    <div
                                        class="w-16 h-16 border border-[#00aff0] rounded-full flex items-center justify-center mx-auto text-[#00aff0] shadow-[0_0_15px_rgba(0,175,240,0.2)]">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <h3 class="font-display text-2xl text-[#0f172a] font-extrabold tracking-tight">
                                        Message Successfully Locked In
                                    </h3>
                                    <p class="text-[#0f172a]/75 font-normal text-sm max-w-md mx-auto leading-relaxed">
                                        Thank you, <strong class="text-[#0f172a]"><?php echo $name; ?></strong>. Your
                                        project request has been logged. An architectural partner will review your
                                        categories of interest (<strong><?php echo $category; ?></strong>) and follow up at
                                        <span class="text-[#00aff0] font-medium"><?php echo $email; ?></span> within 24
                                        standard business hours.
                                    </p>
                                    <div class="pt-6">
                                        <a href="contact.php"
                                            class="text-xs tracking-wider text-[#00aff0] hover:text-[#0f172a] uppercase font-bold transition-colors">
                                            Send Another Message
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Form Inputs -->
                                <?php if (!empty($contact_error)): ?>
                                    <div
                                        class="mb-6 p-4 bg-[#ec3237]/10 border border-[#ec3237]/20 text-[#ec3237] rounded-xl text-sm font-medium">
                                        <?php echo $contact_error; ?>
                                    </div>
                                <?php endif; ?>

                                <form action="contact.php" method="POST" class="space-y-8">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <!-- Name -->
                                        <div class="space-y-2">
                                            <label for="name"
                                                class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Client
                                                Name</label>
                                            <input type="text" id="name" name="name" required
                                                class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                                placeholder="e.g. Alaric K. Vance">
                                        </div>

                                        <!-- Email -->
                                        <div class="space-y-2">
                                            <label for="email"
                                                class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Secure
                                                Contact Email</label>
                                            <input type="email" id="email" name="email" required
                                                class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                                placeholder="e.g. alaric@vancemaritime.com">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <!-- Phone -->
                                        <div class="space-y-2">
                                            <label for="phone"
                                                class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Secure
                                                Phone Number</label>
                                            <input type="tel" id="phone" name="phone" required
                                                class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                                placeholder="e.g. +91 98765 43210">
                                        </div>

                                        <!-- WhatsApp -->
                                        <div class="space-y-2">
                                            <label for="whatsapp"
                                                class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">WhatsApp Number *</label>
                                            <input type="tel" id="whatsapp" name="whatsapp" required
                                                class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300"
                                                placeholder="e.g. +91 98765 43210">
                                        </div>
                                    </div>

                                    <!-- Category Sector Selection -->
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
                                            <div
                                                class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                                ▼
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Message -->
                                    <div class="space-y-2">
                                        <label for="message"
                                            class="block text-[10px] tracking-wider uppercase text-[#64748b] font-bold">Project
                                            Goals & Description</label>
                                        <textarea id="message" name="message" rows="5" required
                                            class="w-full bg-[#f8fafc] border-0 focus:bg-white focus:ring-2 focus:ring-[#00aff0] px-4 py-3.5 text-[#0f172a] placeholder-[#64748b]/70 text-sm tracking-wide rounded-xl focus:outline-none transition-all duration-300 resize-none"
                                            placeholder="Describe your design intentions, coordinates, timeline, or special timber/concrete requirements..."></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="pt-4">
                                        <button type="submit" name="submit_contact"
                                            class="w-full bg-[#0f172a] text-white hover:bg-[#009ece] font-display text-xs font-bold uppercase tracking-wider py-4 rounded-xl transition-all duration-300 hover:shadow-[0_10px_25px_rgba(0,175,240,0.12)]">
                                            Send Secure Inquiry Package
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>

            </div>
        </main>

        <!-- FOOTER SECTION -->
        <footer class="bg-[#f8fafc]/95 py-16 px-6 md:px-16 border-t border-[#e2e8f0]/60 z-20 relative">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
                <!-- Branding -->
                <div class="flex flex-col items-center md:items-start space-y-3">
                    <img src="<?php echo $logo_path; ?>" alt="Delight Builders Logo" class="h-16 w-auto object-contain">
                    <span class="font-display text-[#0f172a] font-extrabold tracking-widest text-base uppercase">DELIGHT
                        BUILDERS</span>
                    <span class="text-[10px] tracking-widest text-[#64748b] font-semibold uppercase">STRUCTURAL POETRY •
                        SINCE 2006</span>
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
<?php
// ==========================================
// DELIGHT BUILDERS - DATABASE CONNECTION & AUTO-MIGRATION
// ==========================================

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'delight_db';

try {
    // 1. First connect to MySQL without selecting a database to ensure we can create it
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// 2. Perform Migration & Auto-Table Creation
try {
    // Admin Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        full_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // Site Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key_name VARCHAR(50) PRIMARY KEY,
        value TEXT
    ) ENGINE=InnoDB;");

    // Portfolio Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS portfolio (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        description TEXT,
        image VARCHAR(255) NOT NULL,
        stage VARCHAR(100) DEFAULT NULL,
        materiality VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // Portfolio Gallery Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        portfolio_id INT NOT NULL,
        src VARCHAR(255) NOT NULL,
        title VARCHAR(100),
        desc_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // Alter table to add stage and materiality to portfolio if they don't exist
    try {
        $pdo->exec("ALTER TABLE portfolio ADD COLUMN stage VARCHAR(100) DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE portfolio ADD COLUMN materiality VARCHAR(100) DEFAULT NULL");
    } catch (PDOException $e) {}

    // Alter table to add stage and materiality to portfolio_gallery if they don't exist
    try {
        $pdo->exec("ALTER TABLE portfolio_gallery ADD COLUMN stage VARCHAR(100) DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE portfolio_gallery ADD COLUMN materiality VARCHAR(100) DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE portfolio_gallery ADD COLUMN show_in_gallery TINYINT(1) DEFAULT 1");
    } catch (PDOException $e) {}

    // Ensure all existing rows have default/migrated values for stages and materiality
    try {
        $pdo->exec("UPDATE portfolio SET stage = 'Construction' WHERE stage IS NULL OR stage = ''");
        $pdo->exec("UPDATE portfolio SET materiality = 'Premium Curated' WHERE materiality IS NULL OR materiality = ''");
        $pdo->exec("UPDATE portfolio_gallery SET stage = 'Consultation' WHERE stage IS NULL AND (title LIKE '%Exterior%' OR title LIKE '%Facade%' OR title LIKE '%Canopy%')");
        $pdo->exec("UPDATE portfolio_gallery SET stage = 'Design & Planning' WHERE stage IS NULL AND (title LIKE '%Foyer%' OR title LIKE '%Exoskeleton%' OR title LIKE '%Arches%' OR title LIKE '%Node%')");
        $pdo->exec("UPDATE portfolio_gallery SET stage = 'Handover' WHERE stage IS NULL AND (title LIKE '%Firepit%' OR title LIKE '%Night%' OR title LIKE '%Lounge%' OR title LIKE '%Water%')");
        $pdo->exec("UPDATE portfolio_gallery SET stage = 'Construction' WHERE stage IS NULL");
        $pdo->exec("UPDATE portfolio_gallery SET materiality = 'Premium Curated' WHERE materiality IS NULL OR materiality = ''");
    } catch (PDOException $e) {}

    // Key Stats Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        number VARCHAR(20) NOT NULL,
        label VARCHAR(100) NOT NULL,
        icon VARCHAR(50) NOT NULL,
        sort_order INT DEFAULT 0
    ) ENGINE=InnoDB;");

    // Core Pillars Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS pillars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        icon VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        sort_order INT DEFAULT 0
    ) ENGINE=InnoDB;");

    // Milestones Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS milestones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        year VARCHAR(20) NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        sort_order INT DEFAULT 0
    ) ENGINE=InnoDB;");

    // Team Members Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        role VARCHAR(100) NOT NULL,
        avatar_text VARCHAR(10) NOT NULL,
        description TEXT NOT NULL,
        sort_order INT DEFAULT 0,
        image VARCHAR(255) DEFAULT NULL,
        parent_id INT DEFAULT NULL
    ) ENGINE=InnoDB;");

    // Alter table to add image and parent_id to team_members if they don't exist
    try {
        $pdo->exec("ALTER TABLE team_members ADD COLUMN image VARCHAR(255) DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE team_members ADD COLUMN parent_id INT DEFAULT NULL");
    } catch (PDOException $e) {}

    // Client Inquiries Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(30),
        whatsapp VARCHAR(30) DEFAULT NULL,
        category VARCHAR(50),
        message TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    try {
        $pdo->exec("ALTER TABLE inquiries ADD COLUMN whatsapp VARCHAR(30) DEFAULT NULL");
    } catch (PDOException $e) {}

    // Portfolio Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS portfolio_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // Testimonials Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(100) NOT NULL,
        client_designation VARCHAR(150) NOT NULL,
        project_name VARCHAR(100) NOT NULL,
        quote TEXT NOT NULL,
        color VARCHAR(20) DEFAULT 'blue',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

} catch (PDOException $e) {
    die("Database Migration Error: " . $e->getMessage());
}

// 3. Auto-Seeder: Populate default content if tables are empty
try {
    // A. Seed Admin User
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
    if ($stmt->fetchColumn() == 0) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $email = 'admin@delightbuilders.com';
        $full_name = 'Principal Admin';
        $insert = $pdo->prepare("INSERT INTO admin_users (username, password, email, full_name) VALUES (?, ?, ?, ?)");
        $insert->execute([$username, $password, $email, $full_name]);
    }

    // B. Seed Site Settings
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $default_settings = [
            'site_title' => 'Delight Builders | Architects of Permanence & Luxury Construction',
            'established_year' => '2006',
            'coordinates' => '40.7128° N, 74.0060° W',
            'contact_email' => 'inquire@delightbuilders.com',
            'contact_phone' => '+91 484 234 5678',
            'business_hours' => 'Monday — Saturday: 9:00 AM — 6:00 PM IST',
            'office_address' => 'First floor, 449/A4, Delight Builders Cherakkalayil Complex, Kakkanad Pallikara Rd, Kakkanad, Kerala • Pin: 683565',
            'google_maps_iframe' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.9064267811978!2d76.40098747479394!3d10.024580290082096!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b07e2f4640c474d%3A0xb28071796b18b1a7!2sDelight%20Builders%20Kakkanad!5e0!3m2!1sen!2sin!4v1782197484219!5m2!1sen!2sin',
            'logo_path' => 'asset/images/logo.png',
            'hero_subtitle' => 'Sculpting structural blueprints into monolithic icons of stone, glass, and timber. We build custom architectural poetry.',
            'philosophy_text_1' => 'We do not merely construct spaces; we synthesize permanent environments. By uniting the tactile gravity of custom-cast concrete with the weightless fluidity of structural glass, Delight Builders challenges the ephemeral nature of modern housing.',
            'philosophy_text_2' => 'Every commission is executed with absolute structural precision. From deep soil diagnostics to custom seismic load profiles, our engineering team constructs structural poetry that guarantees durability across centuries.',
            'about_hero_desc' => 'Creating spaces with purpose and precision. Established in 2006, Delight Builders challenges transient architectural trends, synthesizing volumetric concrete gravity and biophilic structural systems to deliver custom residences and corporate structures calculated to endure for generations.'
        ];
        $insert = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)");
        foreach ($default_settings as $key => $value) {
            $insert->execute([$key, $value]);
        }
    }

    // C. Seed Stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM stats");
    if ($stmt->fetchColumn() == 0) {
        $default_stats = [
            ['number' => '10+', 'label' => 'YEARS OF EXPERIENCE', 'icon' => 'helmet', 'sort_order' => 1],
            ['number' => '250+', 'label' => 'COMPLETED PROJECTS', 'icon' => 'house', 'sort_order' => 2],
            ['number' => '14', 'label' => 'DISTRICTS ACROSS KERALA', 'icon' => 'map-pin', 'sort_order' => 3],
            ['number' => '500+', 'label' => 'HAPPY FAMILIES SERVED', 'icon' => 'family', 'sort_order' => 4]
        ];
        $insert = $pdo->prepare("INSERT INTO stats (number, label, icon, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($default_stats as $s) {
            $insert->execute([$s['number'], $s['label'], $s['icon'], $s['sort_order']]);
        }
    }

    // D. Seed Pillars
    $stmt = $pdo->query("SELECT COUNT(*) FROM pillars");
    if ($stmt->fetchColumn() == 0) {
        $default_pillars = [
            ['title' => 'Tactile Gravity', 'icon' => 'gravity', 'description' => 'We leverage raw, custom-poured concrete to create structural volumes with physical gravity. This guarantees generational durability and strict seismic safety standards.', 'sort_order' => 1],
            ['title' => 'Weightless Fluidity', 'icon' => 'fluidity', 'description' => 'Leveraging structural glass exoskeletons, we frame borderless natural views, distributing daylight deeply while maintaining energy-efficient thermal boundaries.', 'sort_order' => 2],
            ['title' => 'Biophilic Harmony', 'icon' => 'harmony', 'description' => 'We utilize laminated timber (glulam) arches and metabolic shading configurations to foster a carbon-negative dialogue between physical structures and local micro-climates.', 'sort_order' => 3]
        ];
        $insert = $pdo->prepare("INSERT INTO pillars (title, icon, description, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($default_pillars as $p) {
            $insert->execute([$p['title'], $p['icon'], $p['description'], $p['sort_order']]);
        }
    }

    // E. Seed Milestones
    $stmt = $pdo->query("SELECT COUNT(*) FROM milestones");
    if ($stmt->fetchColumn() == 0) {
        $default_milestones = [
            ['year' => '2006', 'title' => 'The Initial Foundation', 'description' => 'Delight Builders opens operations in Kerala, carving out a specialized niche in high-precision structural residential estates and volcanic travertine stone details.', 'sort_order' => 1],
            ['year' => '2007', 'title' => 'Commercial & Steel Scaling', 'description' => 'Integration of grade-5 titanium truss connections and high-strength exoskeletons. We launched column-free designs, scaling our reach to commercial structures.', 'sort_order' => 2],
            ['year' => '2017', 'title' => 'Carbon-Negative Frameworks', 'description' => 'Transition to green and biophilic frameworks, incorporating glulam timber framing arches, dynamic sun louvers, and passive solar greywater pipelines.', 'sort_order' => 3],
            ['year' => '2026', 'title' => 'The Blueprint Age', 'description' => 'Pioneering complete database transparency. We launch our visual blueprints archive to allow clients to track details and geometries online.', 'sort_order' => 4]
        ];
        $insert = $pdo->prepare("INSERT INTO milestones (year, title, description, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($default_milestones as $m) {
            $insert->execute([$m['year'], $m['title'], $m['description'], $m['sort_order']]);
        }
    }

    // F. Seed Team Members
    $stmt = $pdo->query("SELECT COUNT(*) FROM team_members");
    if ($stmt->fetchColumn() == 0) {
        $default_team = [
            ['name' => 'Sterling H. Croft', 'role' => 'Principal Architect & Founder', 'avatar_text' => 'SC', 'description' => 'Spearheads conceptual planning and brutalist massing. Sterling ensures every residence integrates architectural gravity with local local travertine stone geometries.', 'sort_order' => 1, 'parent_id' => null],
            ['name' => 'Elena R. Vane', 'role' => 'Lead Structural Engineer', 'avatar_text' => 'EV', 'description' => 'Directs structural math connections, soil diagnostic systems, seismic profiles, and grade-5 titanium truss connections with absolute mathematical rigor.', 'sort_order' => 2, 'parent_id' => 1],
            ['name' => 'Julian K. Mercer', 'role' => 'Chief Design Officer', 'avatar_text' => 'JM', 'description' => 'Pioneers timber arch structures, passive ventilation systems, and active biophilic elements to merge living micro-climates into private residences.', 'sort_order' => 3, 'parent_id' => 1]
        ];
        $insert = $pdo->prepare("INSERT INTO team_members (name, role, avatar_text, description, sort_order, parent_id) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($default_team as $t) {
            $insert->execute([$t['name'], $t['role'], $t['avatar_text'], $t['description'], $t['sort_order'], $t['parent_id']]);
        }
    }

    // G. Seed Portfolio and child Gallery Blueprints
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio");
    if ($stmt->fetchColumn() == 0) {
        $portfolio_data = [
            [
                "title" => "The Obsidian Villa",
                "category" => "Luxury Residential",
                "description" => "A brutalist concrete and glass masterwork nestled in absolute privacy, featuring cantilevered terraces and integrated infinity pools.",
                "image" => "asset/images/1 (12).jpg",
                "stage" => "Construction",
                "materiality" => "Brutalist Travertine",
                "gallery" => [
                    ["src" => "asset/images/1 (12).jpg", "title" => "Obsidian Exterior", "desc" => "Brutalist raw concrete mass juxtaposed with ultra-clear low-iron panoramic glazing panels.", "stage" => "Consultation", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (1).jpg", "title" => "The Grand Foyer", "desc" => "Entryway detailing incorporating custom cast-concrete structural columns and structural timber frame joints.", "stage" => "Design & Planning", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (2).jpg", "title" => "Living Oasis", "desc" => "Double-height glazing framing panoramic ocean views, integrating natural stone flooring.", "stage" => "Construction", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (3).jpg", "title" => "Infinity Lounge", "desc" => "Cantilevered deck and pool border detail suspended over the coastal cliffs.", "stage" => "Construction", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (4).jpg", "title" => "Bespoke Travertine Stairwell", "desc" => "Floating steps carved from raw local travertine, anchored to a central load-bearing core.", "stage" => "Construction", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (5).jpg", "title" => "Master Wing", "desc" => "Open floor plan master bedroom utilizing passive thermal systems and integrated internal gardens.", "stage" => "Construction", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (6).jpg", "title" => "Private Courtyard", "desc" => "Zen-inspired concrete pool deck offering visual seclusion and reflection pools.", "stage" => "Construction", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (7).jpg", "title" => "Thermal Wine Cellar", "desc" => "Underground vaulted chamber built from sand-blasted volcanic stone for natural climate regulation.", "stage" => "Construction", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (8).jpg", "title" => "Sunken Firepit", "desc" => "Custom-poured terraced concrete outdoor lounge with clean geometric lines.", "stage" => "Handover", "materiality" => "Brutalist Travertine"],
                    ["src" => "asset/images/1 (9).jpg", "title" => "Night Perspective", "desc" => "Smart architectural lighting accents tracing the brutalist angles of the residence at twilight.", "stage" => "Handover", "materiality" => "Brutalist Travertine"]
                ]
            ],
            [
                "title" => "Aether Spine Towers",
                "category" => "Commercial Frameworks",
                "description" => "A column-free glass skyscraper leveraging advanced structural steel grids for optimal light exposure and programmatic flexibility.",
                "image" => "asset/images/1 (13).jpg",
                "stage" => "Handover",
                "materiality" => "Titanium Steel Grid",
                "gallery" => [
                    ["src" => "asset/images/1 (13).jpg", "title" => "Aether Spine Facade", "desc" => "Heavy structural steel grid exoskeleton defining the tower's modern geometric face.", "stage" => "Consultation", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (10).jpg", "title" => "Steel Exoskeleton", "desc" => "High-strength structural steel joints showcasing robotic welding patterns and tension anchors.", "stage" => "Design & Planning", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (11).jpg", "title" => "The Glass Atrium", "desc" => "Oversized structural glass canopy distributing load and flooding the lobby with natural light.", "stage" => "Construction", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (14).jpg", "title" => "Corporate Lobby", "desc" => "Seamless white terrazzo layout with suspended structural steel mezzanine and lighting grids.", "stage" => "Construction", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (15).jpg", "title" => "Conference Tier", "desc" => "Suspended geometric acoustical sound barriers and warm walnut timber wall cladding.", "stage" => "Construction", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (16).jpg", "title" => "Facade Detailing", "desc" => "Double-glazed energy-reflective facade panels with integrated micro-shading systems.", "stage" => "Construction", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (17).jpg", "title" => "Sky Lounge", "desc" => "Cantilevered sky platform offering borderless views of the urban skyline.", "stage" => "Handover", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (18).jpg", "title" => "Structural Node", "desc" => "Grade-5 titanium structural spider connection points absorbing wind shear forces.", "stage" => "Design & Planning", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (19).jpg", "title" => "Mechanical Core", "desc" => "Industrial HVAC integration combining smart airflow control and smart energy consumption metrics.", "stage" => "Construction", "materiality" => "Titanium Steel Grid"],
                    ["src" => "asset/images/1 (20).jpg", "title" => "Plaza Water Walls", "desc" => "Curated public boundary space with thin concrete pools and vertical waterfall noise dampeners.", "stage" => "Handover", "materiality" => "Titanium Steel Grid"]
                ]
            ],
            [
                "title" => "Biophilic Pavilion",
                "category" => "Sustainable Fits",
                "description" => "A carbon-negative wellness center utilizing glulam structural timber, passive geothermal cooling, and smart greywater filters.",
                "image" => "asset/images/1 (21).jpg",
                "stage" => "Handover",
                "materiality" => "Glulam Timber",
                "gallery" => [
                    ["src" => "asset/images/1 (21).jpg", "title" => "Pavilion Canopy", "desc" => "Curved timber structural roof framing merging into the natural landscape.", "stage" => "Consultation", "materiality" => "Glulam Timber"],
                    ["src" => "asset/images/1 (22).jpg", "title" => "Laminated Timber Arches", "desc" => "Glue-laminated structural wood ribs providing organic curves and massive column-free spaces.", "stage" => "Design & Planning", "materiality" => "Glulam Timber"],
                    ["src" => "asset/images/1 (23).jpg", "title" => "Photovoltaic Grid", "desc" => "High-efficiency solar cells flush-integrated into custom timber roof paneling.", "stage" => "Construction", "materiality" => "Glulam Timber"],
                    ["src" => "asset/images/1 (24).jpg", "title" => "Rainwater Columns", "desc" => "Vertical copper filtration conduits routing greywater to biological filtration tanks.", "stage" => "Construction", "materiality" => "Glulam Timber"],
                    ["src" => "asset/images/1 (25).jpg", "title" => "Geothermal Vaults", "desc" => "Subterranean energy exchange pipeline layout supplying passive floor cooling and heating.", "stage" => "Construction", "materiality" => "Glulam Timber"],
                    ["src" => "asset/images/1 (26).jpg", "title" => "Green Wall Atrium", "desc" => "Hydroponic living walls acting as natural oxygen generators and indoor air scrubbers.", "stage" => "Construction", "materiality" => "Glulam Timber"],
                    ["src" => "asset/images/1 (27).jpg", "title" => "CLT Floor Decking", "desc" => "Cross-laminated timber layers left exposed to provide warm texture and low-carbon structural integrity.", "stage" => "Construction", "materiality" => "Glulam Timber"],
                    ["src" => "asset/images/1 (28).jpg", "title" => "Kinetic Solar Fins", "desc" => "Responsive timber louvers tracking solar paths dynamically to optimize light and shading.", "stage" => "Handover", "materiality" => "Glulam Timber"]
                ]
            ]
        ];

        foreach ($portfolio_data as $project) {
            $insert_portfolio = $pdo->prepare("INSERT INTO portfolio (title, category, description, stage, materiality, image) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_portfolio->execute([$project['title'], $project['category'], $project['description'], $project['stage'], $project['materiality'], $project['image']]);
            $portfolio_id = $pdo->lastInsertId();

            $insert_gallery = $pdo->prepare("INSERT INTO portfolio_gallery (portfolio_id, src, title, desc_text, stage, materiality) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($project['gallery'] as $img) {
                $insert_gallery->execute([$portfolio_id, $img['src'], $img['title'], $img['desc'], $img['stage'], $img['materiality']]);
            }
        }
    }

    // H. Seed Portfolio Categories
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_categories");
    if ($stmt->fetchColumn() == 0) {
        $default_cats = ['Luxury Residential', 'Commercial Frameworks', 'Sustainable Fits'];
        $insert_cat = $pdo->prepare("INSERT INTO portfolio_categories (name) VALUES (?)");
        foreach ($default_cats as $cat_name) {
            $insert_cat->execute([$cat_name]);
        }
    }

    // I. Seed Testimonials
    $stmt = $pdo->query("SELECT COUNT(*) FROM testimonials");
    if ($stmt->fetchColumn() == 0) {
        $default_testimonials = [
            [
                'client_name' => 'Isadora R. Sterling',
                'client_designation' => 'Philanthropist & Art Collector',
                'project_name' => 'The Obsidian Villa',
                'quote' => 'Their brutalist gravity combined with carbon-negative glulam timber frames is revolutionary. Our custom seaside villa stands as a generational masterpiece.',
                'color' => 'red',
                'sort_order' => 1
            ],
            [
                'client_name' => 'Alaric K. Vance',
                'client_designation' => 'Managing Director, Vance Maritime',
                'project_name' => 'The Aether Spine Towers',
                'quote' => 'Delight Builders synthesizes raw concrete mass and biophilic glass to create living, breathing structural poetry. The attention to volumetric math was outstanding.',
                'color' => 'blue',
                'sort_order' => 2
            ],
            [
                'client_name' => 'Dr. Cassian G. Vance',
                'client_designation' => 'Director, Kerala Eco-Institute',
                'project_name' => 'The Biophilic Pavilion',
                'quote' => 'The database blueprint transparency allowed us to track every seismic soil calculation and glulam timber joint in real time. Absolute structural confidence.',
                'color' => 'purple',
                'sort_order' => 3
            ]
        ];
        $insert = $pdo->prepare("INSERT INTO testimonials (client_name, client_designation, project_name, quote, color, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($default_testimonials as $t) {
            $insert->execute([$t['client_name'], $t['client_designation'], $t['project_name'], $t['quote'], $t['color'], $t['sort_order']]);
        }
    }
} catch (PDOException $e) {
    die("Database Seeding Error: " . $e->getMessage());
}

/**
 * Helper function to retrieve a setting value from settings table
 */
function get_setting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Helper function to compute initials from name
 */
function get_initials($name) {
    $clean_name = preg_replace('/^(dr\.|mr\.|ms\.|mrs\.|prof\.)\s+/i', '', trim($name));
    $words = explode(' ', $clean_name);
    $initials = '';
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
    } elseif (count($words) === 1 && !empty($words[0])) {
        $initials = strtoupper(substr($words[0], 0, 2));
    } else {
        $initials = 'DB';
    }
    return $initials;
}

-- Delight Builders - Database Backup
-- Generated: 2026-07-16 06:07:09
-- MySQL Server Version: 10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `delight_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `delight_db`;

-- --------------------------------------------------------
-- Table structure for `admin_users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admin_users`
LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES 
('1', 'admin', '$2y$10$qiX95NIS1ieOUekYlspnQOGLZaAS.dxloCimhykCmLFvDlAA7e0tq', 'admin@delightbuilders.com', 'Principal Admin', '2026-06-23 14:37:14');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `inquiries`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `whatsapp` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `inquiries`
LOCK TABLES `inquiries` WRITE;
/*!40000 ALTER TABLE `inquiries` DISABLE KEYS */;
INSERT INTO `inquiries` VALUES 
('7', 'Test User', 'testuser@example.com', '9999999999', 'Luxury Residential', 'TEST1', 'replied', '2026-06-24 09:11:14', NULL),
('8', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', NULL, 'Luxury Residential', 'TEST2\n\n[Planned Capital Scope: 1m-5m]', 'read', '2026-06-24 09:11:56', NULL),
('9', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', NULL, 'Luxury Residential', 'TEST2\n\n[Planned Capital Scope: 1m-5m]', 'read', '2026-06-24 09:12:05', NULL),
('10', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', NULL, 'Commercial', 'TEST3\n\n[Planned Capital Scope: 1m-5m]', 'read', '2026-06-24 09:13:01', NULL),
('11', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', NULL, 'Commercial', 'TEST3\n\n[Planned Capital Scope: 1m-5m]', 'read', '2026-06-24 09:14:33', NULL),
('12', 'Test User', 'testuser@example.com', NULL, 'Luxury Residential', 'test 5', 'read', '2026-06-24 09:18:01', NULL),
('13', 'DIJO', 'DIJO@GMAIL.COM', '9946020724', 'Luxury Residential', 'NEED A FLAT', 'read', '2026-06-24 14:28:31', '9946020724');
/*!40000 ALTER TABLE `inquiries` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `milestones`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `milestones`;
CREATE TABLE `milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `milestones`
LOCK TABLES `milestones` WRITE;
/*!40000 ALTER TABLE `milestones` DISABLE KEYS */;
INSERT INTO `milestones` VALUES 
('1', '2006', 'The Initial Foundation', 'Delight Builders opens operations in Kerala, carving out a specialized niche in high-precision structural residential estates and volcanic travertine stone details.', '1'),
('2', '2007', 'Commercial & Steel Scaling', 'Integration of grade-5 titanium truss connections and high-strength exoskeletons. We launched column-free designs, scaling our reach to commercial structures.', '2'),
('3', '2017', 'Carbon-Negative Frameworks', 'Transition to green and biophilic frameworks, incorporating glulam timber framing arches, dynamic sun louvers, and passive solar greywater pipelines.', '3'),
('4', '2026', 'The Blueprint Age', 'Pioneering complete database transparency. We launch our visual blueprints archive to allow clients to track details and geometries online.', '4');
/*!40000 ALTER TABLE `milestones` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `pillars`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pillars`;
CREATE TABLE `pillars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `pillars`
LOCK TABLES `pillars` WRITE;
/*!40000 ALTER TABLE `pillars` DISABLE KEYS */;
INSERT INTO `pillars` VALUES 
('1', 'Tactile Gravity', 'gravity', 'We leverage raw, custom-poured concrete to create structural volumes with physical gravity. This guarantees generational durability and strict seismic safety standards.', '1'),
('2', 'Weightless Fluidity', 'fluidity', 'Leveraging structural glass exoskeletons, we frame borderless natural views, distributing daylight deeply while maintaining energy-efficient thermal boundaries.', '2'),
('3', 'Biophilic Harmony', 'harmony', 'We utilize laminated timber (glulam) arches and metabolic shading configurations to foster a carbon-negative dialogue between physical structures and local micro-climates.', '3');
/*!40000 ALTER TABLE `pillars` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `portfolio`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `portfolio`;
CREATE TABLE `portfolio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stage` varchar(100) DEFAULT NULL,
  `materiality` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `portfolio`
LOCK TABLES `portfolio` WRITE;
/*!40000 ALTER TABLE `portfolio` DISABLE KEYS */;
INSERT INTO `portfolio` VALUES 
('1', 'The Obsidian Villa', 'Luxury Residential', 'A brutalist concrete and glass masterwork nestled in absolute privacy, featuring cantilevered terraces and integrated infinity pools.', 'asset/images/1 (12).jpg', '2026-06-23 14:37:14', 'Construction', 'Premium Curated'),
('2', 'Aether Spine Towers', 'Commercial', 'A column-free glass skyscraper leveraging advanced structural steel grids for optimal light exposure and programmatic flexibility.', 'asset/images/1 (13).jpg', '2026-06-23 14:37:14', 'Construction', 'Premium Curated'),
('3', 'Biophilic Pavilion', 'Sustainable Fits', 'A carbon-negative wellness center utilizing glulam structural timber, passive geothermal cooling, and smart greywater filters.', 'asset/images/1 (21).jpg', '2026-06-23 14:37:14', 'Construction', 'Premium Curated'),
('10', 'VILLLA', 'Commercial', 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.', 'asset/images/1782289961_kmanorhomepage.jpg', '2026-06-24 14:00:29', 'Design & Planning', 'PREMIUM');
/*!40000 ALTER TABLE `portfolio` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `portfolio_categories`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `portfolio_categories`;
CREATE TABLE `portfolio_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `portfolio_categories`
LOCK TABLES `portfolio_categories` WRITE;
/*!40000 ALTER TABLE `portfolio_categories` DISABLE KEYS */;
INSERT INTO `portfolio_categories` VALUES 
('1', 'Luxury Residential', '2026-06-23 23:41:29'),
('2', 'Commercial', '2026-06-23 23:41:29'),
('3', 'Sustainable Fits', '2026-06-23 23:41:29'),
('5', 'test', '2026-06-23 23:52:02');
/*!40000 ALTER TABLE `portfolio_categories` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `portfolio_gallery`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `portfolio_gallery`;
CREATE TABLE `portfolio_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `portfolio_id` int(11) NOT NULL,
  `src` varchar(255) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `desc_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stage` varchar(100) DEFAULT NULL,
  `materiality` varchar(100) DEFAULT NULL,
  `show_in_gallery` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `portfolio_id` (`portfolio_id`),
  CONSTRAINT `portfolio_gallery_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `portfolio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `portfolio_gallery`
LOCK TABLES `portfolio_gallery` WRITE;
/*!40000 ALTER TABLE `portfolio_gallery` DISABLE KEYS */;
INSERT INTO `portfolio_gallery` VALUES 
('1', '1', 'asset/images/1 (12).jpg', 'Obsidian Exterior', 'Brutalist raw concrete mass juxtaposed with ultra-clear low-iron panoramic glazing panels.', '2026-06-23 14:37:14', 'Consultation', 'Premium Curated', '1'),
('2', '1', 'asset/images/1 (1).jpg', 'The Grand Foyer', 'Entryway detailing incorporating custom cast-concrete structural columns and structural timber frame joints.', '2026-06-23 14:37:14', 'Design & Planning', 'Premium Curated', '1'),
('3', '1', 'asset/images/1 (2).jpg', 'Living Oasis', 'Double-height glazing framing panoramic ocean views, integrating natural stone flooring.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('4', '1', 'asset/images/1 (3).jpg', 'Infinity Lounge', 'Cantilevered deck and pool border detail suspended over the coastal cliffs.', '2026-06-23 14:37:14', 'Handover', 'Premium Curated', '1'),
('5', '1', 'asset/images/1 (4).jpg', 'Bespoke Travertine Stairwell', 'Floating steps carved from raw local travertine, anchored to a central load-bearing core.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('6', '1', 'asset/images/1 (5).jpg', 'Master Wing', 'Open floor plan master bedroom utilizing passive thermal systems and integrated internal gardens.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('7', '1', 'asset/images/1 (6).jpg', 'Private Courtyard', 'Zen-inspired concrete pool deck offering visual seclusion and reflection pools.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('8', '1', 'asset/images/1 (7).jpg', 'Thermal Wine Cellar', 'Underground vaulted chamber built from sand-blasted volcanic stone for natural climate regulation.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('9', '1', 'asset/images/1 (8).jpg', 'Sunken Firepit', 'Custom-poured terraced concrete outdoor lounge with clean geometric lines.', '2026-06-23 14:37:14', 'Handover', 'Premium Curated', '1'),
('10', '1', 'asset/images/1 (9).jpg', 'Night Perspective', 'Smart architectural lighting accents tracing the brutalist angles of the residence at twilight.', '2026-06-23 14:37:14', 'Handover', 'Premium Curated', '1'),
('11', '2', 'asset/images/1 (13).jpg', 'Aether Spine Facade', 'Heavy structural steel grid exoskeleton defining the tower\'s modern geometric face.', '2026-06-23 14:37:14', 'Consultation', 'Premium Curated', '1'),
('12', '2', 'asset/images/1 (10).jpg', 'Steel Exoskeleton', 'High-strength structural steel joints showcasing robotic welding patterns and tension anchors.', '2026-06-23 14:37:14', 'Design & Planning', 'Premium Curated', '1'),
('13', '2', 'asset/images/1 (11).jpg', 'The Glass Atrium', 'Oversized structural glass canopy distributing load and flooding the lobby with natural light.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('14', '2', 'asset/images/1 (14).jpg', 'Corporate Lobby', 'Seamless white terrazzo layout with suspended structural steel mezzanine and lighting grids.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('15', '2', 'asset/images/1 (15).jpg', 'Conference Tier', 'Suspended geometric acoustical sound barriers and warm walnut timber wall cladding.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('16', '2', 'asset/images/1 (16).jpg', 'Facade Detailing', 'Double-glazed energy-reflective facade panels with integrated micro-shading systems.', '2026-06-23 14:37:14', 'Consultation', 'Premium Curated', '1'),
('17', '2', 'asset/images/1 (17).jpg', 'Sky Lounge', 'Cantilevered sky platform offering borderless views of the urban skyline.', '2026-06-23 14:37:14', 'Handover', 'Premium Curated', '1'),
('18', '2', 'asset/images/1 (18).jpg', 'Structural Node', 'Grade-5 titanium structural spider connection points absorbing wind shear forces.', '2026-06-23 14:37:14', 'Design & Planning', 'Premium Curated', '1'),
('19', '2', 'asset/images/1 (19).jpg', 'Mechanical Core', 'Industrial HVAC integration combining smart airflow control and smart energy consumption metrics.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('20', '2', 'asset/images/1 (20).jpg', 'Plaza Water Walls', 'Curated public boundary space with thin concrete pools and vertical waterfall noise dampeners.', '2026-06-23 14:37:14', 'Handover', 'Premium Curated', '1'),
('21', '3', 'asset/images/1 (21).jpg', 'Pavilion Canopy', 'Curved timber structural roof framing merging into the natural landscape.', '2026-06-23 14:37:14', 'Consultation', 'Premium Curated', '1'),
('22', '3', 'asset/images/1 (22).jpg', 'Laminated Timber Arches', 'Glue-laminated structural wood ribs providing organic curves and massive column-free spaces.', '2026-06-23 14:37:14', 'Design & Planning', 'Premium Curated', '1'),
('23', '3', 'asset/images/1 (23).jpg', 'Photovoltaic Grid', 'High-efficiency solar cells flush-integrated into custom timber roof paneling.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('24', '3', 'asset/images/1 (24).jpg', 'Rainwater Columns', 'Vertical copper filtration conduits routing greywater to biological filtration tanks.', '2026-06-23 14:37:14', 'Handover', 'Premium Curated', '1'),
('25', '3', 'asset/images/1 (25).jpg', 'Geothermal Vaults', 'Subterranean energy exchange pipeline layout supplying passive floor cooling and heating.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('26', '3', 'asset/images/1 (26).jpg', 'Green Wall Atrium', 'Hydroponic living walls acting as natural oxygen generators and indoor air scrubbers.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('27', '3', 'asset/images/1 (27).jpg', 'CLT Floor Decking', 'Cross-laminated timber layers left exposed to provide warm texture and low-carbon structural integrity.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('28', '3', 'asset/images/1 (28).jpg', 'Kinetic Solar Fins', 'Responsive timber louvers tracking solar paths dynamically to optimize light and shading.', '2026-06-23 14:37:14', 'Construction', 'Premium Curated', '1'),
('33', '10', 'asset/images/1782290037_gallery_0_kmanorhomepage.jpg', 'Kmanorhomepage', 'Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.', '2026-06-24 14:03:57', 'Design & Planning', 'Premium Curated', '1'),
('36', '10', 'asset/images/1782291129_gallery_0_delight.jpg', 'Delight', '', '2026-06-24 14:22:09', 'Construction', 'Premium Curated', '1');
/*!40000 ALTER TABLE `portfolio_gallery` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `settings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `key_name` varchar(50) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `settings`
LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES 
('about_hero_desc', 'Creating spaces with purpose and precision. Established in 2006, Delight Builders challenges transient architectural trends, synthesizing volumetric concrete gravity and biophilic structural systems to deliver custom residences and corporate structures calculated to endure for generations.'),
('business_hours', 'Monday ÔÇö Saturday: 9:00 AM ÔÇö 6:00 PM IST'),
('contact_email', 'inquire@delightbuilders.com'),
('contact_phone', '+91 484 234 5678'),
('coordinates', '40.7128┬░ N, 74.0060┬░ W'),
('established_year', '2006'),
('google_maps_iframe', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.9064267811978!2d76.40098747479394!3d10.024580290082096!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b07e2f4640c474d%3A0xb28071796b18b1a7!2sDelight%20Builders%20Kakkanad!5e0!3m2!1sen!2sin!4v1782197484219!5m2!1sen!2sin'),
('hero_subtitle', 'Sculpting structural blueprints into monolithic icons of stone, glass, and timber. We build custom architectural poetry.'),
('logo_path', 'asset/images/logo.png'),
('office_address', 'First floor, 449/A4, Delight Builders Cherakkalayil Complex, Kakkanad Pallikara Rd, Kakkanad, Kerala ÔÇó Pin: 683565'),
('philosophy_text_1', 'We do not merely construct spaces; we synthesize permanent environments. By uniting the tactile gravity of custom-cast concrete with the weightless fluidity of structural glass, Delight Builders challenges the ephemeral nature of modern housing.'),
('philosophy_text_2', 'Every commission is executed with absolute structural precision. From deep soil diagnostics to custom seismic load profiles, our engineering team constructs structural poetry that guarantees durability across centuries.'),
('site_title', 'Delight Builders | Architects of Permanence & Luxury Construction');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `stats`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(20) NOT NULL,
  `label` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `stats`
LOCK TABLES `stats` WRITE;
/*!40000 ALTER TABLE `stats` DISABLE KEYS */;
INSERT INTO `stats` VALUES 
('1', '10+', 'YEARS OF EXPERIENCE', 'helmet', '1'),
('2', '250+', 'COMPLETED PROJECTS', 'house', '2'),
('3', '14', 'DISTRICTS ACROSS KERALA', 'map-pin', '3'),
('4', '500+', 'HAPPY FAMILIES SERVED', 'family', '4');
/*!40000 ALTER TABLE `stats` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `team_members`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `team_members`;
CREATE TABLE `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `avatar_text` varchar(10) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `team_members`
LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
INSERT INTO `team_members` VALUES 
('1', 'Sterling H. Croft', 'Principal Architect & Founder', 'SC', 'Spearheads conceptual planning and brutalist massing. Sterling ensures every residence integrates architectural gravity with local local travertine stone geometries.', '1', 'asset/images/1782254291_team_Dijo.gif', NULL),
('4', 'Manoj', 'Techinical Director', 'SC', 'test data', '2', 'asset/images/1782273027_team_Dijo.gif', '1'),
('5', 'SOJIN MATHEW', 'instructor', '2', 'test data', '2', 'asset/images/1782273089_team_Dijo.gif', '1');
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;

-- --------------------------------------------------------
-- Table structure for `testimonials`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) NOT NULL,
  `client_designation` varchar(150) NOT NULL,
  `project_name` varchar(100) NOT NULL,
  `quote` text NOT NULL,
  `color` varchar(20) DEFAULT 'blue',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `testimonials`
LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES 
('1', 'Isadora R. Sterling', 'Philanthropist & Art Collector', 'The Obsidian Villa', 'Their brutalist gravity combined with carbon-negative glulam timber frames is revolutionary. Our custom seaside villa stands as a generational masterpiece.', 'red', '1', '2026-07-02 08:36:52'),
('2', 'Alaric K. Vance', 'Managing Director, Vance Maritime', 'The Aether Spine Towers', 'Delight Builders synthesizes raw concrete mass and biophilic glass to create living, breathing structural poetry. The attention to volumetric math was outstanding.', 'blue', '2', '2026-07-02 08:36:52'),
('3', 'Dr. Cassian G. Vance', 'Director, Kerala Eco-Institute', 'The Biophilic Pavilion', 'The database blueprint transparency allowed us to track every seismic soil calculation and glulam timber joint in real time. Absolute structural confidence.', 'purple', '3', '2026-07-02 08:36:52');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

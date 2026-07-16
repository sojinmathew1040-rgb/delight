<?php
// ==========================================
// DELIGHT BUILDERS - DATABASE BACKUP ENGINE
// ==========================================

// Set infinite time limit and memory limit for large databases
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

// Include database connection
require_once 'admin/db_connection.php';

try {
    // Get list of tables
    $tables = [];
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $sql = "-- Delight Builders - Database Backup\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- MySQL Server Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";

    $sql .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
    $sql .= "/*!40101 SET NAMES utf8 */;\n";
    $sql .= "/*!50503 SET NAMES utf8mb4 */;\n";
    $sql .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n";
    $sql .= "/*!40103 SET TIME_ZONE='+00:00' */;\n";
    $sql .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n";
    $sql .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
    $sql .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n\n";

    $sql .= "CREATE DATABASE IF NOT EXISTS `$db_name` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;\n";
    $sql .= "USE `$db_name`;\n\n";

    foreach ($tables as $table) {
        $sql .= "-- --------------------------------------------------------\n";
        $sql .= "-- Table structure for `$table`\n";
        $sql .= "-- --------------------------------------------------------\n\n";

        // Get drop and create table statement
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $createStmt = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sql .= $createStmt['Create Table'] . ";\n\n";

        // Get table data
        $sql .= "-- Dumping data for table `$table`\n";
        $dataStmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $sql .= "LOCK TABLES `$table` WRITE;\n";
            $sql .= "/*!40000 ALTER TABLE `$table` DISABLE KEYS */;\n";
            
            // Build bulk inserts in chunks of 500 to keep queries reasonable
            $chunks = array_chunk($rows, 500);
            foreach ($chunks as $chunk) {
                $insertLines = [];
                foreach ($chunk as $row) {
                    $values = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote($val);
                        }
                    }
                    $insertLines[] = "(" . implode(", ", $values) . ")";
                }
                $sql .= "INSERT INTO `$table` VALUES \n" . implode(",\n", $insertLines) . ";\n";
            }
            
            $sql .= "/*!40000 ALTER TABLE `$table` ENABLE KEYS */;\n";
            $sql .= "UNLOCK TABLES;\n\n";
        }
    }

    $sql .= "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n";
    $sql .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n";
    $sql .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n";
    $sql .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
    $sql .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n";

    // Write to db folder
    $dir = __DIR__ . '/db';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $backup_file = $dir . '/delight_db.sql';
    if (file_put_contents($backup_file, $sql) !== false) {
        echo "Database backup completed successfully. Saved to " . htmlspecialchars($backup_file);
    } else {
        echo "Error: Failed to write backup file.";
    }
} catch (Exception $e) {
    echo "Backup failed: " . htmlspecialchars($e->getMessage());
}

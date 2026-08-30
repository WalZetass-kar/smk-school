<?php
/**
 * admin/backup.php
 * Fitur 1-Klik Backup Database MySQL (Dump ke file .sql)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireSuperAdmin();

$db = getDB();
$dbName = $db->query('SELECT DATABASE()')->fetchColumn() ?: 'smk_school';

$tables = [];
$result = $db->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$sqlDump = "-- ========================================================\n";
$sqlDump .= "-- Database Backup: " . $dbName . "\n";
$sqlDump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
$sqlDump .= "-- SMK Negeri Contoh Indonesia Database Dump\n";
$sqlDump .= "-- ========================================================\n\n";
$sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
$sqlDump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
$sqlDump .= "SET time_zone = '+07:00';\n\n";

foreach ($tables as $table) {
    // Drop table statement
    $sqlDump .= "-- --------------------------------------------------------\n";
    $sqlDump .= "-- Table structure for table `" . $table . "`\n";
    $sqlDump .= "-- --------------------------------------------------------\n";
    $sqlDump .= "DROP TABLE IF EXISTS `" . $table . "`;\n";

    // Create table statement
    $createTableStmt = $db->query("SHOW CREATE TABLE `" . $table . "`")->fetch(PDO::FETCH_NUM);
    $sqlDump .= $createTableStmt[1] . ";\n\n";

    // Dump Data
    $rows = $db->query("SELECT * FROM `" . $table . "`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $sqlDump .= "-- Dumping data for table `" . $table . "`\n";
        foreach ($rows as $row) {
            $fields = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $fields[] = "NULL";
                } else {
                    $fields[] = $db->quote($val);
                }
            }
            $sqlDump .= "INSERT INTO `" . $table . "` VALUES (" . implode(', ', $fields) . ");\n";
        }
        $sqlDump .= "\n";
    }
}

$sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

// Stream download
$filename = 'backup_' . $dbName . '_' . date('Ymd_His') . '.sql';

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($sqlDump));
header('Cache-Control: must-revalidate');
header('Pragma: public');

echo $sqlDump;
exit;

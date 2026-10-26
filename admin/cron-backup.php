<?php
/**
 * admin/cron-backup.php
 * Automated Scheduled Database Backup CLI / Disaster Recovery Script
 * Usage in Crontab / Task Scheduler:
 *   php C:\xampp\htdocs\smk-school\admin\cron-backup.php
 */
require_once __DIR__ . '/../config/config.php';

// If run via web browser, enforce Superadmin authorization
if (PHP_SAPI !== 'cli') {
    require_once __DIR__ . '/../includes/auth.php';
    requireSuperAdmin();
}

$db = getDB();
$dbName = $db->query('SELECT DATABASE()')->fetchColumn() ?: 'smk_school';

$backupDir = BASE_PATH . '/assets/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Ensure .htaccess inside backups folder completely blocks direct web access
$htaccessPath = $backupDir . '/.htaccess';
if (!file_exists($htaccessPath)) {
    file_put_contents($htaccessPath, "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
}

// Generate Dump
$tables = [];
$result = $db->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$sqlDump  = "-- ========================================================\n";
$sqlDump .= "-- Automated Backup: " . $dbName . "\n";
$sqlDump .= "-- Timestamp: " . date('Y-m-d H:i:s') . "\n";
$sqlDump .= "-- Trigger: " . (PHP_SAPI === 'cli' ? 'Cron Job / CLI Scheduler' : 'Superadmin Web Manual Trigger') . "\n";
$sqlDump .= "-- ========================================================\n\n";
$sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
$sqlDump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
$sqlDump .= "SET time_zone = '+07:00';\n\n";

foreach ($tables as $table) {
    $sqlDump .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
    $createStmt = $db->query("SHOW CREATE TABLE `" . $table . "`")->fetch(PDO::FETCH_NUM);
    $sqlDump .= $createStmt[1] . ";\n\n";

    $rows = $db->query("SELECT * FROM `" . $table . "`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
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

$filename = 'backup_auto_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $dbName) . '_' . date('Ymd_His') . '.sql';
$destPath = $backupDir . '/' . $filename;
file_put_contents($destPath, $sqlDump);

// Log activity
logActivity('AUTOMATED_BACKUP', 'backup', "Backup otomatis berhasil dibuat: $filename (" . formatBytes(filesize($destPath)) . ")");

// Housekeeping: purge backups older than 14 days
$files = glob($backupDir . '/backup_auto_*.sql');
$now = time();
$deletedCount = 0;
foreach ($files as $f) {
    if (is_file($f) && ($now - filemtime($f) > 14 * 86400)) {
        @unlink($f);
        $deletedCount++;
    }
}

if (PHP_SAPI === 'cli') {
    echo "[OK] Automated backup created: $filename\n";
    echo "[OK] Cleaned $deletedCount expired backups (>14 days)\n";
} else {
    setFlash('success', "Backup otomatis berhasil disimpan di server: $filename (" . formatBytes(filesize($destPath)) . ")");
    redirect(BASE_URL . '/admin/backup.php');
}

<?php
/**
 * database.php
 * Single PDO connection, shared across the whole app via getDB().
 * Uses prepared statements everywhere — never build raw SQL from user input.
 */

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host   = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: 'smk_school';
            $user   = getenv('DB_USER') ?: 'root';
            $pass   = getenv('DB_PASS') !== false ? (string)getenv('DB_PASS') : '';

            $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8mb4';
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES    => false,
                ]);
            } catch (PDOException $e) {
                // Never leak DSN/credentials or raw exception details to the browser.
                error_log('Database connection failed: ' . $e->getMessage());
                die('Koneksi database gagal. Silakan hubungi administrator.');
            }
        }
        return self::$instance;
    }
}

function getDB(): PDO
{
    return Database::getInstance();
}

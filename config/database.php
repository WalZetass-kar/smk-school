<?php
/**
 * database.php
 * Single PDO connection, shared across the whole app via getDB().
 * Uses prepared statements everywhere — never build raw SQL from user input.
 */

class Database
{
    private static ?PDO $instance = null;

    // --- Adjust these four values to match your local/hosting setup ---
    private static string $host   = 'localhost';
    private static string $dbname = 'smk_school';
    private static string $user   = 'root';
    private static string $pass   = '';
    // --------------------------------------------------------------------

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . self::$host . ';dbname=' . self::$dbname . ';charset=utf8mb4';
            try {
                self::$instance = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES    => false,
                ]);
            } catch (PDOException $e) {
                // Never leak DSN/credentials or raw exception details to the browser.
                error_log('Database connection failed: ' . $e->getMessage());
                die('Koneksi database gagal. Silakan cek konfigurasi di config/database.php.');
            }
        }
        return self::$instance;
    }
}

function getDB(): PDO
{
    return Database::getInstance();
}

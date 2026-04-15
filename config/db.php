<?php
// ============================================================
//  DARBCO System — PDO Database Connection
//  File   : config/db.php
//  Purpose: Provides a singleton PDO instance for the entire
//           application. All models must obtain their database
//           handle exclusively through Database::getInstance().
// ============================================================

declare(strict_types=1);

class Database
{
    // --------------- Configuration ---------------
    private const DB_HOST    = 'localhost';
    private const DB_PORT    = '3306';
    private const DB_NAME    = 'darbco_system';
    private const DB_USER    = 'root';          // ← change in production
    private const DB_PASS    = 'Accountloui123';              // ← change in production
    private const DB_CHARSET = 'utf8mb4';

    // --------------- Singleton State -------------
    private static ?PDO $instance = null;

    /**
     * Prevent direct instantiation — use getInstance() instead.
     */
    private function __construct() {}
    private function __clone()     {}

    /**
     * Returns the shared PDO connection, creating it on first call.
     *
     * @throws RuntimeException if the connection cannot be established.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_PORT,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                // Throw exceptions on errors instead of silently failing
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                // Return rows as associative arrays by default
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // Use real prepared statements (not emulated) for security
                PDO::ATTR_EMULATE_PREPARES   => false,

                // Keep the connection alive / reconnect automatically
                PDO::ATTR_PERSISTENT         => false,

                // Force strict UTF-8 on every new connection
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                // Log the raw error server-side; never expose it to the client
                error_log('[DARBCO DB ERROR] ' . $e->getMessage());

                // Throw a sanitised exception so controllers can handle it
                throw new RuntimeException(
                    'Database connection failed. Please contact the system administrator.',
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$instance;
    }
}

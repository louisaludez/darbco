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
            $envFile = dirname(__DIR__) . '/.env';
            $env = [];
            if (file_exists($envFile)) {
                $env = parse_ini_file($envFile);
            }
            
            $host    = $env['DB_HOST']    ?? 'localhost';
            $port    = $env['DB_PORT']    ?? '3306';
            $dbname  = $env['DB_NAME']    ?? 'darbco_system';
            $user    = $env['DB_USER']    ?? 'root';
            $pass    = $env['DB_PASS']    ?? '';
            $charset = $env['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $host,
                $port,
                $dbname,
                $charset
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
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
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

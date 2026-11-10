<?php
/**
 * PROJECT: BLACKOUT — Database Connection Handler
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        global $dbConfig;

        if (self::$instance !== null) {
            return self::$instance;
        }

        // Try MySQL first if driver is mysql
        if ($dbConfig['driver'] === 'mysql') {
            try {
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset=utf8mb4";
                self::$instance = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                return self::$instance;
            } catch (PDOException $e) {
                // If MySQL fails, fallback gracefully to SQLite
                error_log("MySQL connection failed: " . $e->getMessage() . ". Falling back to SQLite.");
            }
        }

        // SQLite initialization
        $sqliteFile = $dbConfig['sqlite_path'];
        $isNew = !file_exists($sqliteFile);
        
        self::$instance = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        if ($isNew) {
            self::initSqlite(self::$instance);
        }

        return self::$instance;
    }

    private static function initSqlite(PDO $pdo): void {
        $schemaPath = APP_ROOT . '/database/schema.sql';
        $seedPath = APP_ROOT . '/database/seed.sql';

        if (file_exists($schemaPath)) {
            $schema = file_get_contents($schemaPath);
            // Convert MySQL specific AUTO_INCREMENT to SQLite AUTOINCREMENT
            $schema = preg_replace('/INT AUTO_INCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $schema);
            $schema = preg_replace('/MEDIUMTEXT/i', 'TEXT', $schema);
            $pdo->exec($schema);
        }

        if (file_exists($seedPath)) {
            $seed = file_get_contents($seedPath);
            $pdo->exec($seed);
        }
    }
}

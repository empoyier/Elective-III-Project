<?php
/**
 * APEX Car Rental Management System
 * Database Connection & Transaction Wrapper (PDO Singleton)
 */

class Database {
    private static ?PDO $instance = null;

    /**
     * Get or initialize PDO singleton instance
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // If apex_rentals database doesn't exist yet, connect without dbname to allow auto-setup
                if (str_contains($e->getMessage(), 'Unknown database')) {
                    $rawDsn = sprintf('mysql:host=%s;port=%d;charset=%s', $config['host'], $config['port'], $config['charset']);
                    return new PDO($rawDsn, $config['username'], $config['password'], $config['options']);
                }
                throw new RuntimeException("Database connection error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Start a database transaction
     */
    public static function beginTransaction(): bool {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Commit a database transaction
     */
    public static function commit(): bool {
        return self::getInstance()->commit();
    }

    /**
     * Roll back a database transaction
     */
    public static function rollBack(): bool {
        if (self::getInstance()->inTransaction()) {
            return self::getInstance()->rollBack();
        }
        return false;
    }

    /**
     * Execute a prepared query and return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row as associative array
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::query($sql, $params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Fetch all matching rows as associative array
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get last inserted ID
     */
    public static function lastInsertId(): string {
        return self::getInstance()->lastInsertId();
    }
}

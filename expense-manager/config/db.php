<?php
/**
 * Database Configuration & Connection (PDO)
 * Daily Expense Management System
 */

declare(strict_types=1);

// Database Credentials - update for your local environment (XAMPP default: user 'root', empty password)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'expense_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO connection instance
 *
 * @return PDO
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error in production and show friendly message
            error_log('Database Connection Error: ' . $e->getMessage());
            die(
                '<div style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:24px;border:1px solid #f87171;border-radius:8px;background:#fef2f2;color:#991b1b;">' .
                '<h2 style="margin-top:0;">Database Connection Failed</h2>' .
                '<p>Could not connect to the MySQL database. Please verify:</p>' .
                '<ul>' .
                '<li>MySQL is running in XAMPP / WAMP control panel</li>' .
                '<li>The database <strong>' . htmlspecialchars(DB_NAME) . '</strong> has been imported from <code>database.sql</code></li>' .
                '<li>Credentials in <code>config/db.php</code> match your MySQL server</li>' .
                '</ul>' .
                '<p><em>Error: ' . htmlspecialchars($e->getMessage()) . '</em></p>' .
                '</div>'
            );
        }
    }

    return $pdo;
}

<?php
/**
 * Spendly - Database Connection Manager (PDO)
 */

/**
 * Returns a shared PDO database instance.
 *
 * @return PDO
 */
function get_db_connection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $configFile = __DIR__ . '/../config/database.php';

    if (!file_exists($configFile)) {
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Configuration Required — Spendly</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8f9fa; color: #1f2937; margin: 0; padding: 2rem; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; max-width: 520px; width: 100%; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                h1 { font-size: 1.25rem; margin-top: 0; color: #b91c1c; }
                p { line-height: 1.5; color: #4b5563; font-size: 0.95rem; }
                code { background: #f3f4f6; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.875rem; color: #111827; }
                ol { padding-left: 1.25rem; color: #4b5563; font-size: 0.95rem; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>Database Configuration Not Found</h1>
                <p>Spendly requires a local database configuration file before it can run.</p>
                <ol>
                    <li>Copy <code>config/database.example.php</code> to <code>config/database.php</code></li>
                    <li>Update your database name, username, and password in <code>config/database.php</code></li>
                    <li>Import <code>database/schema.sql</code> into your MySQL server</li>
                    <li>Refresh this page</li>
                </ol>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    $config = require $configFile;

    $host    = $config['host'] ?? '127.0.0.1';
    $port    = $config['port'] ?? 3306;
    $dbname  = $config['dbname'] ?? 'spendly_db';
    $charset = $config['charset'] ?? 'utf8mb4';
    $user    = $config['username'] ?? 'root';
    $pass    = $config['password'] ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Log the error internally if error logging is enabled
        error_log('Database connection error: ' . $e->getMessage());

        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Connection Error — Spendly</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8f9fa; color: #1f2937; margin: 0; padding: 2rem; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; max-width: 520px; width: 100%; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                h1 { font-size: 1.25rem; margin-top: 0; color: #b91c1c; }
                p { line-height: 1.5; color: #4b5563; font-size: 0.95rem; }
                code { background: #f3f4f6; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.875rem; color: #111827; }
                ul { padding-left: 1.25rem; color: #4b5563; font-size: 0.95rem; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>Unable to Connect to Database</h1>
                <p>Spendly could not connect to your MySQL database. Please verify:</p>
                <ul>
                    <li>MySQL service is started in XAMPP Control Panel.</li>
                    <li>Database <code>spendly_db</code> exists (imported from <code>database/schema.sql</code>).</li>
                    <li>Credentials in <code>config/database.php</code> match your XAMPP configuration.</li>
                </ul>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    return $pdo;
}

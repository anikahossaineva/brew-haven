<?php
declare(strict_types=1);

require_once __DIR__ . '/security.php';

// XAMPP defaults. Override these with environment variables in deployed environments.
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'coffee_shop';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $conn = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log('Brew Haven database connection failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('The café is temporarily unavailable. Check the database setup and try again.');
}

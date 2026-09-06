<?php
declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('KATOC_DB_HOST') ?: '127.0.0.1';
    $database = getenv('KATOC_DB_NAME') ?: 'katoc';
    $username = getenv('KATOC_DB_USER') ?: 'root';
    // Matches the local XAMPP phpMyAdmin configuration; environment variables override it.
    $password = getenv('KATOC_DB_PASSWORD') ?: '102006';

    $connection = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $connection;
}

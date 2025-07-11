<?php
// Database configuration
define('DB_CHARSET', 'utf8mb4');

// Database type - can be 'sqlite' or 'postgresql'
define('DB_TYPE', getenv('DB_TYPE') ?: 'sqlite');

// SQLite configuration
define('DB_PATH', __DIR__ . '/../database/task_manager.db');

// PostgreSQL configuration from environment variables
define('DB_HOST', getenv('PGHOST') ?: 'localhost');
define('DB_PORT', getenv('PGPORT') ?: '5432');
define('DB_NAME', getenv('PGDATABASE') ?: 'task_manager');
define('DB_USER', getenv('PGUSER') ?: 'postgres');
define('DB_PASSWORD', getenv('PGPASSWORD') ?: '');
define('DATABASE_URL', getenv('DATABASE_URL') ?: '');

// Function to get database DSN based on type
function getDatabaseDSN() {
    if (DB_TYPE === 'postgresql' && !empty(DATABASE_URL)) {
        return DATABASE_URL;
    } elseif (DB_TYPE === 'postgresql') {
        return "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    } else {
        return "sqlite:" . DB_PATH;
    }
}

// Function to get database credentials
function getDatabaseCredentials() {
    if (DB_TYPE === 'postgresql') {
        return [DB_USER, DB_PASSWORD];
    } else {
        return [null, null];
    }
}
?>

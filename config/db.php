<?php
/**
 * Database connection helper
 */
require_once __DIR__ . "/../vendor/autoload.php"; // Adjusted path

// Check if the file exists before trying to load it
$envPath = __DIR__ . "/../.env";
if (!file_exists($envPath)) {
    die("Error: The .env file does not exist at the path: $envPath");
}
Dotenv\Dotenv::createImmutable(__DIR__ . "/../")->load();
function getDbConnection()
{
    // Get environment variables
    $host = $_ENV["DB_HOST"] ?? "localhost";
    $username = $_ENV["DB_USERNAME"] ?? "root";
    $password = $_ENV["DB_PASSWORD"] ?? "";
    $database = $_ENV["DB_DATABASE"] ?? "cause_registration";

    // Create connection
    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// If you need to create the database and tables, you can run this once
function setupDatabase()
{
    $host = $_ENV["DB_HOST"] ?? "localhost";
    $username = $_ENV["DB_USERNAME"] ?? "root";
    $password = $_ENV["DB_PASSWORD"] ?? "";
    $database = $_ENV["DB_DATABASE"] ?? "cause_registration";

    // Connect without database selection
    $conn = new mysqli($host, $username, $password);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) !== true) {
        die("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db($database);

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        age INT(3) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        supports_cause TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) !== true) {
        die("Error creating table: " . $conn->error);
    }

    $conn->close();

    return true;
}

// Uncomment this line to set up the database on first run
setupDatabase();

?>

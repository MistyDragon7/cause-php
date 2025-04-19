<?php
/**
 * Database connection helper
 */
require_once __DIR__ . "/../vendor/autoload.php"; // Adjusted path

$envPath = __DIR__ . "/../.env";
if (!file_exists($envPath)) {
    die("Error: The .env file does not exist at the path: $envPath");
}
Dotenv\Dotenv::createImmutable(__DIR__ . "/../")->load();

function getDbConnection()
{
    $host = $_ENV["DB_HOST"] ?? "localhost";
    $port = $_ENV["DB_PORT"] ?? 3306;
    $username = $_ENV["DB_USERNAME"] ?? "root";
    $password = $_ENV["DB_PASSWORD"] ?? "";
    $database = $_ENV["DB_DATABASE"] ?? "registrations";

    $conn = new mysqli($host, $username, $password, $database, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function setupDatabase()
{
    $host = $_ENV["DB_HOST"] ?? "localhost";
    $port = $_ENV["DB_PORT"] ?? 3306;
    $username = $_ENV["DB_USERNAME"] ?? "root";
    $password = $_ENV["DB_PASSWORD"] ?? "";
    $database = $_ENV["DB_DATABASE"] ?? "cause_registration";

    $conn = new mysqli($host, $username, $password, "", $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) !== true) {
        die("Error creating database: " . $conn->error);
    }

    $conn->select_db($database);

    $sql = "CREATE TABLE IF NOT EXISTS registrations (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        age INT(3) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        support TINYINT(1) NOT NULL DEFAULT 0,
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

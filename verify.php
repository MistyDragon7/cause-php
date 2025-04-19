<?php
require_once "vendor/autoload.php";
if (file_exists(__DIR__ . "/.env")) {
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

session_start();

$host = $_ENV["DB_HOST"];
$port = $_ENV["DB_PORT"] ?? 3306;
$dbname = $_ENV["DB_NAME"];
$user = $_ENV["DB_USER"];
$pass = $_ENV["DB_PASS"];

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "DB Connection failed: " . $e->getMessage(),
    ]);
    exit();
}

// Get input from the frontend
$data = json_decode(file_get_contents("php://input"), true);

// Log the received data for debugging
file_put_contents("debug.log", print_r($data, true), FILE_APPEND);

// Validate required fields
$required_fields = ["name", "age", "phno", "email", "support", "otp"];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields.",
        ]);
        exit();
    }
}

$name = $data["name"];
$age = $data["age"];
$phno = $data["phno"];
$email = $data["email"];
$support = $data["support"];
$submitted_otp = trim((string) $data["otp"]); // Trim and cast to string

// Validate OTP
if (!isset($_SESSION["otp"])) {
    file_put_contents("debug.log", "Session OTP not set.\n", FILE_APPEND);
    echo json_encode([
        "status" => "error",
        "message" => "No OTP found in session.",
    ]);
    exit();
}

$stored_otp = trim((string) $_SESSION["otp"]); // Trim and cast to string

// Log the stored and submitted OTP for debugging
file_put_contents(
    "debug.log",
    "Stored OTP: " . $stored_otp . "\n",
    FILE_APPEND
);
file_put_contents(
    "debug.log",
    "Submitted OTP: " . $submitted_otp . "\n",
    FILE_APPEND
);

if ($submitted_otp !== $stored_otp) {
    echo json_encode(["status" => "error", "message" => "Invalid OTP"]);
    exit();
}

// OTP is correct, proceed with the registration

try {
    // Save user data in the database
    $stmt = $pdo->prepare(
        "INSERT INTO registrations (name, age, phone, email, support) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$name, $age, $phno, $email, $support]);

    // Clear OTP from session after successful verification
    unset($_SESSION["otp"]);

    echo json_encode([
        "status" => "success",
        "message" => "Registration successful!",
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage(),
    ]);
}
?>

<?php
session_start();
require_once "../config/db.php";

// Check if accessing directly or through AJAX
$is_ajax =
    !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
    strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";

// Get form data from POST or JSON
if ($is_ajax) {
    header("Content-Type: application/json");
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);
    $entered_otp = $data["otp"] ?? "";
} else {
    $entered_otp = $_POST["otp"] ?? "";
}

// Check if OTP session exists
if (!isset($_SESSION["otp"]) || !isset($_SESSION["form_data"])) {
    if ($is_ajax) {
        echo json_encode([
            "status" => "error",
            "message" => "Session expired or invalid. Please try again.",
        ]);
    } else {
        echo "<p>Session expired or invalid. <a href='index.html'>Please try again</a>.</p>";
    }
    exit();
}

// Verify OTP
if ($entered_otp == $_SESSION["otp"]) {
    // Extract user data from session
    $userData = $_SESSION["form_data"];

    // Connect to database
    $conn = getDbConnection(); // Assuming this function is defined in db.php

    // Prepare and execute SQL statement
    $stmt = $conn->prepare(
        "INSERT INTO users (name, age, phone, email, supports_cause) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sissi",
        $userData["name"],
        $userData["age"],
        $userData["phno"],
        $userData["email"],
        $userData["support"]
    );

    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    // Clear the session data
    unset($_SESSION["otp"], $_SESSION["form_data"]);

    if ($success) {
        if ($is_ajax) {
            echo json_encode([
                "status" => "success",
                "message" =>
                    "OTP verified successfully! Thank you for registering.",
            ]);
        } else {
            echo "<p>OTP verified successfully! Thank you for registering.</p>";
            echo "<p><a href='index.html'>Back to home</a></p>";
        }
    } else {
        if ($is_ajax) {
            echo json_encode([
                "status" => "error",
                "message" => "Error saving your information. Please try again.",
            ]);
        } else {
            echo "<p>Error saving your information. <a href='index.html'>Please try again</a>.</p>";
        }
    }
} else {
    if ($is_ajax) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid OTP. Please try again.",
        ]);
    } else {
        echo "<p>Invalid OTP. Please try again.</p>";
        echo '<form method="POST" action="verify.php">
                <input type="text" name="otp" placeholder="Enter OTP" required />
                <button type="submit">Verify OTP</button>
              </form>';
    }
}
?>

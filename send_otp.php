<?php
require_once "vendor/autoload.php"; // Composer autoloader

use Dotenv\Dotenv;

// Load environment variables safely
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Check for POST request
if (
    isset($_SERVER["REQUEST_METHOD"]) &&
    $_SERVER["REQUEST_METHOD"] === "POST"
) {
    $email = $_POST["email"] ?? "";

    if (empty($email)) {
        echo "Error: Missing email.";
        exit();
    }

    $otp = rand(100000, 999999); // Generate OTP

    // Check if all SMTP vars are loaded
    $required_env_vars = [
        "SMTP_HOST",
        "SMTP_PORT",
        "SMTP_USER",
        "SMTP_PASS",
        "SMTP_FROM_NAME",
        "SMTP_FROM_EMAIL",
    ];
    foreach ($required_env_vars as $var) {
        if (empty($_ENV[$var])) {
            echo "Error: Missing SMTP configuration for $var.";
            exit();
        }
    }

    try {
        // Set up SwiftMailer transport
        $transport = (new Swift_SmtpTransport(
            $_ENV["SMTP_HOST"],
            (int) $_ENV["SMTP_PORT"]
        ))
            ->setUsername($_ENV["SMTP_USER"])
            ->setPassword($_ENV["SMTP_PASS"])
            ->setEncryption("ssl");

        $mailer = new Swift_Mailer($transport);

        // Create and send message
        $message = (new Swift_Message("Your OTP Code"))
            ->setFrom([$_ENV["SMTP_FROM_EMAIL"] => $_ENV["SMTP_FROM_NAME"]])
            ->setTo([$email])
            ->setBody("Your OTP code is: $otp");

        $result = $mailer->send($message);

        if ($result) {
            echo "OTP sent to $email.";
        } else {
            echo "Failed to send OTP.";
        }
    } catch (Exception $e) {
        echo "Mailer Error: " . $e->getMessage();
    }
} else {
    echo "This endpoint only accepts POST requests.";
}
?>

<?php
require_once "vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load(); // This loads environment variables

session_start(); // Start the session

header("Content-Type: application/json"); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate the email address
    if (empty($data["email"])) {
        echo json_encode([
            "status" => "error",
            "message" => "Email address is required.",
        ]);
        exit();
    }

    $email = $data["email"];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email address format.",
        ]);
        exit();
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Store OTP in session for later validation
    $_SESSION["otp"] = $otp;

    try {
        // SMTP configuration
        $transport = (new Swift_SmtpTransport(
            $_ENV["SMTP_HOST"],
            $_ENV["SMTP_PORT"]
        ))
            ->setUsername($_ENV["SMTP_USER"])
            ->setPassword($_ENV["SMTP_PASS"])
            ->setEncryption("ssl");
        $mailer = new Swift_Mailer($transport);

        // Create message
        $message = (new Swift_Message("Your OTP Code"))
            ->setFrom([$_ENV["SMTP_FROM_EMAIL"] => $_ENV["SMTP_FROM_NAME"]])
            ->setTo([$email]) // Send OTP to the email address
            ->setBody("Your OTP code is: $otp");

        // Send the message
        $mailer->send($message);

        echo json_encode([
            "status" => "success",
            "message" =>
                "OTP sent successfully to your email! Please check your spam as well.",
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Mailer Error: " . $e->getMessage(),
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method.",
    ]);
}
?>

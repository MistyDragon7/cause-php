<?php
require_once "vendor/autoload.php";
if (file_exists(__DIR__ . "/.env")) {
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

session_start();
header("Content-Type: application/json"); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data["email"])) {
        echo json_encode([
            "status" => "error",
            "message" => "Email address is required.",
        ]);
        exit();
    }

    $email = $data["email"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email address format.",
        ]);
        exit();
    }

    $otp = rand(100000, 999999);

    $_SESSION["otp"] = $otp;

    try {
        $transport = (new Swift_SmtpTransport(
            $_ENV["SMTP_HOST"],
            $_ENV["SMTP_PORT"]
        ))
            ->setUsername($_ENV["SMTP_USER"])
            ->setPassword($_ENV["SMTP_PASS"])
            ->setEncryption("ssl");
        $mailer = new Swift_Mailer($transport);
        $message = (new Swift_Message("Your OTP Code"))
            ->setFrom([$_ENV["SMTP_FROM_EMAIL"] => $_ENV["SMTP_FROM_NAME"]])
            ->setTo([$email])
            ->setBody("Your OTP code is: $otp");

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

<?php
session_start();

// Manual PHPMailer Integration
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $otp = rand(100000, 999999);
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_otp'] = $otp;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ENTER YOUR EMAIL';
        $mail->Password   = 'ENTER YOUR APP PASSWORD'; // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('ENTER YOUR EMAIL', 'WASP Security');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'WASP | Password Reset Authorization';
        $mail->Body    = "
        <div style='background:#030305; color:#fff; padding:20px; font-family:sans-serif; border:1px solid #fbff00;'>
            <h2 style='color:#fbff00;'>NEURAL LINK RECOVERY</h2>
            <p>A password reset was requested for your WASP uplink.</p>
            <div style='font-size:24px; letter-spacing:5px; padding:10px; background:#111; text-align:center; color:#fbff00; border:1px dashed #fbff00;'>
                $otp
            </div>
            <p style='color:#555;'>If you did not request this, secure your terminal immediately.</p>
        </div>";

        $mail->send();
        header("Location: verify_otp.php");
        exit();
    } catch (Exception $e) {
        $error = "Transmission Failed: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WASP | Recover Uplink</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@500&display=swap" rel="stylesheet">
    <style>
        body { background: #030305; color: #fff; font-family: 'Rajdhani', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .panel { background: rgba(13, 13, 17, 0.9); padding: 40px; border: 1px solid rgba(251, 255, 0, 0.3); width: 350px; text-align: center; box-shadow: 0 0 20px rgba(251, 255, 0, 0.1); }
        h2 { font-family: 'Orbitron'; color: #fbff00; font-size: 1rem; letter-spacing: 2px; margin-bottom: 30px; }
        input { width: 100%; padding: 12px; margin: 15px 0; background: #000; border: 1px solid #333; color: #fbff00; box-sizing: border-box; outline: none; }
        input:focus { border-color: #fbff00; }
        button { width: 100%; padding: 15px; background: #fbff00; color: #000; border: none; font-family: 'Orbitron'; cursor: pointer; font-weight: bold; letter-spacing: 1px; transition: 0.3s; }
        button:hover { background: #fff; box-shadow: 0 0 15px #fbff00; }
    </style>
</head>
<body>
    <div class="panel">
        <h2>RECOVER UPLINK</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="ENTER REGISTERED EMAIL" required>
            <button type="submit">GENERATE OTP</button>
        </form>
        <?php if(isset($error)) echo "<p style='color:#ff2a2a; margin-top:15px;'>$error</p>"; ?>
    </div>
    <?php include 'global_footer.php'; ?>
</body>
</html>
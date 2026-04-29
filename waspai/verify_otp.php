<?php
session_start();
if (!isset($_SESSION['reset_otp'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = $_POST['otp'];
    if ($user_otp == $_SESSION['reset_otp']) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "AUTHORIZATION CODE INVALID";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WASP | Verify OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@500&display=swap" rel="stylesheet">
    <style>
        body { background: #030305; color: #fff; font-family: 'Rajdhani', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .panel { background: rgba(13, 13, 17, 0.9); padding: 40px; border: 1px solid rgba(0, 242, 255, 0.3); width: 350px; text-align: center; }
        h2 { font-family: 'Orbitron'; color: #00f2ff; font-size: 1rem; letter-spacing: 2px; }
        input { width: 100%; padding: 12px; margin: 20px 0; background: #000; border: 1px solid #333; color: #00f2ff; text-align: center; font-size: 1.8rem; letter-spacing: 8px; outline: none; }
        button { width: 100%; padding: 15px; background: #00f2ff; color: #000; border: none; font-family: 'Orbitron'; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="panel">
        <h2>VERIFY IDENTITY</h2>
        <form method="POST">
            <input type="text" name="otp" maxlength="6" placeholder="000000" autocomplete="off" required>
            <button type="submit">VALIDATE CODE</button>
        </form>
        <?php if(isset($error)) echo "<p style='color:#ff2a2a; margin-top:15px;'>$error</p>"; ?>
    </div>
    <?php include 'global_footer.php'; ?>
</body>
</html>
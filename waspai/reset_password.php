<?php
session_start();
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass === $confirm_pass) {
        $conn = new mysqli("localhost", "root", "", "wasp_db");
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_pass, $email);
        
        if ($stmt->execute()) {
            // Success - clear sessions
            session_unset();
            session_destroy();
            echo "<script>alert('PASSWORD RECONSTRUCTED. REDIRECTING TO LOGIN...'); window.location='login.php';</script>";
        } else {
            $error = "DATABASE ERROR: UPLINK FAILED";
        }
        $stmt->close();
        $conn->close();
    } else {
        $error = "PASSWORDS DO NOT MATCH";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WASP | Reset Neural Key</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@500&display=swap" rel="stylesheet">
    <style>
        body { background: #030305; color: #fff; font-family: 'Rajdhani', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .panel { background: rgba(13, 13, 17, 0.9); padding: 40px; border: 1px solid #fbff00; width: 350px; }
        h2 { font-family: 'Orbitron'; color: #fbff00; text-align:center; font-size: 1rem; letter-spacing: 2px; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #000; border: 1px solid #333; color: #fff; box-sizing: border-box; outline: none; }
        button { width: 100%; padding: 15px; background: #fbff00; color: #000; border: none; font-family: 'Orbitron'; cursor: pointer; font-weight: bold; margin-top:10px; }
    </style>
</head>
<body>
    <div class="panel">
        <h2>RESET PASSWORD</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="NEW PASSWORD" required>
            <input type="password" name="confirm_password" placeholder="CONFIRM NEW PASSWORD" required>
            <button type="submit">UPDATE CREDENTIALS</button>
        </form>
        <?php if(isset($error)) echo "<p style='color:#ff2a2a; text-align:center;'>$error</p>"; ?>
    </div>
    <?php include 'global_footer.php'; ?>
</body>
</html>
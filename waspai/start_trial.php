<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "wasp_db");
$user_id = $_SESSION['user_id'];

// 1. Check if the user has already exhausted their trial
$check = $conn->prepare("SELECT trial_used FROM users WHERE id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$result = $check->get_result();
$user = $result->fetch_assoc();

if ($user['trial_used'] == 1) {
    // User already used a trial, redirect back with error
    header("Location: subscription.php?error=trial_exhausted");
    exit();
}

// 2. Calculate expiration (3 days from now)
$expires = date('Y-m-d H:i:s', strtotime('+3 days'));

// 3. Update user: Activate subscription, mark trial as used, and set expiry
$update = $conn->prepare("UPDATE users SET is_subscribed = 1, trial_used = 1, subscription_expires = ? WHERE id = ?");
$update->bind_param("si", $expires, $user_id);

if ($update->execute()) {
    // Success animation or direct redirect
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>WASP | Trial Authorized</title>
        <link href='https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap' rel='stylesheet'>
        <style>
            body { background: #030305; color: #fbff00; font-family: 'Orbitron', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .box { border: 1px solid #fbff00; padding: 40px; text-align: center; box-shadow: 0 0 30px rgba(251, 255, 0, 0.2); }
        </style>
    </head>
    <body>
        <div class='box'>
            <h1>TRIAL_UPLINK_GRANTED</h1>
            <p style='color: #fff;'>3-DAY NEURAL ACCESS ACTIVE</p>
            <script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>
        </div>
    </body>
    </html>";
} else {
    echo "Error initializing trial: " . $conn->error;
}

$update->close();
$conn->close();
?>
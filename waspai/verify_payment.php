<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$conn = new mysqli("localhost", "root", "", "wasp_db");
$user_id = $_SESSION['user_id'];

// In a real production app, you would verify the signature here.
// For your test environment, we check if the razorpay_payment_id exists.
if (isset($_POST['razorpay_payment_id'])) {
    
    // UPDATE THE DATABASE TO GIVE ACCESS
    $update = $conn->prepare("UPDATE users SET is_subscribed = 1 WHERE id = ?");
    $update->bind_param("i", $user_id);
    
    if ($update->execute()) {
        // Success! Send them back to the dashboard.
        header("Location: index.php?success=uplink_active");
    } else {
        echo "Database Error: " . $conn->error;
    }
    $update->close();
} else {
    echo "Payment Failed or Cancelled.";
    echo "<br><a href='subscription.php'>Try Again</a>";
}
?>
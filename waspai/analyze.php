<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Database Connection
$conn = new mysqli("localhost", "root", "", "wasp_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_user = $_SESSION['user_id'];
    $target_identifier = "";

    // 3. Determine Identifier (File or URL)
    if (isset($_FILES['scan_file']) && $_FILES['scan_file']['error'] == 0) {
        $target_identifier = "FILE: " . $_FILES['scan_file']['name'];
    } else {
        $target_identifier = !empty($_POST['url']) ? trim($_POST['url']) : '';
    }

    // Stop if input is empty
    if (empty($target_identifier)) {
        header("Location: index.php");
        exit();
    }

    // 4. DATABASE LOOKUP: Has this specific target been scanned before?
    $check_stmt = $conn->prepare("SELECT status FROM scans WHERE url = ? LIMIT 1");
    $check_stmt->bind_param("s", $target_identifier);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // MATCH FOUND: Retrieve the existing status to ensure consistency
        $row = $check_result->fetch_assoc();
        $final_status = $row['status'];
    } else {
        // NO MATCH: Perform a fresh "Neural Scan"
        $outcomes = ['CLEAN', 'MALICIOUS', 'CLEAN', 'CLEAN', 'CLEAN']; 
        $final_status = $outcomes[array_rand($outcomes)];
    }
    $check_stmt->close();

    // 5. INSERT: Record this specific scan instance for the current user
    // This allows the same URL to appear in different users' histories with the same result
    $stmt = $conn->prepare("INSERT INTO scans (url, status, user_id, scan_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssi", $target_identifier, $final_status, $current_user);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "DATABASE ERROR: UPLINK FAILED";
    }

    $stmt->close();
}

$conn->close();
?>
<?php
// WASP AI - Extension Bridge (Proxies to analyze.php)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

// 1. Get data from Extension
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$url = $data['url'] ?? '';

if (empty($url)) {
    echo json_encode(['status' => 'ERROR']);
    exit;
}

// 2. Setup the environment for analyze.php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['url'] = $url;

// Start session and force a user_id so analyze.php doesn't redirect to login
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1; 

// 3. EXECUTE ANALYZE.PHP (and catch the redirect)
ob_start(); // Start intercepting output
include 'analyze.php';
ob_end_clean(); // Throw away any redirects/HTML from analyze.php

// 4. GET THE RESULT FROM DATABASE
$conn = new mysqli("localhost", "root", "", "wasp_db");
$stmt = $conn->prepare("SELECT status FROM scans WHERE url = ? ORDER BY scan_date DESC LIMIT 1");
$stmt->bind_param("s", $url);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'status' => $res['status'] ?? 'CLEAN',
    'url' => $url
]);
$conn->close();
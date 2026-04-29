<?php
ob_start(); 
session_start();

// 1. AUTHENTICATION CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "wasp_db");
$current_user_id = $_SESSION['user_id'];

// 2. SUBSCRIPTION PROTECTION (FORCED REDIRECT)
// Fetching expiry date alongside status for the timer
$check_sub = $conn->prepare("SELECT is_subscribed, subscription_expires FROM users WHERE id = ?");
$check_sub->bind_param("i", $current_user_id);
$check_sub->execute();
$sub_res = $check_sub->get_result()->fetch_assoc();

// If the user isn't found OR is_subscribed is 0, false, or "0"
if (!$sub_res || $sub_res['is_subscribed'] == 0) {
    header("Location: subscription.php");
    echo "<script>window.location.href='subscription.php';</script>";
    exit();
}

// Check if subscription has expired
$expiry_time = $sub_res['subscription_expires'];
if ($expiry_time && strtotime($expiry_time) < time()) {
    $conn->query("UPDATE users SET is_subscribed = 0 WHERE id = $current_user_id");
    header("Location: subscription.php?status=expired");
    exit();
}
$check_sub->close();

// --- PAGINATION LOGIC ---
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Get total count for pagination links
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM scans WHERE user_id = ?");
$count_stmt->bind_param("i", $current_user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);
$count_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WASP | Watchdog AI Command</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root { 
            --neon-yellow: #fbff00; 
            --danger: #ff2a2a; 
            --safe: #00f2ff; 
            --dark-bg: #030305;
            --panel-bg: rgba(13, 13, 17, 0.85);
            --glass-border: rgba(251, 255, 0, 0.15);
        }
        
        body { 
            background: var(--dark-bg);
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(20, 20, 30, 1) 0%, var(--dark-bg) 100%),
                url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            color: #f0f0f0; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0; 
            padding: 40px 20px;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Moving Neon Grid Floor Effect */
        body::after {
            content: "";
            position: fixed;
            bottom: -50%; left: -50%; width: 200%; height: 200%;
            background-image: 
                linear-gradient(var(--glass-border) 1px, transparent 1px),
                linear-gradient(90deg, var(--glass-border) 1px, transparent 1px);
            background-size: 60px 60px;
            transform: perspective(500px) rotateX(60deg);
            z-index: -1;
            animation: grid-move 20s linear infinite;
            opacity: 0.2;
        }
        @keyframes grid-move { from { background-position: 0 0; } to { background-position: 0 60px; } }

        /* TRIAL TIMER STYLING */
        .trial-monitor {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(251, 255, 0, 0.1);
            border: 1px solid var(--neon-yellow);
            border-top: none;
            padding: 10px 30px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.7rem;
            letter-spacing: 3px;
            color: var(--neon-yellow);
            z-index: 3000;
            clip-path: polygon(0 0, 100% 0, 90% 100%, 10% 100%);
            display: flex;
            align-items: center;
            gap: 15px;
            backdrop-filter: blur(10px);
        }
        #countdown-timer { font-family: 'Share Tech Mono', monospace; font-weight: bold; font-size: 0.9rem; }

        #alert-overlay {
            display: none; 
            position: fixed; 
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle, rgba(255, 0, 0, 0.3) 0%, rgba(0,0,0,0.9) 100%);
            z-index: 1001; 
            pointer-events: none;
            animation: red-alert-flash 0.5s infinite alternate;
        }
        @keyframes red-alert-flash { from { opacity: 0.3; } to { opacity: 1; } }

        .header { 
            text-align: center; 
            margin-bottom: 80px; 
        }

        .header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 5rem;
            font-weight: 900;
            margin: 0;
            letter-spacing: 25px;
            color: #fff;
            text-shadow: 
                0 0 10px var(--neon-yellow),
                0 0 20px var(--neon-yellow),
                0 0 40px rgba(251, 255, 0, 0.6);
            animation: glow-pulse 3s infinite ease-in-out;
        }
        @keyframes glow-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

        .header .subtitle {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-yellow);
            letter-spacing: 8px;
            margin-top: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 500;
        }

        .grid-container { 
            display: grid; 
            grid-template-columns: 450px 1fr; 
            gap: 40px; 
            max-width: 1600px; 
            margin: auto; 
            z-index: 2;
        }

        .panel { 
            background: var(--panel-bg);
            border: 1px solid var(--glass-border);
            padding: 40px; 
            border-radius: 4px;
            backdrop-filter: blur(20px);
            box-shadow: 0 0 40px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        /* Gold Line Accent */
        .panel::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--neon-yellow), transparent);
        }

        h3 { 
            font-family: 'Orbitron', sans-serif;
            color: #fff; 
            font-size: 0.8rem; 
            letter-spacing: 4px;
            margin-bottom: 40px; 
            text-transform: uppercase;
            display: flex;
            align-items: center;
        }
        h3::after { content: ""; flex: 1; height: 1px; background: var(--glass-border); margin-left: 20px; }

        .input-group {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        input[type="text"] { 
            flex: 1;
            padding: 18px; 
            background: rgba(0,0,0,0.6); 
            border: 1px solid var(--glass-border);
            color: var(--neon-yellow); 
            font-family: 'Share Tech Mono', monospace;
            outline: none;
            transition: 0.3s;
        }
        input[type="text"]:focus { border-color: var(--neon-yellow); box-shadow: 0 0 15px rgba(251, 255, 0, 0.2); }

        /* Premium File Upload Button */
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.05);
            border: 1px dashed var(--glass-border);
            color: #888;
            padding: 0 20px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 1.2rem;
        }
        .file-upload-label:hover { border-color: var(--neon-yellow); color: var(--neon-yellow); background: rgba(251, 255, 0, 0.05); }
        #file-input { display: none; }

        button { 
            width: 100%;
            background: var(--neon-yellow); 
            color: #000; 
            border: none; 
            padding: 20px; 
            font-family: 'Orbitron', sans-serif;
            font-weight: 900; 
            cursor: pointer;
            transition: 0.4s;
            letter-spacing: 6px;
            text-transform: uppercase;
            clip-path: polygon(5% 0, 100% 0, 95% 100%, 0% 100%);
        }
        button:hover { 
            background: #fff; 
            box-shadow: 0 0 30px var(--neon-yellow);
            transform: scale(1.02);
        }

        table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
        th { text-align: left; color: #555; font-size: 0.7rem; padding: 10px 20px; letter-spacing: 2px; }
        td { 
            padding: 22px 20px; 
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid rgba(255,255,255,0.05);
            font-family: 'Rajdhani', sans-serif;
        }
        
        .status-badge { 
            padding: 6px 15px; 
            font-size: 0.65rem; 
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 3px;
        }
        .bg-danger { border: 1px solid var(--danger); color: var(--danger); background: rgba(255, 42, 42, 0.1); text-shadow: 0 0 10px var(--danger); }
        .bg-safe { border: 1px solid var(--safe); color: var(--safe); background: rgba(0, 242, 255, 0.1); text-shadow: 0 0 10px var(--safe); }

        .system-stat { font-family: 'Share Tech Mono', monospace; font-size: 0.8rem; color: #666; margin-top: 15px; display: flex; align-items: center; }
        .active-dot { 
            width: 8px; height: 8px; background: var(--safe); border-radius: 50%;
            margin-right: 15px; box-shadow: 0 0 10px var(--safe);
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.3); opacity: 0.5; } 100% { transform: scale(1); opacity: 1; } }
        
        .nav-actions {
            position: absolute;
            top: 40px;
            right: 50px;
            display: flex;
            gap: 20px;
            z-index: 2000;
        }
        
        .logout-btn {
            color: #fff;
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.7rem;
            letter-spacing: 2px;
            padding: 10px 25px;
            border: 1px solid var(--danger);
            background: rgba(255, 42, 42, 0.05);
            transition: 0.3s;
            cursor: pointer;
            pointer-events: auto;
            position: relative;
        }
        .logout-btn:hover { background: var(--danger); color: #000; box-shadow: 0 0 20px var(--danger); }

        /* Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
            font-family: 'Orbitron', sans-serif;
        }
        .page-link {
            padding: 8px 15px;
            border: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
            color: var(--neon-yellow);
            text-decoration: none;
            font-size: 0.7rem;
            transition: 0.3s;
            clip-path: polygon(10% 0, 100% 0, 90% 100%, 0% 100%);
        }
        .page-link:hover:not(.disabled) {
            background: var(--neon-yellow);
            color: #000;
        }
        .page-link.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        .page-info {
            color: #555;
            font-size: 0.7rem;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<?php if ($expiry_time): ?>
<div class="trial-monitor">
    <span>NEURAL_UPLINK_EXPIRY:</span>
    <span id="countdown-timer">CALCULATING...</span>
</div>
<?php endif; ?>

<div class="nav-actions">
    <a href="logout.php" class="logout-btn">LOGOUT SESSION</a>
</div>

<div id="alert-overlay"></div>
<audio id="siren-sound" src="siren.mp3" preload="auto" loop></audio>

<div class="header">
    <h1>WASP</h1>
    <div class="subtitle">Watchdog AI for Secure Protection</div>
</div>

<div class="grid-container">
    <div class="panel">
        <h3>Command Authorization</h3>
        <form action="analyze.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" name="url" placeholder="PASTE TARGET URL..." autocomplete="off">
                <label for="file-input" class="file-upload-label" title="Upload File for Scan">
                    <span>+</span>
                </label>
                <input type="file" name="scan_file" id="file-input">
            </div>
            <button type="submit">EXECUTE NEURAL SCAN</button>
        </form>
        
        <div style="margin-top: 80px;">
            <div class="system-stat"><span class="active-dot"></span> DATABASE: SYNCHRONIZED</div>
            <div class="system-stat"><span class="active-dot"></span> THREAT ENGINE: ONLINE</div>
            <div class="system-stat"><span class="active-dot" style="background:var(--neon-yellow); box-shadow:0 0 10px var(--neon-yellow);"></span> UPLINK: SECURE</div>
        </div>
    </div>

    <div class="panel">
        <h3>Live Intelligence Stream</h3>
        <table>
            <thead>
                <tr>
                    <th>TARGET IDENTIFIER</th>
                    <th>THREAT EVALUATION</th>
                    <th>SCAN AGE</th>
                    <th>DOMAIN INTELLIGENCE</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch scans with LIMIT and OFFSET
                $stmt = $conn->prepare("SELECT * FROM scans WHERE user_id = ? ORDER BY scan_date DESC LIMIT ? OFFSET ?");
                $stmt->bind_param("iii", $current_user_id, $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $isFirst = true;
                $triggerAlert = false;

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $isMalicious = ($row['status'] == 'MALICIOUS');
                        $statusClass = $isMalicious ? 'bg-danger' : 'bg-safe';
                        
                        if($isFirst && $isMalicious && $page == 1) {
                            $triggerAlert = true;
                        }

                        $detailColor = (strpos($row['risk_detail'], 'CRITICAL') !== false) ? 'var(--danger)' : 'var(--safe)';
                        $scanTime = strtotime($row['scan_date']);
                        $displayAge = date("H:i:s", $scanTime);

                        echo "<tr>
                                <td style='color: #eee; font-family: \"Share Tech Mono\", monospace; font-size: 0.9rem;'>" . htmlspecialchars(substr($row['url'], 0, 50)) . "</td>
                                <td><span class='status-badge $statusClass'>{$row['status']}</span></td>
                                <td style='color: #666; font-size: 0.75rem; font-family: \"Share Tech Mono\";'>$displayAge</td>
                                <td style='font-weight: 700; color: $detailColor; font-size: 0.75rem;'>" . htmlspecialchars($row['risk_detail']) . "</td>
                              </tr>";
                        $isFirst = false;
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; color:#444; padding: 60px; font-family:\"Orbitron\";'>AWAITING INTERCEPTION...</td></tr>";
                }
                $stmt->close();
                ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <a href="?page=<?php echo $page - 1; ?>" class="page-link <?php echo ($page <= 1) ? 'disabled' : ''; ?>">PREVIOUS_LINK</a>
            <span class="page-info">NODE <?php echo $page; ?> / <?php echo $total_pages; ?></span>
            <a href="?page=<?php echo $page + 1; ?>" class="page-link <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">NEXT_LINK</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // 1. REAL-TIME COUNTDOWN LOGIC
    <?php if ($expiry_time): ?>
    const expiryDate = new Date("<?php echo date('M d, Y H:i:s', strtotime($expiry_time)); ?>").getTime();
    
    const x = setInterval(function() {
        const now = new Date().getTime();
        const distance = expiryDate - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown-timer").innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

        if (distance < 0) {
            clearInterval(x);
            document.getElementById("countdown-timer").innerHTML = "UPLINK_EXPIRED";
            window.location.reload(); // Force trigger the protection redirect
        }
    }, 1000);
    <?php endif; ?>

    // 2. ALERT & FILE LOGIC
    <?php if($triggerAlert): ?>
        const overlay = document.getElementById('alert-overlay');
        const audio = document.getElementById('siren-sound');
        function startAlarm() {
            overlay.style.display = 'block';
            audio.play().catch(e => {
                window.addEventListener('click', () => { startAlarm(); }, { once: true });
            });
            setTimeout(() => {
                audio.pause();
                audio.currentTime = 0;
                overlay.style.display = 'none';
            }, 4000);
        }
        window.onload = startAlarm;
    <?php endif; ?>

    document.getElementById('file-input').onchange = function() {
        if(this.files[0]) alert("File Selected: " + this.files[0].name + " | Ready for Neural Scan.");
    };
</script>
<?php include 'global_footer.php'; ?>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WASP | Authorization Required</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --neon-yellow: #fbff00; 
            --danger: #ff2a2a; 
            --dark-bg: #030305;
            --panel-bg: rgba(13, 13, 17, 0.9);
        }
        
        body { 
            background: var(--dark-bg);
            color: #fff; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0; 
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
        }

        /* Animated Scanline Background */
        body::before {
            content: " ";
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            z-index: 2;
            background-size: 100% 2px, 3px 100%;
            pointer-events: none;
        }

        .sub-container {
            width: 100%;
            max-width: 500px;
            padding: 50px;
            background: var(--panel-bg);
            border: 1px solid rgba(251, 255, 0, 0.3);
            text-align: center;
            position: relative;
            box-shadow: 0 0 50px rgba(0,0,0,1);
            backdrop-filter: blur(10px);
        }

        .sub-container::after {
            content: "ACCESS_DENIED";
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--danger);
            color: #000;
            font-family: 'Orbitron';
            padding: 5px 20px;
            font-size: 0.8rem;
            font-weight: 900;
            letter-spacing: 3px;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--neon-yellow);
            text-shadow: 0 0 15px rgba(251, 255, 0, 0.5);
        }

        p {
            color: #888;
            letter-spacing: 1px;
            margin-bottom: 40px;
        }

        .tier-card {
            border: 1px solid var(--neon-yellow);
            padding: 30px;
            background: rgba(251, 255, 0, 0.02);
            transition: 0.3s;
        }

        .price {
            font-size: 3rem;
            font-family: 'Orbitron';
            font-weight: 900;
            margin: 20px 0;
            display: block;
        }

        .features {
            list-style: none;
            padding: 0;
            text-align: left;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        .features li {
            margin-bottom: 10px;
            color: #ccc;
        }

        .features li::before {
            content: ">> ";
            color: var(--neon-yellow);
        }

        .btn-upgrade {
            display: block;
            width: 100%;
            padding: 20px;
            background: var(--neon-yellow);
            color: #000;
            text-decoration: none;
            font-family: 'Orbitron';
            font-weight: 900;
            letter-spacing: 3px;
            transition: 0.4s;
            clip-path: polygon(5% 0, 100% 0, 95% 100%, 0% 100%);
            margin-bottom: 15px;
        }

        .btn-upgrade:hover {
            background: #fff;
            box-shadow: 0 0 30px var(--neon-yellow);
            transform: scale(1.05);
        }

        /* Trial Button Styling */
        .btn-trial {
            display: block;
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-decoration: none;
            font-family: 'Orbitron';
            font-size: 0.75rem;
            letter-spacing: 2px;
            transition: 0.3s;
            clip-path: polygon(5% 0, 100% 0, 95% 100%, 0% 100%);
        }

        .btn-trial:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #fff;
            color: var(--neon-yellow);
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #555;
            text-decoration: none;
            font-size: 0.7rem;
            letter-spacing: 2px;
        }
        .back-link:hover { color: var(--danger); }
    </style>
</head>
<body>

<div class="sub-container">
    <h1>NEURAL ELITE</h1>
    <p>Your trial or access has expired. Re-authorize uplink to continue neural scanning operations.</p>

    <div class="tier-card">
        <span style="letter-spacing: 5px; color: #555; font-size: 0.7rem;">COMMANDER UPLINK</span>
        <span class="price">₹139<small style="font-size: 1rem;">/mo</small></span>
        
        <ul class="features">
            <li>Unlimited Neural URL Scans</li>
            <li>Deep File Integrity Analysis</li>
            <li>Real-time Threat Interception</li>
            <li>Priority Uplink Latency</li>
        </ul>

        <a href="process_payment.php" class="btn-upgrade">ACTIVATE UPLINK</a>
        <a href="start_trial.php" class="btn-trial">START 3-DAY NEURAL TRIAL</a>
    </div>

    <a href="logout.php" class="back-link">TERMINATE SESSION</a>
</div>
<?php include 'global_footer.php'; ?>
</body>
</html>
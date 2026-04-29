<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; // Changed from username to email
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Database Connection
    $conn = new mysqli("localhost", "root", "", "wasp_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validation
    if ($password !== $confirm_password) {
        $error = "ENCRYPTION MISMATCH: PASSWORDS DO NOT MATCH";
    } else {
        // Check if email already exists in the system
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "IDENTIFIER TAKEN: EMAIL ALREADY REGISTERED";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            /* FIX: We insert the $email into BOTH the 'email' and 'username' columns.
               This prevents the "Duplicate entry '' for key 'username'" error.
            */
            $stmt = $conn->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $message = "OPERATIVE REGISTERED. PROCEED TO LOGIN.";
            } else {
                $error = "SYSTEM ERROR: UPLINK FAILED";
            }
            $stmt->close();
        }
        $check->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WASP | Operative Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root { 
            --neon-yellow: #fbff00; 
            --danger: #ff2a2a; 
            --safe: #00f2ff;
            --dark-bg: #030305;
            --panel-bg: rgba(13, 13, 17, 0.9);
            --glass-border: rgba(0, 242, 255, 0.15);
        }
        
        body { 
            background: var(--dark-bg);
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(20, 20, 30, 1) 0%, var(--dark-bg) 100%),
                url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            color: #f0f0f0; 
            font-family: 'Rajdhani', sans-serif; 
            margin: 0; 
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Animated Grid Background */
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

        .register-panel { 
            width: 450px;
            background: var(--panel-bg);
            border: 1px solid var(--glass-border);
            padding: 40px 50px; 
            border-radius: 4px;
            backdrop-filter: blur(25px);
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            position: relative;
            text-align: center;
        }

        .register-panel::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--safe), transparent);
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            letter-spacing: 10px;
            margin: 0 0 10px 0;
            color: #fff;
            text-shadow: 0 0 15px var(--safe);
        }

        .subtitle {
            font-family: 'Orbitron', sans-serif;
            color: var(--safe);
            font-size: 0.6rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.55rem;
            letter-spacing: 2px;
            margin-bottom: 8px;
            color: #888;
        }

        input { 
            width: 100%;
            padding: 14px; 
            background: rgba(0,0,0,0.5); 
            border: 1px solid var(--glass-border);
            color: var(--safe); 
            font-family: 'Share Tech Mono', monospace;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--safe); background: rgba(0, 242, 255, 0.05); }

        .status-msg {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.75rem;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid transparent;
        }
        .error { color: var(--danger); border-color: rgba(255, 42, 42, 0.2); background: rgba(255, 42, 42, 0.05); }
        .success { color: var(--safe); border-color: rgba(0, 242, 255, 0.2); background: rgba(0, 242, 255, 0.05); }

        button { 
            width: 100%;
            background: var(--safe); 
            color: #000; 
            border: none; 
            padding: 18px; 
            font-family: 'Orbitron', sans-serif;
            font-weight: 900; 
            cursor: pointer;
            transition: 0.4s;
            letter-spacing: 4px;
            text-transform: uppercase;
            clip-path: polygon(5% 0, 100% 0, 95% 100%, 0% 100%);
            margin-top: 10px;
        }
        button:hover { 
            background: #fff; 
            box-shadow: 0 0 30px var(--safe);
            transform: translateY(-2px);
        }

        .auth-footer {
            margin-top: 25px;
        }

        .auth-link {
            font-family: 'Rajdhani', sans-serif;
            color: #666;
            text-decoration: none;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        .auth-link:hover { color: var(--safe); }
    </style>
</head>
<body>

<div class="register-panel">
    <h1>WASP</h1>
    <div class="subtitle">New Operative Enrollment</div>

    <?php if($error): ?>
        <div class="status-msg error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($message): ?>
        <div class="status-msg success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <label>ASSIGN IDENTIFIER (EMAIL)</label>
            <input type="email" name="email" required autocomplete="off">
        </div>

        <div class="input-group">
            <label>ACCESS CRYPT (PASSWORD)</label>
            <input type="password" name="password" required>
        </div>

        <div class="input-group">
            <label>CONFIRM CRYPT</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit">AUTHORIZE ACCOUNT</button>
    </form>

    <div class="auth-footer">
        <a href="login.php" class="auth-link">ALREADY HAVE ACCESS? LOGIN HERE</a>
    </div>
</div>
<?php include 'global_footer.php'; ?>
</body>
</html>
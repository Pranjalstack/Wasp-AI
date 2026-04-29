<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email']; // Changed from username to email
    $password = $_POST['password'];

    // Database Connection
    $conn = new mysqli("localhost", "root", "", "wasp_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query updated to check email column instead of username
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: index.php");
            exit();
        } else {
            $error = "INVALID CREDENTIALS: ACCESS DENIED";
        }
    } else {
        $error = "IDENTIFIER NOT FOUND IN DATABASE";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WASP | Neural Uplink Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root { 
            --neon-yellow: #00f2ff; /* Matched to Register Cyan */
            --danger: #ff2a2a; 
            --dark-bg: #030305;
            --panel-bg: rgba(13, 13, 17, 0.9);
            --glass-border: rgba(0, 242, 255, 0.15); /* Matched to Register Cyan */
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

        /* Matching Animated Grid Floor */
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

        .login-panel { 
            width: 400px;
            background: var(--panel-bg);
            border: 1px solid var(--glass-border);
            padding: 50px; 
            border-radius: 4px;
            backdrop-filter: blur(25px);
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            position: relative;
            text-align: center;
        }

        .login-panel::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--neon-yellow), transparent);
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            letter-spacing: 15px;
            margin: 0 0 10px 0;
            color: #fff;
            text-shadow: 0 0 15px var(--neon-yellow);
        }

        .subtitle {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-yellow);
            font-size: 0.65rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 40px;
            opacity: 0.8;
        }

        .input-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.6rem;
            letter-spacing: 2px;
            margin-bottom: 10px;
            color: #888;
        }

        input { 
            width: 100%;
            padding: 15px; 
            background: rgba(0,0,0,0.5); 
            border: 1px solid var(--glass-border);
            color: var(--neon-yellow); 
            font-family: 'Share Tech Mono', monospace;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--neon-yellow); background: rgba(0, 242, 255, 0.05); }

        .error-msg {
            color: var(--danger);
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.75rem;
            margin-bottom: 20px;
            text-shadow: 0 0 10px var(--danger);
        }

        button { 
            width: 100%;
            background: var(--neon-yellow); 
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
            box-shadow: 0 0 30px var(--neon-yellow);
            transform: translateY(-2px);
        }

        .auth-footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .auth-link {
            font-family: 'Rajdhani', sans-serif;
            color: #666;
            text-decoration: none;
            font-size: 0.8rem;
            transition: 0.3s;
            border-bottom: 1px solid transparent;
        }
        .auth-link:hover { color: var(--neon-yellow); border-color: var(--neon-yellow); }

        .sys-status {
            position: fixed;
            bottom: 20px;
            left: 20px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.7rem;
            color: #444;
        }
    </style>
</head>
<body>

<div class="login-panel">
    <h1>WASP</h1>
    <div class="subtitle">Neural Uplink Portal</div>

    <?php if($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <label>IDENTIFIER (EMAIL)</label>
            <input type="email" name="email" required autocomplete="off">
        </div>

        <div class="input-group">
            <label>ACCESS CRYPT (PASSWORD)</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">INITIALIZE LOGIN</button>
    </form>

    <div class="auth-footer">
        <a href="forgot_password.php" class="auth-link">FORGET PASSWORD?</a>
        <a href="register.php" class="auth-link">NEW OPERATIVE?</a>
    </div>
</div>

<div class="sys-status">
    UPLINK STATUS: STANDBY // PORT 8080: ACTIVE
</div>
<?php include 'global_footer.php'; ?>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$razorpay_key = "ENTER YOUR API";
$amount = 13900; // Updated to match your screenshot (₹139.00 = 13900 paise)
$currency = "INR";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WASP | Processing Uplink</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #030305; 
            color: #fbff00; 
            font-family: 'Orbitron', sans-serif; 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
        }
        .loader { 
            border: 3px solid #111; 
            border-top: 3px solid #fbff00; 
            border-radius: 50%; 
            width: 60px; 
            height: 60px; 
            animation: spin 1s linear infinite; 
            margin-bottom: 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .status-text { letter-spacing: 4px; font-size: 0.8rem; }
        
        /* Hide the default ugly button Razorpay generates */
        .razorpay-payment-button { display: none; }
    </style>
</head>
<body>
    <div class="loader"></div>
    <div class="status-text">INITIALIZING SECURE GATEWAY...</div>
    
    <form action="verify_payment.php" method="POST" id="razorpay-form">
        <script
            src="https://checkout.razorpay.com/v1/checkout.js"
            data-key="<?php echo $razorpay_key; ?>"
            data-amount="<?php echo $amount; ?>"
            data-currency="<?php echo $currency; ?>"
            data-name="WASP NEURAL"
            data-description="Neural Elite Access"
            data-image="https://cdn-icons-png.flaticon.com/512/2092/2092215.png"
            data-prefill.name="Agent"
            data-prefill.email="agent@wasp.ai"
            data-theme.color="#fbff00">
        </script>
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
    </form>

    <script>
        // Force the checkout to open as soon as the library is ready
        window.onload = function() {
            const rzpButton = document.querySelector('.razorpay-payment-button');
            if(rzpButton) {
                rzpButton.click();
            } else {
                // Fallback if button isn't rendered yet
                setTimeout(() => {
                    document.querySelector('.razorpay-payment-button').click();
                }, 1500);
            }
        };
    </script>
    <?php include 'global_footer.php'; ?>
</body>
</html>
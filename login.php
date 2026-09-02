<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// One browser session can only represent one logged-in account at a time.
// Require logout before another role/account can use this login page.
if (isset($_SESSION["user_id"])) {
    $targetPage = (($_SESSION["role"] ?? "") === "admin")
        ? "admin_dashboard.php"
        : "index.php";

    header("Location: " . $targetPage);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CalorEat Login</title>

    <link rel="stylesheet" href="assets/css/login.css?v=2">
    <link rel="stylesheet" href="assets/css/forgot-password.css?v=2">
</head>

<body>

    <div class="login-container">

        <div class="left-box">
            <div class="logo-box">
                <img src="images/logo.png" alt="CalorEat Logo" class="logo">
            </div>
            
            <h1 class="system-name">CalorEat</h1>
        </div>

        <div class="middle-line"></div>

        <div class="right-box">
            <form class="login-form" action="login_process.php" method="POST">

                <h2 class="login-title">
                    Hello,<br>
                    Welcome to CalorEat
                </h2>

                <input type="text" name="username" class="input-box" placeholder="Username or Email">

                <div class="password-section">

                    <div class="password-wrapper">
                        <input 
                            type="password"
                            name="password"
                            id="password"
                            class="input-box password-input"
                            placeholder="Password"
                        >
                        
                        <button type="button" class="toggle-password" data-target="password">
                            <img src="images/password/view.png" alt="Show Password">
                        </button>
                    </div>

                    <a href="#" class="forgot-link" id="openForgotModal">Forgot Password</a>
                    
                </div>

                <div class="button-area">
                    <button type="submit" class="login-button">Log In</button>
                </div>

                <p class="register-text">
                    Don't have an account?
                    <a href="register.php">Register</a>
                </p>

            </form>

        </div>

    </div>

    <!-- Forgot Password Pop up-->
     <div class="modal-overlay" id="forgotModal">
        <div class="forgot-card">
            <button type="button" class="close-modal" id="closeForgotModal">X</button>

            <div class="forgot-step" id="emailStep">
                <h2 class="forgot-title">Forgot Password</h2>

                <p class="forgot-text">Enter your email address to receive the OTP code.</p>

                <input
                    type="email"
                    class="forgot-input"
                    id="forgotEmail"
                    placeholder="Email Address"
                    autocomplete="email"
                >

                <button type="button" class="forgot-submit" id="forgotSubmit">Submit</button>

                <p class="otp-message" id="emailMessage"></p>
            </div>

            <div class="forgot-step otp-step" id="otpStep">
                <h2 class="forgot-title">Enter OTP</h2>

                <p class="forgot-text">Please enter the 6-digit OTP code sent to your email.</p>

                <div class="otp-boxes">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                </div>

                <a href="#" class="resend-otp" id="resendOtp">Resend OTP</a>

                <p class="otp-message" id="otpMessage"></p>

                <button type="button" class="forgot-submit" id="verifyOtp">Verify</button>
            </div>

            <!--Reset Step-->
            <div class="forgot-step reset-step" id="resetStep">
                <h2 class="forgot-title">Reset Password</h2>

                <p class="forgot-text">Enter your new password below.</p>

                <div class="reset-password-wrapper">
                    <input
                        type="password"
                        class="reset-input"
                        id="newPassword"
                        placeholder="New Password"
                        autocomplete="new-password"
                    >

                    <button type="button" class="reset-toggle-password" data-target="newPassword">
                        <img src="images/password/view.png" alt="Show Password">
                    </button>
                </div>

                <div class="reset-password-wrapper">
                    <input
                        type="password"
                        class="reset-input"
                        id="confirmNewPassword"
                        placeholder="Confirm Password"
                        autocomplete="new-password"
                    >

                    <button type="button" class="reset-toggle-password" data-target="confirmNewPassword">
                        <img src="images/password/view.png" alt="Show Confirm Password">
                    </button>
                </div>

                <button type="button" class="forgot-submit" id="resetPasswordBtn">Reset</button>

                <p class="otp-message" id="passwordMessage"></p>
            </div>

            <!--Success Step-->
            <div class="forgot-step success-step" id="resetSuccessStep">
                <img src="images/accept.png" alt="Success" class="success-icon">
                
                <h2 class="forgot-title">Successful</h2>

                <p class="forgot-text">Your password has been reset successfully.</p>

                <button type="button" class="forgot-submit" id="backToLoginBtn">Back to Log In</button>
            </div>
            
        </div>

     </div>

    <script src="assets/js/login.js"></script>
    <script src="assets/js/forgot-password.js?v=2"></script>
    <script src="assets/js/modal-reset.js?v=2"></script>

</body>
</html>
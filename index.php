<?php
session_start(); // Start the session

require 'includes/dbconnect.php';

// Initialize variables
$loginUsername = $loginPassword = "";
$loginError = "";

// Check for success message from session
$successMessage = "";
if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginUsername = trim($_POST["username"]);
    $loginPassword = $_POST["password"];

    // Admin credentials (hashed for security)
    $specificEmail = "dennislaysonjr3@gmail.com";
    $specificUsername = "dslaysonjr";
    $specificHashedPassword = '$2y$10$eImiTXuWVxfM37uY4JANjQ=='; // Replace with the correct hash

    if (($loginUsername === $specificEmail || $loginUsername === $specificUsername) && password_verify($loginPassword, $specificHashedPassword)) {
        $_SESSION['user'] = $loginUsername;
        $_SESSION['role'] = 'admin';
        header("Location: /PharmaEase/PharmaEase-Final/components/Admin/manage_orders.php");
        exit();
    }

    // Check the database for regular users
    $stmt = $pdo->prepare("SELECT password FROM registered_users WHERE username = ? OR contact_number = ? OR email = ?");
    $stmt->execute([$loginUsername, $loginUsername, $loginUsername]);

    if ($stmt->rowCount() > 0) {
        $storedPassword = $stmt->fetchColumn();

        // Verify the password
        if (password_verify($loginPassword, $storedPassword)) {
            $_SESSION['user'] = $loginUsername;
            $_SESSION['role'] = 'user';
            header("Location: home.php");
            exit();
        } else {
            $loginError = "Invalid password.";
        }
    } else {
        $loginError = "No account found with the provided username, contact number, or email.";
    }
}
$hasLoginError = !empty($loginError) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/mainstyles.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <title>PharmaEase</title>
</head>
<body>
    <!-- <div class="page-transition">
        <div class="div"></div>
        <div class="div"></div>
        <div class="div"></div>
        <div class="div"></div>
        <ul class="preload">
          <li></li>
          <li></li>
          <li></li>
          <li></li>
          <li></li>
        </ul>
    </div> -->
    <div class="container">
        <div class="img">
            <img src="assets/LoginCover.png" alt="Cover">
        </div>
        <div class="form">
            <img src="assets/PharmaEaseFull.png" alt="Logo" class="logo-img">
            <h2>Log in</h2>
            <?php if (!empty($successMessage)): ?>
                <h2 class="success-message" style="color: #88c273;">
                    <?php echo htmlspecialchars($successMessage); ?>
                </h2>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" placeholder="Username, Email, or Contact Number" name="username" value="<?php echo htmlspecialchars($loginUsername); ?>" required>
                <input type="password" placeholder="Password" name="password" required>
                <div class="check">
                    <div>
                        <input type="checkbox" id="remember-me">
                        <label for="remember-me">Remember me</label>
                    </div>
                    <span><a href="#" onclick="fadeOutAndRedirect('/PharmaEase/PharmaEase-Final/components/main/forgotpassword.php')">Forget Password</a></span>
                </div>
                <button type="submit" class="button" name="login"><span><strong>LOG IN</strong></span></button>
                <?php if (!empty($loginError)) echo "<p style='color:red;'>$loginError</p>"; ?>
                <p>Don't have an account yet?</p>
                <a href="authentication/registration.php">
                <button type="button" class="button" id="register-button" onclick="fadeOutAndRedirect('/PharmaEase/PharmaEase-Final/components/registration/registration.php')"><span><strong>REGISTER</strong></span></button>
                </a>
            </form>
        </div>
    </div>
    <script>
        const hasLoginError = <?php echo $hasLoginError; ?>;
        const hasSuccessMessage = <?php echo !empty($successMessage) ? 'true' : 'false'; ?>;

        if (!hasLoginError && !hasSuccessMessage) {
            document.addEventListener("DOMContentLoaded", () => {
                const preloader = document.querySelector(".page-transition");
                const container = document.querySelector(".container");
                const preloaderDivs = document.querySelectorAll(".page-transition .div");
                const preloaderDots = document.querySelectorAll(".preload li");

                const slideDown = gsap.timeline({ paused: true });
                const loading = gsap.timeline({ paused: true, repeat: 1 });
                const slideUp = gsap.timeline({ paused: true });

                // Slide down animation for preloader
                slideDown.to(preloaderDivs, {
                    duration: 0.5,
                    bottom: "0%",
                    ease: "power2.in",
                    stagger: 0.2,
                });

                // Loading animation for dots
                loading.from(preloaderDots, {
                    duration: 0.5,
                    y: -15,
                    autoAlpha: 0,
                    ease: "power1.in",
                    stagger: 0.2,
                }).to(preloaderDots, {
                    duration: 0.5,
                    y: 35,
                    autoAlpha: 0,
                    ease: "power1.in",
                    stagger: 0.1,
                });

                // Slide up animation for preloader
                slideUp.to(preloaderDivs, {
                    duration: 0.5,
                    bottom: "100%",
                    ease: "power2.out",
                    stagger: 0.2,
                });

                // Run animations sequentially
                slideDown
                    .play()
                    .add(loading.play(), "+=0.5")
                    .add(slideUp.play(), "+=0.5")
                    .eventCallback("onComplete", () => {
                        preloader.style.display = "none";
                        container.style.opacity = 1;
                    });
            });
        } else {
            document.querySelector(".page-transition").style.display = "none";
            document.querySelector(".container").style.opacity = 1;
        }
    </script>
</body>
</html>
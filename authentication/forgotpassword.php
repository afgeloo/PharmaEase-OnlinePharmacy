<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmaease_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

$forgotUsername = $forgotEmail = $forgotContactNumber = "";
$usernameError = $emailError = $contactNumberError = $generalError = "";
$showErrors = false;  
// Max attempts and cooldown duration in seconds (1 minute)
$maxAttempts = 3;
$cooldownDuration = 60;

// Track attempts and cooldown using session
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Check if the user is on cooldown
if ($_SESSION['attempts'] >= $maxAttempts) {
    $timePassed = time() - $_SESSION['last_attempt_time'];
    if ($timePassed < $cooldownDuration) {
        $remainingTime = $cooldownDuration - $timePassed;
        $showErrors = true;
        $generalError = "You have exceeded the maximum number of attempts. Please try again in <span id='countdown'>$remainingTime</span> seconds.";
    } else {
        // Reset attempts after cooldown
        $_SESSION['attempts'] = 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_SESSION['attempts'] < $maxAttempts) {
    // Get the user input for each field
    $forgotUsername = $_POST["username"];
    $forgotEmail = $_POST["email"];
    $forgotContactNumber = $_POST["contact_number"];

    // Check if the username exists in the database
    $stmt = $conn->prepare("SELECT user_id FROM registered_users WHERE username = ?");
    $stmt->bind_param("s", $forgotUsername);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $usernameError = "Username does not exist.";
    }
    $stmt->close();

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT user_id FROM registered_users WHERE email = ?");
    $stmt->bind_param("s", $forgotEmail);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $emailError = "Email does not exist.";
    }
    $stmt->close();

    // Check if the contact number exists in the database
    $stmt = $conn->prepare("SELECT user_id FROM registered_users WHERE contact_number = ?");
    $stmt->bind_param("s", $forgotContactNumber);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $contactNumberError = "Contact number does not exist.";
    }
    $stmt->close();

    // If all fields are valid, proceed to the next page
    if (empty($usernameError) && empty($emailError) && empty($contactNumberError)) {
        $_SESSION['reset_user'] = [
            'username' => $forgotUsername,
            'email' => $forgotEmail,
            'contact_number' => $forgotContactNumber
        ];
        header("Location: newpassword.php");
        exit();
    } else {
        $_SESSION['attempts'] += 1;
        $_SESSION['last_attempt_time'] = time();
        $generalError = "Please ensure all fields are correct.";
        $showErrors = true;  // Set the flag to show errors
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/forgotpassword.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" type="image/png" href="../assets/PharmaEaseLogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <title>Forgot Password</title>
    <script>
        // JavaScript function to allow only numeric input and limit to 11 digits
        function validateContactNumber(event) {
            const input = event.target;
            // Allow only numbers and prevent input if it's not a number or the length exceeds 11
            input.value = input.value.replace(/[^0-9]/g, '').slice(0, 11);
        }

        // Countdown timer for cooldown
        function startCooldownTimer(remainingTime) {
            const countdownElement = document.getElementById('countdown');
            const submitButton = document.querySelector("button[type='submit']");

            let timeLeft = remainingTime;

            const timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval); // Stop the timer when it reaches 0
                    countdownElement.textContent = ''; // Clear countdown
                    submitButton.disabled = false; // Enable the form submit button
                } else {
                    countdownElement.textContent = timeLeft; // Update the countdown display
                    timeLeft--;
                }
            }, 1000);
        }

        window.onload = function() {
            // Check if the user is on cooldown and start the countdown
            const remainingTime = <?php echo isset($remainingTime) ? $remainingTime : 0; ?>;
            if (remainingTime > 0) {
                // Disable form submission while on cooldown
                document.querySelector("button[type='submit']").disabled = true;
                startCooldownTimer(remainingTime);
            }
        }
    </script>
</head>
<body>
    <div class="container fade-in">
        <div class="main">
            <h2>Forgot Password</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label class="text-start">Username:</label>
                <input class="input" type="text" name="username" value="<?php echo htmlspecialchars($forgotUsername); ?>" required>
                <?php if ($showErrors && !empty($usernameError)) echo "<div class='error'>$usernameError</div>"; ?>

                <label class="text-start">Email:</label>
                <input class="input" type="text" name="email" value="<?php echo htmlspecialchars($forgotEmail); ?>" required>
                <?php if ($showErrors && !empty($emailError)) echo "<div class='error'>$emailError</div>"; ?>

                <label class="text-start">Contact Number:</label>
                <input class="input" type="text" name="contact_number" value="<?php echo htmlspecialchars($forgotContactNumber); ?>" required maxlength="11" oninput="validateContactNumber(event)">
                <?php if ($showErrors && !empty($contactNumberError)) echo "<div class='error'>$contactNumberError</div>"; ?>

                <button class="submit button" type="submit"><strong>Proceed</strong></button>
                <?php if ($showErrors && !empty($generalError)) echo "<div class='error'>$generalError</div>"; ?>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const container = document.querySelector('.container');
            container.classList.add('fade-in');
        });
    </script>
</body>
</html>

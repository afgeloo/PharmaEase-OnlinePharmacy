<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmaease_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

$newPassword = $confirmPassword = "";
$passwordError = $confirmPasswordError = $generalError = "";
$showErrors = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    if (empty($newPassword)) {
        $passwordError = "Password is required";
    } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/", $newPassword)) {
        $passwordError = "Password must contain letters, numbers, and symbols, with a minimum of 8 characters";
    }

    if ($newPassword !== $confirmPassword) {
        $confirmPasswordError = "Passwords do not match";
    }

    if (empty($passwordError) && empty($confirmPasswordError)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $username = $_SESSION['reset_user']['username'];

        $stmt = $conn->prepare("UPDATE registered_users SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashedPassword, $username);

        if ($stmt->execute()) {
            $successMessage = "Password reset successful!";
            header("Location: ../index.php");
            exit();
        } else {
            $generalError = "Error: " . $conn->error;
        }
    } else {
        $showErrors = true;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/newpassword.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" type="image/png" href="../assets/PharmaEaseLogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <title>Reset Password</title>
</head>
<body>
    <div class="container fade-in">
        <div class="main">
            <h2>Reset Password</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label>New Password:</label>
                <input class="input" type="password" name="new_password" value="<?php echo htmlspecialchars($newPassword); ?>" required>
                <?php if ($showErrors && !empty($passwordError)) echo "<div class='error'>$passwordError</div>"; ?>

                <label>Confirm Password:</label>
                <input class="input" type="password" name="confirm_password" value="<?php echo htmlspecialchars($confirmPassword); ?>" required>
                <?php if ($showErrors && !empty($confirmPasswordError)) echo "<div class='error'>$confirmPasswordError</div>"; ?>

                <button class="submit button" type="submit" name="submit"><strong>Reset Password</strong></button>
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

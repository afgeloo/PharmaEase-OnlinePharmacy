<?php
// verify_registration.php
session_start(); // Start the session

ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmaease_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

$verificationCode = "";
$verificationError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify'])) {
    $verificationCode = $_POST["verification_code"];

    if (empty($verificationCode)) {
        $verificationError = "Verification code is required";
    } elseif (!preg_match("/^\d{6}$/", $verificationCode)) {
        $verificationError = "Invalid verification code format";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM registered_users WHERE code_verification = ? AND is_verified = 0");
        $stmt->bind_param("i", $verificationCode);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $updateStmt = $conn->prepare("UPDATE registered_users SET is_verified = 1, code_verification = NULL WHERE code_verification = ?");
            $updateStmt->bind_param("i", $verificationCode);
            if ($updateStmt->execute()) {
                $_SESSION['successMessage'] = "Your email has been verified successfully!";
                header("Location: ../index.php");
                exit();
            } else {
                $verificationError = "Error updating record: " . $conn->error;
            }
        } else {
            $verificationError = "Invalid or expired verification code.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Verify Registration</title>
    <link rel="stylesheet" type="text/css" href="../css/verify_registration.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container fade-in">
        <div class="main">
            <img src="../assets/PharmaEaseFull.png" alt="Logo" class="logo-img">
            <h2>Verify Your Email</h2><br>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data">
                <label>Verification Code:</label>
                <input class="input" type="text" name="verification_code" value="<?php echo htmlspecialchars($verificationCode); ?>">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$verificationError</div>"; ?>

                <input class="submit button" type="submit" name="verify" value="Verify">
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
<?php
// registration.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmaease_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

$firstName = $lastName = $birthday = $age = $contactNumber = $email = $address = $username = $password = $confirmPassword = "";
$firstNameError = $lastNameError = $birthdayError = $ageError = $contactError = $emailError = $addressError = $usernameError = $passwordError = $confirmPasswordError = "";
$errors = 0;
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Retrieve and sanitize input
    $firstName = $_POST["first_name"];
    $lastName = $_POST["last_name"];
    $birthday = $_POST["birthday"];
    $age = $_POST["age"];
    $contactNumber = $_POST["contact_number"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    // Validate inputs
    if (empty($firstName)) $firstNameError = "First Name is required";
    if (empty($lastName)) $lastNameError = "Last Name is required";
    if (empty($age)) {
        $ageError = "Age is required";
    } elseif (!is_numeric($age) || $age < 0) {
        $ageError = "Age must be a positive number";
    } elseif ($age < 13) {
        $ageError = "You must be at least 13 years old";
    }
    
    if (empty($birthday)) {
        $birthdayError = "Birthday is required";
    } else {
        $birthYear = (int)substr($birthday, 0, 4); // Extract the year
        $currentYear = date("Y");
    
        if ($birthYear < 1000 || $birthYear > $currentYear) {
            $birthdayError = "Birth year must be a valid 4-digit number and not exceed the current year";
        }
    }    

    if (empty($contactNumber)) $contactError = "Contact Number is required";
    elseif (!preg_match("/^\d{11}$/", $contactNumber)) $contactError = "Contact number must be 11 digits";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $emailError = "Email is not valid";

    if (empty($address)) $addressError = "Address is required";
    if (empty($username)) $usernameError = "Username is required";

    // if (empty($password)) $passwordError = "Password is required";
    // elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/", $password)) {
    //     $passwordError = "Password must contain letters, numbers, and symbols, with a minimum of 8 characters";
    // }

    if ($password !== $confirmPassword) $confirmPasswordError = "Passwords do not match";

    if (!$firstNameError && !$lastNameError && !$birthdayError && !$ageError && !$contactError && !$emailError && !$addressError && !$usernameError && !$passwordError && !$confirmPasswordError) {
        // Check if email, username, or contact number already exists
        $checkQuery = $conn->prepare("SELECT email, username, contact_number FROM registered_users WHERE email = ? OR username = ? OR contact_number = ?");
        $checkQuery->bind_param("sss", $email, $username, $contactNumber);
        $checkQuery->execute();
        $checkResult = $checkQuery->get_result();
    
        if ($checkResult->num_rows > 0) {
            $existingData = $checkResult->fetch_assoc();
            if ($existingData['email'] === $email) {
                $emailError = "Email already exists.";
            }
            if ($existingData['username'] === $username) {
                $usernameError = "Username already exists.";
            }
            if ($existingData['contact_number'] === $contactNumber) {
                $contactError = "Contact number already exists.";
            }
        } else {
            // Generate verification code
            $verificationCode = rand(100000, 999999);
    
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO registered_users (first_name, last_name, birthday, age, contact_number, email, address, username, password, is_verified, code_verification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");
            $stmt->bind_param("sssisssssi", $firstName, $lastName, $birthday, $age, $contactNumber, $email, $address, $username, $hashedPassword, $verificationCode);
    
            if ($stmt->execute()) {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Mailer = "smtp";
                    //$mail->SMTPDebug = 1;
                    $mail->SMTPAuth = TRUE;
                    $mail->SMTPSecure = "tls";
                    $mail->Port = 587;
                    $mail->Host = "smtp.gmail.com";
                    $mail->Username = "pharmaease.info@gmail.com";
                    $mail->Password = "mgbo dlkk ukoo feve";
    
                    $mail->setFrom("pharmaease.info@gmail.com", "PharmaEase");
                    $mail->addAddress($email, $firstName . ' ' . $lastName);
                    $mail->isHTML(true);
                    $mail->Subject = "Email Verification";
                    $mail->Body = "Your verification code is: <b>$verificationCode</b>";
    
                    $mail->send();
                    header("Location: verify_registration.php");
                    exit();
                } catch (Exception $e) {
                    $successMessage = "Registration successful, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $successMessage = "Error: " . $conn->error;
            }
        }
        $checkQuery->close();
    }
}    

$conn->close();
?>
<!DOCTYPE HTML>
<html>
<head>
<title>PHP Registration Form</title>
    <link rel="stylesheet" type="text/css" href="../css/registration.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <script>
    function calculateAge() {
    const birthdayInput = document.getElementById('birthday'); // Get the input element
    const ageInput = document.getElementById('age');
    const birthday = new Date(birthdayInput.value); // Parse the input value as a Date
    const today = new Date();

    if (isNaN(birthday)) {
        ageInput.value = ""; // Clear the age if the input is invalid
        return;
    }

    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    const dayDiff = today.getDate() - birthday.getDate();

    // Adjust for incomplete birthdays in the current year
    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
        age--;
    }

    // Prevent negative age
    age = Math.max(age, 0);
    ageInput.value = age;

    // Restrict invalid birth years and future dates
    restrictBirthYear();
}

function restrictBirthYear() {
    const birthdayInput = document.getElementById('birthday');
    const value = birthdayInput.value;

    if (value) {
        const birthYear = parseInt(value.split('-')[0], 10); // Extract year portion
        const currentYear = new Date().getFullYear();
        const maxDate = new Date().toISOString().split('T')[0]; // Current date in YYYY-MM-DD

        // Check if birth year is valid
        if (birthYear < 1000 || birthYear > currentYear) {
            alert("Invalid birth year. Please enter a valid year (4 digits) that does not exceed the current year.");
            birthdayInput.value = ""; // Clear invalid input
            return;
        }

        // Check if date is in the future
        if (value > maxDate) {
            alert("The selected date cannot be in the future.");
            birthdayInput.value = ""; // Clear invalid input
        }
    }
}


        function validateContactNumber(input) {
            input.value = input.value.replace(/\D/g, '').substring(0, 11);
        }
    </script>
</head>
<body>
    <div class="container fade-in">
        <div class="main">
            <img src="../assets/PharmaEaseFull.png" alt="Logo" class="logo-img">
            <h2>Registration Form</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data">
                <label>First Name:</label>
                <input class="input" type="text" name="first_name" value="<?php echo $firstName; ?>">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$firstNameError</div>"; ?>

                <label>Last Name:</label>
                <input class="input" type="text" name="last_name" value="<?php echo $lastName; ?>">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$lastNameError</div>"; ?>

                <label>Birthday:</label>
                <input 
                    class="input" 
                    type="date" 
                    name="birthday" 
                    id="birthday" 
                    value="<?php echo $birthday; ?>" 
                    onchange="calculateAge()" 
                    onkeydown="return false;" 
                    oninput="restrictBirthYear()">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$birthdayError</div>"; ?>

                <label>Age:</label>
                <input class="input" type="number" name="age" id="age" value="<?php echo $age; ?>" readonly>
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$ageError</div>"; ?>

                <label>Contact Number:</label>
                <input class="input" type="text" name="contact_number" value="<?php echo $contactNumber; ?>" oninput="validateContactNumber(this)">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$contactError</div>"; ?>

                <label>Email:</label>
                <input class="input" type="email" name="email" value="<?php echo $email; ?>">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$emailError</div>"; ?>

                <label>Address:</label>
                <textarea class="input" name="address"><?php echo $address; ?></textarea>
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$addressError</div>"; ?>

                <label>Username:</label>
                <input class="input" type="text" name="username" value="<?php echo $username; ?>">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$usernameError</div>"; ?>

                <label>Password:</label>
                <input class="input" type="password" name="password">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$passwordError</div>"; ?>

                <label>Confirm Password:</label>
                <input class="input" type="password" name="confirm_password">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo "<div class='error'>$confirmPasswordError</div>"; ?>

                <input class="submit button" type="submit" name="submit" value="Register">
                <p><?php echo $successMessage; ?></p>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const container = document.querySelector('.container');
        const inputs = document.querySelectorAll('input, textarea');

        container.classList.add('fade-in');

        inputs.forEach((input, index) => {
            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Prevent form submission

                    // If it's a textarea, make sure it doesn't jump to the next input
                    if (input.tagName.toLowerCase() === 'textarea') {
                        return;
                    }

                    const nextInput = inputs[index + 1]; // Get the next input field
                    if (nextInput) {
                        nextInput.focus(); // Focus on the next input field
                    }
                }
            });
        });
    });
</script>
</body>
</html>
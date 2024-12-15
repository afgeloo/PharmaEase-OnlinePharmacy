<?php
session_start();
require 'includes/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission for updates
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Retrieve and sanitize input
    $firstName = $_POST["first_name"];
    $lastName = $_POST["last_name"];
    $birthday = $_POST["birthday"];
    $contactNumber = $_POST["contact_number"];
    $email = $_POST["email"];
    $address = $_POST["address"];

    // Validate inputs
    if (empty($firstName)) $errors[] = "First Name is required";
    if (empty($lastName)) $errors[] = "Last Name is required";
    if (empty($birthday)) {
        $errors[] = "Birthday is required";
    } else {
        $birthYear = (int)substr($birthday, 0, 4); // Extract the year
        $currentYear = date("Y");
    
        if ($birthYear < 1000 || $birthYear > $currentYear) {
            $errors[] = "Birth year must be a valid 4-digit number and not exceed the current year";
        }
    }

    if (empty($contactNumber)) {
        $errors[] = "Contact Number is required";
    } elseif (!preg_match("/^09\d{9}$/", $contactNumber)) {
        $errors[] = "Contact number must start with 09 and be 11 digits long";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email is not valid";

    if (empty($address)) $errors[] = "Address is required";

    if (empty($errors)) {
        $sql = "UPDATE registered_users SET first_name=?, last_name=?, birthday=?, contact_number=?, email=?, address=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $firstName, $lastName, $birthday,  $contactNumber, $email, $address, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch user data
$sql = "SELECT first_name, last_name, birthday, contact_number, email, address, username 
        FROM registered_users 
        WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - PharmaEase</title>
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/home.css">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #FFF9F0;
        }
        .account-form input[readonly],
        .account-form textarea[readonly] {
            background-color: #e9ecef;
        }
        .edit-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4">My Account</h1>
    <div class="card p-4">
        <form class="account-form" id="account-form" method="POST">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-4">Account Information</h2>
                <button type="button" class="btn btn-secondary edit-btn" id="editButton">Edit</button>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly required>
                    
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly required>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="birthday" class="form-label">Birthday</label>
                    <input type="date" class="form-control" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>" readonly required>
                </div>
                <div class="col-md-6">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="contact_number" 
                        name="contact_number" 
                        value="<?php echo htmlspecialchars($user['contact_number']); ?>" 
                        readonly 
                        required 
                        maxlength="11" 
                        pattern="09\d{9}" 
                        inputmode="numeric"
                    >
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>
                </div>
                <div class="col-md-6">
                    <label for="username" class="form-label">Username (cannot be changed)</label>
                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
            </div>

            <div class="mt-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" readonly required><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <div class="mt-4 d-none" id="saveChangesDiv">
                <button type="submit" class="btn btn-primary w-100" name="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editButton = document.getElementById('editButton');
    const accountForm = document.getElementById('account-form');
    const saveChangesDiv = document.getElementById('saveChangesDiv');

    // Enable editing when "Edit" button is clicked
    editButton.addEventListener('click', function () {
        const inputs = accountForm.querySelectorAll('input, textarea');
        inputs.forEach((input) => {
            if (input.id !== 'username') {
                input.removeAttribute('readonly');
            }
        });

        saveChangesDiv.classList.remove('d-none');
        editButton.disabled = true;
    });

    // Prevent typing in birthday (force user to select from date picker)
    const birthdayField = document.getElementById('birthday');
    birthdayField.addEventListener('click', function () {
        if (birthdayField.readOnly) {
            birthdayField.removeAttribute('readonly');
        }
    });

    // Validate contact number input (must start with 09 and be numeric)
    const contactNumberField = document.getElementById('contact_number');
    contactNumberField.addEventListener('input', function () {
        let value = contactNumberField.value;

        // Allow only numbers and prevent symbols and letters
        value = value.replace(/[^0-9]/g, '');
        if (!/^09\d{0,9}$/.test(value)) {
            contactNumberField.setCustomValidity("Contact number must start with 09 and be 11 digits long");
        } else {
            contactNumberField.setCustomValidity('');
        }

        contactNumberField.value = value;
    });
</script>
</body>
</html>

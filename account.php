<?php
session_start();
require 'includes/dbconnect.php';

// Check user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    // Calculate age from birthday if needed
    $age = 0;
    if (!empty($birthday)) {
        $birthDate = new DateTime($birthday);
        $today = new DateTime('today');
        $age = $birthDate->diff($today)->y;
    }

    $sql = "UPDATE registered_users SET first_name=?, last_name=?, birthday=?, age=?, contact_number=?, email=?, address=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $first_name, $last_name, $birthday, $age, $contact_number, $email, $address, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch user data
$sql = "SELECT first_name, last_name, birthday, age, contact_number, email, address, username FROM registered_users WHERE user_id = ? LIMIT 1";
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
            background-color: #f8f9fa;
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
                    <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" readonly required pattern="[0-9]{11}">
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
                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
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

    editButton.addEventListener('click', function() {
        // Toggle fields to editable
        const inputs = accountForm.querySelectorAll('input, textarea');
        inputs.forEach((input) => {
            if (input.id !== 'username') {
                input.removeAttribute('readonly');
            }
        });

        // Show save changes button
        saveChangesDiv.classList.remove('d-none');
        editButton.disabled = true;
    });
</script>
</body>
</html>

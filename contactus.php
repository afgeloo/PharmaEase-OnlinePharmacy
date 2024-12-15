<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/dbconnect.php';

// Assuming user is logged in and user_id is stored in the session
$user_id = $_SESSION['user_id']; // Get the logged-in user_id from the session

// Fetch user information based on user_id
$sql = "SELECT first_name, last_name, contact_number, email FROM registered_users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize variables
$full_name = $contact_number = $email = '';

if ($result->num_rows > 0) {
    // Fetch the user data
    $user = $result->fetch_assoc();
    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
    $contact_number = $user['contact_number'];
    $email = $user['email'];

    // Concatenate first_name and last_name to get full_name
    $full_name = $first_name . ' ' . $last_name;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $message = htmlspecialchars($_POST['message']);

    // Display a thank-you message and prevent page reload
    echo "<script>
        alert('Thank you for your feedback!');
        console.log('Submitted Data:');
        console.log('Name: {$name}');
        console.log('Email: {$email}');
        console.log('Phone: {$phone}');
        console.log('Message: {$message}');
        // Keep the page open, no redirection
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/contactus.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="css/reset.css">
  <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
  <title>Welcome to PharmaEase</title>
  <script src="js/products.js"></script>
</head>
<body>
<div class="container">

<?php include 'includes/header.php'; ?>

  <div class="container">  
    <form id="contact" action="" method="post">
      <h3>Leave a message!</h3>
      <h4>Contact us today, and get reply within 24 hours!</h4>
      
      <fieldset>
        <input placeholder="Your name" type="text" name="name" tabindex="1" value="<?php echo htmlspecialchars($full_name); ?>" required autofocus>
      </fieldset>
      
      <fieldset>
        <input placeholder="Your Email Address" type="email" name="email" tabindex="2" value="<?php echo htmlspecialchars($email); ?>" required>
      </fieldset>
      
      <fieldset>
        <input placeholder="Your Phone Number" type="tel" name="phone" tabindex="3" value="<?php echo htmlspecialchars($contact_number); ?>" required>
      </fieldset>
      
      <fieldset>
        <textarea placeholder="Type your Message Here...." name="message" tabindex="5" required></textarea>
      </fieldset>
      
      <fieldset>
        <button name="submit" type="submit" id="contact-submit" data-submit="...Sending">Submit</button>
      </fieldset>
    </form>
  </div>

<?php include 'includes/footer.php'; ?>

</body>
</html>

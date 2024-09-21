<?php

include_once('connect.php');

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connect(); 

    // Validate and sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Check if email and password are not empty
    if (!empty($email) && !empty($password)) {
        // Prepare and execute the SQL statement to check user credentials
        $sql = "SELECT id, username, email, password, phone FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            // Check if the user exists
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $user_name, $user_email, $hashed_password, $user_phone);
                $stmt->fetch();

                // Verify the password
                if (password_verify($password, $hashed_password)) {
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $user_name;
                    $_SESSION['user_email'] = $user_email;
                    $_SESSION['user_contact'] = $user_phone; // Store phone in session for filtering bookings
                    header('Location: index.php'); // Redirect to home page after successful login
                    exit();
                } else {
                    // Display an error message if the password is incorrect
                    echo "<div class='error'>Invalid email or password!</div>";
                }
            } else {
                // Display an error message if the email is not found in the database
                echo "<div class='error'>Invalid email or password!</div>";
            }

            $stmt->close();
        } else {
            // Display an error message if the SQL statement preparation fails
            echo "<div class='error'>Failed to prepare the SQL statement.</div>";
        }
    } else {
        // Display an error message if email or password is empty
        echo "<div class='error'>Email and password are required!</div>";
    }

    $conn->close();
}
?>
<div class="form-container">
    <h2 class="form-title">Login</h2>
    <form method="POST" action="index.php?page=login">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit"><i class="bi bi-box-arrow-in-right"></i> Login</button>
    </form>
    <p class="already-have-account">
        <i class="bi bi-arrow-left"></i> Don't have an account? <a href="index.php?page=register">Register here</a>.
    </p>
</div>
<link rel="stylesheet" href="./css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

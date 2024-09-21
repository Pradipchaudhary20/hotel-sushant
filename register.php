<?php

include_once('connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = connect();

// Check if the connection is established
if (!$conn) {
    die("Database connection failed.");
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate name (must have at least a first and last name)
    if (str_word_count($username) < 2) {
        echo "<p class='error'>Please enter both first and last names.</p>";
        exit();
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p class='error'>Please enter a valid email address.</p>";
        exit();
    }

    // Validate phone (must start with 04 and be exactly 10 digits)
    if (!preg_match('/^04\d{8}$/', $phone)) {
        echo "<p class='error'>Contact number must start with '04' and be 10 digits long.</p>";
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<p class='error'>Passwords do not match!</p>";
        exit();
    }

    // Check for duplicate email or phone
    $sql_check = "SELECT * FROM users WHERE email = ? OR phone = ?";
    $stmt_check = $conn->prepare($sql_check);

    if ($stmt_check) {
        $stmt_check->bind_param("ss", $email, $phone);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            echo "<p class='error'>Email or phone number already exists!</p>";
            $stmt_check->close();
            exit();
        }
        $stmt_check->close();
    } else {
        echo "<p class='error'>Error checking for duplicates: " . $conn->error . "</p>";
        exit();
    }

    // If validation passes, hash the password and insert the user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql_insert = "INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);

    if ($stmt_insert) {
        $stmt_insert->bind_param("ssss", $username, $email, $phone, $hashed_password);

        if ($stmt_insert->execute()) {
            echo "<script>
                    alert('Registration successful! Redirecting to login page.');
                    window.location.href = 'index.php?page=login';
                  </script>";
        } else {
            echo "<p class='error'>Error: " . $stmt_insert->error . "</p>";
        }

        $stmt_insert->close();
    } else {
        echo "<p class='error'>Error preparing statement: " . $conn->error . "</p>";
    }

    $conn->close();
}
?>

<div class="form-container">
    <h2 class="form-title">Register</h2>
    <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required pattern="04\d{8}" title="Phone number must start with 04 and be 10 digits">
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit"><i class="bi bi-person-plus-fill"></i> Register</button>
    </form>
    <p class="already-have-account">
        <i class="bi bi-arrow-left"></i> Already have an account? <a href="index.php?page=login">Please sign in</a>
    </p>
</div>

<link rel="stylesheet" href="./css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

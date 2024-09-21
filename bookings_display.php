<?php
include_once('connect.php'); 

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = connect(); 

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the user is logged in by checking session variables
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_contact'])) {
    echo "<p>Please log in to view your bookings.</p>";
    exit;
}

// Fetch the user's email and contact from the session
$user_email = $_SESSION['user_email'];
$user_contact = $_SESSION['user_contact'];

function getUserBookings($conn, $email, $contact) {
    // SQL query to fetch bookings where the email and contact match the logged-in user
    $sql = "SELECT 
                b.id, 
                b.checkinDate, 
                b.checkoutDate, 
                b.user_name, 
                b.user_email, 
                b.user_contact, 
                r.name AS room_name
            FROM 
                bookings b
            JOIN 
                rooms r ON b.room = r.id
            WHERE 
                b.user_email = ? AND b.user_contact = ?
            ORDER BY 
                b.checkinDate ASC";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    // Check if the statement was prepared successfully
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    // Bind the user's email and contact to the statement
    $stmt->bind_param("ss", $email, $contact);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if any bookings were found and return them as an associative array
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$bookings = getUserBookings($conn, $user_email, $user_contact); // Fetch bookings for the logged-in user
$conn->close(); // Close the database connection
?>

<!-- HTML to display bookings -->
<div class="bookings-container-wrapper">
    <h2>Your Bookings</h2>
    <div class="bookings-container">
        <?php if (empty($bookings)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-details">
                        <h3><?php echo htmlspecialchars($booking['room_name']); ?></h3>
                        <p><strong>User Name:</strong> <?php echo htmlspecialchars($booking['user_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['user_email']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($booking['user_contact']); ?></p>
                        <p><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['checkinDate']); ?></p>
                        <p><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['checkoutDate']); ?></p>
                        <form method="post" action="./cancel.php">
                            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                            <button type="submit" class="cancel-button">Cancel</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<link rel="stylesheet" href="./css/mybookings.css">
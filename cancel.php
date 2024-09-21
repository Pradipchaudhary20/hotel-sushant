<?php
// Include the connection file
include_once('connect.php');

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header('Location: index.php?page=login');
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connect(); // Ensure the connection is established
    $booking_id = $_POST['booking_id'];

    // Validate that the booking_id is not empty and is a number
    if (!empty($booking_id) && is_numeric($booking_id)) {
        // Prepare the SQL statement to delete the booking
        $sql = "DELETE FROM bookings WHERE id = ? AND user_email = ?";

        // Ensure the statement is prepared correctly
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters (i for integer, s for string)
            $stmt->bind_param("is", $booking_id, $_SESSION['user_email']);
            
            // Execute the statement
            if ($stmt->execute()) {
                // Check if a row was affected (meaning the booking was deleted)
                if ($stmt->affected_rows > 0) {
                    // Booking cancelled successfully, show alert and redirect
                    echo "<script>
                            alert('Booking cancelled successfully!');
                            window.location.href = 'index.php';
                          </script>";
                    exit;
                } else {
                    echo "<div class='error'>Failed to cancel booking. Please try again or contact support.</div>";
                }
            } else {
                // Error executing the query
                echo "<div class='error'>An error occurred while cancelling your booking. Please try again later.</div>";
            }
            $stmt->close();
        } else {
            // Error preparing the statement
            echo "<div class='error'>Failed to prepare the SQL statement.</div>";
        }
    } else {
        // Invalid booking_id
        echo "<div class='error'>Invalid booking ID.</div>";
    }
    $conn->close(); // Close the database connection
} else {
    // Redirect if the request method is not POST
    header('Location: index.php?page=mybookings');
    exit;
}
?>

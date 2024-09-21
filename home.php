<?php
// Include necessary files
include_once('connect.php');
include_once('rooms.php'); 
include_once('RoomStatus.php');
// Create database connection
$dbConnection = connect();

// Check if the connection is established
if (!$dbConnection) {
    http_response_code(500);
    die(json_encode(["message" => "Database connection failed."]));
}

// Fetch all rooms
$rooms = fetchAllRooms($dbConnection);

// Initialize variables for check-in, check-out, and persons
$checkinDate = isset($_GET['checkin']) ? $_GET['checkin'] : null;
$checkoutDate = isset($_GET['checkout']) ? $_GET['checkout'] : null;
$numberOfPersons = isset($_GET['persons']) ? (int)$_GET['persons'] : null;

// Validate user input for check-in, check-out, and persons
if ($checkinDate && $checkoutDate && $numberOfPersons) {
    // Display only available rooms
    if (!empty($rooms) && is_array($rooms)) {
        echo '<div class="rooms-container">'; // Flex container for horizontal layout
        foreach ($rooms as $room) {
            // Check availability of each room using calculateAvailableSlots function
            $availableSlots = calculateAvailableSlots($dbConnection, $room['id'], $checkinDate); // Check availability based on check-in date

            // Show the room only if there are available slots
            if ($availableSlots > 0) {
                renderRoomCard($room); // Function to display room card
            }
        }
        echo '</div>'; // Close flex container
    } else {
        echo "<p>No rooms found.</p>";
    }
}
// Close the connection
$dbConnection->close();
?>

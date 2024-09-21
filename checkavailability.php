<?php
// Include the connection and room functions
include_once('connect.php');
include_once('RoomStatus.php'); // Ensure calculateAvailableSlots is defined here
include_once('rooms.php');

// Initialize variables to hold form data and availability results
$availabilityMessage = '';
$availableRooms = [];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $checkinDate = $_POST['checkin_date'] ?? '';
    $checkoutDate = $_POST['checkout_date'] ?? '';
    $numberOfPersons = isset($_POST['persons']) ? (int)$_POST['persons'] : 0;

    // Validate dates and ensure check-in is before check-out and check-in is today or later
    $currentDate = date('Y-m-d');
    if (
        empty($checkinDate) || empty($checkoutDate) || $numberOfPersons <= 0 ||
        strtotime($checkinDate) >= strtotime($checkoutDate) || strtotime($checkinDate) < strtotime($currentDate)
    ) {
        $availabilityMessage = "<p style='color: red;'>Please provide valid check-in and check-out dates, with check-out after check-in, and a positive number of persons.</p>";
    } else {
        // Connect to the database
        $dbConnection = connect();

        // Check if the connection was successful
        if ($dbConnection === false) {
            $availabilityMessage = "<p style='color: red;'>Database connection failed. Please try again later.</p>";
        } else {
            // Fetch available rooms based on the selected dates
            $availableRooms = fetchAvailableRooms($dbConnection, $checkinDate, $checkoutDate);

            // If no rooms are available, display a message
            if (empty($availableRooms)) {
                $availabilityMessage = "<p style='color: red;'>Sorry, no rooms are available for your selected dates.</p>";
            } else {
                $availabilityMessage = "<p style='color: white; margin: 20px auto; text-align: center; width: 100%;'>Rooms are available for your selected dates!</p>";
            }

            // Close the connection
            $dbConnection->close();
        }
    }
}
?>
<div class="main-content">
    <div class="check-availability">
        <form method="POST" action="index.php?page=checkavailability">
            <div>
                <label for="checkin_date">Check-in:</label>
                <input type="date" id="checkin_date" name="checkin_date" required value="<?php echo htmlspecialchars($checkinDate); ?>">
            </div>
            <div>
                <label for="checkout_date">Check-out:</label>
                <input type="date" id="checkout_date" name="checkout_date" required value="<?php echo htmlspecialchars($checkoutDate); ?>">
            </div>
            <div>
                <label for="persons">Persons:</label>
                <input type="number" id="persons" name="persons" min="1" required value="<?php echo htmlspecialchars($numberOfPersons); ?>">
            </div>
            <button type="submit">Search</button>
        </form>
        <?php if (!empty($availabilityMessage)): ?>
            <p class="availability-message"><?php echo $availabilityMessage; ?></p>
        <?php endif; ?>
    </div>
    <div class="available-rooms">
        <?php
        if (!empty($availableRooms)) {
            foreach ($availableRooms as $room) {
                renderRoomCard($room);
            }
        }
        ?>
    </div>
</div>

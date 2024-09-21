<?php

// Function to fetch all rooms from the database
function fetchAllRooms($dbConnection) {
    $sql = "SELECT id, name, facilities, image, price_per_night FROM rooms"; // Fetch facilities and other room details
    $result = $dbConnection->query($sql);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC); // Fetch as associative array
    } else {
        // Handle errors or empty results
        if ($dbConnection->error) {
            error_log("Error fetching rooms: " . $dbConnection->error);
            return ["error" => "Error fetching rooms from the database."];
        } else {
            return []; // No rooms found
        }
    }
}

// Function to fetch available rooms based on check-in and check-out dates
function fetchAvailableRooms($dbConnection, $checkinDate, $checkoutDate) {
    $sql = "
        SELECT r.id, r.name, r.facilities, r.image, r.price_per_night 
        FROM rooms r
        WHERE r.id NOT IN (
            SELECT b.room 
            FROM bookings b
            WHERE (b.checkinDate < ? AND b.checkoutDate > ?)
               OR (b.checkinDate < ? AND b.checkoutDate > ?)
               OR (b.checkinDate >= ? AND b.checkoutDate <= ?)
        )
    ";
    
    $stmt = $dbConnection->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssss", $checkoutDate, $checkinDate, $checkoutDate, $checkinDate, $checkinDate, $checkoutDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC); // Fetch as associative array
        } else {
            return []; // No rooms available for the selected dates
        }

        $stmt->close();
    } else {
        error_log("Error preparing statement: " . $dbConnection->error);
        return ["error" => "Error fetching available rooms from the database."];
    }
}

// Function to display each room card with facilities as a bullet point list
function renderRoomCard($room) {
    // Convert image data to base64 format if image data exists
    $imageSource = !empty($room['image']) ? 'data:image/png;base64,' . base64_encode($room['image']) : 'default-image-path.jpg'; // Use a default image if not available
    $facilitiesList = explode(',', $room['facilities']); // Convert facilities to an array

    echo '
        <div class="room-card">
            <img class="room-image" alt="' . htmlspecialchars($room['name']) . '" src="' . $imageSource . '">
            <div class="room-details">
                <h3 class="room-title">' . htmlspecialchars($room['name']) . '</h3>
                <ul class="room-facilities-list">';
    
    // Display facilities as a bullet list
    if (!empty($facilitiesList)) {
        foreach ($facilitiesList as $facility) {
            echo '<li>' . htmlspecialchars(trim($facility)) . '</li>'; // Trim and sanitize each facility
        }
    } else {
        echo '<li>No facilities listed.</li>'; 
    }
    
    echo '  </ul>
                <h4 class="price">$' . htmlspecialchars($room['price_per_night']) . ' / per night</h4>
                <div class="buttons">
                    <button class="book-room" 
                            data-room-name="' . htmlspecialchars($room['name']) . '" 
                            data-room-id="' . htmlspecialchars($room['id']) . '">
                        Book Now
                    </button>
                </div>
            </div>
        </div>
    ';
}
?>

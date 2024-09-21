<?php

// Function to calculate available slots for a room based on room ID and check-in date
function calculateAvailableSlots($dbConnection, $roomId, $checkinDate) {
    // Retrieve the number of bookings for the specified room and date
    $totalBookings = retrieveBookingsCount($dbConnection, $roomId, $checkinDate);

    // Define the maximum bookings allowed per room per day
    $maxAllowedBookings = 3;

    // Calculate the available slots by subtracting the total bookings from the maximum allowed
    $remainingSlots = $maxAllowedBookings - $totalBookings;

    // Return the number of available slots, ensuring it's not negative
    return max($remainingSlots, 0);
}

// Helper function to fetch the count of bookings for a specific room ID and check-in date
function retrieveBookingsCount($dbConnection, $roomId, $checkinDate) {
    $sqlQuery = "SELECT COUNT(*) FROM bookings WHERE room = ? AND checkinDate = ?";
    $preparedStatement = $dbConnection->prepare($sqlQuery);
    $preparedStatement->bind_param("is", $roomId, $checkinDate);
    $preparedStatement->execute();
    $queryResult = $preparedStatement->get_result();
    $numberOfBookings = $queryResult->fetch_row()[0];
    $preparedStatement->close();

    return $numberOfBookings;
}

?>

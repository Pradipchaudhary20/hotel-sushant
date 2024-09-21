<?php
include_once('./connect.php');
include_once('./RoomStatus.php'); // Assuming this is now named properly or contains the refactored function

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden status
    echo json_encode(["message" => "You must be logged in to book a room.", "redirect" => "index.php?page=login"]);
    exit;
}

$body = file_get_contents('php://input');
$data = json_decode($body, true); // Decode as associative array

// Validate that all required fields are present
if (!isset($data['room_id'], $data['fullname'], $data['email'], $data['contact'], $data['checkinDate'], $data['checkoutDate'], $data['checkinPersons'])) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: Missing required fields."]);
    exit;
}

// Extract and trim input data
$roomId = (int)$data['room_id'];
$fullName = trim($data['fullname']);
$userEmail = trim($data['email']);
$userContact = trim($data['contact']);
$checkinDate = $data['checkinDate'];
$checkoutDate = $data['checkoutDate'];
$numberOfPersons = (int)$data['checkinPersons'];

// Retrieve logged-in user's details from session
$loggedInEmail = trim($_SESSION['user_email']);
$loggedInName = trim($_SESSION['username']);
$loggedInContact = trim($_SESSION['user_contact']);

// Debugging output
error_log("Logged-in Email: $loggedInEmail, Input Email: $userEmail");
error_log("Logged-in Name: $loggedInName, Input Name: $fullName");
error_log("Logged-in Contact: $loggedInContact, Input Contact: $userContact");

// Validate that booking details match the logged-in user's details
if ($userEmail !== $loggedInEmail || strtolower($fullName) !== strtolower($loggedInName) || $userContact !== $loggedInContact) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: The booking details must match the details used during login. Please use the same email, name, and contact number."]);
    exit;
}

// Validate full name (must contain at least two parts)
if (str_word_count($fullName) < 2) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: Full name must include both first and last names."]);
    exit;
}

// Validate email format
if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: Please enter a valid email address."]);
    exit;
}

// Validate phone number (must start with 04 and be 10 digits)
if (!preg_match('/^04\d{8}$/', $userContact)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: Contact number must start with 04 and be 10 digits long."]);
    exit;
}

// Validate that checkout date is after check-in date
if (strtotime($checkoutDate) <= strtotime($checkinDate)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: Checkout date must be after check-in date."]);
    exit;
}

// Validate that the number of persons is positive
if ($numberOfPersons <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: Number of persons must be a positive number."]);
    exit;
}

// Connect to the database and fetch room details to determine allowed number of persons
$dbConnection = connect();
$roomQuery = "SELECT name FROM rooms WHERE id = ?";
$stmt = $dbConnection->prepare($roomQuery);
$stmt->bind_param("i", $roomId);
$stmt->execute();
$roomDetails = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Validate the number of persons based on the room type
if ($roomDetails) {
    $roomName = $roomDetails['name'];
    $maxAllowedPersons = 0;

    if (strpos($roomName, 'Twin') !== false) {
        $maxAllowedPersons = 2;
    } elseif (strpos($roomName, 'Presidential') !== false) {
        $maxAllowedPersons = 5;
    } else {
        $maxAllowedPersons = 3;
    }

    if ($numberOfPersons > $maxAllowedPersons) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input: The number of persons exceeds the room capacity. $roomName allows up to $maxAllowedPersons persons."]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input: Room not found."]);
    exit;
}

// Check available slots for the given room and check-in date using the refactored function
$availableSlots = calculateAvailableSlots($dbConnection, $roomId, $checkinDate);

if ($availableSlots > 0) {
    $insertBooking = "INSERT INTO bookings (room, user_name, user_email, user_contact, checkinDate, checkoutDate, checkinPersons) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
    $bookingStmt = $dbConnection->prepare($insertBooking);
    $bookingStmt->bind_param("isssssi", $roomId, $fullName, $userEmail, $userContact, $checkinDate, $checkoutDate, $numberOfPersons);
    
    if ($bookingStmt->execute()) {
        echo json_encode(["message" => "Booking Successful!"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Booking Unsuccessful! Error: " . $dbConnection->error]);
    }
    $bookingStmt->close();
} else {
    http_response_code(400);
    echo json_encode(["message" => "Booking Unsuccessful! Room is not available for the selected date."]);
}

$dbConnection->close();
?>

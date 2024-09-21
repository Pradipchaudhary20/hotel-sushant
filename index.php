<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking</title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="./css/styles.css"> 
    <link rel="stylesheet" href="./css/checkrooms.css"> 
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Righteous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
</head>

<body>
    <?php
        // Including the header
        include_once('./header.php');
    ?>
    <div id="banner-bg">
        <div class="banner">
            <img src="./images/banner.jpg" alt="Hotel Banner" class="banner-image">
        </div>
    </div>
    <main>
        <?php
        // Dynamically including content based on the 'page' URL parameter
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            switch ($page) {
                case 'login':
                    include('./login.php');
                    break;
                case 'register':
                    include('./register.php');
                    break;
                case 'about':
                    include('./about.php');
                    break;
                case 'gallery':
                    include('./gallery.php');
                    break;
                case 'mybookings': 
                    include('./bookings_display.php');
                    break;
                case 'home':
                default:
                    include_once('./checkavailability.php');
                    include('./home.php');
                    break;
            }
        } else {
            // Default to home and availability checks
            include_once('./checkavailability.php');
            include('./home.php');
        }
        ?>
    </main>

    <?php
        // Including the footer
        include_once('./footer.php');
    ?>
    <!-- Booking Modal -->
    <div id="bookingModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" aria-label="Close">&times;</span>
            <form id="bookRoomForm">
                <h2>Book Room</h2>
                <input type="text" name="fullname" placeholder="Enter Full Name" required>
                <input type="email" name="email" placeholder="Enter Email" required>
                <input type="tel" name="contact" placeholder="Enter Contact" required pattern="04\d{8}" title="Contact number must start with 04 and be 10 digits long">
                <input type="date" name="checkinDate" required>
                <input type="date" name="checkoutDate" required>
                <input type="number" name="checkinPersons" placeholder="Number of Persons" required min="1">
                <input type="hidden" name="room_name" value="">
                <input type="hidden" name="room_id" value="">
                <button type="submit">Book Room</button>
            </form>
        </div>
    </div>
    <script src="./js/booking.js"></script>
</body>
</html>

document.addEventListener('DOMContentLoaded', () => {
    const bookingModal = document.getElementById('bookingModal');
    const closeBtn = document.querySelector('.close');
    const bookRoomForm = document.getElementById('bookRoomForm');

    // Show the modal with room details
    document.querySelectorAll('.book-room').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default button behavior
            const roomName = e.target.getAttribute('data-room-name');
            const roomId = e.target.getAttribute('data-room-id');
            bookRoomForm.room_name.value = roomName;
            bookRoomForm.room_id.value = roomId;
            bookingModal.style.display = 'block';
        });
    });

    // Close modal when clicking the close button or outside the modal
    closeBtn.addEventListener('click', () => {
        bookingModal.style.display = 'none';
    });
    window.addEventListener('click', (e) => {
        if (e.target == bookingModal) {
            bookingModal.style.display = 'none';
        }
    });

    // Handle form submission with AJAX
    bookRoomForm.addEventListener('submit', (e) => {
        e.preventDefault();

        // Form validation
        const fullName = bookRoomForm.fullname.value.trim();
        const email = bookRoomForm.email.value.trim();
        const contact = bookRoomForm.contact.value.trim();
        const checkinDate = new Date(bookRoomForm.checkinDate.value);
        const checkoutDate = new Date(bookRoomForm.checkoutDate.value);
        const persons = bookRoomForm.checkinPersons.value.trim();

        let isValid = true;
        let errorMessage = '';

        // Validate full name
        if (fullName.split(' ').length < 2) {
            isValid = false;
            errorMessage += 'Full name must include both first and last names.\n';
        }

        // Validate email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            isValid = false;
            errorMessage += 'Please enter a valid email address.\n';
        }

        // Validate phone number
        if (!contact.startsWith('04') || contact.length !== 10 || isNaN(contact)) {
            isValid = false;
            errorMessage += 'Contact number must start with 04 and be 10 digits long.\n';
        }

        // Validate check-out date is after check-in date
        if (checkoutDate <= checkinDate) {
            isValid = false;
            errorMessage += 'Checkout date must be after check-in date.\n';
        }

        // Validate number of persons
        if (isNaN(persons) || persons <= 0) {
            isValid = false;
            errorMessage += 'Number of persons must be a positive number.\n';
        }

        // Display error messages if validation fails
        if (!isValid) {
            alert(errorMessage);
            return;
        }

        // If valid, proceed with form submission
        const formData = new FormData(bookRoomForm);
        const data = Object.fromEntries(formData.entries());

        fetch('./Room_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.redirect) {
                // If the response contains a redirect URL, show alert and redirect to login page
                alert(data.message);
                window.location.href = data.redirect;
            } else {
                alert(data.message); // Display server response
                if (data.message.includes("Successful")) {
                    bookingModal.style.display = 'none';
                    bookRoomForm.reset(); // Reset the form after successful booking
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});

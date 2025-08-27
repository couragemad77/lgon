// gutu-hospital/assets/js/main.js

// This function runs when the entire HTML document has been loaded.
$(document).ready(function() {

    // --- Variable Declarations ---
    const loginModalBtn = $('#login-modal-btn');
    const loginModal = $('#login-modal');
    const loginForm = $('#login-form');
    const roleButtons = $('.role-btn');
    const signupLink = $('#signup-link');

    // --- Event Handlers ---

    // 1. Show the Login Modal
    loginModalBtn.on('click', function() {
        loginModal.addClass('active');
    });

    // 2. Hide the Login Modal if clicking on the overlay (the dark background)
    loginModal.on('click', function(event) {
        // We check if the clicked element is the overlay itself, not the container inside it.
        if ($(event.target).is(loginModal)) {
            loginModal.removeClass('active');
            resetModal(); // Reset the form when closing
        }
    });

    // 3. Handle Role Selection
    roleButtons.on('click', function() {
        // Get the role from the 'data-role' attribute of the clicked button
        const selectedRole = $(this).data('role');

        // Visually mark the selected button
        roleButtons.removeClass('selected');
        $(this).addClass('selected');

        // Set the hidden input value in the form
        $('#login-role').val(selectedRole);

        // Show the login form
        loginForm.slideDown(); // A nice sliding effect

        // Show or hide the "Sign Up" link based on the role
        if (selectedRole === 'patient') {
            signupLink.show();
        } else {
            signupLink.hide();
        }
    });


    // --- Helper Functions ---

    /**
     * Resets the modal to its initial state when closed.
     */
    function resetModal() {
        roleButtons.removeClass('selected'); // Unselect role buttons
        loginForm.hide();                    // Hide the form
        loginForm[0].reset();                // Clear any typed text in the form
    }

});
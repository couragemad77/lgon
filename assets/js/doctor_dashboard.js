// gutu-hospital/assets/js/doctor_dashboard.js (NEW ADVANCED VERSION)
$(document).ready(function() {

    const loadingModal = $('#loading-modal');
    const declineConfirmModal = $('#decline-confirm-modal');

    /**
     * Shows a popup notification message at the top right of the screen.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'error'.
     */
    function showPopupNotification(message, type = 'success') {
        // Create the notification element
        const notification = $('<div></div>')
            .addClass('popup-notification')
            .addClass(type)
            .text(message);
        
        // Add it to the body, show it, then remove it after a delay
        $('body').append(notification);
        setTimeout(() => notification.addClass('show'), 10); // Add a tiny delay for the CSS transition to work
        setTimeout(() => {
            notification.removeClass('show');
            // Remove the element from the DOM after the fade-out transition
            setTimeout(() => notification.remove(), 500);
        }, 3000); // Notification stays for 3 seconds
    }

    /**
     * Sends the final AJAX request to the backend.
     * @param {number} appointmentId - The ID of the appointment.
     * @param {string} action - 'approve_appointment' or 'decline_appointment'.
     */
    function performAction(appointmentId, status) { // Changed 'action' to 'status'
        const tableRow = $('#appointment-row-' + appointmentId);

        // Show the loading modal
        loadingModal.addClass('active');

        $.ajax({
            url: '/gutu-hospital/backend/appointment_handler.php', // CHANGED URL
            type: 'POST',
            dataType: 'json',
            data: {
                appointment_id: appointmentId,
                action: 'update_appointment_status', // CHANGED Action
                status: status // ADDED status
            },
            success: function(response) {
                loadingModal.removeClass('active');
                
                if (response.success) {
                    const successMessage = (status === 'approved') 
                        ? 'Appointment approved successfully!' 
                        : 'Appointment declined successfully!';
                    showPopupNotification(successMessage, 'success');

                    tableRow.fadeOut(500, function() {
                        $(this).remove();
                        if ($('#pending-requests-table tbody tr').length === 0) {
                            $('#pending-requests-table tbody').html('<tr><td colspan="3">No pending requests.</td></tr>');
                        }
                    });
                } else {
                    showPopupNotification(response.message || 'An error occurred.', 'error');
                }
            },
            error: function(xhr) {
                loadingModal.removeClass('active');
                showPopupNotification('An error occurred. Please try again.', 'error');
                console.error("Action Error:", xhr.responseText);
            }
        });
    }

    // --- Event Handlers ---

    // When a doctor clicks the main "Approve" button
    $('#pending-requests-table').on('click', '.approve-btn', function() {
        const appointmentId = $(this).data('appid');
        performAction(appointmentId, 'approved');
    });

    // When a doctor clicks the main "Decline" button
    $('#pending-requests-table').on('click', '.decline-btn', function() {
        const appointmentId = $(this).data('appid');
        // Store the ID on the confirmation button and show the confirmation modal
        $('#confirm-decline-btn').data('appid', appointmentId);
        declineConfirmModal.addClass('active');
    });

    // When the doctor confirms the decline inside the modal
    $('#confirm-decline-btn').on('click', function() {
        const appointmentId = $(this).data('appid');
        declineConfirmModal.removeClass('active');
        performAction(appointmentId, 'declined');
    });

    // When the doctor cancels the decline
    $('.cancel-decline-btn').on('click', function() {
        declineConfirmModal.removeClass('active');
    });

    // --- NEW: Event Handler for "Call Next Patient" ---
    $('#call-next-btn').on('click', function() {
        const queueList = $('.live-queue-list');
        const firstPatientInQueue = queueList.find('li:first');

        if (!firstPatientInQueue.length) {
            showPopupNotification('The queue is empty.', 'error');
            return;
        }

        loadingModal.addClass('active');

        $.ajax({
            url: '/gutu-hospital/backend/appointment_handler.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'call_next_patient'
            },
            success: function(response) {
                loadingModal.removeClass('active');
                if (response.success) {
                    showPopupNotification(`Calling ${response.patientName} now.`, 'success');
                    firstPatientInQueue.fadeOut(500, function() {
                        $(this).remove();
                        if (queueList.find('li').length === 0) {
                            // If the queue is now empty, show a message
                            $('.card:has(.live-queue-list)').append('<p>No patients are currently checked in.</p>');
                            $('#call-next-btn').remove();
                        }
                    });
                } else {
                    showPopupNotification(response.message || 'Could not call next patient.', 'error');
                }
            },
            error: function(xhr) {
                loadingModal.removeClass('active');
                showPopupNotification('An error occurred. Please try again.', 'error');
                console.error("Call Patient Error:", xhr.responseText);
            }
        });
    });
});

$(document).ready(function() {

    // --- LOGIC FOR THE MAIN DASHBOARD (index.php) ---

    // 1. Generate QR Codes for all approved appointments
    $('.qrcode-container').each(function() {
        const qrData = $(this).data('qrdata');
        if (qrData) {
            new QRCode(this, {
                text: qrData,
                width: 128,
                height: 128,
            });
        }
    });

    // 2. Handle QR Code Download
    $('.download-qr-btn').on('click', function() {
        const targetSelector = $(this).data('target');
        const fileName = $(this).data('filename');
        
        // The QRCode.js library generates a <canvas> element inside the div
        const canvas = $(targetSelector).find('canvas')[0];

        if (canvas) {
            // Create a temporary link element
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = fileName;
            
            // Programmatically click the link to trigger the download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('Could not find QR Code to download.');
        }
    });


    // --- LOGIC FOR THE BOOKING PAGE (book-appointment.php) ---
    const bookingModal = $('#booking-modal');
    if (bookingModal.length) {
        // ... (All the modal and booking form logic remains the same)
        const bookingForm = $('#booking-form');
        const appointmentDateInput = $('#appointmentDate');
        const timeSlotsContainer = $('#time-slots');

        $('.book-now-btn').on('click', function() {
            const doctorId = $(this).data('doctorId');
            const doctorName = $(this).data('doctorName');
            const department = $(this).data('department');
            $('#modal-doctor-name').text(doctorName);
            $('#modal-department').text(department);
            $('#modal-doctor-id').val(doctorId);
            $('#modal-form-department').val(department);
            bookingModal.addClass('active');
        });

        bookingModal.on('click', function(event) {
            if ($(event).is(bookingModal)) {
                bookingModal.removeClass('active');
                resetModal();
            }
        });

        appointmentDateInput.on('change', function() {
            const selectedDate = $(this).val();
            const doctorId = $('#modal-doctor-id').val();
            if (!doctorId || !selectedDate) return;
            timeSlotsContainer.html('<p>Loading...</p>');
            $.ajax({
                url: '/gutu-hospital/backend/appointment_handler.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_available_slots', doctorId: doctorId, date: selectedDate },
                success: function(response) {
                    timeSlotsContainer.empty();
                    if (response.length > 0) {
                        response.forEach(function(slot) {
                            $('<button type="button"></button>').addClass('btn time-slot-btn').text(slot).data('time', slot).appendTo(timeSlotsContainer);
                        });
                    } else {
                        timeSlotsContainer.html('<p>No slots available.</p>');
                    }
                }
            });
        });

        timeSlotsContainer.on('click', '.time-slot-btn', function() {
            timeSlotsContainer.find('.time-slot-btn').removeClass('selected');
            $(this).addClass('selected');
            $('#modal-time-slot').val($(this).data('time'));
        });

        bookingForm.on('submit', function(event) {
            event.preventDefault();
            if (!$('#modal-time-slot').val()) {
                alert('Please select a time slot.');
                return;
            }
            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    alert('Success! Your appointment request has been sent.');
                    window.location.href = '/gutu-hospital/patient/index.php';
                },
                error: function() {
                    alert('An error occurred.');
                }
            });
        });

        function resetModal() {
            bookingForm[0].reset();
            timeSlotsContainer.html('<p>Please select a date.</p>');
            $('#modal-time-slot').val('');
        }
    }
});
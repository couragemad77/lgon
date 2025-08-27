<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';


// Fetch all doctors' details for display
$sql = "SELECT u.id AS user_id, d.id AS doctor_table_id, u.fullName, d.profilePictureUrl, d.specialization, d.departments 
        FROM users u 
        JOIN doctors d ON u.id = d.user_id 
        WHERE u.role = 'doctor' ORDER BY u.fullName ASC";
$result = mysqli_query($link, $sql);
$doctors = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<style>
    /* Specific styles for the doctors grid */
    .doctors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    .doctor-card {
        background-color: var(--secondary-color);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .doctor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(159, 122, 234, 0.2);
    }
    .doctor-card img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent-color);
    }
    .doctor-card h3 {
        margin: 1rem 0 0.2rem;
    }
    .doctor-card .specialization {
        color: var(--hover-color);
        font-weight: bold;
    }
    #time-slots {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 1rem;
    }
    .time-slot-btn {
        background-color: var(--primary-color);
        border: 1px solid var(--accent-color);
        color: var(--accent-color);
    }
    .time-slot-btn:hover, .time-slot-btn.selected {
        background-color: var(--accent-color);
        color: white;
    }
    .time-slot-btn.disabled {
        background-color: #333;
        color: #777;
        border-color: #555;
        cursor: not-allowed;
    }
</style>

<h1>Book an Appointment</h1>
<p>Select a doctor to begin the booking process.</p>

<div class="doctors-grid">
    <?php if (empty($doctors)): ?>
        <p>No doctors are available at the moment.</p>
    <?php else: ?>
        <?php foreach ($doctors as $doctor): ?>
            <div class="doctor-card">
                <img src="/gutu-hospital<?php echo htmlspecialchars($doctor['profilePictureUrl'] ?? '/assets/images/profile_pictures/default-profile.png'); ?>" alt="Dr. <?php echo htmlspecialchars($doctor['fullName']); ?>">
                <h3>Dr. <?php echo htmlspecialchars($doctor['fullName']); ?></h3>
                <p class="specialization"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                <p><?php echo htmlspecialchars($doctor['departments']); ?></p>
                <button class="btn btn-primary book-now-btn" 
                        data-doctor-id="<?php echo $doctor['doctor_table_id']; ?>"
                        data-doctor-name="Dr. <?php echo htmlspecialchars($doctor['fullName']); ?>"
                        data-department="<?php echo htmlspecialchars($doctor['departments']); ?>">
                    Book Now
                </button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Booking Modal (Initially Hidden) -->
<div class="modal-overlay" id="booking-modal">
    <div class="modal-container" style="max-width: 500px;">
        <div class="modal-header">
            <h2 id="modal-doctor-name">Book with Dr. Name</h2>
            <p id="modal-department">Department</p>
        </div>
        
        <form id="booking-form" action="../backend/appointment_handler.php" method="POST">
            <input type="hidden" name="action" value="book_appointment">
            <input type="hidden" name="doctorId" id="modal-doctor-id">
            <input type="hidden" name="department" id="modal-form-department">
            <input type="hidden" name="time" id="modal-time-slot">

            <div class="form-group">
                <label for="appointmentDate">Select Date</label>
                <input type="date" name="date" id="appointmentDate" class="form-group" style="width: 100%; padding: 10px;" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Select an Available Time</label>
                <div id="time-slots">
                    <p>Please select a date to see available times.</p>
                </div>
            </div>

            <button type="submit" id="final-book-btn" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Confirm Booking</button>
        </form>
    </div>
</div>

<script src="/gutu-hospital/assets/js/libraries/jquery.min.js"></script>
<script src="/gutu-hospital/assets/js/patient_dashboard.js"></script>
<?php require_once '../templates/dashboard_footer.php'; ?>
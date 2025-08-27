<!-- gutu-hospital/signup.php -->
<?php include 'templates/header.php'; ?>

<style>
    /* Add some specific styles for the signup container */
    .signup-container {
        max-width: 800px;
        margin: 120px auto 50px; /* Add margin-top to clear the fixed navbar */
        padding: 2rem 3rem;
        background-color: var(--secondary-color);
        border-radius: 12px;
    }
    .signup-container h1 {
        text-align: center;
        color: var(--accent-color);
    }
    .grid-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .full-width {
        grid-column: 1 / -1;
    }
    @media (max-width: 768px) {
        .grid-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="signup-container">
    <h1>Create Patient Account</h1>
    <p style="text-align: center; margin-bottom: 2rem;">Please fill out this form to register.</p>

    <!-- The form will send data to backend/auth.php using the POST method -->
    <form action="backend/auth.php" method="POST">
        <!-- This hidden input tells our backend script which action to perform -->
        <input type="hidden" name="action" value="signup">

        <div class="grid-form">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" name="fullName" id="fullName" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password (min 8 chars)</label>
                <input type="password" name="password" id="password" minlength="8" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" name="confirmPassword" id="confirmPassword" required>
            </div>

            <div class="form-group">
                <label for="dateOfBirth">Date of Birth</label>
                <input type="date" name="dateOfBirth" id="dateOfBirth" required>
            </div>

            <div class="form-group">
                <label for="phoneNumber">Phone Number</label>
                <input type="tel" name="phoneNumber" id="phoneNumber" required>
            </div>

            <div class="form-group full-width">
                <label for="address">Address</label>
                <textarea name="address" id="address" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender" required>
                    <option value="">--Please choose an option--</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Prefer not to say">Prefer not to say</option>
                </select>
            </div>

            <div class="form-group">
                <label for="emergencyContactName">Emergency Contact Name</label>
                <input type="text" name="emergencyContactName" id="emergencyContactName" required>
            </div>

             <div class="form-group full-width">
                <label for="emergencyContactPhone">Emergency Contact Phone</label>
                <input type="tel" name="emergencyContactPhone" id="emergencyContactPhone" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Register</button>
        <p style="text-align: center; margin-top: 1rem;">Already have an account? <a href="index.php" >Login here</a>.</p>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
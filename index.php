<!-- gutu-hospital/index.php -->
<?php include 'templates/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Advanced Care, Community Focus</h1>
        <p>Welcome to Gutu Rural Hospital. Your health is our priority. Schedule and manage your appointments with ease.</p>
        <button onclick="document.getElementById('login-modal-btn').click();" class="btn btn-primary">Book an Appointment</button>
    </div>
</section>

<!-- Other sections for the landing page -->
<section id="about" style="padding: 100px 2rem; text-align: center;">
    <h2>About Us</h2>
    <p>Information about the hospital's history and mission.</p>
</section>

<section id="departments" style="padding: 100px 2rem; text-align: center; background-color: var(--secondary-color);">
    <h2>Our Departments</h2>
    <p>Details about the different medical departments available.</p>
</section>

<section id="contact" style="padding: 100px 2rem; text-align: center;">
    <h2>Contact Us</h2>
    <p>Contact information, address, and a map.</p>
</section>

<!-- Login Modal HTML -->
<div class="modal-overlay" id="login-modal">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Hospital Portal Login</h2>
            <p>Please select your role to continue</p>
        </div>

        <div class="role-selection">
            <button class="role-btn" data-role="patient">I am a Patient</button>
            <button class="role-btn" data-role="doctor">I am a Doctor</button>
            <button class="role-btn" data-role="receptionist">I am a Receptionist</button>
        </div>

        <!-- Login Form - Initially Hidden -->
        <form id="login-form" action="backend/auth.php" method="POST" style="display: none;">
            <input type="hidden" name="role" id="login-role">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            
            <div class="modal-footer">
                <a href="/gutu-hospital/signup.php" id="signup-link">Don't have an account? Sign Up</a>
            </div>
        </form>
    </div>
</div>


<?php include 'templates/footer.php'; ?>
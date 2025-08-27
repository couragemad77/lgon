<!-- gutu-hospital/templates/dashboard_footer.php -->
    </main> <!-- end of .main-content -->

    <!-- =============================================================== -->
    <!--                       MODALS FOR ALL PAGES                      -->
    <!-- =============================================================== -->

    <!-- Generic Loading Modal -->
    <div class="modal-overlay" id="loading-modal">
        <div class="modal-container" style="max-width: 250px; text-align: center;">
            <div class="loader"></div>
            <p style="margin-top: 1rem; font-weight: bold;">Processing...</p>
        </div>
    </div>

    <!-- Decline Confirmation Modal -->
    <div class="modal-overlay" id="decline-confirm-modal">
        <div class="modal-container" style="max-width: 450px; text-align: center;">
            <div class="modal-header">
                <h2>Are you sure?</h2>
                <p>Are you sure you want to decline this appointment? The patient will be notified.</p>
            </div>
            <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem;">
                <!-- The 'data-appid' will be set by JavaScript -->
                <button id="confirm-decline-btn" class="btn btn-danger" data-appid="">Yes, Decline</button>
                <button class="btn btn-secondary cancel-decline-btn">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- JQUERY SCRIPT -->
    <script src="/gutu-hospital/assets/js/libraries/jquery.min.js"></script>
    
</body>
</html>
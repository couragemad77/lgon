document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('qr-scanner-modal');
    const scanBtn = document.getElementById('scan-qr-btn');
    const resultsDiv = document.getElementById('qr-reader-results');

    // Function to show the modal
    function showModal() {
        modal.style.display = 'flex';
    }

    // Function to hide the modal
    function hideModal() {
        modal.style.display = 'none';
    }

    // Close modal if overlay is clicked
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            hideModal();
            // It's good practice to stop the scanner when the modal is closed
            html5QrcodeScanner.clear();
        }
    });

    let html5QrcodeScanner;

    scanBtn.addEventListener('click', () => {
        showModal();

        // Only initialize the scanner if it hasn't been already
        if (!html5QrcodeScanner || !html5QrcodeScanner.isScanning) {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", 
                { fps: 10, qrbox: { width: 250, height: 250 } },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
    });

    function onScanSuccess(decodedText, decodedResult) {
        // Handle the scanned code -- send it to the backend
        console.log(`Code matched = ${decodedText}`, decodedResult);

        // Stop scanning
        html5QrcodeScanner.clear();
        hideModal();
        
        // Use Fetch API to send the QR data to the backend
        fetch('/gutu-hospital/backend/appointment_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=check_in_patient&qrCodeData=${encodeURIComponent(decodedText)}`
        })
        .then(response => response.json())
        .then(data => {
            resultsDiv.style.display = 'block';
            resultsDiv.textContent = data.message;
            if(data.success) {
                resultsDiv.className = 'alert alert-success';
                // Reload the page to show the updated "checked-in" status in the table
                setTimeout(() => {
                    window.location.reload();
                }, 2000); // Wait 2 seconds before reloading
            } else {
                resultsDiv.className = 'alert alert-danger';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsDiv.style.display = 'block';
            resultsDiv.className = 'alert alert-danger';
            resultsDiv.textContent = 'An error occurred while checking in.';
        });
    }

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning.
        // console.warn(`Code scan error = ${error}`);
    }
});

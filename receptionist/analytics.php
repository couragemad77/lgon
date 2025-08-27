<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

// --- DATA FETCHING FOR CHARTS ---

// 1. Daily Patient Volume (Last 7 Days)
$daily_volume_sql = "SELECT DATE(appointmentDateTime) AS appt_date, COUNT(id) AS count 
                     FROM appointments 
                     WHERE appointmentDateTime >= CURDATE() - INTERVAL 7 DAY
                     GROUP BY appt_date 
                     ORDER BY appt_date ASC";
$daily_result = mysqli_query($link, $daily_volume_sql);
$daily_data = mysqli_fetch_all($daily_result, MYSQLI_ASSOC);

$daily_labels = [];
$daily_values = [];
// Initialize last 7 days with 0 count
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_labels[] = date('D, M j', strtotime($date));
    $daily_values[$date] = 0;
}
foreach ($daily_data as $row) {
    $daily_values[$row['appt_date']] = $row['count'];
}
$daily_values_json = json_encode(array_values($daily_values));
$daily_labels_json = json_encode($daily_labels);


// 2. Appointment Distribution by Department
$dept_dist_sql = "SELECT department, COUNT(id) AS count 
                  FROM appointments 
                  GROUP BY department 
                  ORDER BY count DESC";
$dept_result = mysqli_query($link, $dept_dist_sql);
$dept_data = mysqli_fetch_all($dept_result, MYSQLI_ASSOC);
$dept_labels = json_encode(array_column($dept_data, 'department'));
$dept_values = json_encode(array_column($dept_data, 'count'));

?>

<h1>Analytics & Reports</h1>
<p>Visualizing hospital appointment trends and data.</p>

<div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Daily Volume Chart -->
    <div class="card">
        <h3>Daily Patient Volume (Last 7 Days)</h3>
        <canvas id="dailyVolumeChart"></canvas>
    </div>

    <!-- Department Distribution Chart -->
    <div class="card">
        <h3>Appointments by Department</h3>
        <canvas id="departmentDistChart"></canvas>
    </div>
</div>

<!-- Include Chart.js library -->
<script src="/gutu-hospital/assets/js/libraries/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctxDaily = document.getElementById('dailyVolumeChart').getContext('2d');
    const ctxDept = document.getElementById('departmentDistChart').getContext('2d');

    // Chart.js Global Styling
    Chart.defaults.color = '#E6E6FA';
    Chart.defaults.borderColor = 'rgba(230, 230, 250, 0.2)';

    // 1. Daily Volume Bar Chart
    new Chart(ctxDaily, {
        type: 'bar',
        data: {
            labels: <?php echo $daily_labels_json; ?>,
            datasets: [{
                label: 'Total Appointments',
                data: <?php echo $daily_values_json; ?>,
                backgroundColor: 'rgba(159, 122, 234, 0.6)', // Accent color with transparency
                borderColor: '#9F7AEA',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // 2. Department Distribution Pie Chart
    new Chart(ctxDept, {
        type: 'pie',
        data: {
            labels: <?php echo $dept_labels; ?>,
            datasets: [{
                label: 'Appointments',
                data: <?php echo $dept_values; ?>,
                backgroundColor: [
                    'rgba(159, 122, 234, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                ],
                borderColor: '#1A1A2E',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
});
</script>

<?php require_once '../templates/dashboard_footer.php'; ?>

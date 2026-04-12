<?php
session_start();

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../config/database.php';

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$app_sql = "SELECT * FROM scholarship_applications WHERE student_id = ? ORDER BY application_date DESC LIMIT 1";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->bind_param("s", $student_id);
$app_stmt->execute();
$application = $app_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Scholarship System</title>
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="dashboard-header">
        <h2>Scholarship Portal</h2>
        <div class="user-nav">
            <span>Welcome, <strong><?php echo htmlspecialchars($student['full_name']); ?></strong></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container dashboard-container">
        <div class="welcome-card">
            <h1>Welcome, <?php echo htmlspecialchars($student['full_name']); ?>!</h1>
            <p>ID: <?php echo htmlspecialchars($student['student_id']); ?> | Course: <?php echo htmlspecialchars($student['course']); ?></p>
        </div>

        <div class="quick-actions">
            <div class="action-card">
                <h3>New Application</h3>
                <p>Submit a new scholarship application for this semester.</p>
                <a href="#" class="btn-action btn-apply-now">Apply Now →</a>
            </div>

            <div class="action-card">
                <h3>Application History</h3>
                <p>View the status of your submitted applications.</p>
                <a href="#" class="btn-action btn-green">Check Status →</a>
            </div>

            <div class="action-card">
                <h3>My Profile</h3>
                <p>Update your personal and contact information.</p>
                <a href="#" class="btn-action btn-purple">View Profile →</a>
            </div>
        </div>

        <?php if ($application): ?>
            <div class="status-summary-card">
                <h3>Latest Application Status</h3>
                <div class="status-info">
                    <p><strong>Scholarship:</strong> <?php echo htmlspecialchars($application['scholarship_type']); ?></p>
                    <p><strong>Status:</strong>
                        <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                            <?php echo $application['status']; ?>
                        </span>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="applyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Scholarship Application Form</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" id="modalFormContainer">
                <div class="loading-container">Loading form...</div>
            </div>
        </div>
    </div>

    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>My Profile</h3>
                <span class="close-profile-modal">&times;</span>
            </div>
            <div class="modal-body" id="profileModalContainer">
                <div class="loading-container">Loading profile...</div>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal">
        <div class="modal-content status-modal-wide">
            <div class="modal-header">
                <h3>Application History</h3>
                <span class="close-status-modal">&times;</span>
            </div>
            <div class="modal-body" id="statusModalContainer">
                <div class="loading-container">Loading applications...</div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> Scholarship Management System. All rights reserved.</p>
    </footer>

    <script src="../student/script.js"></script>
</body>

</html>
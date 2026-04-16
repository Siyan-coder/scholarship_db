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

// Check reapplication eligibility
$can_apply = true;
$apply_disabled_message = '';

if ($application) {
    if ($application['status'] === 'Pending' || $application['status'] === 'Approved') {
        $can_apply = false;
        $apply_disabled_message = "You already have an active application. Only one application is accepted per student.";
    } elseif ($application['status'] === 'Rejected') {
        // Check if same semester
        $last_app_date = $application['application_date'];
        $last_app_year = date('Y', strtotime($last_app_date));
        $last_app_month = date('m', strtotime($last_app_date));
        $current_year = date('Y');
        $current_month = date('m');
        
        $last_semester = '';
        if ($last_app_month >= 8 && $last_app_month <= 12) {
            $last_semester = 'Fall';
        } elseif ($last_app_month >= 1 && $last_app_month <= 5) {
            $last_semester = 'Spring';
        } else {
            $last_semester = 'Summer';
        }
        
        $current_semester = '';
        if ($current_month >= 8 && $current_month <= 12) {
            $current_semester = 'Fall';
        } elseif ($current_month >= 1 && $current_month <= 5) {
            $current_semester = 'Spring';
        } else {
            $current_semester = 'Summer';
        }
        
        if ($last_app_year == $current_year && $last_semester == $current_semester) {
            $can_apply = false;
            $apply_disabled_message = "Your previous application was rejected. You can only reapply in the next semester.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, initial-scale=1.0, viewport-fit=cover">
    <title>Student Dashboard - Scholarship System</title>
    <link rel="stylesheet" href="../student/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="page-title">
        <h1>Scholarship Portal</h1>
    </div>

    <div class="dashboard-page">
    <div class="dashboard-wrapper">
        <div class="container dashboard-container">
            <div class="welcome-card">
                <div class="welcome-content">
                    <h1>Welcome, <?php echo htmlspecialchars($student['full_name']); ?>!</h1>
                    <p>ID: <?php echo htmlspecialchars($student['student_id']); ?> | Course: <?php echo htmlspecialchars($student['course']); ?></p>
                </div>
                <div class="welcome-actions">
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>

            <div class="quick-actions">
                <div class="action-card <?php echo !$can_apply ? 'disabled-card' : ''; ?>">
                    <h3>New Application</h3>
                    <p>Submit a new scholarship application for this semester.</p>
                    <?php if ($can_apply): ?>
                        <a href="#" class="btn-action btn-apply-now">Apply Now →</a>
                    <?php else: ?>
                        <div class="disabled-message"><?php echo htmlspecialchars($apply_disabled_message); ?></div>
                    <?php endif; ?>
                </div>

            <div class="action-card">
                <h3>Application History</h3>
                <p>View the status of your submitted applications.</p>
                <a href="#" class="btn-action btn-green">Check Status →</a>
            </div>

            <div class="action-card">
                <h3>My Profile</h3>
                <p>View your personal information.</p>
                <a href="#" class="btn-action btn-purple">View Profile →</a>
            </div>
        </div>

        <?php if ($application): ?>
            <div class="status-summary-card">
                <h3>Latest Application Status</h3>
                <div class="status-info">
                    <p><strong>Scholarship:</strong> <?php echo htmlspecialchars($application['scholarship_type']); ?></p>
                    <p><strong>GPA:</strong> <?php echo number_format($application['gpa'], 2); ?></p>
                    <p><strong>Status:</strong>
                        <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                            <?php echo $application['status']; ?>
                        </span>
                    </p>
                    <p><strong>Application Date:</strong> <?php echo date('F d, Y', strtotime($application['application_date'])); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="info-card">
                <h3>No Application Yet</h3>
                <p>You haven't submitted any scholarship application. Click "Apply Now" to get started.</p>
            </div>
        <?php endif; ?>
    </div>
    </div>
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

    <script src="script.js"></script>
</body>
</html>
<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM scholarship_applications WHERE student_id = ? ORDER BY application_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="status-table-container">
    <?php if ($result->num_rows > 0): ?>
        <table class="status-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Scholarship Type</th>
                    <th>GPA</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="status-row status-<?php echo strtolower($row['status']); ?>-row">
                        <td><?php echo date('M d, Y', strtotime($row['application_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['scholarship_type']); ?></td>
                        <td><?php echo number_format($row['gpa'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="status-footer-note">
            <?php
            $latest = $result->fetch_assoc();
            $result->data_seek(0);
            if ($latest && $latest['status'] === 'Rejected'):
                $app_date = $latest['application_date'];
                $app_month = date('m', strtotime($app_date));
                $next_semester = '';
                if ($app_month >= 8 && $app_month <= 12) {
                    $next_semester = 'Spring ' . (date('Y', strtotime($app_date)) + 1);
                } elseif ($app_month >= 1 && $app_month <= 5) {
                    $next_semester = 'Summer ' . date('Y', strtotime($app_date));
                } else {
                    $next_semester = 'Fall ' . date('Y', strtotime($app_date));
                }
            ?>
                <div class="rejection-note">
                    <strong>📌 Important:</strong> Your application was rejected. You may reapply in the next semester (<?php echo $next_semester; ?>).
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <p>You haven't submitted any applications yet.</p>
            <p class="empty-hint">Click "Apply Now" on your dashboard to get started.</p>
        </div>
    <?php endif; ?>
</div>


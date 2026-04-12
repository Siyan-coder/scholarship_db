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
                    <tr>
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
    <?php else: ?>
        <div class="loading-container">
            <p>You haven't submitted any applications yet.</p>
        </div>
    <?php endif; ?>
</div>
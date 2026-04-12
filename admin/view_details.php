<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
include '../config/database.php';

$id = $_GET['id'];
$sql = "SELECT * FROM scholarship_applications WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Application Details</title>
    <link rel="stylesheet" href="../style/style.css">  
    <style>
        .admin-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h2>Application Details</h2>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
    <div class="container">
        <div class="card">
            <?php if($row): ?>
            <table>
                <tr><th>Full Name</th><td><?php echo htmlspecialchars($row['full_name']); ?></td></tr>
                <tr><th>Student ID</th><td><?php echo htmlspecialchars($row['student_id']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($row['email']); ?></td></tr>
                <tr><th>Course</th><td><?php echo htmlspecialchars($row['course']); ?></td></tr>
                <tr><th>Year Level</th><td><?php echo htmlspecialchars($row['year_level']); ?></td></tr>
                <tr><th>GPA</th><td><?php echo $row['gpa']; ?></td></tr>
                <tr><th>Scholarship Type</th><td><?php echo htmlspecialchars($row['scholarship_type']); ?></td></tr>
                <tr><th>Address</th><td><?php echo htmlspecialchars($row['address']); ?></td></tr>
                <tr><th>Contact Number</th><td><?php echo htmlspecialchars($row['contact_number']); ?></td></tr>
                <tr><th>Eligibility</th><td><?php echo $row['eligibility']; ?></td></tr>
                <tr><th>Status</th><td><?php echo $row['status']; ?></td></tr>
                <tr><th>Application Date</th><td><?php echo $row['application_date']; ?></td></tr>
            </table>
            <?php else: echo "Application not found."; endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
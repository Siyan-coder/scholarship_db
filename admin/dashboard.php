<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../config/database.php';

if (isset($_POST['update_status'])) {
    $app_id = $_POST['app_id'];
    $new_status = $_POST['new_status'];
    
    $sql = "UPDATE scholarship_applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $app_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: dashboard.php');
    exit();
}

if (isset($_GET['delete'])) {
    $app_id = $_GET['delete'];
    $sql = "DELETE FROM scholarship_applications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php');
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$course_filter = isset($_GET['course_filter']) ? $_GET['course_filter'] : '';

$sql = "SELECT * FROM scholarship_applications WHERE eligibility = 'eligible' AND 1=1";
if (!empty($search)) {
    $sql .= " AND full_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if (!empty($status_filter)) {
    $sql .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}
if (!empty($course_filter)) {
    $sql .= " AND course = '" . $conn->real_escape_string($course_filter) . "'";
}
$sql .= " ORDER BY application_date DESC";

$result = $conn->query($sql);

$total_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications WHERE eligibility = 'eligible'")->fetch_assoc()['total'];
$pending_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications WHERE eligibility = 'eligible' AND status='Pending'")->fetch_assoc()['total'];
$approved_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications WHERE eligibility = 'eligible' AND status='Approved'")->fetch_assoc()['total'];
$rejected_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications WHERE eligibility = 'eligible' AND status='Rejected'")->fetch_assoc()['total'];

$courses_result = $conn->query("SELECT DISTINCT course FROM scholarship_applications WHERE eligibility = 'eligible' ORDER BY course");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Scholarship Management</title>
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
        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-bar input, .filter-bar select {
            width: auto;
            padding: 8px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #3498db;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
        }
        .btn-approve { background-color: #2ecc71; }
        .btn-reject { background-color: #e74c3c; }
        .btn-delete { background-color: #95a5a6; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h2>Admin Dashboard - Scholarship Management</h2>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">Total Applications: <?php echo $total_apps; ?></div>
            <div class="stat-card" style="background-color: #f39c12;">Pending: <?php echo $pending_apps; ?></div>
            <div class="stat-card" style="background-color: #2ecc71;">Approved: <?php echo $approved_apps; ?></div>
            <div class="stat-card" style="background-color: #e74c3c;">Rejected: <?php echo $rejected_apps; ?></div>
        </div>
        
        <div class="card">
            <h3>Search and Filter Applications</h3>
            <form method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Search by student name" value="<?php echo htmlspecialchars($search); ?>">
                <select name="status_filter">
                    <option value="">All Status</option>
                    <option value="Pending" <?php if($status_filter=='Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Approved" <?php if($status_filter=='Approved') echo 'selected'; ?>>Approved</option>
                    <option value="Rejected" <?php if($status_filter=='Rejected') echo 'selected'; ?>>Rejected</option>
                </select>
                <select name="course_filter">
                    <option value="">All Courses</option>
                    <?php while($row = $courses_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['course']); ?>" <?php if($course_filter==$row['course']) echo 'selected'; ?>><?php echo htmlspecialchars($row['course']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Filter</button>
                <a href="dashboard.php" class="btn" style="background-color: #95a5a6;">Reset</a>
            </form>
        </div>
        
        <div class="card">
            <h3>All Scholarship Applications (Eligible Only)</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Student ID</th><th>Course</th><th>GPA</th><th>Eligibility</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td><?php echo $row['gpa']; ?></td>
                        <td class="<?php echo ($row['eligibility']=='eligible')?'qualified':'not-qualified'; ?>"><?php echo $row['eligibility']; ?></td>
                        <td><span class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                        <td class="action-buttons">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="new_status" value="Approved">
                                <button type="submit" name="update_status" class="btn-small btn-approve">Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="new_status" value="Rejected">
                                <button type="submit" name="update_status" class="btn-small btn-reject">Reject</button>
                            </form>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Delete this application?');">Delete</a>
                            <a href="view_details.php?id=<?php echo $row['id']; ?>" class="btn-small" style="background-color: #3498db;">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr><td colspan="8">No eligible applications found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
include '../config/database.php';

if (isset($_POST['update_status']) && isset($_POST['ajax'])) {
    $app_id = $_POST['app_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE scholarship_applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $app_id);
    $stmt->execute();
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    $conn->close();
    exit();
}

if (isset($_GET['delete']) && isset($_GET['ajax'])) {
    $app_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM scholarship_applications WHERE id = ?");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    $conn->close();
    exit();
}

if (isset($_GET['student_info']) && isset($_GET['ajax'])) {
    $student_id = $_GET['student_info'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$student) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
        $conn->close();
        exit();
    }

    $check = $conn->prepare("SELECT COUNT(*) AS total FROM scholarship_applications WHERE student_id = ?");
    $check->bind_param("s", $student_id);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existing['total'] > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'This student already has application history.']);
        $conn->close();
        exit();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'student' => [
        'full_name' => $student['full_name'],
        'student_id' => $student['student_id'],
        'email' => $student['email'],
        'course' => $student['course'],
        'year_level' => $student['year_level'],
        'address' => $student['address'],
        'contact_number' => $student['contact_number']
    ]]);
    $conn->close();
    exit();
}

if (isset($_POST['add_application']) && isset($_POST['ajax'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    $gpa = trim($_POST['gpa'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    $response = ['success' => false, 'errors' => []];

    if ($student_id === '') {
        $response['errors'][] = 'Student ID is required.';
    }

    $allowed_gpa = ['1.00','1.25','1.50','1.75','2.00','2.25','2.50','2.75','3.00','5.00'];
    if (!in_array($gpa, $allowed_gpa, true)) {
        $response['errors'][] = 'Please select a valid GWA.';
    }

    if (empty($response['errors'])) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$student) {
            $response['errors'][] = 'Student not found.';
        }
    }

    if (empty($response['errors'])) {
        $check = $conn->prepare("SELECT COUNT(*) AS total FROM scholarship_applications WHERE student_id = ?");
        $check->bind_param("s", $student_id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing['total'] > 0) {
            $response['errors'][] = 'This student already has an application history.';
        }
    }

    if (empty($response['errors'])) {
        $scholarship_type = 'Academic Excellence Scholarship';
        $eligibility = (floatval($gpa) <= 2.50) ? 'eligible' : 'not eligible';

        $insert = $conn->prepare(
            "INSERT INTO scholarship_applications (student_id, full_name, email, course, year_level, gpa, scholarship_type, address, contact_number, status, eligibility)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)"
        );
        $insert->bind_param(
            "sssssdssss",
            $student['student_id'],
            $student['full_name'],
            $student['email'],
            $student['course'],
            $student['year_level'],
            $gpa,
            $scholarship_type,
            $address,
            $contact_number,
            $eligibility
        );

        if ($insert->execute()) {
            $response['success'] = true;
            $response['message'] = 'Application added successfully.';
        } else {
            $response['errors'][] = 'Failed to save application. Please try again.';
        }
        $insert->close();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
    exit();
}

if (isset($_GET['eligible_students']) && $_GET['eligible_students']) {
    $response = ['success' => true, 'students' => []];
    $eligibleQuery = "SELECT student_id, full_name, course FROM students WHERE student_id NOT IN (SELECT student_id FROM scholarship_applications) ORDER BY full_name";
    $eligibleResult = $conn->query($eligibleQuery);
    if ($eligibleResult) {
        while ($studentRow = $eligibleResult->fetch_assoc()) {
            $response['students'][] = $studentRow;
        }
        $eligibleResult->close();
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
    exit();
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$course_filter = $_GET['course_filter'] ?? '';

$sql = "SELECT * FROM scholarship_applications WHERE 1=1";
if ($search)
    $sql .= " AND full_name LIKE '%" . $conn->real_escape_string($search) . "%'";
if ($status_filter)
    $sql .= " AND status='" . $conn->real_escape_string($status_filter) . "'";
if ($course_filter)
    $sql .= " AND course='" . $conn->real_escape_string($course_filter) . "'";
$sql .= " ORDER BY CASE WHEN status='Pending' THEN 1 WHEN status='Approved' THEN 2 WHEN status='Rejected' THEN 3 END, full_name ASC";

$result = $conn->query($sql);

$courses_result = $conn->query("SELECT DISTINCT course FROM scholarship_applications ORDER BY course");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Manage Applications</title>
    <link rel="stylesheet" href="../admin/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <span class="logo-icon">🎓</span>
                    <span class="logo-text">SCHOLARSHIP</span>
                </div>
            </div>
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span class="menu-text">Dashboard</span>
                </a>
                <a href="manageApplications.php" class="menu-item active">
                    <span class="menu-icon">📋</span>
                    <span class="menu-text">Applications</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="#" id="btn-logout" class="menu-item">
                    <span class="menu-icon">🚪</span>
                    <span class="menu-text">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <h1>Manage Applications</h1>
                <div class="header-right">
                    <div class="user-profile">
                        <span class="username">Admin User</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div id="ajax-page-content" class="content-area">
                <div class="card">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:15px;">
                        <h3 style="margin:0;">Search and Filter Applications</h3>
                    </div>
                    <form method="GET" class="filter-bar ajax-filter">
                        <div class="filter-row">
                            <input type="text" name="search" placeholder="Search by Student Name"
                                value="<?= htmlspecialchars($search) ?>">
                        </div>

                        <div class="filter-row">
                            <select name="status_filter">
                                <option value="">All Status</option>
                                <option value="Pending" <?php if ($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Approved" <?php if ($status_filter == 'Approved') echo 'selected'; ?>>Approved</option>
                                <option value="Rejected" <?php if ($status_filter == 'Rejected') echo 'selected'; ?>>Rejected</option>
                            </select>
                        </div>

                        <div class="filter-row">
                            <select name="course_filter">
                                <option value="">All Courses</option>
                                <?php while ($row = $courses_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row['course']); ?>" <?php if ($course_filter == $row['course']) echo 'selected'; ?>>
                                        <?= htmlspecialchars($row['course']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-row">
                            <button type="button" class="reset-applications">Reset</button>
                        </div>
                    </form>
                </div>

                <div style="margin:15px 0; display:flex; justify-content:flex-end;">
                    <button type="button" class="btn btn-add-application">Add Application</button>
                </div>

                <div class="card">
                    <h3>All Scholarship Applications</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>GPA</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td><?= htmlspecialchars($row['course']) ?></td>
                                        <td><?= $row['gpa'] ?></td>
                                        <td><span class="status-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                                        <td class="action-buttons">
                                            <button type="button" class="btn-small btn-approve btn-modal-status" data-id="<?= $row['id'] ?>" data-status="Approved">Approve</button>
                                            <button type="button" class="btn-small btn-reject btn-modal-status" data-id="<?= $row['id'] ?>" data-status="Rejected">Reject</button>
                                            <button type="button" class="btn-small btn-delete" data-id="<?= $row['id'] ?>">Delete</button>
                                            <a href="view_details.php?id=<?= $row['id'] ?>" class="btn-small btn-view">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No applications found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="bgModal" class="bgModal">
        <div id="modal" class="modal">
            <div id="modal-content">
                <div id="modal-body"></div>
            </div>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>

<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
include '../config/database.php';


if (isset($_GET['check_new'])) {
    $result = $conn->query("SELECT MAX(id) AS latest_id FROM scholarship_applications");
    $row = $result->fetch_assoc();

    echo json_encode([
        'latest_id' => (int) $row['latest_id']
    ]);
    exit();
}


if (isset($_GET['chart_course'])) {
    $course = $_GET['course'] ?? '';

    $sql = "SELECT course, COUNT(*) as total FROM scholarship_applications";
    if ($course) {
        $sql .= " WHERE course = '" . $conn->real_escape_string($course) . "'";
    }
    $sql .= " GROUP BY course";

    $res = $conn->query($sql);
    $data = [];

    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit();
}

// Chart data: Status distribution
if (isset($_GET['chart_status'])) {
    $res = $conn->query("
        SELECT status, COUNT(*) as total
        FROM scholarship_applications
        GROUP BY status
    ");

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit();
}





$total_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications")->fetch_assoc()['total'];
$pending_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications WHERE status='Pending'")->fetch_assoc()['total'];
$approved_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications WHERE status='Approved'")->fetch_assoc()['total'];
$rejected_apps = $conn->query("SELECT COUNT(*) as total FROM scholarship_applications WHERE status='Rejected'")->fetch_assoc()['total'];

$courses_result = $conn->query("SELECT DISTINCT course FROM scholarship_applications ORDER BY course");

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../admin/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <span class="logo-text">SCHOLARSHIP
                        MANAGEMENT
                    </span>
                </div>
            </div>
            <nav class="sidebar-menu">
                <a href="#" class="menu-item active" data-section="dashboard">
                    <span class="menu-text">Dashboard</span>
                </a>
                <a href="#" class="menu-item" data-section="applications">
                    <span class="menu-text">Applications</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="#" id="btn-logout" class="menu-item">
                    <span class="menu-text">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <h1>Dashboard</h1>
                <div class="header-right">
                    <div class="user-profile">
                        <span class="username">Admin User</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area" id="page-content">
                <!-- Initial dashboard content loaded here -->
                <!-- Stat Cards -->
                <div class="stats-grid">
                    <div class="stat-card stat-card-blue">
                        <div class="stat-label">Total Applications</div>
                        <div class="stat-value"><?= $total_apps ?></div>
                    </div>
                    <div class="stat-card stat-card-orange">
                        <div class="stat-label">Total Pending Applications</div>
                        <div class="stat-value"><?= $pending_apps ?></div>
                    </div>
                    <div class="stat-card stat-card-green">
                        <div class="stat-label">Total Approved Applications</div>
                        <div class="stat-value"><?= $approved_apps ?></div>
                    </div>
                    <div class="stat-card stat-card-red">
                        <div class="stat-label">Total Rejected Applications</div>
                        <div class="stat-value"><?= $rejected_apps ?></div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="charts-grid">
                    <div class="card chart-card">
                        <h3>Total Applications per Course</h3>
                        <select id="courseFilter">
                            <option value="">All Courses</option>
                            <?php $courses_result->data_seek(0); while ($c = $courses_result->fetch_assoc()): ?>
                                <option value="<?= $c['course'] ?>">
                                    <?= $c['course'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <canvas id="courseChart"></canvas>
                    </div>

                    <div class="card chart-card">
                        <h3>Application Status Distribution</h3>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="bgModal" class="bgModal">
        <div id="modal" class="modal">
            <div id="modal-content">
                <div id="modal-body">
                </div>
            </div>
        </div>
    </div>

</body>

</html>
<?php $conn->close(); ?>
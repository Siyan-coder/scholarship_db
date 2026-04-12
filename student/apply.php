<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    die("Session expired. Please login again.");
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$prefill = [
    'full_name' => $student['full_name'] ?? '',
    'student_id' => $student['student_id'] ?? '',
    'email' => $student['email'] ?? '',
    'course' => $student['course'] ?? '',
    'year_level' => $student['year_level'] ?? '',
    'address' => $student['address'] ?? '',
    'contact_number' => $student['contact_number'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_application'])) {
    $gpa = $_POST['gpa'];
    $gpa_float = floatval($gpa);

    if ($gpa_float >= 1.00 && $gpa_float <= 2.50) {
        $eligibility = "eligible";

        $insert_sql = "INSERT INTO scholarship_applications
    (student_id, full_name, email, course, year_level, gpa, scholarship_type, address, contact_number, status, eligibility)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)";

        $insert_stmt = $conn->prepare($insert_sql);

        $insert_stmt->bind_param(
            "sssssdssss",
            $prefill['student_id'],   // secure
            $prefill['full_name'],
            $prefill['email'],
            $prefill['course'],                  // ✅ DB course only
            $_POST['year_level'],
            $gpa,
            $_POST['scholarship_type'],
            $_POST['address'],
            $_POST['contact_number'],
            $eligibility
        );


        if ($insert_stmt->execute()) {
            ?>
            <div id="successModalContent" class="success-container">
                <div class="check-icon green-theme">✔</div>
                <h2>Submitted Successfully!</h2>
                <p>Your application has been sent and is now pending for review.</p>
                <button type="button" onclick="location.reload()" class="btn-action">Back to Dashboard</button>
            </div>
            <?php
            exit();
        }
    }
    exit();
}
?>

<div id="applyFormContent">
    <div class="gpa-info">
        <strong>Eligibility:</strong> GPA 1.00 - 2.50 = Qualified | 2.75 - 5.00 = Not Qualified
    </div>

    <form id="scholarshipForm" method="POST">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($prefill['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Student ID *</label>
            <input type="text" name="student_id" class="readonly-input"
                value="<?php echo htmlspecialchars($prefill['student_id']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($prefill['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Course *</label>

            <input type="text" name="course" class="readonly-input" value="<?= htmlspecialchars($prefill['course']) ?>" readonly
                class="form-control">
        </div>

        <div class="form-group">
            <label>Year Level *</label>
            <select name="year_level" required>
                <option value="">-- Select Year --</option>
                <option value="1st Year" <?php echo ($prefill['year_level'] == '1st Year') ? 'selected' : ''; ?>>1st Year
                </option>
                <option value="2nd Year" <?php echo ($prefill['year_level'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year
                </option>
                <option value="3rd Year" <?php echo ($prefill['year_level'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year
                </option>
                <option value="4th Year" <?php echo ($prefill['year_level'] == '4th Year') ? 'selected' : ''; ?>>4th Year
                </option>
            </select>
        </div>

        <div class="form-group">
            <label>General Point Average (GPA) *</label>
            <input type="number" name="gpa" id="gpaInput" step="0.01" min="1.00" max="5.00" placeholder="Example: 1.25"
                required>
        </div>

        <div id="eligibilityWarning" class="eligibility-warning">
            <strong>⚠️ Not Eligible!</strong> GPA must be between 1.00 and 2.50 to qualify.
        </div>

        <div class="form-group">
            <label>Scholarship Type *</label>
            <select name="scholarship_type" required>
                <option value="">-- Select Scholarship --</option>
                <option value="Academic Excellence Scholarship">Academic Excellence Scholarship</option>
                <option value="Athletic Scholarship">Athletic Scholarship</option>
                <option value="Need-Based Scholarship">Need-Based Scholarship</option>
                <option value="Leadership Scholarship">Leadership Scholarship</option>
            </select>
        </div>

        <div class="form-group">
            <label>Home Address</label>
            <textarea name="address" rows="2"><?php echo htmlspecialchars($prefill['address']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number"
                value="<?php echo htmlspecialchars($prefill['contact_number']); ?>">
        </div>

        <button type="submit" name="submit_application" id="submitBtn" class="btn-action full-width">Submit
            Application</button>
    </form>
</div>
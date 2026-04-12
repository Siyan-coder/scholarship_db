<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact_number'];
    $address = $_POST['address'];

    $update_sql = "UPDATE students SET full_name = ?, email = ?, contact_number = ?, address = ? WHERE student_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssss", $full_name, $email, $contact, $address, $student_id);

    if ($stmt->execute()) {
?>
        <div id="profileSuccessContent" class="success-container">
            <div class="check-icon purple-theme">✔</div>
            <h2>Profile Updated!</h2>
            <p>Your personal information has been successfully updated.</p>
            <button type="button" onclick="location.reload()" class="btn-action btn-purple">Close</button>
        </div>
<?php
        exit();
    }
    exit();
}

$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div id="profileFormContainer">
    <form id="profileForm">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Student ID</label>
            <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($user['student_id']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Course</label>
            <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($user['course']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>">
        </div>

        <div class="form-group">
            <label>Home Address</label>
            <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
        </div>

        <button type="submit" id="saveProfileBtn" class="btn-action btn-purple full-width">Save Changes</button>
    </form>
</div>
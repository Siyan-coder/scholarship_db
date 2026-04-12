<?php
session_start();
include '../config/database.php';

if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $student_id = trim($_POST['student_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $course = trim($_POST['course']);
    $year_level = trim($_POST['year_level']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    if (empty($student_id)) $errors[] = "Student ID is required.";
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($password)) $errors[] = "Password is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $check_sql = "SELECT id FROM students WHERE student_id = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $student_id, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "Student ID or Email already exists.";
        }
        $check_stmt->close();
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO students (student_id, full_name, email, password, course, year_level, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $student_id, $full_name, $email, $hashed_password, $course, $year_level, $contact_number, $address);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
            $student_id = $full_name = $email = $course = $year_level = $contact_number = $address = '';
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Scholarship System</title>
    <link rel="stylesheet" href="../style/style.css">

</head>

<body>
    <div class="register-card">
        <h2>Student Registration</h2>

        <?php if ($success): ?>
            <div class="msg success-msg"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="msg error-msg">
                <?php foreach ($errors as $error) echo "<div>$error</div>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" name="student_id" value="<?php echo htmlspecialchars($student_id ?? ''); ?>" placeholder="e.g. 2023-0001" required>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" placeholder="John Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="email@example.com" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" placeholder="Repeat password" required>
                </div>
            </div>

            <div class="form-group">
                <label>Course *</label>
                <select name="course" required>
                    <option value="">Select your course</option>
                    <option value="BS in Civil Engineering (BSCE)">BS in Civil Engineering (BSCE)</option>
                    <option value="BS in Mechanical Engineering (BSME)">BS in Mechanical Engineering (BSME)</option>
                    <option value="BS in Electrical Engineering (BSEE)">BS in Electrical Engineering (BSEE)</option>
                    <option value="BS in Electronics Engineering (BSECE)">BS in Electronics Engineering (BSECE)</option>
                    <option value="BS in Computer Engineering (BSCpE)">BS in Computer Engineering (BSCpE)</option>
                    <option value="BS in Industrial Engineering (BSIE)">BS in Industrial Engineering (BSIE)</option>
                    <option value="BS in Mechatronics Engineering (BSMEE)">BS in Mechatronics Engineering (BSMEE)</option>
                    <option value="BS in Manufacturing Engineering (BSMfE)">BS in Manufacturing Engineering (BSMfE)</option>
                    <option value="BS in Information Technology (BSIT)">BS in Information Technology (BSIT)</option>
                    <option value="BS in Library Information Science">BS in Library Information Science</option>
                    <option value="BS in Biology">BS in Biology</option>
                    <option value="BS in Mathematics">BS in Mathematics</option>
                    <option value="BS in Environmental Science">BS in Environmental Science</option>
                    <option value="BS in Food Technology">BS in Food Technology</option>
                    <option value="BS in Medical Technology">BS in Medical Technology</option>
                    <option value="BA in Broadcasting">BA in Broadcasting</option>
                    <option value="BA in Journalism">BA in Journalism</option>
                    <option value="Bachelor of Performing Arts (Theater Track)">Bachelor of Performing Arts (Theater Track)</option>
                    <option value="BA in Malikhaing Pagsulat (Creative Writing)">BA in Malikhaing Pagsulat (Creative Writing)</option>
                    <option value="BS in Accountancy">BS in Accountancy</option>
                    <option value="BS in Business Administration (Management, Entrepreneurship)">BS in Business Administration (Management, Entrepreneurship)</option>
                    <option value="BS in Accounting and Information System">BS in Accounting and Information System</option>
                    <option value="Bachelor in Industrial Technology (BIT - Ladderized)">Bachelor in Industrial Technology (BIT - Ladderized)</option>
                    <option value="BS in Psychology">BS in Psychology</option>
                    <option value="BS in Social Work">BS in Social Work</option>
                    <option value="Bachelor in Public Administration">Bachelor in Public Administration</option>
                    <option value="BS in Tourism Management">BS in Tourism Management</option>
                    <option value="BS in Hospitality Management">BS in Hospitality Management</option>
                    <option value="BS in Home Economics">BS in Home Economics</option>
                    <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                    <option value="Bachelor of Secondary Education (Major in Bio Sci)">Bachelor of Secondary Education (Major in Bio Sci)</option>
                    <option value="Bachelor of Secondary Education (Major in English)">Bachelor of Secondary Education (Major in English)</option>
                    <option value="Bachelor of Secondary Education (Major in Filipino)">Bachelor of Secondary Education (Major in Filipino)</option>
                    <option value="Bachelor of Secondary Education (Major in Math)">Bachelor of Secondary Education (Major in Math)</option>
                    <option value="BS in Criminology">BS in Criminology</option>
                    <option value="BA in Legal Management">BA in Legal Management</option>
                    <option value="BS in Nursing">BS in Nursing</option>
                    <option value="Bachelor of Fine Arts">Bachelor of Fine Arts</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Year Level *</label>
                    <select name="year_level" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="<?php echo htmlspecialchars($contact_number ?? ''); ?>" placeholder="09xxxxxxxxx">
                </div>
            </div>

            <div class="form-group">
                <label>Home Address</label>
                <textarea name="address" rows="2" placeholder="Street, City, Province"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
            </div>

            <button type="submit" name="register" class="btn-action full-width" style="border:none; cursor:pointer; margin-top: 10px;">Create Account</button>
        </form>

        <div class="login-footer">
            <p>Already have an account? <a href="login.php" style="color: #2ecc71; font-weight: bold; text-decoration: none;">Login here</a></p>
        </div>
    </div>
</body>

</html>
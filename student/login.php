<?php
session_start();
include '../config/database.php';

if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$reg_success = '';
$reg_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $student_id = trim($_POST['student_id']);
    $password = $_POST['password'];

    if (!empty($student_id) && !empty($password)) {
        $sql = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            if (password_verify($password, $student['password'])) {
                $_SESSION['student_logged_in'] = true;
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['student_name'] = $student['full_name'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Student ID not found.";
        }
    }
}

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

    if (empty($student_id)) $reg_errors[] = "Student ID is required.";
    if (empty($full_name)) $reg_errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $reg_errors[] = "Invalid email format.";
    if (strlen($password) < 6) $reg_errors[] = "Password must be 6+ characters.";
    if ($password !== $confirm_password) $reg_errors[] = "Passwords do not match.";

    if (empty($reg_errors)) {
        $check_sql = "SELECT id FROM students WHERE student_id = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $student_id, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $reg_errors[] = "Student ID or Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO students (student_id, full_name, email, password, course, year_level, contact_number, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $student_id, $full_name, $email, $hashed_password, $course, $year_level, $contact_number, $address);
            if ($stmt->execute()) {
                $reg_success = "Registration successful! You can now login.";
            } else {
                $reg_errors[] = "Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login - Scholarship System</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../student/style.css">
    <style>
        .auth-wrapper {
            display: flex;
            width: 100%;
            max-width: 1100px;
            height: auto;
            min-height: 550px;
            background: #f7f8fa;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(0,0,0,0.25);
            margin: 20px;
        }
        
        @media (max-width: 900px) {
            .auth-wrapper {
                flex-direction: column;
                margin: 10px;
                border-radius: 20px;
            }
            
            .auth-side {
                display: none;
            }
            
            .auth-form {
                padding: 30px !important;
            }
        }
    </style>
</head>

<body class="auth-page">
  <div class="auth-wrapper">

    <div class="auth-form">
      <h2>Hello Again!</h2>
      <p class="auth-subtitle">Sign in to your student account</p>

      <?php if ($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if ($reg_success): ?>
        <div class="success-msg"><?php echo $reg_success; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <input type="text" name="student_id" placeholder="Student ID" required>
        </div>

        <div class="form-group">
          <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" name="login" class="btn-register">
          Sign In
        </button>
      </form>

      <div class="login-footer">
        <p>
          Don't have an account? <a href="javascript:void(0)" id="openRegModal" style="color: #2ecc71; font-weight: bold; text-decoration: none;">Register here</a>
        </p>
      </div>
    </div>

    <div class="auth-side">
      <div class="auth-side-text">
        <h3>Scholarship System</h3>
        <p>Everything you need, in one place</p>
      </div>
    </div>

  </div>

<div id="registerModal" class="modal" <?php echo (!empty($reg_errors)) ? 'style="display:block;"' : ''; ?>>
  <div class="auth-modal-content">
    <div class="auth-modal-header">
      <h3>Create Student Account</h3>
      <span class="close-modal" id="closeRegModal">&times;</span>
    </div>

    <div class="modal-body">
        <?php if (!empty($reg_errors)): ?>
            <div class="error-msg">
                <?php foreach ($reg_errors as $err) echo "<div>⚠️ $err</div>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="student_id" placeholder="Student ID *" required>
                </div>
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Full Name *" required>
                </div>
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address *" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password *" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password *" required>
                </div>
            </div>

            <div class="form-group">
                <select name="course" required>
                    <option value="">Select your course *</option>
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
                    <select name="year_level" required>
                        <option value="">Year Level *</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="contact_number" placeholder="Contact Number">
                </div>
            </div>

            <div class="form-group">
                <textarea name="address" rows="2" placeholder="Home Address"></textarea>
            </div>

            <button type="submit" name="register" class="btn-register">
                Create Account
            </button>
        </form>
    </div>
  </div>
</div>

<script src="script.js"></script>
</body>
</html>
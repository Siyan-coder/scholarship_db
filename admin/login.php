<?php
session_start();

$admin_username = 'admin';
$admin_password = $_SESSION['admin_password'] ?? 'admin123';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}

/* FORGOT PASSWORD (simple reset) */
if (isset($_POST['reset_password'])) {
    $admin_password = 'admin123'; // you can change this manually later
    $success = "Password reset successful. Default password restored.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Login - Scholarship System</title>
    <link rel="stylesheet" href="../admin/style.css">
</head>

<body class="auth-page">

  <div class="auth-wrapper admin-layout">

    <!-- LEFT: GRADIENT PANEL (ADMIN MESSAGE) -->
    <div class="auth-side admin-side">
      <div class="auth-side-text">
        <h3>Admin Portal</h3>
        <p>
          Manage students, scholarships,<br>
          and system records securely.
        </p>
      </div>
    </div>

    <!-- RIGHT: ADMIN LOGIN FORM -->
    <div class="auth-form admin-form">
      <h2>Welcome Back</h2>
      <p class="auth-subtitle">Administrator Access</p>

      <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <input type="text" name="username" placeholder="Admin Username" required>
        </div>

        <div class="form-group">
          <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" name="login" class="btn-register">
          Sign In
        </button>
      </form>
    </div>

  </div>

</body>

</html>
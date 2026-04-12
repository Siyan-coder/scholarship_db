<?php
session_start();

$admin_username = 'admin';
$admin_password = 'admin123';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username == $admin_username && $password == $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Scholarship System</title>
    <link rel="stylesheet" href="../style/style.css"> 
    <style>
        .login-box {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if (isset($error)) echo '<div class="alert alert-error">' . $error . '</div>'; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
            <p style="margin-top: 15px; text-align: center;"><a href="../student/login.php">← Back to Student Login</a></p>
        </form>
    </div>
</body>
</html>
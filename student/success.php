<?php
session_start();
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted - Scholarship System</title>
    <link rel="stylesheet" href="../student/style.css">



</head>

<body>
    <div class="header">
        <h2>Application Status</h2>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <div class="container success-container">
        <div class="card" style="text-align: center;">
            <div class="success-icon">✓</div>
            <h2>Application Submitted Successfully!</h2>


            <?php
            $eligibility = $_GET['eligibility'] ?? '';
            $gpa = $_GET['gpa'] ?? '';
            ?>

            <div id="successModalContent">
                <h3>✅ Application Submitted Successfully</h3>

                <?php if ($eligibility === 'eligible'): ?>
                    <p><strong>✓ ELIGIBLE</strong></p>
                    <p>
                        Based on your GPA (<strong><?php echo htmlspecialchars($gpa); ?></strong>),
                        you meet the eligibility requirement (1.00 – 2.50).
                    </p>
                <?php else: ?>
                    <p><strong>✗ NOT ELIGIBLE</strong></p>
                    <p>
                        Your GPA (<strong><?php echo htmlspecialchars($gpa); ?></strong>)
                        does not meet the scholarship requirement.
                    </p>
                <?php endif; ?>

                <hr>

                <p>Your application has been recorded and is now pending review.</p>

                <div class="actions">
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    <a href="status.php" class="btn btn-primary">Check Status</a>
                </div>
            </div>


            <p style="margin-top: 20px;">Your application has been stored in our database. You can check your
                application status anytime.</p>

            <div class="gpa-info">
                <strong>GPA Eligibility Guidelines:</strong><br>
                <span class="qualified-badge">Qualified: GPA 1.00 - 2.50</span><br>
                <span class="not-qualified-badge">Not Qualified: GPA 2.75 - 5.00</span>
                <p style="margin-top: 10px; font-size: 14px; color: #666;">Your submitted GPA:
                    <strong><?php echo htmlspecialchars($gpa); ?></strong></p>
            </div>

            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="status.php" class="btn btn-primary">Check Status</a>
            </div>
        </div>

        <div class="card" style="margin-top: 20px; text-align: center;">
            <h3>What's Next?</h3>
            <ul style="list-style: none; padding: 0; margin-top: 15px;">
                <li style="margin-bottom: 10px;">The scholarship committee will review your application</li>
                <li style="margin-bottom: 10px;">Review process typically takes 5-7 business days</li>
                <li style="margin-bottom: 10px;">You will be notified via email about the decision</li>
                <li>You can check the status anytime from your dashboard</li>
            </ul>
        </div>
    </div>
</body>

</html>
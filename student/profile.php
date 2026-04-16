<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div id="profileViewContainer">
    <div class="profile-view-card">
        <div class="profile-avatar">
           
            <p class="profile-note">Information cannot be modified after registration</p>
        </div>
        
        <div class="profile-info-grid">
            <div class="info-row">
                <div class="info-label">Full Name</div>
                <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Student ID</div>
                <div class="info-value"><?php echo htmlspecialchars($user['student_id']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Email Address</div>
                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Course</div>
                <div class="info-value"><?php echo htmlspecialchars($user['course']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Year Level</div>
                <div class="info-value"><?php echo htmlspecialchars($user['year_level']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Contact Number</div>
                <div class="info-value"><?php echo htmlspecialchars($user['contact_number'] ?: 'Not provided'); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Home Address</div>
                <div class="info-value"><?php echo htmlspecialchars($user['address'] ?: 'Not provided'); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Registered On</div>
                <div class="info-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
            </div>
        </div>
        
        <div class="profile-footer-note">
            <p>⚠️ For any corrections to your personal information, please contact the Registrar's Office.</p>
        </div>
    </div>
</div>

<style>
.profile-view-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.profile-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    text-align: center;
}

.avatar-circle {
    font-size: 48px;
    margin-bottom: 10px;
}

.profile-avatar h3 {
    margin: 0 0 5px 0;
    font-size: 20px;
}

.profile-note {
    font-size: 12px;
    opacity: 0.8;
    margin: 0;
}

.profile-info-grid {
    padding: 20px;
}

.info-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    width: 140px;
    font-weight: 600;
    color: #2c3e50;
}

.info-value {
    flex: 1;
    color: #555;
}

.profile-footer-note {
    background: #fff3cd;
    padding: 12px 20px;
    font-size: 13px;
    color: #856404;
    border-top: 1px solid #ffeeba;
}

.profile-footer-note p {
    margin: 0;
}

@media (max-width: 600px) {
    .info-row {
        flex-direction: column;
        padding: 10px 0;
    }
    
    .info-label {
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>
<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['student_id'])) {
    die(json_encode(['error' => 'Session expired']));
}

$student_id = $_SESSION['student_id'];

// Get student info
$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Check for existing application and get locked GPA if any
$check_app_sql = "SELECT gpa, status, application_date FROM scholarship_applications WHERE student_id = ? ORDER BY application_date DESC LIMIT 1";
$check_stmt = $conn->prepare($check_app_sql);
$check_stmt->bind_param("s", $student_id);
$check_stmt->execute();
$existing_app = $check_stmt->get_result()->fetch_assoc();

$locked_gpa = $existing_app ? $existing_app['gpa'] : null;
$last_status = $existing_app ? $existing_app['status'] : null;
$last_app_date = $existing_app ? $existing_app['application_date'] : null;

// Check if student can reapply (if rejected, must wait for next semester)
$can_reapply = true;
$reapply_message = '';

if ($last_status === 'Rejected' && $last_app_date) {
    // Get current semester based on date
    $last_app_year = date('Y', strtotime($last_app_date));
    $last_app_month = date('m', strtotime($last_app_date));
    $current_year = date('Y');
    $current_month = date('m');
    
    // Determine semesters: Fall (Aug-Dec), Spring (Jan-May), Summer (Jun-Jul)
    $last_semester = '';
    if ($last_app_month >= 8 && $last_app_month <= 12) {
        $last_semester = 'Fall';
    } elseif ($last_app_month >= 1 && $last_app_month <= 5) {
        $last_semester = 'Spring';
    } else {
        $last_semester = 'Summer';
    }
    
    $current_semester = '';
    if ($current_month >= 8 && $current_month <= 12) {
        $current_semester = 'Fall';
    } elseif ($current_month >= 1 && $current_month <= 5) {
        $current_semester = 'Spring';
    } else {
        $current_semester = 'Summer';
    }
    
    // Check if same semester and year
    if ($last_app_year == $current_year && $last_semester == $current_semester) {
        $can_reapply = false;
        $reapply_message = "Your previous application was rejected. You can only reapply in the next semester. Please wait until " . get_next_semester($current_semester, $current_year) . ".";
    } elseif ($last_app_year == $current_year && $last_semester !== $current_semester) {
        $reapply_message = "You may reapply this semester as your previous application was rejected last semester.";
    } else {
        $reapply_message = "You may reapply this academic year.";
    }
}

function get_next_semester($current_semester, $year) {
    switch($current_semester) {
        case 'Fall': return 'Spring ' . ($year + 1);
        case 'Spring': return 'Summer ' . $year;
        case 'Summer': return 'Fall ' . $year;
        default: return 'next semester';
    }
}

// Fix: Use null coalescing operator to handle undefined keys
$prefill = [
    'full_name' => $student['full_name'] ?? '',
    'student_id' => $student['student_id'] ?? '',
    'email' => $student['email'] ?? '',
    'course' => $student['course'] ?? '',
    'year_level' => $student['year_level'] ?? '',
    'address' => $student['address'] ?? 'Not provided',
    'contact_number' => $student['contact_number'] ?? 'Not provided',
    'locked_gpa' => $locked_gpa,
    'has_applied' => !is_null($locked_gpa)
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_application'])) {
    // Check if already has an application
    if ($prefill['has_applied']) {
        echo '
        <div id="errorModalContent">
            <h3>Already Applied</h3>
            <p>You have already submitted a scholarship application.</p>
            <p>Your application is currently under review.</p>
            <p>Only one application is accepted per student.</p>
        </div>';
        exit();
    }
    
    // Check reapplication rule for rejected students
    if (!$can_reapply) {
        echo '
        <div id="errorModalContent">
            <h3>Reapplication Not Allowed</h3>
            <p>' . htmlspecialchars($reapply_message) . '</p>
            <p>Please wait until the next semester to submit a new application.</p>
        </div>';
        exit();
    }
    
    $full_name = $prefill['full_name'];
    $student_id_input = trim($_POST['student_id']);
    $email = $prefill['email'];
    $course = $prefill['course'];
    $year_level = $prefill['year_level'];
    $gpa = $_POST['gpa'];
    $address = trim($_POST['address'] ?? $prefill['address']);
    $contact_number = trim($_POST['contact_number'] ?? $prefill['contact_number']);
    
    $errors = [];
    
    // Validate GPA
    $gpa_values = ['1.00', '1.25', '1.50', '1.75', '2.00', '2.25', '2.50', '2.75', '3.00', '5.00'];
    if (!in_array($gpa, $gpa_values)) {
        $errors[] = "Please select a valid GPA";
    } else {
        $gpa_float = floatval($gpa);
        if ($gpa_float >= 1.00 && $gpa_float <= 2.50) {
            $eligibility = "eligible";
        } else {
            $eligibility = "not eligible";
            $errors[] = "Your GPA ($gpa) does NOT meet the eligibility requirement. Only GPA 1.00 - 2.50 is eligible for scholarship.";
        }
    }
    
    if (empty($errors)) {
        // Fixed scholarship type (only one type)
        $scholarship_type = "Academic Excellence Scholarship";
        
        $sql = "INSERT INTO scholarship_applications (student_id, full_name, email, course, year_level, gpa, scholarship_type, address, contact_number, status, eligibility) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdssss", $student_id_input, $full_name, $email, $course, $year_level, $gpa, $scholarship_type, $address, $contact_number, $eligibility);
        
        if ($stmt->execute()) {
            echo '
            <div id="successModalContent">
                <div class="success-icon">✓</div>
                <h3>Application Submitted Successfully</h3>
                <div class="eligibility-box ' . $eligibility . '">
                    <strong>Eligibility:</strong> ' . htmlspecialchars(ucfirst($eligibility)) . '
                </div>
                <div class="gpa-box">
                    <strong>GPA Submitted:</strong> ' . htmlspecialchars($gpa) . '
                </div>
                <p>Your application has been recorded and is now pending review.</p>
                <p class="next-steps">You will be notified once a decision has been made.</p>
            </div>';
            exit();
        } else {
            echo '
            <div id="errorModalContent">
                <h3>Submission Error</h3>
                <p>There was an error processing your application. Please try again.</p>
            </div>';
            exit();
        }
        $stmt->close();
    } else {
        echo '
        <div id="errorModalContent">
            <h3>Submission Error</h3>
            <ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul></div>';
        exit();
    }
}
?>

<div id="applyFormContent" style="padding: 0;">
    <div class="gpa-info-banner" style="font-size: 12px; padding: 8px 12px;">
        <strong>Eligibility:</strong> GPA 1.00 – 2.50 = Qualified | 2.75 – 5.00 = Not Qualified
    </div>
    
    <?php if (!$can_reapply && $last_status === 'Rejected'): ?>
    <div class="warning-banner" style="padding: 8px 12px; font-size: 12px;">
        <strong>Notice:</strong> <?php echo htmlspecialchars($reapply_message); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($prefill['has_applied']): ?>
    <div class="info-banner" style="padding: 8px 12px; font-size: 12px;">
        <strong>Status:</strong> You have already submitted an application. 
        Current Status: <strong><?php echo htmlspecialchars($last_status); ?></strong>
        <?php if ($last_status === 'Rejected' && $can_reapply): ?>
        <br>✅ You may submit a new application for this semester.
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ((!$prefill['has_applied'] || ($last_status === 'Rejected' && $can_reapply)) && !($last_status === 'Rejected' && !$can_reapply)): ?>
    
    <form id="scholarshipForm" method="POST">
        <div class="form-section" style="margin-bottom: 12px;">
            <h4 style="font-size: 14px; margin-bottom: 10px;">Student Information</h4>
            
            <!-- Full Name - Row 1 -->
            <div class="form-group" style="margin-bottom: 8px;">
                <label style="font-size: 12px;">Full Name</label>
                <input type="text" class="readonly-input" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($prefill['full_name']); ?>" readonly>
            </div>
            
            <!-- Student ID and Email - Row 2 (2 columns) -->
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Student ID</label>
                    <input type="text" class="readonly-input" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($prefill['student_id']); ?>" readonly>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Email Address</label>
                    <input type="email" class="readonly-input" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($prefill['email']); ?>" readonly>
                </div>
            </div>
            
            <!-- Course and Year Level - Row 3 (2 columns) -->
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Course</label>
                    <input type="text" class="readonly-input" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($prefill['course']); ?>" readonly>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Year Level</label>
                    <input type="text" class="readonly-input" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($prefill['year_level']); ?>" readonly>
                </div>
            </div>
        </div>
        
        <div class="form-section" style="margin-bottom: 12px;">
            <h4 style="font-size: 14px; margin-bottom: 10px;">Application Details</h4>
            
            <!-- GWA Field -->
            <div class="form-group" style="margin-bottom: 8px;">
                <label style="font-size: 12px;">General Weighted Average (GWA) *</label>
                <?php if ($prefill['has_applied'] && $last_status !== 'Rejected'): ?>
                    <input type="text" class="readonly-input locked-gpa" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($locked_gpa); ?>" readonly>
                    <small style="font-size: 10px;">GWA locked after submission</small>
                <?php else: ?>
                    <select name="gpa" id="gpaInput" required style="padding: 6px; font-size: 13px; width: 100%;">
                        <option value="">Select GWA</option>
                        <option value="1.00">1.00 (Excellent)</option>
                        <option value="1.25">1.25</option>
                        <option value="1.50">1.50</option>
                        <option value="1.75">1.75</option>
                        <option value="2.00">2.00</option>
                        <option value="2.25">2.25</option>
                        <option value="2.50">2.50</option>
                        <option value="2.75">2.75</option>
                        <option value="3.00">3.00</option>
                        <option value="5.00">5.00</option>
                    </select>
                <?php endif; ?>
            </div>
            
            <!-- Address and Contact - Row 4 (2 columns) -->
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Home Address</label>
                    <input type="text" class="readonly-input" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($prefill['address']); ?>" readonly>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Contact Number</label>
                    <input type="text" class="readonly-input" style="padding: 6px; font-size: 13px;" value="<?php echo htmlspecialchars($prefill['contact_number']); ?>" readonly>
                </div>
            </div>
            
            <div id="eligibilityWarning" class="eligibility-warning" style="display:none; font-size: 12px; padding: 6px; margin: 8px 0;">
                ⚠️ GPA must be between 1.00 and 2.50 to qualify.
            </div>
            
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($prefill['student_id']); ?>">
            <input type="hidden" name="course" value="<?php echo htmlspecialchars($prefill['course']); ?>">
            <input type="hidden" name="year_level" value="<?php echo htmlspecialchars($prefill['year_level']); ?>">
            <input type="hidden" name="address" value="<?php echo htmlspecialchars($prefill['address']); ?>">
            <input type="hidden" name="contact_number" value="<?php echo htmlspecialchars($prefill['contact_number']); ?>">
            
            <button type="submit" name="submit_application" id="submitBtn" class="btn-action full-width" style="padding: 10px; font-size: 14px; margin-top: 10px;" disabled>Submit Application</button>
        </div>
    </form>
    
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('#gpaInput').on('change', function() {
        var gpa = parseFloat($(this).val());
        var btn = $('#submitBtn');
        var warning = $('#eligibilityWarning');
        
        if (!isNaN(gpa) && gpa >= 1.0 && gpa <= 2.5) {
            btn.prop('disabled', false).css({opacity: '1', cursor: 'pointer'});
            warning.hide();
        } else if (!isNaN(gpa)) {
            btn.prop('disabled', true).css({opacity: '0.7', cursor: 'not-allowed'});
            warning.show();
        } else {
            btn.prop('disabled', true).css({opacity: '0.7', cursor: 'not-allowed'});
            warning.hide();
        }
    });
});
</script>


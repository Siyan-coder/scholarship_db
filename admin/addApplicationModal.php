<div id="addApplicationFormContent" style="padding: 0;">
  <div class="gpa-info-banner" style="font-size: 12px; padding: 8px 12px;">
    <strong>Eligibility:</strong> GPA 1.00 – 2.50 = Qualified | 2.75 – 5.00 = Not Qualified
  </div>
  
  <div id="addAppMessage"></div>
  
  <form id="addApplicationForm">
    <div class="form-section" style="margin-bottom: 12px; padding: 0 12px;">
      <h4 style="font-size: 14px; margin-bottom: 10px;">Student Selection</h4>
      
      <!-- Student ID Dropdown -->
      <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 12px;">Student ID *</label>
        <select id="adminStudentId" name="student_id" required style="padding: 6px; font-size: 13px; width: 100%;">
          <option value="">Loading students...</option>
        </select>
      </div>
    </div>
    
    <div class="form-section" style="margin-bottom: 12px; padding: 0 12px;">
      <h4 style="font-size: 14px; margin-bottom: 10px;">Student Information</h4>
      
      <!-- Full Name -->
      <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 12px;">Full Name</label>
        <input type="text" id="adminFullName" class="readonly-input" readonly style="padding: 6px; font-size: 13px;">
      </div>
      
      <!-- Email and Course - Row 2 (2 columns) -->
      <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-size: 12px;">Email Address</label>
          <input type="email" id="adminEmail" class="readonly-input" readonly style="padding: 6px; font-size: 13px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-size: 12px;">Course</label>
          <input type="text" id="adminCourse" class="readonly-input" readonly style="padding: 6px; font-size: 13px;">
        </div>
      </div>
      
      <!-- Year Level and Address - Row 3 (2 columns) -->
      <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-size: 12px;">Year Level</label>
          <input type="text" id="adminYearLevel" class="readonly-input" readonly style="padding: 6px; font-size: 13px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-size: 12px;">Contact Number</label>
          <input type="text" id="adminContact" class="readonly-input" readonly style="padding: 6px; font-size: 13px;">
        </div>
      </div>
      
      <!-- Address Full Width -->
      <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 12px;">Home Address</label>
        <input type="text" id="adminAddress" class="readonly-input" readonly style="padding: 6px; font-size: 13px;">
      </div>
    </div>
    
    <div class="form-section" style="margin-bottom: 12px; padding: 0 12px;">
      <h4 style="font-size: 14px; margin-bottom: 10px;">Application Details</h4>
      
      <!-- GWA Field -->
      <div class="form-group" style="margin-bottom: 8px;">
        <label style="font-size: 12px;">General Weighted Average (GWA) *</label>
        <select id="adminGpa" name="gpa" required style="padding: 6px; font-size: 13px; width: 100%;">
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
      </div>
      
      <div id="eligibilityWarning" class="eligibility-warning" style="display:none; font-size: 12px; padding: 6px; margin: 8px 0;">
        ⚠️ GPA must be between 1.00 and 2.50 to qualify.
      </div>
      
      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top: 15px;">
        <button type="submit" class="btn" style="padding: 10px 20px; font-size: 14px;">Save Application</button>
        <button type="button" id="cancelAddApplication" class="btn" style="background:#95a5a6; padding: 10px 20px; font-size: 14px;">Cancel</button>
      </div>
    </div>
  </form>
</div>

$(document).ready(function () {
  $(".btn-apply-now").on("click", function (e) {
    e.preventDefault();
    $("#applyModal").fadeIn(300);
    $.ajax({
      url: "apply.php",
      type: "GET",
      success: function (response) {
        var formHtml =
          $(response).find("#applyFormContent").length > 0
            ? $(response).find("#applyFormContent").html()
            : response;
        $("#modalFormContainer").html(formHtml);
      },
      error: function () {
        $("#modalFormContainer").html(
          '<p style="color:red; text-align:center;">Error loading form.</p>',
        );
      },
    });
  });

  $(document).on("input change", "#gpaInput", function () {
    const gpa = parseFloat($(this).val());
    const btn = $("#submitBtn");
    const warning = $("#eligibilityWarning");

    if (gpa >= 1.0 && gpa <= 2.5) {
      btn.prop("disabled", false).css({ opacity: "1", cursor: "pointer" });
      warning.hide();
    } else {
      btn.prop("disabled", true).css({ opacity: "0.7", cursor: "not-allowed" });
      if ($(this).val() !== "") warning.show();
      else warning.hide();
    }
  });

  $(".btn-green").on("click", function (e) {
    e.preventDefault();
    $("#statusModal").fadeIn(300);
    $.ajax({
      url: "status.php",
      type: "GET",
      success: function (response) {
        $("#statusModalContainer").html(response);
      },
      error: function () {
        $("#statusModalContainer").html(
          '<p style="color:red; text-align:center;">Error loading status.</p>',
        );
      },
    });
  });

  $(document).on("submit", "#scholarshipForm", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("submit_application", true);
    const submitBtn = $("#submitBtn");
    const originalText = submitBtn.text();

    submitBtn.text("Processing...").prop("disabled", true);
    $.ajax({
      url: "apply.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.includes("successModalContent")) {
          $("#modalFormContainer").html(res);
          $("#applyModal .modal-header h3").text("Submission Successful");
        } else {
          alert("Submission failed. Please check your data.");
          submitBtn.text(originalText).prop("disabled", false);
        }
      },
      error: function () {
        alert("Server error. Ensure XAMPP/Apache is running.");
        submitBtn.text(originalText).prop("disabled", false);
      },
    });
  });

  $(".btn-purple").on("click", function (e) {
    e.preventDefault();
    $("#profileModal").fadeIn(300);
    $.ajax({
      url: "profile.php",
      type: "GET",
      success: function (response) {
        $("#profileModalContainer").html(response);
      },
      error: function () {
        $("#profileModalContainer").html(
          '<p style="color:red; text-align:center;">Error loading profile.</p>',
        );
      },
    });
  });

  $(document).on("submit", "#profileForm", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("update_profile", true);
    const saveBtn = $("#saveProfileBtn");
    const originalText = saveBtn.text();

    saveBtn.text("Updating...").prop("disabled", true);
    $.ajax({
      url: "profile.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.includes("profileSuccessContent")) {
          $("#profileModalContainer").html(res);
          $("#profileModal .modal-header h3").text("Update Successful");
        } else {
          alert("Update failed. Please try again.");
          saveBtn.text(originalText).prop("disabled", false);
        }
      },
      error: function () {
        alert("Server error. Could not update profile.");
        saveBtn.text(originalText).prop("disabled", false);
      },
    });
  });

  $("#openRegModal").on("click", function () {
    $("#registerModal").show();
  });

  $("#closeRegModal").on("click", function () {
    $("#registerModal").hide();
  });

  $(".close-modal, .close-profile-modal, .close-status-modal").on(
    "click",
    function () {
      $(".modal").fadeOut(300);
      $("#registerModal").hide();
    },
  );

  $(window).on("click", function (e) {
    if ($(e.target).hasClass("modal")) {
      $(".modal").fadeOut(300);
      $("#registerModal").hide();
    }
  });
});

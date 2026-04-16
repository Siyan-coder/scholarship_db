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

        // Re-attach GPA change handler after loading
        setTimeout(function () {
          if ($('#gpaInput').length) {
            $('#gpaInput').on('change', function () {
              var gpa = parseFloat($(this).val());
              var btn = $('#submitBtn');
              var warning = $('#eligibilityWarning');

              if (!isNaN(gpa) && gpa >= 1.0 && gpa <= 2.5) {
                btn.prop('disabled', false).css({ opacity: '1', cursor: 'pointer' });
                warning.hide();
              } else if (!isNaN(gpa)) {
                btn.prop('disabled', true).css({ opacity: '0.7', cursor: 'not-allowed' });
                warning.show();
              } else {
                btn.prop('disabled', true).css({ opacity: '0.7', cursor: 'not-allowed' });
                warning.hide();
              }
            });
          }
        }, 100);
      },
      error: function () {
        $("#modalFormContainer").html(
          '<p style="color:red; text-align:center;">Error loading form.</p>',
        );
      },
    });
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
          $("#applyModal .modal-header h3").text("Application Submitted");
        } else if (res.includes("errorModalContent")) {
          $("#modalFormContainer").html(res);
          $("#applyModal .modal-header h3").text("Submission Error");
          submitBtn.text(originalText).prop("disabled", false);
        } else {
          $("#modalFormContainer").html('<div class="error-container">' + res + '</div>');
          submitBtn.text(originalText).prop("disabled", false);
        }
      },
      error: function () {
        $("#modalFormContainer").html(
          '<p style="color:red; text-align:center;">Server error. Please try again.</p>'
        );
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
      if ($("#successModalContent").length) {
        forceReloadLatestApplicationStatus();
      }
    },
  );

  $(window).on("click", function (e) {
    if ($(e.target).hasClass("modal")) {
      $(".modal").fadeOut(300);
      $("#registerModal").hide();
      if ($("#successModalContent").length) {
        forceReloadLatestApplicationStatus();
      }
    }
  });

  let lastAppId = null;
  let lastAppStatus = null;
  let lastHistorySig = null;

  function forceReloadLatestApplicationStatus() {
    lastAppId = null;
    lastAppStatus = null;
    lastHistorySig = null;
    checkStudentApplicationChanges();
  }

  function checkStudentApplicationChanges() {
    $.getJSON("?check_student_apps=1", function (data) {
      if (lastAppId === null && lastAppStatus === null && lastHistorySig === null) {
        lastAppId = data.latest_id;
        lastAppStatus = data.latest_status;
        lastHistorySig = data.history_signature;
        return;
      }

      if (data.latest_id !== lastAppId) {
        location.reload();
        return;
      }

      if (data.latest_status !== lastAppStatus) {
        location.reload();
        return;
      }

      if (data.history_signature !== lastHistorySig) {
        location.reload();
        return;
      }
    });
  }

  setInterval(checkStudentApplicationChanges, 5000);
});
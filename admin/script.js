$(function () {
  let currentSection = 'dashboard';
  let lastKnownId = null;
  let courseChart, statusChart;
  let dashboardContent = $('#page-content').html();

  function openModal() {
    document.body.classList.add('modal-open');
    $('#bgModal,#modal').fadeIn(150);
    $('#bgModal').off('click').on('click', closeModal);
    $('#modal').off('click').on('click', function (e) {
      e.stopPropagation();
    });
  }

  function closeModal() {
    $('#bgModal,#modal').fadeOut(150, function () {
      $('#modal-body').html('');
      document.body.classList.remove('modal-open');
    });
  }

  function updateActiveMenu(section) {
    $('.sidebar-menu .menu-item').removeClass('active');
    $('.sidebar-menu .menu-item[data-section="' + section + '"]').addClass('active');
  }

  function updateHeader(section) {
    $('.top-header h1').text(section === 'applications' ? 'Manage Applications' : 'Dashboard');
  }

  function loadApplications(query = {}) {
    currentSection = 'applications';
    updateActiveMenu('applications');
    updateHeader('applications');

    query = query || {};
    query.ajax = 1;

    const queryString = $.param(query);
    const url = 'manageApplications.php' + (queryString ? ('?' + queryString) : '');

    $('#page-content').load(url + ' #ajax-page-content', function () {
      if ($(this).find('.chart-card').length === 0) {
        $('#courseChart, #statusChart').remove();
      }
    });
  }

  function refreshCurrentSection() {
    if (currentSection === 'applications') {
      loadApplications();
    } else {
      currentSection = 'dashboard';
      updateActiveMenu('dashboard');
      updateHeader('dashboard');
      $('#page-content').html(dashboardContent);
      loadCourseChart();
      loadStatusChart();
    }
  }

  function loadCourseChart(course = '') {
    $.getJSON('dashboard.php', { chart_course: 1, course: course }, function (data) {
      const labels = data.map(d => d.course);
      const totals = data.map(d => d.total);

      if (courseChart) courseChart.destroy();

      courseChart = new Chart(document.getElementById('courseChart'), {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Total Applications',
            data: totals,
            backgroundColor: '#3498db'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          aspectRatio: 2,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          }
        }
      });
    });
  }

  function loadStatusChart() {
    $.getJSON('dashboard.php', { chart_status: 1 }, function (data) {
      const labels = data.map(d => d.status);
      const totals = data.map(d => d.total);

      const statusColors = {
        'Pending': '#f39c12',
        'Approved': '#2ecc71',
        'Rejected': '#e74c3c'
      };

      if (statusChart) statusChart.destroy();

      statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: totals,
            backgroundColor: labels.map(status => statusColors[status] || '#ccc')
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          aspectRatio: 1.5,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    });
  }

  function handleStatusChange(appId, status) {
    $.post('manageApplications.php', { ajax: 1, update_status: 1, app_id: appId, new_status: status }, function () {
      refreshCurrentSection();
      closeModal();
    }, 'json');
  }

  function handleDelete(appId) {
    $.getJSON('manageApplications.php', { ajax: 1, delete: appId }, function () {
      refreshCurrentSection();
    });
  }

  function confirmAction(message, callback) {
    $('#modal-body').html(
      '<p>' + message + '</p>' +
      '<div style="margin-top:10px;">' +
      '<button id="confirmAction" class="btn">Yes</button> ' +
      '<button id="cancelAction" class="btn" style="background:#95a5a6;">No</button>' +
      '</div>'
    );
    openModal();
    $('#cancelAction').off().on('click', closeModal);
    $('#confirmAction').off().on('click', callback);
  }

  $(document).on('click', '.sidebar-menu .menu-item', function (e) {
    e.preventDefault();
    const section = $(this).data('section');
    if (section === 'applications') {
      loadApplications();
    } else if (section === 'dashboard') {
      currentSection = 'dashboard';
      updateActiveMenu('dashboard');
      updateHeader('dashboard');
      $('#page-content').html(dashboardContent);
      loadCourseChart();
      loadStatusChart();
    }
  });

  $(document).on('change', '#courseFilter', function () {
    loadCourseChart(this.value);
  });

  $(document).on('change', '.ajax-filter select, .ajax-filter input', function () {
    const query = {};
    $('.ajax-filter').serializeArray().forEach(function (item) {
      query[item.name] = item.value;
    });
    loadApplications(query);
  });

  $(document).on('submit', '.ajax-filter', function (e) {
    e.preventDefault();
  });

  $(document).on('click', '.reset-applications', function () {
    $('.ajax-filter input, .ajax-filter select').val('');
    loadApplications();
  });

  $(document).on('click', '.btn-modal-status', function (e) {
    e.preventDefault();
    const appId = $(this).data('id');
    const status = $(this).data('status');

    confirmAction('Are you sure you want to ' + status.toLowerCase() + ' this application?', function () {
      handleStatusChange(appId, status);
    });
  });

  $(document).on('click', '.btn-delete', function (e) {
    e.preventDefault();
    const appId = $(this).data('id');

    confirmAction('Are you sure you want to delete this application?', function () {
      handleDelete(appId);
    });
  });

  $(document).on('click', '.btn-view', function (e) {
    e.preventDefault();
    const url = $(this).attr('href');

    $('#modal-body').load(url, function () {
      $('#closeView').remove();
      $('#modal-body').append('<div style="margin-top:15px; text-align:center;"><button id="closeView" class="btn">Close</button></div>');
      $('#closeView').off().on('click', closeModal);
    });

    openModal();
    $('#bgModal').off().on('click', closeModal);
  });

  function showAddApplicationModal() {
    $('#modal-body').load('addApplicationModal.php', function () {
      openModal();
      // Disable save button initially
      $('#addApplicationForm').find('button[type="submit"]').prop('disabled', true).css({opacity: '0.7', cursor: 'not-allowed'});
      loadEligibleStudents();
      $('#cancelAddApplication').off().on('click', closeModal);
    });
  }

  function loadEligibleStudents() {
    const studentSelect = $('#adminStudentId');
    studentSelect.prop('disabled', true).html('<option value="">Loading students...</option>');
    setAddAppAlert('Loading eligible students...', false);

    $.getJSON('manageApplications.php', { ajax: 1, eligible_students: 1 }, function (response) {
      if (!response.success) {
        setAddAppAlert('Unable to load student list.');
        studentSelect.prop('disabled', true).html('<option value="">Unable to load students</option>');
        return;
      }

      if (!response.students.length) {
        setAddAppAlert('No students without applications are currently available.', true);
        studentSelect.prop('disabled', true).html('<option value="">No eligible students available</option>');
        return;
      }

      const options = ['<option value="">Select a student</option>'];
      response.students.forEach(function (student) {
        options.push('<option value="' + student.student_id + '">' + student.student_id + ' – ' + student.full_name + '</option>');
      });
      studentSelect.prop('disabled', false).html(options.join(''));
      setAddAppAlert('Select a student and choose GWA to add the application.', false);
    }).fail(function () {
      setAddAppAlert('Unable to load student list. Please try again.');
      studentSelect.prop('disabled', true).html('<option value="">Unable to load students</option>');
    });
  }
  function setAddAppAlert(message, isError = true) {
    $('#addAppMessage').html(
      '<div style="padding:12px;margin-bottom:12px;border-radius:8px;font-size:14px;color:' + (isError ? '#7f1d1d' : '#064e3b') + ';background:' + (isError ? '#fee2e2' : '#d1fae5') + ';border:1px solid ' + (isError ? '#fecaca' : '#6ee7b7') + ';">' + message + '</div>'
    );
  }

  function resetAddApplicationForm() {
    $('#adminStudentId').prop('disabled', true).html('<option value="">Loading students...</option>');
    $('#adminFullName, #adminEmail, #adminCourse, #adminYearLevel, #adminAddress, #adminContact').val('');
    $('#adminGpa').val('');
    setAddAppAlert('Select a student from the list to load details.');
  }

  $(document).on('click', '.btn-add-application', function (e) {
    e.preventDefault();
    showAddApplicationModal();
    resetAddApplicationForm();
  });

  $(document).on('change', '#adminStudentId', function () {
    const studentId = $(this).val().trim();
    if (!studentId) {
      resetAddApplicationForm();
      return;
    }

    $.getJSON('manageApplications.php', { ajax: 1, student_info: studentId }, function (response) {
      if (!response.success) {
        setAddAppAlert(response.message || 'Student data could not be loaded.');
        $('#adminFullName, #adminEmail, #adminCourse, #adminYearLevel, #adminAddress, #adminContact').val('');
        return;
      }

      const student = response.student;
      $('#adminFullName').val(student.full_name);
      $('#adminEmail').val(student.email);
      $('#adminCourse').val(student.course);
      $('#adminYearLevel').val(student.year_level);
      $('#adminAddress').val(student.address);
      $('#adminContact').val(student.contact_number);
      setAddAppAlert('Student loaded. Select GWA and save to add the application.', false);
    }).fail(function () {
      setAddAppAlert('Unable to load student details.');
    });
  });

  $(document).on('change', '#adminGpa', function () {
    const gpa = parseFloat($(this).val());
    const submitBtn = $('#addApplicationForm').find('button[type="submit"]');
    const warning = $('#eligibilityWarning');
    
    if (!isNaN(gpa) && gpa >= 1.0 && gpa <= 2.5) {
      submitBtn.prop('disabled', false).css({opacity: '1', cursor: 'pointer'});
      warning.hide();
    } else if (!isNaN(gpa)) {
      submitBtn.prop('disabled', true).css({opacity: '0.7', cursor: 'not-allowed'});
      warning.show();
    } else {
      submitBtn.prop('disabled', true).css({opacity: '0.7', cursor: 'not-allowed'});
      warning.hide();
    }
  });

  $(document).on('submit', '#addApplicationForm', function (e) {
    e.preventDefault();
    const studentId = $('#adminStudentId').val().trim();
    const gpa = $('#adminGpa').val();
    const address = $('#adminAddress').val().trim();
    const contactNumber = $('#adminContact').val().trim();

    if (!studentId) {
      setAddAppAlert('Student ID is required.');
      return;
    }
    if (!gpa) {
      setAddAppAlert('Please select a GWA.');
      return;
    }

    // Validate GPA eligibility
    const gpaValue = parseFloat(gpa);
    if (gpaValue > 2.50) {
      setAddAppAlert('GPA must be between 1.00 and 2.50 to qualify.');
      return;
    }

    $.post('manageApplications.php', {
      ajax: 1,
      add_application: 1,
      student_id: studentId,
      gpa: gpa,
      address: address,
      contact_number: contactNumber
    }, function (response) {
      if (!response.success) {
        setAddAppAlert(response.errors ? response.errors.join('<br>') : response.message || 'Unable to save application.');
        return;
      }
      refreshCurrentSection();
      closeModal();
    }, 'json').fail(function () {
      setAddAppAlert('Unable to save application. Please try again.');
    });
  });

  $(document).on('click', '#cancelAddApplication', function (e) {
    e.preventDefault();
    closeModal();
  });

  $('#btn-logout').on('click', function (e) {
    e.preventDefault();
    $('#modal-body').html(
      '<p>Are you sure you want to log out?</p>' +
      '<button id="confirmLogout" class="btn">Log Out</button> ' +
      '<button id="cancelAction" class="btn" style="background:#95a5a6;">No</button>'
    );

    openModal();

    $('#confirmLogout').off().on('click', function () {
      window.location.href = 'logout.php';
    });

    $('#cancelAction').off().on('click', closeModal);
    $('#bgModal').off().on('click', closeModal);
  });

  function checkForNewApplications() {
    $.getJSON('dashboard.php?check_new=1', function (data) {
      if (lastKnownId === null) {
        lastKnownId = data.latest_id;
        return;
      }
      if (data.latest_id > lastKnownId) {
        refreshCurrentSection();
      }
    });
  }

  setInterval(checkForNewApplications, 5000);
  refreshCurrentSection();
});

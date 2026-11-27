    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <div class="fees-page">

      <form id="addFeesForm" class="fees-card add-fees">
        <input type="hidden" id="csrf_token_name" value="<?= $this->security->get_csrf_token_name(); ?>">
        <input type="hidden" id="csrf_token_hash" value="<?= $this->security->get_csrf_hash(); ?>">

        <h2>Add Fee Type</h2>
        <!-- Branch Dropdown -->
        <div class="form-group">
          <label>Branch <span class="required">*</span></label>
          <select class="form-control" name="branch_id" id="branchSelect" required>
            <option value="">Select Branch</option>
            <?php foreach ($branches as $branch): ?>
              <option value="<?= $branch['id']; ?>"><?= $branch['name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>


        <div class="form-group">
          <label>Fee Name <span class="required">*</span></label>
          <input type="text" name="fee_name" class="form-control" placeholder="Enter Fee Name">
        </div>

        <div class="form-group">
          <label>Student Category <span class="required">*</span></label>
          <select class="form-control" name="student_category[]" id="studentCategorySelect" multiple="multiple">
            <option value="">Select Category</option>
            <!-- Options will be appended here via JS -->
          </select>
        </div>


        <!-- <div class="form-group">
          <label>Fee Code <span class="required">*</span></label>
          <input type="text" name="fee_code" class="form-control" placeholder="Enter Fee Code">
        </div> -->

        <div class="form-group">
          <label>Frequency <span class="required">*</span></label>
          <select class="form-control" name="frequency" id="frequencySelect">
            <option value="">Select Frequency</option>
            <option value="annually">Annually</option>
            <option value="half-yearly">Half Yearly</option>
            <option value="quarterly">Quarterly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>

        <div class="form-group month-checks" id="monthCheckboxes">
          <label><input type="checkbox" name="months[]" value="4"> April</label>
          <label><input type="checkbox" name="months[]" value="5"> May</label>
          <label><input type="checkbox" name="months[]" value="6"> June</label>
          <label><input type="checkbox" name="months[]" value="7"> July</label>
          <label><input type="checkbox" name="months[]" value="8"> August</label>
          <label><input type="checkbox" name="months[]" value="9"> September</label>
          <label><input type="checkbox" name="months[]" value="10"> October</label>
          <label><input type="checkbox" name="months[]" value="11"> November</label>
          <label><input type="checkbox" name="months[]" value="12"> December</label>
          <label><input type="checkbox" name="months[]" value="1"> January</label>
          <label><input type="checkbox" name="months[]" value="2"> February</label>
          <label><input type="checkbox" name="months[]" value="3"> March</label>
        </div>

        <button type="submit" class="save-btn">Save</button>
      </form>


      <div class="fees-card fees-list">
        <h2>Fee Type List</h2>
        <!-- <input type="text" class="search-input" placeholder="Search..."> -->

        <table class="fees-table" id="feeTable">
          <thead>
            <tr>
              <th>Name</th>
              <!-- <th>Fees Code</th> -->
              <th>Student Category</th>
              <th>Frequency</th>
              <th>selected Months</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="feeTypeTable">
            <tr>
              <!-- <td>1st to 5th</td>
              <td>NWF</td>
              <td>
                <a href="#" title="Edit">
                  <img src="<?= asset_url('assets/images/edit.png') ?>" alt="Edit" class="action-icon" width="20" height="20">
                </a>
                <a href="#" title="Delete">
                  <img src="<?= asset_url('assets/images/bin.png') ?>" alt="Delete" class="action-icon" width="20" height="20">
                </a>
              </td> -->

            </tr>
            <!-- Repeat similar rows -->
          </tbody>
        </table>
      </div>
    </div>



    <!-- Delete Modal -->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteFeeModal" tabindex="-1" role="dialog" aria-labelledby="deleteFeeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteFeeModalLabel">Confirm Delete</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this fee type?
          </div>
          <div class="modal-footer">
            <input type="hidden" id="deleteFeeId">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" id="confirmDeleteFee" class="btn btn-danger">Delete</button>
          </div>
        </div>
      </div>
    </div>


    <!-- Edit Fee Modal -->
    <div class="modal fade" id="editFeeModal" tabindex="-1" role="dialog" aria-labelledby="editFeeModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form id="editFeeForm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Fee Type</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <input type="hidden" id="edit_fee_id" name="fee_id">
              <div class="form-group">
          <label>Branch <span class="required">*</span></label>
          <select class="form-control" name="editbranch_id" id="editbranchSelect" required>
            <option value="">Select Branch</option>
            <?php foreach ($branches as $branch): ?>
              <option value="<?= $branch['id']; ?>"><?= $branch['name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
              <div class="form-group">
                <label>Fee Type Name</label>
                <input type="text" class="form-control" name="fee_name" id="edit_fee_name" required>
              </div>
              <!-- <div class="form-group">
                <label>Fee Code</label>
                <input type="text" class="form-control" name="fee_code" id="edit_fee_code" required>
              </div> -->
              <div class="form-group">
                <label>Student Category <span class="required">*</span></label>
                <select class="form-control" name="student_category[]" id="edit_student_category"multiple="multiple">
                  <option value="">Select Category</option>
                  <!-- Options will be appended here via JS -->
                </select>
              </div>
              <div class="form-group">
                <label>Frequency <span class="required">*</span></label>
                <select class="form-control" name="frequency" id="edit_frequency">
                  <option value="">Select Frequency</option>
                  <option value="annually">Annually</option>
                  <option value="half-yearly">Half Yearly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </div>

              <div id="editMonthCheckboxes">
                <label><input type="checkbox" name="months[]" value="4"> Apr</label>
                <label><input type="checkbox" name="months[]" value="5"> May</label>
                <label><input type="checkbox" name="months[]" value="6"> Jun</label>
                <label><input type="checkbox" name="months[]" value="7"> Jul</label>
                <label><input type="checkbox" name="months[]" value="8"> Aug</label>
                <label><input type="checkbox" name="months[]" value="9"> Sep</label>
                <label><input type="checkbox" name="months[]" value="10"> Oct</label>
                <label><input type="checkbox" name="months[]" value="11"> Nov</label>
                <label><input type="checkbox" name="months[]" value="12"> Dec</label>
                <label><input type="checkbox" name="months[]" value="1"> Jan</label>
                <label><input type="checkbox" name="months[]" value="2"> Feb</label>
                <label><input type="checkbox" name="months[]" value="3"> Mar</label>
              </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script>
      $(document).ready(function() {


        const baseUrl = "<?= base_url(); ?>"; // ensure this is printed in your view
        const iconEdit = "<?= asset_url('assets/images/edit.png') ?>";
        const iconBin = "<?= asset_url('assets/images/bin.png') ?>";

        // Fetch categories and populate dropdown
        function loadStudentCategories() {
          $.ajax({
            url: baseUrl + "student/get_student_categories",
            method: "GET",
            dataType: "json",
            success: function(data) {
              let options = '<option value="">Select Category</option>';
              $.each(data, function(index, category) {
                options += `<option value="${category.name}">${category.name}</option>`;
              });
              $('#studentCategorySelect').html(options);
            },
            error: function() {
              console.error("Failed to load categories");
            }
          });
        }


        loadStudentCategories(); // Call on page load


        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        loadFeeType();
        $('#studentCategorySelect').select2({
          placeholder: "Select Categories",
          allowClear: true,
          tags: true
        });
        $('#edit_student_category').select2({
          placeholder: "Select Categories",
          allowClear: true,
          tags: true
        });

        $('#addFeesForm').on('submit', function(e) {
          e.preventDefault();

          let formData = $(this).serializeArray();
          formData.push({
            name: csrfName,
            value: csrfHash
          }); // Add CSRF token

          $.ajax({
            url: "<?php echo base_url('fees/save_fee'); ?>",
            method: "POST",
            data: $.param(formData),
            dataType: "json",
            success: function(response) {
              if (response.status === 'success') {
                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'success',
                  title: response.message,
                  showConfirmButton: false,
                  timer: 5000,
                  timerProgressBar: true,
                  customClass: {
                    popup: 'swal-success-custom'
                  }
                });

                $('.add-fees')[0].reset(); // clear form
                $('#studentCategorySelect').val(null).trigger('change');
                loadFeeType();
              } else {
                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'error',
                  title: response.message,
                  showConfirmButton: false,
                  timer: 5000,
                  timerProgressBar: true,
                  customClass: {
                    popup: 'swal-error-custom'
                  }
                });
              }
            },
            error: function() {
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Something went wrong!',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                  popup: 'swal-error-custom'
                }
              });
            }
          });
        });


        function loadFeeType() {
          $.ajax({
            url: '<?php echo base_url("fees/fetch") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
              // Destroy DataTable BEFORE updating HTML
              if ($.fn.DataTable.isDataTable('#feeTable')) {
                $('#feeTable').DataTable().destroy();
              }

              const monthNames = {
                1: 'January',
                2: 'February',
                3: 'March',
                4: 'April',
                5: 'May',
                6: 'June',
                7: 'July',
                8: 'August',
                9: 'September',
                10: 'October',
                11: 'November',
                12: 'December'
              };

              let html = '';
              $.each(data, function(i, row) {
                // Convert "1,4,7" to "January, April, July"
                let monthLabel = '';
                if (row.month) {
                  monthLabel = row.month
                    .split(',')
                    .map(m => monthNames[parseInt(m.trim())])
                    .filter(Boolean)
                    .join(', ');
                }

                html += `
          <tr>
            <td>${row.fee_name}</td>
            <td>${row.student_category}</td>
            <td>${row.frequency}</td>
            <td>${monthLabel}</td>
            <td>
              <a href="#" title="Edit" class="editBtn" data-id="${row.id}" data-name="${row.fee_name}"  data-category="${encodeURIComponent(row.student_category)}"data-frequency="${row.frequency}"data-months="${row.month}"data-branch="${row.branch_id}">
                <img src="${iconEdit}" width="23">
              </a>
              <span style="margin: 0 5px; color: #ccc;">|</span>
              <a href="#" class="openDeleteModal" data-id="${row.id}" title="Delete">
                <img src="${iconBin}" width="23">
              </a>
            </td>
          </tr>
        `;
              });

              $('#feeTypeTable').html(html);

              // Re-initialize DataTable
              $('#feeTable').DataTable({
                responsive: true
              });
            },
            error: function() {
              alert('Failed to fetch fee types.');
            }
          });
        }


        $(document).on('click', '.openDeleteModal', function(e) {
          e.preventDefault();
          let feeId = $(this).data('id');
          $('#deleteFeeId').val(feeId); // set in hidden input
          $('#deleteFeeModal').modal('show'); // show modal
        });

        $('#confirmDeleteFee').on('click', function() {
          let id = $('#deleteFeeId').val();

          $.ajax({
            url: '<?= base_url("fees/delete_fee_type") ?>',
            type: 'POST',
            data: {
              id: id,
              '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
              $('#deleteFeeModal').modal('hide');

              if (response.status === 'success') {
                loadFeeType();
                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'success',
                  title: response.message,
                  showConfirmButton: false,
                  timer: 5000,
                  timerProgressBar: true,
                  customClass: {
                    popup: 'swal-success-custom'
                  }
                });
              } else {
                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'error',
                  title: response.message,
                  showConfirmButton: false,
                  timer: 5000,
                  timerProgressBar: true,
                  customClass: {
                    popup: 'swal-error-custom'
                  }
                });
              }
            },
            error: function() {
              alert('Error deleting fee type.');
            }
          });
        });

        $('#editFeeForm').on('submit', function(e) {
          e.preventDefault();

          let formData = $(this).serializeArray();
          formData.push({
            name: '<?php echo $this->security->get_csrf_token_name(); ?>',
            value: '<?php echo $this->security->get_csrf_hash(); ?>'
          });

          $.ajax({
            url: '<?php echo base_url("fees/update_fee"); ?>',
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(res) {
              if (res.status === 'success') {
                $('#editFeeModal').modal('hide');
                loadFeeType(); // Refresh table
                Swal.fire({
                  toast: true,
                  icon: 'success',
                  title: res.message,
                  position: 'top-end',
                  timer: 3000,
                  showConfirmButton: false,
                  timer: 3000,
                  timerProgressBar: true,
                  customClass: {
                    popup: 'swal-success-custom'
                  }
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: res.message,
                  position: 'top-end',
                  timer: 3000,
                  showConfirmButton: false,
                  timer: 3000,
                  timerProgressBar: true,
                  customClass: {
                    popup: 'swal-error-custom'
                  }
                });
              }
            }
          });
        });


      });
      document.addEventListener("DOMContentLoaded", function() {
        const monthGroups = {
          annually: [4],
          "half-yearly": [4, 10], // April, October
          quarterly: [4, 8, 12], // April, Aug, Dec
          monthly: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
        };

        function updateMonths(selectedFreq, monthContainer, preselectedMonths = []) {
          const checkboxes = monthContainer.querySelectorAll("input[type='checkbox']");
          monthContainer.style.display = monthGroups[selectedFreq] ? "block" : "none";

          checkboxes.forEach(cb => cb.checked = false); // clear all first

          if (preselectedMonths.length > 0) {
            // If we have stored months, mark them
            checkboxes.forEach(cb => {
              if (preselectedMonths.includes(parseInt(cb.value))) {
                cb.checked = true;
              }
            });
          } else if (monthGroups[selectedFreq]) {
            // If no stored months, auto-check based on frequency
            checkboxes.forEach(cb => {
              if (monthGroups[selectedFreq].includes(parseInt(cb.value))) {
                cb.checked = true;
              }
            });
          }
        }

        function setupFrequencyHandler(frequencySelectId, monthContainerId) {
          const frequencySelect = document.getElementById(frequencySelectId);
          const monthContainer = document.getElementById(monthContainerId);

          frequencySelect.addEventListener("change", function() {
            updateMonths(this.value, monthContainer);
          });
        }

        // Setup for Add Fee form
        setupFrequencyHandler("frequencySelect", "monthCheckboxes");

        // Setup for Edit Fee form
        setupFrequencyHandler("edit_frequency", "editMonthCheckboxes");

        const baseUrl = "<?= base_url(); ?>"; // ensure this is printed in your view
        function loadCategories(selectedCategories = "") {
          $.ajax({
            url: baseUrl + "student/get_student_categories",
            method: "GET",
            dataType: "json",
            success: function(data) {
              let options = '<option value="">Select Category</option>';
              $.each(data, function(index, category) {
                let selected = (category.name === selectedCategories) ? 'selected' : '';
                options += `<option value="${category.name}" ${selected}>${category.name}</option>`;
              });
              $('#edit_student_category').html(options);
              // ✅ Set selected values for Select2
      if (selectedCategories.length > 0) {
        $('#edit_student_category').val(selectedCategories).trigger('change');
      }
            },
            error: function() {
              console.error("Failed to load categories");
            }
          });
        }


        // When opening edit modal
        $(document).on('click', '.editBtn', function() {
  const id = $(this).data('id');
  const name = $(this).data('name');
  const encodedCategory = $(this).data('category');
  const frequency = $(this).data('frequency');
  const months = $(this).data('months');
  const branchId = $(this).data('branch');
    // console.log("Edit modal data:", { id, name, category: encodedCategory, frequency, months, branchId });

  // ✅ Decode category JSON
  let categories = [];
  try {
    const decoded = decodeURIComponent(encodedCategory);
    categories = JSON.parse(decoded); // e.g. ["Reapeter Admission"]
  } catch (error) {
    console.error("Category parse error:", error);
  }

  $('#edit_fee_id').val(id);
  $('#edit_fee_name').val(name);
  $('#edit_frequency').val(frequency);
  $('#editbranchSelect').val(branchId);

  // ✅ Load categories and preselect
  loadCategories(categories);

  // ✅ Handle months
  const preselectedMonths = months ? String(months).split(',').map(m => parseInt(m)) : [];
  updateMonths(frequency, document.getElementById("editMonthCheckboxes"), preselectedMonths);

  $('#editFeeModal').modal('show');
});

      });
    </script>
    <!-- <script src="../assets/js/feeCollect/feeType.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= asset_url('assets/js/feeCollect/feeType.js'); ?>"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
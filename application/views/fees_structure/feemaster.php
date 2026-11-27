        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

        <style>
          .class-group {
            margin-bottom: 15px;
          }

          .class-group label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
          }

          .row-pair {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
          }

          .class-name,
          .class-amount {
            width: 50%;
            padding: 0 10px;
          }

          .class-name {
            font-weight: 500;
            border: 1px solid black;
          }
        </style>
        <div class="fees-page">
          <form id="addFeeMasterForm" class="fees-card add-feeMaster">
            <input type="hidden" id="csrf_token_name" value="<?= $this->security->get_csrf_token_name(); ?>">
            <input type="hidden" id="csrf_token_hash" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="fees-card">
              <h2>Add Fees Master : 2024-25</h2>

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
                <label>Fees Type <span class="required">*</span></label>
                <select class="form-control" name="fee_types" id="feeTypes" required>
                  <option value="">Select Fee Types</option>
                </select>
              </div>

              <div class="form-group">
                <label>Student Category</label>
                <select class="form-control" name="student_category[]" id="studentCategorySelect" multiple="multiple">
                  <option value="">Select Category</option>
                  <!-- Options will be appended here via JS -->
                </select>
              </div>
              <div class="form-group" id="classAmountSection">
                <!-- <label>Assign Amount for Each Class and Choose Due Date</label> -->
                <div class="row">
                  <div class="col-md-6">Assign Amount for Each Class</div>
                  <div class="col-md-6" style="text-align: right;">Choose Due Date</div>
                </div>
                <div id="classAmountsContainer">
                  <?php foreach ($classes as $class): ?>
                    <div class="class-group row-pair">
                      <div class="class-name">
                        <?= htmlspecialchars($class['name']) ?>
                      </div>
                      <div class="class-amount">
                        <input type="hidden" name="classes[]" value="<?= $class['id'] ?>">
                        <input type="number" name="amount[]" placeholder="₹ Amount" class="form-control">
                        <input type="hidden" name="fine_amount[]">
                      </div>
                      <div class="class-due-date">
                        <!-- <input type="date" name="due_date[]" class="form-control"> -->
                        <select name="due_day[]" class="form-control due-day-select">
                          <option value="">Select Day</option>
                          <?php for ($d = 1; $d <= 31; $d++): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                          <?php endfor; ?>
                        </select>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>



              <!-- <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date">
              </div> -->

              <!-- <div class="form-group">
            <label>Amount (₹) <span class="required">*</span></label>
            <input type="number" name="amount">
          </div> -->

              <div class="form-group">
                <label>Fine Type</label>
                <div class="radio-group">
                  <label><input type="radio" name="fine_type" value="" checked> None</label>
                  <label><input type="radio" name="fine_type" value="percentage"> Percentage</label>
                  <label><input type="radio" name="fine_type" value="fix amount"> Fix Amount</label>
                </div>
              </div>

              <div class="form-group split-inputs">
                <div id="percentageBox" style="display:none;">
                  <label>Percentage (%)</label>
                  <input type="number" name="per_val">
                </div>
                <div id="amountBox" style="display:none;">
                  <label>Fix Amount (₹)</label>
                  <input type="number" name="amt_val">
                </div>
              </div>

              <button type="submit" class="save-btn">Save</button>
          </form>
        </div>

        <div class="fees-card fees-list">
          <h2>Fees Master List : 2024-25</h2>
          <!-- <input type="text" class="search-input" placeholder="Search..."> -->

          <table id="feeTable" class="fees-table">
            <thead>
              <tr>
                <th>Fee Types</th>
                <th>Student Category</th>
                <!-- <th>Due Date</th> -->
                <th>Amount</th>
                <th>Fine Type</th>
                <th>Percentage value</th>
                <th>Fix Amount value</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="feeMasterTable">
              <tr>

                <td>
                  <a href="#" title="Edit">
                    <img src="<?= asset_url('assets/images/edit.png') ?>" alt="Edit" class="action-icon" width="20" height="20">
                  </a>
                  <a href="#" title="Delete">
                    <img src="<?= asset_url('assets/images/bin.png') ?>" alt="Delete" class="action-icon" width="20" height="20">
                  </a>
                </td>

              </tr>
              <!-- Repeat similar rows -->
            </tbody>
          </table>
        </div>
        </div>



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
                Are you sure you want to delete this fee master?
              </div>
              <div class="modal-footer">
                <input type="hidden" id="deleteFeeId">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteFee" class="btn btn-danger">Delete</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Fee Master Modal -->
        <div class="modal fade" id="editFeeMasterModal" tabindex="-1" role="dialog" aria-labelledby="editFeeMasterModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

              <form id="editFeeMasterForm">
                <!-- CSRF Tokens -->
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                  value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="fee_master_id" id="edit_fee_id">

                <div class="modal-header">
                  <h5 class="modal-title" id="editFeeMasterModalLabel">Edit Fees Master : 2024-25</h5>
                  <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                  </button>
                </div>

                <div class="modal-body">
                  <!-- Branch Dropdown -->
                  <div class="form-group">
                    <label>Branch <span class="required">*</span></label>
                    <select class="form-control" name="branch_id" id="editbranchSelect" required>
                      <option value="">Select Branch</option>
                      <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id']; ?>"><?= $branch['name']; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Fees Type <span class="required">*</span></label>
                    <select class="form-control" name="fee_types" id="edit_feeTypes">
                      <option value="">Select Fee Types</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Student Category</label>
                    <select class="form-control" name="student_category[]" id="edit_student_category" multiple="multiple">
                      <option value="">Select Category</option>
                    </select>
                  </div>

                  <!-- <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date" id="edit_due_date" class="form-control">
                  </div>

                  <div class="form-group">
                    <label>Amount (₹) <span class="required">*</span></label>
                    <input type="number" name="amount" id="edit_amount" class="form-control">
                  </div> -->
                  <div class="form-group" id="editClassAmountSection">
                    <label>Assigned Amounts for Each Class</label>
                    <div id="editClassAmountsContainer">
                      <?php foreach ($classes as $class): ?>
                        <div class="class-group row-pair">
                          <div class="class-name">
                            <?= htmlspecialchars($class['name']) ?>
                          </div>
                          <div class="class-amount">
                            <input type="hidden" name="classes[]" value="<?= $class['id'] ?>">
                            <input type="number" name="amount[]"
                              id="edit_amount_<?= $class['id'] ?>"
                              placeholder="₹ Amount"
                              class="form-control">
                          </div>
                          <div class="class-due-date">
                            <!-- <input type="date" name="due_date[]" class="form-control"> -->
                            <select name="due_day[]" class="form-control due-day-select">
                              <option value="">Select Day</option>
                              <?php for ($d = 1; $d <= 31; $d++): ?>
                                <option value="<?= $d ?>"><?= $d ?></option>
                              <?php endfor; ?>
                            </select>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Fine Type</label>
                    <div class="radio-group">
                      <label><input type="radio" name="fine_type" value="" id="edit_fine_none"> None</label>
                      <label><input type="radio" name="fine_type" value="percentage" id="edit_fine_percentage"> Percentage</label>
                      <label><input type="radio" name="fine_type" value="fix amount" id="edit_fine_fix"> Fix Amount</label>
                    </div>
                  </div>

                  <div class="form-group split-inputs">
                    <div>
                      <label>Percentage (%) <span class="required">*</span></label>
                      <input type="number" name="per_val" id="edit_per_val" class="form-control">
                    </div>
                    <div>
                      <label>Fix Amount (₹) <span class="required">*</span></label>
                      <input type="number" name="amt_val" id="edit_amt_val" class="form-control">
                    </div>
                  </div>
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Update</button>
                </div>
              </form>

            </div>
          </div>
        </div>



        <script>
          const iconEdit = "<?= asset_url('assets/images/edit.png') ?>";
          const iconBin = "<?= asset_url('assets/images/bin.png') ?>";
          document.addEventListener("DOMContentLoaded", function() {
            const fineTypeRadios = document.querySelectorAll("input[name='fine_type']");
            const percentageBox = document.getElementById("percentageBox");
            const amountBox = document.getElementById("amountBox");

            function toggleFineType() {
              let selected = document.querySelector("input[name='fine_type']:checked").value;
              percentageBox.style.display = (selected === "percentage") ? "block" : "none";
              amountBox.style.display = (selected === "fix amount") ? "block" : "none";
            }

            fineTypeRadios.forEach(radio => {
              radio.addEventListener("change", toggleFineType);
            });

            // Run once on page load
            toggleFineType();
          });


          document.addEventListener("DOMContentLoaded", function() {
            const fineTypeRadios = document.querySelectorAll("input[name='fine_type']");
            const percentageBox = document.getElementById("percentageBox");
            const amountBox = document.getElementById("amountBox");
            const percentageInput = document.querySelector("input[name='per_val']");
            const classContainer = document.getElementById("classAmountsContainer");

            // toggle input boxes
            fineTypeRadios.forEach(radio => {
              radio.addEventListener("change", function() {
                if (this.value === "percentage") {
                  percentageBox.style.display = "block";
                  amountBox.style.display = "none";
                } else if (this.value === "fix amount") {
                  amountBox.style.display = "block";
                  percentageBox.style.display = "none";
                } else {
                  percentageBox.style.display = "none";
                  amountBox.style.display = "none";
                }
              });
            });

            // recalc fine whenever percentage or amounts change
            function calculateFine() {
              const per = parseFloat(percentageInput.value) || 0;
              const classGroups = classContainer.querySelectorAll(".class-group");

              classGroups.forEach(group => {
                const amountInput = group.querySelector("input[name='amount[]']");
                const hiddenFine = group.querySelector("input[name='fine_amount[]']");

                if (amountInput && hiddenFine) {
                  const amt = parseFloat(amountInput.value) || 0;
                  const fine = (per > 0) ? ((amt * per) / 100).toFixed(2) : 0;
                  hiddenFine.value = fine;
                }
              });
            }

            percentageInput.addEventListener("input", calculateFine);
            classContainer.addEventListener("input", calculateFine); // triggers when class amounts change
          });
        </script>





        <script>
          $(document).ready(function() {

            loadFeeMaster();
            const baseUrl = "<?= base_url(); ?>";

            function loadStudentCategories() {
              $.ajax({
                url: baseUrl + "student/get_student_categories",
                method: "GET",
                dataType: "json",
                success: function(data) {
                  let options = '<option value="">Select Category</option><option value="all">Select All</option>';
                  $.each(data, function(index, category) {
                    options += `<option value="${category.id}">${category.name}</option>`;
                  });
                  $('#studentCategorySelect').html(options);
                },
                error: function() {
                  console.error("Failed to load categories");
                }
              });
            }
            loadStudentCategories();

            function loadFeeTypes() {
              $.ajax({
                url: baseUrl + "fees/fetch",
                method: "GET",
                dataType: "json",
                success: function(data) {
                  let options = '<option value="">Select Types</option>';
                  $.each(data, function(index, feeTypes) {
                    options += `<option value="${feeTypes.id}">${feeTypes.fee_name}</option>`;
                  });
                  $('#feeTypes').html(options);
                },
                error: function() {
                  console.error("Failed to load fee Types");
                }
              });
            }
            loadFeeTypes();
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


            // Adding FeeMaster Form
            var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

            $('#addFeeMasterForm').on('submit', function(e) {
              e.preventDefault();

              let formData = $(this).serializeArray();
              formData.push({
                name: csrfName,
                value: csrfHash
              }); // Add CSRF token

              $.ajax({
                url: "<?php echo base_url('fees/save_feeMaster'); ?>",
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

                    $('.add-feeMaster')[0].reset(); // clear form
                    $('#studentCategorySelect').val(null).trigger('change');
                    loadFeeMaster();
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






            function loadFeeMaster() {
              $.ajax({
                url: '<?php echo base_url("fees/fetchFeeMaster") ?>',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                  // Destroy DataTable BEFORE updating HTML
                  if ($.fn.DataTable.isDataTable('#feeTable')) {
                    $('#feeTable').DataTable().destroy();
                  }

                  let html = '';
                  $.each(data, function(i, row) {

                    html += `
          <tr>
            <td>${row.fees_type}</td>
            <td>${row.student_cat}</td>
            <td>${row.class_amounts ? row.class_amounts : '-'}</td>
            <td>${row.fine_type}</td>
            <td>${row.per_val}</td>
            <td>${row.amt_val}</td>
            <td>
              <a href="#" 
   title="Edit" 
   class="editBtn" 
   data-id="${row.id}" 
   data-fee_types="${row.fees_type}" 
   data-student_category="${encodeURIComponent(row.student_category)}" 
   data-due_date="${row.due_date}" 
   data-amount="${row.amount}" 
   data-fine_type="${row.fine_type}" 
   data-per_val="${row.per_val}" 
   data-branch="${row.branch_id}"
   data-amt_val="${row.amt_val}">
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

                  $('#feeMasterTable').html(html);

                  // Re-initialize DataTable
                  $('#feeTable').DataTable({
                    responsive: true
                  });
                },
                error: function() {
                  alert('Failed to fetch fee Master.');
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
                url: '<?= base_url("fees/delete_fee_master") ?>',
                type: 'POST',
                data: {
                  id: id,
                  '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                  $('#deleteFeeModal').modal('hide');

                  if (response.status === 'success') {
                    loadFeeMaster();
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


            $(document).on('click', '.editBtn', function() {
              // Get values from the clicked button's data attributes
              let id = $(this).data('id');
              let feeTypes = $(this).data('fee_types');
              let studentCategory = $(this).data('student_category');
              let perVal = $(this).data('per_val');
              let amtVal = $(this).data('amt_val');
              let due_date = $(this).data('due_date');
              let fineType = $(this).data('fine_type');
              const branchId = $(this).data('branch');
              // ✅ Decode category JSON
              let categories = [];
              try {
                const decoded = decodeURIComponent(studentCategory);
                categories = JSON.parse(decoded); // e.g. ["Reapeter Admission"]
              } catch (error) {
                console.error("Category parse error:", error);
              }
              // console.log(branchId,feeTypes,categories,due_date);

              // Set hidden ID
              $('#edit_fee_id').val(id);

              // Set fine type radios
              if (fineType === 'percentage') {
                $('#edit_fine_percentage').prop('checked', true);
              } else if (fineType === 'fix amount') {
                $('#edit_fine_fix').prop('checked', true);
              } else {
                $('#edit_fine_none').prop('checked', true);
              }

              // Set other single inputs
              $('#edit_per_val').val(perVal);
              $('#edit_amt_val').val(amtVal);
              $('#editbranchSelect').val(branchId);

              // Load Fee Types dropdown with selected
              function loadFeeTypesDropdown(selectedType = "") {
                $.ajax({
                  url: baseUrl + "fees/fetch",
                  method: "GET",
                  dataType: "json",
                  success: function(data) {
                    let options = '<option value="">Select Types</option>';
                    $.each(data, function(index, fee) {
                      let isSelected = (fee.fee_name === selectedType) ? "selected" : "";
                      options += `<option value="${fee.id}" ${isSelected}>${fee.fee_name}</option>`;
                    });
                    $('#edit_feeTypes').html(options);
                  }
                });
              }

              // Load Student Categories dropdown with selected
              function loadStudentCategoriesDropdown(selectedCategories = []) {
                $.ajax({
                  url: baseUrl + "fees/fetch_categories",
                  method: "GET",
                  dataType: "json",
                  success: function(data) {
                    let options = '<option value="">Select Category</option>';

                    $.each(data, function(index, category) {
                      // Compare IDs since your DB stores ["1","2"]
                      let isSelected = selectedCategories.includes(category.id.toString()) ? "selected" : "";
                      options += `<option value="${category.id}" ${isSelected}>${category.name}</option>`;
                    });

                    $('#edit_student_category').html(options);

                    // Trigger Select2 or refresh
                    $('#edit_student_category').val(selectedCategories).trigger('change');
                  },
                  error: function() {
                    console.error("Failed to load categories.");
                  },
                });
              }


              // 🔹 New AJAX: Load class-wise allocations
              $.ajax({
                url: baseUrl + "fees/get_fee_allocation_details",
                type: "POST",
                data: {
                  fee_master_id: id
                },
                dataType: "json",
                success: function(data) {
                  if (data.length > 0) {
                    data.forEach(function(row) {
                      $("#edit_amount_" + row.class_id).val(row.amount);
                      // $("#edit_due_date_" + row.class_id).val(row.due_date);
                      // Extract day (1–31) from date string
                      const dueDay = row.due_date ? new Date(row.due_date).getDate() : "";
                      $("#editClassAmountsContainer")
                        .find(`input[name='classes[]'][value='${row.class_id}']`)
                        .closest(".class-group")
                        .find(".due-day-select")
                        .val(dueDay);

                    });
                  }
                },
                error: function() {
                  console.error("Failed to load class allocations");
                }
              });

              // Load dropdowns with selected values
              loadFeeTypesDropdown(feeTypes);
              loadStudentCategoriesDropdown(categories);

              // Finally show modal
              $('#editFeeMasterModal').modal('show');
            });


            $('#editFeeMasterForm').on('submit', function(e) {
              e.preventDefault();
              $.ajax({
                url: baseUrl + "fees/update_fee_master",
                method: "POST",
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                  if (res.status === 'success') {
                    $('#editFeeMasterModal').modal('hide');
                    loadFeeMaster(); // Refresh table
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
                      toast: true,
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
                },
                error: function() {
                  alert("Failed to update fee master.");
                }
              });
            });
            $('#feeTypes').on('change', function() {
              var feeTypeId = $(this).val();
              if (!feeTypeId) return;

              // AJAX to get month for selected fee type
              $.ajax({
                url: '<?= base_url("fees/getFeeTypeMonth") ?>',
                type: 'POST',
                data: {
                  fee_type_id: feeTypeId
                },
                dataType: 'json',
                success: function(res) {
                  if (res.status === 'success') {
                    $('#feeMonth').val(res.month); // store month number in hidden field
                  } else {
                    $('#feeMonth').val('');
                  }
                }
              });
            });


          });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <div class="fees-page">

      <div class="fees-card add-fees">
        <form id="addFeesDiscountForm">
          <h2>Add Fees Discount</h2>

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
            <select class="form-control" name="fee_type_id" id="feeTypes" required>
              <option value="">Select Fee Types</option>
            </select>
          </div>

          <div class="form-group">
            <label>Name <span class="required">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Enter name" required>
          </div>

          <div class="form-group">
            <label>Discount Code <span class="required">*</span></label>
            <input type="text" name="discount_code" class="form-control" placeholder="Enter Discount Code" required>
          </div>

          <div class="form-group">
            <label>Discount Type</label>
            <div class="radio-group">
              <label><input type="radio" name="discount_type" value="none" checked> None</label>
              <label><input type="radio" name="discount_type" value="percentage"> Percentage</label>
              <label><input type="radio" name="discount_type" value="fixed"> Fixed Amount</label>
            </div>
          </div>

          <div class="form-group split-inputs">
            <div>
              <label>Percentage (%)</label>
              <input type="number" name="percentage" class="form-control">
            </div>
            <div>
              <label>Fix Amount (₹)</label>
              <input type="number" name="fixed_amount" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="4"></textarea>
          </div>

          <button class="save-btn" type="submit">Save</button>
        </form>
      </div>



      <div class="fees-card fees-list">
        <h2>Fees Discount List</h2>
        <!-- <input type="text" class="search-input" placeholder="Search..."> -->

        <table class="table table-bordered" id="discountTable">
          <thead>
            <tr>
              <th>Branch</th>
              <th>Fee Type</th>
              <th>Name</th>
              <th>Code</th>
              <th>Type</th>
              <th>Percentage</th>
              <th>Amount</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="feeDiscountTable"></tbody>
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
            Are you sure you want to delete this fee Discount?
          </div>
          <div class="modal-footer">
            <input type="hidden" id="deleteFeeId">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" id="confirmDeleteFee" class="btn btn-danger">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Fees Discount Modal -->
    <div class="modal fade" id="editDiscountModal" tabindex="-1" role="dialog" aria-labelledby="editDiscountModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <form id="editFeesDiscountForm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Fees Discount</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

              <input type="hidden" name="discount_id" id="edit_discount_id">

              <!-- Branch -->
              <div class="form-group">
                <label>Branch <span class="required">*</span></label>
                <select class="form-control" name="branch_id" id="edit_branchSelect" required>
                  <option value="">Select Branch</option>
                  <?php foreach ($branches as $branch): ?>
                    <option value="<?= $branch['id']; ?>"><?= $branch['name']; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Fee Type -->
              <div class="form-group">
                <label>Fees Type <span class="required">*</span></label>
                <select class="form-control" name="fee_type_id" id="edit_feeTypes" required>
                  <option value="">Select Fee Types</option>
                </select>
              </div>

              <!-- Name -->
              <div class="form-group">
                <label>Name <span class="required">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
              </div>

              <!-- Code -->
              <div class="form-group">
                <label>Discount Code <span class="required">*</span></label>
                <input type="text" name="discount_code" id="edit_discount_code" class="form-control" required>
              </div>

              <!-- Discount Type -->
              <div class="form-group">
                <label>Discount Type</label>
                <div class="radio-group">
                  <label>
                    <input type="radio" name="discount_type" value="none"> None
                  </label>
                  <label>
                    <input type="radio" name="discount_type" value="percentage"> Percentage
                  </label>
                  <label>
                    <input type="radio" name="discount_type" value="fixed"> Fixed Amount
                  </label>
                </div>
              </div>

              <!-- Values -->
              <div class="form-group row">
                <div class="col-md-6">
                  <label>Percentage (%)</label>
                  <input type="number" name="percentage" id="edit_percentage" class="form-control">
                </div>
                <div class="col-md-6">
                  <label>Fix Amount (₹)</label>
                  <input type="number" name="fixed_amount" id="edit_fixed_amount" class="form-control">
                </div>
              </div>

              <!-- Description -->
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
              </div>

            </div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Update</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>

          </div>
        </form>
      </div>
    </div>


    <script>
      const iconEdit = "<?= asset_url('assets/images/edit.png') ?>";
      const iconBin = "<?= asset_url('assets/images/bin.png') ?>";
      const iconTag = "<?= asset_url('assets/images/tag.png') ?>";
      $(document).ready(function() {

        const baseUrl = "<?= base_url(); ?>";

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
              $('#edit_feeTypes').html(options);
            },
            error: function() {
              console.error("Failed to load fee Types");
            }
          });
        }
        loadFeeTypes();
        let discountTable;

        // 🔹 Load Fee Discount List
        function loadFeeDiscount() {
          $.ajax({
            url: baseUrl + "fees/fetchFeeDiscount",
            method: "GET",
            dataType: "json",
            success: function(data) {

              // Destroy DataTable if exists
              if ($.fn.DataTable.isDataTable('#discountTable')) {
                $('#discountTable').DataTable().destroy();
              }

              let html = '';

              $.each(data, function(i, row) {
                html += `
                        <tr>
                            <td>${row.branch_name}</td>
                            <td>${row.fees_type}</td>
                            <td>${row.name}</td>
                            <td>${row.discount_code}</td>
                            <td>${row.discount_type}</td>
                            <td>${row.percentage ? row.percentage + '%' : '-'}</td>
                            <td>${row.amount ? '₹' + row.amount : '-'}</td>
                            <td>
                             <!-- ASSIGN BUTTON -->
    <a href="<?= base_url('fees_structure/assign_discount/') ?>${row.id}" 
       title="Assign Discount">
       <img src="${iconTag}" width="22">
    </a>

    <span style="margin:0 5px;">|</span>
                                <a href="#" class="editDiscount"
   data-id="${row.id}"
   data-branch="${row.branch_id}"
data-fee_type_id="${row.fee_type_id}"
   data-name="${row.name}"
   data-code="${row.discount_code}"
   data-discount_type="${row.discount_type}"
   data-discount_type="${row.discount_type}"
   data-percentage="${row.percentage}"
   data-amount="${row.fixed_amount}"
   data-description="${row.description}">
   <img src="${iconEdit}" width="22">
</a>

                                <span style="margin:0 5px;">|</span>
                                <a href="#" class="openDeleteModal" data-id="${row.id}">
                                    <img src="${iconBin}" width="22">
                                </a>
                            </td>
                        </tr>
                    `;
              });

              $('#feeDiscountTable').html(html);
              // $('#edit_feeTypes').html(options);

              // Reinitialize DataTable
              discountTable = $('#discountTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                  [0, 'asc']
                ]
              });
            },
            error: function() {
              alert('Failed to load fee discount list');
            }
          });
        }

        // 🔹 Initial Load
        loadFeeDiscount();

        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
        // 🔹 After Save → Reload Table
        $('#addFeesDiscountForm').on('submit', function(e) {
          e.preventDefault();

          let formData = $(this).serializeArray();
          formData.push({
            name: csrfName,
            value: csrfHash
          });

          $.ajax({
            url: baseUrl + "fees/save_discount",
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
                  timer: 4000
                });

                $('#addFeesDiscountForm')[0].reset();
                $('#branchSelect').val('');
                $('#feeTypes').val('');

                // ✅ Reload Datatable
                loadFeeDiscount();
              } else {
                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'error',
                  title: response.message,
                  showConfirmButton: false,
                  timer: 4000
                });
              }
            }
          });
        });
        $(document).on('click', '.openDeleteModal', function(e) {
          e.preventDefault();
          let feeId = $(this).data('id');
          $('#deleteFeeId').val(feeId); // set in hidden input
          $('#deleteFeeModal').modal('show'); // show modal
        });
        $('#confirmDeleteFee').on('click', function() {
          let id = $('#deleteFeeId').val();

          $.ajax({
            url: '<?= base_url("fees/delete_discount_fee") ?>',
            type: 'POST',
            data: {
              id: id,
              '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
              $('#deleteFeeModal').modal('hide');

              if (response.status === 'success') {
                loadFeeDiscount();
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


        $(document).on('click', '.editDiscount', function(e) {
          e.preventDefault();
          let feeTypeId = $(this).data('fee_type_id');

          $('#edit_discount_id').val($(this).data('id'));
          $('#edit_branchSelect').val($(this).data('branch'));
          $('#edit_name').val($(this).data('name'));
          $('#edit_discount_code').val($(this).data('code'));
          $('#edit_percentage').val($(this).data('percentage'));
          $('#edit_fixed_amount').val($(this).data('amount'));
          $('#edit_description').val($(this).data('description'));

          let discountType = $(this).data('discount_type');

          $('input[name="discount_type"]').prop('checked', false);
          $('input[name="discount_type"][value="' + discountType + '"]').prop('checked', true);

          setTimeout(function() {
            $('#edit_feeTypes').val(feeTypeId).trigger('change');
          }, 100);

          $('#editDiscountModal').modal('show');
        });



        $('#editFeesDiscountForm').on('submit', function(e) {
          e.preventDefault();

          let csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
          let csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

          let formData = $(this).serializeArray();
          formData.push({
            name: csrfName,
            value: csrfHash
          });

          $.ajax({
            url: "<?= base_url('fees/update_discount'); ?>",
            type: "POST",
            data: $.param(formData),
            dataType: "json",
            success: function(res) {
              if (res.status === 'success') {
                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'success',
                  title: res.message,
                  showConfirmButton: false,
                  timer: 3000
                });

                $('#editDiscountModal').modal('hide');
                loadFeeDiscount(); // refresh table
              }
            }
          });
        });


      });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables -->
<script src="<?= asset_url('assets/vendor/datatables/media/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/media/js/dataTables.bootstrap.min.js') ?>"></script>

<!-- DataTables Buttons Extension -->
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.colVis.min.js') ?>"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/media/css/dataTables.bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/buttons.dataTables.min.css') ?>">
<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<?php if (is_superadmin_loggedin()): ?>
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><?= translate('select_ground') ?></h4>
        </header>
        <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
        <div class="panel-body">
            <div class="row mb-sm">
                <div class="col-md-offset-3 col-md-6">
                    <div class="form-group">
                        <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                        <?php
                        $arrayBranch = $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control'
						data-plugin-selectTwo data-width='100%'");
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-offset-10 col-md-2">
                    <button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i class="fas fa-filter"></i> <?= translate('filter') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
<?php
endif;
?>

<?php
if (!empty($branch_id)) :
?>
        <div class="content-wrapper panel">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="page-title mb-0"><?= $title ?></h3>
                </div>

                <!-- Filter Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">From Date</label>
                                    <input type="date" name="from_date" id="from_date" class="form-control shadow-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">To Date</label>
                                    <input type="date" name="to_date" id="to_date" class="form-control shadow-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Fees Type <span class="required">*</span></label>
                                    <select class="form-control" name="fee_types[]" id="feeTypes" multiple>
                                        <option value="">Select Fee Types</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm" style="margin-top:26px;">
                                        <i class="fa fa-search me-2"></i> Fetch Records
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <p class="mt-3"><i class="fa fa-info-circle me-2"></i>Please select a date range and click “Fetch Records”.</p>

                <!-- Results Area -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-body">
                        <div id="resultArea" class="mt-3 text-center text-muted">
                            <div class="table-responsive mt-4">
                                <table id="feesTable" class="table table-bordered table-striped align-middle table-export w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th></th>
                                            <th>Sr.no</th>
                                            <th>Submitted Date</th>
                                            <th>Reciept No</th>
                                            <th>Register No</th>
                                            <th>Student Name</th>
                                            <th>Class & Section</th>
                                            <th>Roll No</th>
                                            <th>Fee Type(Head)</th>
                                            <th>Mode</th>
                                            <th>Fine</th>
                                            <th>Discount</th>
                                            <th>Paid Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Initially empty -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-semibold bg-light">
                                            <td colspan="10" class="text-end"></td>
                                            <td>Total Fine: ₹0.00</td>
                                            <td>Total Discount: ₹0.00</td>
                                            <td>Total Paid: ₹0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php
endif;
?>

<!-- Optional styling enhancement -->
<style>
    body {
        background-color: #f7f7f7ff;
    }

    .card {
        border-radius: 12px;
    }

    .table {
        margin-top: 15px;
    }

    .table th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 600;
    }

    .table td {
        vertical-align: middle;
    }

    .page-title {
        font-weight: 600;
        color: #2c3e50;
    }

    #resultArea table {
        border-radius: 8px;
        overflow: hidden;
    }

    #resultArea p.text-danger {
        font-weight: 500;
    }

    #resultArea {
        margin-top: 25px;
        /* space between search form and results */
    }

    /* Table Styles */
    #feesTable {
        border-radius: 10px;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    #feesTable th {
        background-color: #f1f3f6;
        color: #333;
        font-weight: 600;
        font-size: 13px;
        letter-spacing: 0.3px;
    }

    #feesTable td {
        vertical-align: middle;
        color: #444;
        font-size: 14px;
    }

    #feesTable tr:hover {
        background-color: #f8fbff;
    }

    /* Table Footer */
    #feesTable tfoot td {
        font-weight: 600;
        background-color: #f8f9fa;
        color: #2c3e50;
    }

    /* Messages */
    #resultArea p {
        font-size: 15px;
    }

    .text-danger,
    .text-info {
        font-weight: 500;
    }
</style>
<!-- Payment details modal -->
<div class="modal fade" id="studentPaymentsModal" tabindex="-1" role="dialog" aria-labelledby="studentPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt"></i> Payment details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="studentPaymentsInfo" class="mb-2"></div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="studentPaymentsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Receipt No</th>
                                <th>Date</th>
                                <th>Fee Head</th>
                                <th>Payment Method</th>
                                <th>Fine</th>
                                <th>Discount</th>
                                <th>Paid Amount</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-end fw-semibold">Total Paid:</td>
                                <td id="modalTotalPaid">₹0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- AJAX Script -->
<!-- <script>
$(document).ready(function() {
  // Initialize empty DataTable on page load
  let table = $('#feesTable').DataTable({
    pageLength: 10,
    order: [[1, 'desc']], // sort by Date
    columnDefs: [{ targets: 0, orderable: false }],
    language: { search: "_INPUT_", searchPlaceholder: "Search by student, class, or fee type..." }
  });

  $('#filterForm').on('submit', function(e) {
    e.preventDefault();

    let from_date = $('#from_date').val();
    let to_date = $('#to_date').val();

    if (from_date === '' || to_date === '') {
      alert('Please select both From and To dates.');
      return;
    }

    $('#resultArea p').hide(); // hide info text

    $.ajax({
      url: "<?= base_url('fees/fetch_paid_students') ?>",
      type: "POST",
      data: { from_date, to_date },
      dataType: "json",
      success: function(response) {
        table.clear().draw(); // Clear previous data

        let total = 0;
        if (response.data.length > 0) {
          $.each(response.data, function(index, row) {
            total += parseFloat(row.paid_amount);
            table.row.add([
              index + 1,
              row.date,
              row.register_no ?? '',
              row.first_name + ' ' + row.last_name,
              row.class_name + ' (' + row.section_name + ')',
              row.roll_no,
              row.fee_name,
              row.payment_method,
              row.fine,
              row.discount,
              '₹' + parseFloat(row.paid_amount).toFixed(2)
            ]).draw(false);
          });
        }

        // Update total in footer
        $('#feesTable tfoot td:last').html('Total Paid: ₹' + total.toFixed(2));

        if (response.data.length === 0) {
          alert('No records found for selected date range.');
        }
      },
      error: function() {
        alert('Something went wrong while fetching data.');
      }
    });
  });
});
</script> -->
<!-- <script>
    $(document).ready(function() {
        //   let table = $('#feesTable').DataTable({
        //     pageLength: 10,
        //     order: [[1, 'desc']],
        //     columnDefs: [{ targets: 0, orderable: false }],
        //     language: { search: "_INPUT_", searchPlaceholder: "Search by student, class, or fee type..." }
        //   });

        // ✅ Initialize DataTable safely (prevents reinitialization)
        var table;
        if ($.fn.DataTable.isDataTable('#feesTable')) {
            table = $('#feesTable').DataTable();
        } else {
            table = $('#feesTable').DataTable({
                dom: '<"row mb-2"<"col-md-6"B><"col-md-6"f>>tipr',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Fee Defaulters Report'
                    }
                ],
                pageLength: 25
            });
        }

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            let from_date = $('#from_date').val();
            let to_date = $('#to_date').val();
            let branch_id = $('#branch_id').val();

            if (from_date === '' || to_date === '' || branch_id === '') {
                alert('Please select Branch, From Date, and To Date.');
                return;
            }

            $('#resultArea p').hide();

            $.ajax({
                url: "<?= base_url('fees/fetch_paid_students') ?>",
                type: "POST",
                data: {
                    from_date,
                    to_date,
                    branch_id: "<?= $branch_id ?>" // ✅ use PHP branch_id selected above
                },
                dataType: "json",
                success: function(response) {
                    table.clear().draw();

                    let total = 0;

                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(index, row) {
                            // sum only numeric paid amounts
                            const paidValues = row.paid_amount.split("\n").map(v => parseFloat(v) || 0);
                            const totalPaidForStudent = paidValues.reduce((a, b) => a + b, 0);
                            total += parseFloat(row.total_paid_sum || 0);
                            // Convert multiline values to arrays and calculate totals
                            const fineTotal = row.fine.split("\n").reduce((a, b) => a + parseFloat(b || 0), 0);
                            const discountTotal = row.discount.split("\n").reduce((a, b) => a + parseFloat(b || 0), 0);
                            const paidTotal = row.paid_amount.split("\n").reduce((a, b) => a + parseFloat(b || 0), 0);

                            // Format single total values
                            const fineFormatted = fineTotal.toFixed(2);
                            const discountFormatted = discountTotal.toFixed(2);
                            const paidFormatted = '₹' + paidTotal.toFixed(2);

                            table.row.add([
                                index + 1,
                                row.date.replace(/\n/g, "<br>"),
                                row.register_no ?? '',
                                row.receipt_no.replace(/\n/g, "<br>"),
                                row.first_name + ' ' + row.last_name,
                                row.class_name + ' (' + row.section_name + ')',
                                row.roll_no,
                                row.fee_name.replace(/\n/g, "<br>"),
                                row.payment_method.replace(/\n/g, "<br>"),
                                // row.fine.replace(/\n/g, "<br>"),
                                // row.discount.replace(/\n/g, "<br>"),
                                // '₹' + row.paid_amount.replace(/\n/g, "<br>")
                                fineFormatted,
                                discountFormatted,
                                paidFormatted
                            ]);
                        });

                        table.draw();
                        $('#feesTable tfoot td:last').html('Total Paid: ₹' + total.toFixed(2));
                    } else {
                        table.clear().draw();
                        $('#feesTable tbody').html('<tr><td colspan="11" class="text-center text-danger">No records found for selected filters.</td></tr>');
                        $('#feesTable tfoot td:last').html('Total Paid: ₹0.00');
                    }
                },
                error: function() {
                    alert('Something went wrong while fetching data.');
                }
            });
        });
    });
</script> -->
<script>
    $(document).ready(function() {
         const baseUrl = "<?= base_url(); ?>";

        // Load fee types dynamically
        function loadFeeTypes() {
            $.ajax({
                url: baseUrl + "fees/fetch",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    let options = '<option value="">Select Fees Types</option>';
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
         // Initialize Select2 for multi-select fee types
        $('#feeTypes').select2({
            placeholder: "Select one or more Fee Types",
            allowClear: true,
            width: '100%'
        });

        var table;
        if ($.fn.DataTable.isDataTable('#feesTable')) {
            table = $('#feesTable').DataTable();
        } else {
            table = $('#feesTable').DataTable({
                dom: '<"row mb-2"<"col-md-6"B><"col-md-6"f>>tipr',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Paid Students Report'
                    }
                ],
                pageLength: 25
            });
        }

        // ✅ Expandable Row Formatter
        function formatDetails(row) {

            let dates = row.date.split("\n");
            let receipts = row.receipt_no.split("\n");
            let feeNames = row.fee_name.split("\n");
            let payMethods = row.payment_method.split("\n");
            let fines = row.fine.split("\n");
            let discounts = row.discount.split("\n");
            let paidAmounts = row.paid_amount.split("\n");

            // ✅ Fix Missing Dates & Payment Methods
            for (let i = 0; i < receipts.length; i++) {
                if (!dates[i] || dates[i].trim() === "") dates[i] = dates[0];
                if (!payMethods[i] || payMethods[i].trim() === "") payMethods[i] = payMethods[0];
            }

            let html = `<table class="table table-bordered table-sm mt-2">
            <thead class="table-secondary">
                <tr>
                    <th>Submitted Date</th>
                    <th>Receipt No</th>
                    <th>Fee Type (Head)</th>
                    <th>Payment Method</th>
                    <th>Fine</th>
                    <th>Discount</th>
                    <th>Paid</th>
                </tr>
            </thead><tbody>`;

            for (let i = 0; i < receipts.length; i++) {
                html += `<tr>
                <td>${dates[i] || ''}</td>
                <td>${receipts[i] || ''}</td>
                <td>${feeNames[i] || ''}</td>
                <td>${payMethods[i] || ''}</td>
                <td>${fines[i] || ''}</td>
                <td>${discounts[i] || ''}</td>
                <td>₹${paidAmounts[i] || ''}</td>
            </tr>`;
            }

            html += `</tbody></table>`;
            return html;
        }

        let globalResponse = [];

        // ✅ Expand / Collapse by Click on Icon Only
        $('#feesTable tbody').on('click', 'span.toggle-details', function() {

            let tr = $(this).closest('tr');
            let row = table.row(tr);
            let rowData = row.data();

            let items = JSON.parse(rowData[13]); // ← getting items from hidden column

            if (row.child.isShown()) {
                row.child.hide();
                $(this).text('➕');
                return;
            }

            let html = `
        <table class="table table-bordered mt-2">
            <thead class="table-light">
                <tr>
                    <th>Fee Type (Head)</th>
                    <th>Fine</th>
                    <th>Discount</th>
                    <th>Paid Amount</th>
                </tr>
            </thead>
            <tbody>
    `;

            items.forEach(f => {
                html += `
            <tr>
                <td>${f.fee_name}</td>
                <td>${f.fine.toFixed(2)}</td>
                <td>${f.discount.toFixed(2)}</td>
                <td>₹${f.paid_amount.toFixed(2)}</td>
            </tr>`;
            });

            html += `</tbody></table>`;

            row.child(html).show();
            $(this).text('➖');
        });



        // ✅ Fetch and Display Data
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            let from_date = $('#from_date').val();
            let to_date = $('#to_date').val();
            let branch_id = "<?= $branch_id ?>";
             let feeTypes = $('#feeTypes').val();

            if (from_date === '' || to_date === '' || branch_id === '') {
                alert('Please select Branch, From Date, and To Date.');
                return;
            }

            $('#resultArea p').hide();

            $.ajax({
                url: "<?= base_url('fees/fetch_paid_students') ?>",
                type: "POST",
                data: {
                    from_date,
                    to_date,
                    feeTypes,
                    branch_id: "<?= $branch_id ?>"
                },
                dataType: "json",
                success: function(response) {

                    table.clear().draw();
                    globalResponse = response.data || [];
                    let grandTotal = 0;

                    if (globalResponse.length > 0) {

                        globalResponse.forEach((row, index) => {

                            let receiptCount = row.fee_count; // because fee_count tells if expandable
                            let toggleIcon = row.item_count > 1 ? `<span class="toggle-details" style="cursor:pointer;font-size:18px;">➕</span>` : '';
                  
                            let paidTotal = parseFloat(row.total_paid_sum);
                            grandTotal += paidTotal;

                            table.row.add([
                                toggleIcon,
                                index + 1,
                                row.date.replace(/\n/g, "<br>"),
                                row.receipt_no.replace(/\n/g, "<br>"),
                                row.register_no ?? '',
                                row.first_name + ' ' + row.last_name,
                                row.class_name + ' (' + row.section_name + ')',
                                row.roll_no,
                                row.fee_name.replace(/\n/g, "<br>"),
                                row.payment_method.replace(/\n/g, "<br>"),
                                parseFloat(row.total_fine_sum).toFixed(2),
                                parseFloat(row.total_discount_sum).toFixed(2),
                                '₹' + paidTotal.toFixed(2),

                                row.items,
                                row.item_count
                            ]);

                        });

                        table.draw();
                        $('#feesTable tfoot td:last').html('Total: ₹' + grandTotal.toFixed(2));
                    } else {
                        table.clear().draw();
                        $('#feesTable tbody').html('<tr><td colspan="13" class="text-center text-danger">No records found.</td></tr>');
                        $('#feesTable tfoot td:last').html('Total: ₹0.00');
                    }
                },
                error: function() {
                    alert('Something went wrong.');
                }
            });

        });

    });
</script>





<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> -->
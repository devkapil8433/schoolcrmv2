<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
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
    if (is_superadmin_loggedin()) :
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
                                <!-- <div class="col-md-3">
                                    <label class="form-label fw-semibold">Date</label>
                                    <input type="date" name="as_of" id="as_of" class="form-control shadow-sm">
                                </div> -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Select Month</label>
                                    <select name="month[]" id="month" class="form-control shadow-sm" multiple>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Select Class</label>
                                    <select name="class_id" id="class_id" class="form-control form-control-lg shadow-sm"
                                        onchange="getSectionByClass(this.value)">
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>"><?= $class['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="error"></span>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Select Section</label>
                                    <select name="section_id[]" id="section_id" class="form-control form-control-lg shadow-sm">
                                        <option value="">-- Select Class First --</option>
                                    </select>
                                    <span class="error"></span>
                                </div>
                                <div class="col-md-3">
                                    <label>Fees Type <span class="required">*</span></label>
                                    <select class="form-control" name="fee_types[]" id="feeTypes" multiple>
                                        <option value="">Select Fee Types</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Min Due Amount</label>
                                    <input type="number" name="min_due" id="min_due" class="form-control shadow-sm" placeholder="e.g. 500">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Max Due Amount</label>
                                    <input type="number" name="max_due" id="max_due" class="form-control shadow-sm" placeholder="e.g. 2000">
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm" style="margin-top:26px;">
                                        <i class="fa fa-search me-2"></i> Fetch Defaulters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Results Area -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-body">
                        <div id="resultArea" class="mt-3 text-center text-muted">
                            <h4 id="monthHeading" class="fw-bold mt-3" style="display:none; color:red;text-align:left;"></h4>
                            <div class="table-responsive mt-4">
                                <div class="mb-2">
                                    <button id="sendMessageBtn" class="btn btn-warning btn-sm">
                                        <i class="fas fa-paper-plane"></i> Send Message
                                    </button>
                                </div>

                                <table id="feesTable" class="table table-bordered table-export w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th><input type="checkbox" id="checkAll"></th>
                                            <th>Sr.no</th>
                                            <th>Register No</th>
                                            <th>Student Name</th>
                                            <th>Roll No</th>
                                            <th>Class & Section</th>
                                            <th>Fee Type(Head)</th>
                                            <th>Assigned Amount</th>
                                            <th>Fine</th>
                                            <th>Paid Amount</th>
                                            <th>Outstanding(Due)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-footer-totals">
                                            <td colspan="7" class="text-end">Totals:</td>
                                            <td id="totalFee">0.00</td>
                                            <td id="totalFine">0.00</td>
                                            <td id="totalPaid">0.00</td>
                                            <td id="totalDue">0.00</td>
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
endif;
?>


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
        white-space: pre-line;
        /* Allows \n to show as line breaks */
        vertical-align: top;
        /* Aligns text to top of the cell */
        padding: 6px 10px;
    }

    #feesTable tr:hover {
        background-color: #f8fbff;
    }

    /* Table Footer */
    #feesTable tfoot td {
        font-weight: 600;
        background-color: #f8f9fa;
        color: #fe0404;
        font-size: 25px;
    }

    /* Messages */
    #resultArea p {
        font-size: 15px;
    }

    .text-danger,
    .text-info {
        font-weight: 500;
    }

    .table-footer-totals {
        background: #f9f2d9 !important;
        /* Light gold */
        font-size: 15px;
        font-weight: 700;
        height: 50px;
    }

    .table-footer-totals td {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
        vertical-align: middle !important;
    }

    .toggle-fee-details {
        color: #0d6efd;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }

    .toggle-fee-details:hover {
        text-decoration: underline;
    }

    .fee-breakdown {
        display: none;
        margin-top: 6px;
        line-height: 1.6;
    }

    .fee-row-total {
        font-weight: bold;
        display: block;
    }
</style>


<script>
    $('#checkAll').on('click', function() {
        $('.student-check').prop('checked', this.checked);
    });
</script>
<script>
    $(document).on('click', '#sendMessageBtn', function() {
        let selected = [];
        $('.student-check:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert("Please select at least one student.");
            return;
        }

        console.log("Selected Student IDs:", selected);
        alert("we will send a message to their parents to notify about due fee : " + selected.join(', '));
    });

    $(document).on('click', '.toggle-fee-details', function(e) {
        e.preventDefault();
        const rowId = $(this).data('row');
        const $details = $('.' + rowId);
        const isVisible = $details.first().is(':visible');
        $details.toggle(!isVisible);
        $(this).text(isVisible ? 'Click to view more' : 'Click to hide');
    });
</script>

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
        $('#month').select2({
            placeholder: "Select one or more Months",
            allowClear: true,
            width: '100%'
        });

        // Set today's date in the "as_of" field
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        $('#as_of').val(`${yyyy}-${mm}-${dd}`);

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

        function getMonthName(monthValue) {
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            return months[parseInt(monthValue) - 1];
        }



        // ✅ Handle form submit and refresh table data without reinitializing DataTable
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            // let as_of = $('#as_of').val();
            let month = $('#month').val();
            let class_id = $('#class_id').val();
            let section_id = $('#section_id').val();
            let feeTypes = $('#feeTypes').val();
            let min_due = $('#min_due').val();
            let max_due = $('#max_due').val();

            let hasMonth = Array.isArray(month) ? month.length > 0 : !!month;
            if (!hasMonth && (!class_id || !section_id)) {
                alert('Please select a month, or both class and section.');
                return;
            }

            $('#feesTable tbody').html('<tr><td colspan="10" class="text-center"><i class="fa fa-spinner fa-spin me-2"></i>Loading, please wait...</td></tr>');

            $.ajax({
                url: "<?= base_url('fees/fetch_defaulters') ?>",
                type: "POST",
                data: {
                    month,
                    class_id,
                    section_id,
                    feeTypes,
                    min_due,
                    max_due
                },
                dataType: "json",
                success: function(response) {
                    // ✅ Show Heading
                    let selectedMonths = $('#month').val();

                    if (selectedMonths && selectedMonths.length > 0) {

                        let monthNames = selectedMonths.map(m => getMonthName(m)).join(", ");

                        $('#monthHeading')
                            .html(`Dues List for the Month(s): <span style="color:red; font-weight:bold;">${monthNames}</span>`)
                            .show();

                    } else {
                        $('#monthHeading').hide();
                    }

                    table.clear();

                    let total_fee = 0,
                        total_paid = 0,
                        total_due = 0;
                        let total_fine = 0;

                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(index, row) {

                            // Split Fee Names + Values (All 3 columns must align)
                            let feeNames = row.fee_name.toString().split(/\n|,/).map(x => x.trim()).filter(x => x !== "");
                            let feesArr = row.total_fee.toString().split(/\n|,/).map(x => parseFloat(x.trim() || 0));
                            let paidArr = row.total_paid.toString().split(/\n|,/).map(x => parseFloat(x.trim() || 0));
                            let dueArr = row.outstanding.toString().split(/\n|,/).map(x => parseFloat(x.trim() || 0));
                            let fineArr = row.fine.toString().split(/\n|,/).map(x => parseFloat(x.trim() || 0));

                            // ✅ Group by Fee Name
                            let grouped = {};

                            feeNames.forEach((name, i) => {
                                if (!grouped[name]) {
                                    grouped[name] = {
                                        fee: 0,
                                        paid: 0,
                                        due: 0,
                                        fine:0
                                    };
                                }
                                grouped[name].fee += feesArr[i] || 0;
                                grouped[name].paid += paidArr[i] || 0;
                                grouped[name].due += dueArr[i] || 0;
                                grouped[name].fine += fineArr[i] || 0;
                            });

                            // ✅ Totals for each row
                            let feeTotal = Object.values(grouped).reduce((s, v) => s + v.fee, 0);
                            let paidTotal = Object.values(grouped).reduce((s, v) => s + v.paid, 0);
                            let dueTotal = Object.values(grouped).reduce((s, v) => s + v.due, 0);
                            let fineTotal = Object.values(grouped).reduce((s, v) => s + v.fine, 0);

                            // Collapsed by default: totals only; expand via "Click to view more"
                            let rowId = 'fee-detail-' + index;
                            let feeNameDetail = Object.keys(grouped).join("<br>");
                            let feeDetail = Object.values(grouped).map(v => `₹${v.fee.toFixed(2)}`).join("<br>");
                            let paidDetail = Object.values(grouped).map(v => `₹${v.paid.toFixed(2)}`).join("<br>");
                            let dueDetail = Object.values(grouped).map(v => `₹${v.due.toFixed(2)}`).join("<br>");
                            let fineDetail = Object.values(grouped).map(v => `₹${v.fine.toFixed(2)}`).join("<br>");

                            let feeNameDisplay = `<a href="javascript:void(0)" class="toggle-fee-details" data-row="${rowId}">Click to view more</a>
                                <div class="fee-breakdown ${rowId}">${feeNameDetail}</div>`;
                            let feeDisplay = `<span class="fee-row-total" style="color:#007bff;">₹${feeTotal.toFixed(2)}</span>
                                <div class="fee-breakdown ${rowId}">${feeDetail}</div>`;
                            let fineDisplay = `<span class="fee-row-total" style="color:#ff9800;">₹${fineTotal.toFixed(2)}</span>
                                <div class="fee-breakdown ${rowId}">${fineDetail}</div>`;
                            let paidDisplay = `<span class="fee-row-total" style="color:#28a745;">₹${paidTotal.toFixed(2)}</span>
                                <div class="fee-breakdown ${rowId}">${paidDetail}</div>`;
                            let dueDisplay = `<span class="fee-row-total" style="color:#dc3545;">₹${dueTotal.toFixed(2)}</span>
                                <div class="fee-breakdown ${rowId}">${dueDetail}</div>`;

                            // ✅ **Add to GRAND TOTALS here**
                            total_fee += feeTotal;
                            total_paid += paidTotal;
                            total_due += dueTotal;
                            total_fine += fineTotal;

                            // ✅ Add to DataTable
                            table.row.add([
                                '<input type="checkbox" class="student-check" value="' + row.student_id + '">',
                                index + 1,
                                row.register_no,
                                row.first_name + ' ' + row.last_name,
                                row.roll_no,
                                row.class_name + ' (' + row.section_name + ')',
                                feeNameDisplay,
                                feeDisplay,
                                fineDisplay,
                                paidDisplay,
                                dueDisplay
                            ]);

                        });



                        // ✅ Draw table after adding all rows
                        table.draw();

                        $('#totalFee').text('₹' + total_fee.toFixed(2));
                        $('#totalFine').text('₹' + total_fine.toFixed(2));
                        $('#totalPaid').text('₹' + total_paid.toFixed(2));
                        $('#totalDue').text('₹' + total_due.toFixed(2));

                    } else {
                        table.clear().draw();
                        $('#feesTable tbody').html('<tr><td colspan="10" class="text-center text-danger">No defaulters found for selected filter.</td></tr>');
                        $('#totalFee, #totalPaid, #totalDue').text('₹0.00');
                    }
                },
                error: function() {
                    $('#feesTable tbody').html('<tr><td colspan="10" class="text-center text-danger">Something went wrong while fetching data.</td></tr>');
                }
            });

        });
    });
</script>
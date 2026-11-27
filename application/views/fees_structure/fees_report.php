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
    <section class="panel fee-collection-filter">
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

                                <div class="col-md-4">
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

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Select Section</label>
                                    <select name="section_id" id="section_id" class="form-control form-control-lg shadow-sm">
                                        <option value="">-- Select Class First --</option>
                                    </select>
                                    <span class="error"></span>
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 shadow-sm" style="margin-top:26px;">
                                        <i class="fa fa-search me-2"></i> Fetch Fees Report
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

                                <table id="feesTable" class="table table-bordered table-export w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th><input type="checkbox" id="checkAll"></th>
                                            <th>Sr.no</th>
                                            <th>Register No</th>
                                            <th>Student Name</th>
                                            <th>Class & Section</th>
                                            <th>Last Amount</th>
                                            <th>Books Amount</th>
                                            <th>Annual Charges</th>
                                            <th class="month-col" data-month="4">April</th>
                                            <th class="month-col" data-month="5">May</th>
                                            <th class="month-col" data-month="6">June</th>
                                            <th class="month-col" data-month="7">July</th>
                                            <th class="month-col" data-month="8">August</th>
                                            <th class="month-col" data-month="9">September</th>
                                            <th class="month-col" data-month="10">October</th>
                                            <th class="month-col" data-month="11">November</th>
                                            <th class="month-col" data-month="12">December</th>
                                            <th class="month-col" data-month="1">January</th>
                                            <th class="month-col" data-month="2">February</th>
                                            <th class="month-col" data-month="3">March</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-footer-totals">
                                            <td colspan="6" class="text-end">Totals:</td>
                                            <td id="totalFee">0.00</td>
                                            <td id="totalFine">0.00</td>
                                            <td id="totalPaid">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
                                            <td id="totalDue">0.00</td>
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
    .month-blurred {
        filter: blur(3px);
        opacity: 0.4;
        color: #999 !important;
        user-select: none;
        pointer-events: none;
    }

    th.month-blurred {
        background-color: #f0f0f0 !important;
    }

    td.month-blurred {
        background-color: #fafafa;
    }
</style>

<script>
(function () {
    // Academic year: April → March. Active = April through current month.
    var currentMonth = <?= (int) date('n') ?>;
    var academicMonths = [
        { key: 'april', num: 4 },
        { key: 'may', num: 5 },
        { key: 'june', num: 6 },
        { key: 'july', num: 7 },
        { key: 'august', num: 8 },
        { key: 'september', num: 9 },
        { key: 'october', num: 10 },
        { key: 'november', num: 11 },
        { key: 'december', num: 12 },
        { key: 'january', num: 1 },
        { key: 'february', num: 2 },
        { key: 'march', num: 3 }
    ];

    function academicIndex(monthNum) {
        return monthNum >= 4 ? monthNum - 4 : monthNum + 8;
    }

    function isActiveMonth(monthNum) {
        return academicIndex(monthNum) <= academicIndex(currentMonth);
    }

    function formatMonthValue(value, monthNum) {
        return parseFloat(value || 0).toFixed(2);
    }

    // Blur future month headers
    $('#feesTable thead th.month-col').each(function () {
        var monthNum = parseInt($(this).data('month'), 10);
        if (!isActiveMonth(monthNum)) {
            $(this).addClass('month-blurred');
        }
    });

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url('fees/fetch_fees_report') ?>",
            type: "POST",
            data: $(this).serialize() + '&<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>',
            dataType: "json",
            success: function (response) {
                var table = $('#feesTable').DataTable();
                table.clear();

                $.each(response, function (index, row) {
                    var monthCells = academicMonths.map(function (m) {
                        return formatMonthValue(row[m.key], m.num);
                    });

                    table.row.add([
                        '<input type="checkbox" class="row-check">',
                        index + 1,
                        row.register_no,
                        row.student_name,
                        row.class_name + ' (' + row.section_name + ')',
                        parseFloat(row.last_amount || 0).toFixed(2),
                        parseFloat(row.books_amount || 0).toFixed(2),
                        parseFloat(row.annual_charges || 0).toFixed(2)
                    ].concat(monthCells));
                });
                table.draw();

                // Blur months after current academic month (columns start at index 8)
                $('#feesTable tbody tr').each(function () {
                    $(this).find('td').each(function (colIndex) {
                        if (colIndex < 8) return;
                        var monthMeta = academicMonths[colIndex - 8];
                        if (monthMeta && !isActiveMonth(monthMeta.num)) {
                            $(this).addClass('month-blurred');
                        }
                    });
                });
            },
            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });
    });
})();
</script>
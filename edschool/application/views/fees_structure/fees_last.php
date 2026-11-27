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
                                        <i class="fa fa-search me-2"></i> Fetch Fees
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
                                            <th>Father Name</th>
                                            <th>Last Balance</th>
                                            <th>Current Balance</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-footer-totals">
                                            <!-- <td colspan="6" class="text-end">Totals:</td> -->
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
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

<!-- <script>
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url('fees/fetch_fees_last') ?>",
            type: "POST",
            data: $(this).serialize() + '&<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>',
            dataType: "json",
            success: function(response) {
                var table = $('#feesTable').DataTable();
                table.clear();

                $.each(response, function(index, row) {
                    table.row.add([
                        '<input type="checkbox" class="row-check">',
                        index + 1,
                        row.register_no,
                        row.student_name,
                        row.class_name + ' (' + row.section_name + ')',
                        row.father_name, '<input type="text" class="form-control last-balance-input" data-student="' + row.student_id + '" value="' + (row.last_balance ?? '') + '" />', row.current_balance,
                        '', '', ''
                    ]);
                });
                table.draw();
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
</script> -->
<script>
$('#filterForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: "<?= base_url('fees/fetch_fees_last') ?>",
        type: "POST",
        data: $(this).serialize() + '&<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>',
        dataType: "json",
        success: function(response) {

            var table = $('#feesTable').DataTable();
            table.clear();

            $.each(response, function(index, row) {
                table.row.add([
                    '<input type="checkbox" class="row-check">',
                    index + 1,
                    row.register_no,
                    row.student_name,
                    row.class_name + ' (' + row.section_name + ')',
                    row.father_name,

                    '<input type="text" class="form-control last-balance-input" ' +
                        'id="last_balance_' + row.student_id + '" ' +
                        'data-student="' + row.student_id + '" ' +
                        'value="' + (row.last_balance ?? '') + '" />',

                    row.current_balance,

                    '<button class="btn btn-sm btn-primary save-last-balance" data-student="' + row.student_id + '">Save</button>',

                    '', ''
                ]);
            });

            table.draw();
        },
        error: function(xhr) {
            console.log(xhr.responseText);
        }
    });
});


// -----------------------------------------
// SAVE BUTTON CLICK EVENT (AJAX)
// -----------------------------------------
$(document).on('click', '.save-last-balance', function () {
    var student_id = $(this).data('student');
    var balance = $('#last_balance_' + student_id).val();

    $.ajax({
        url: "<?= base_url('fees/save_last_balance') ?>",
        type: "POST",
        data: {
            student_id: student_id,
            balance: balance,
            '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
        },
        dataType: "json",
        success: function(res) {
            if (res.status) {
                alert('Last balance saved successfully!');
            } else {
                alert('Failed to save balance');
            }
        }
    });
});
</script>

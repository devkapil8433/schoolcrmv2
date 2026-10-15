<!-- <pre>
<?php print_r($classSetAmounts); ?>
</pre> -->
<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/media/css/dataTables.bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/buttons.dataTables.min.css') ?>">
<script src="<?= asset_url('assets/vendor/datatables/media/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/media/js/dataTables.bootstrap.min.js') ?>"></script>

<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.colVis.min.js') ?>"></script>

<style>
    .fee-structure-table th {
        background: #f5f7fa;
        text-align: center;
        font-weight: 600;
    }

    .fee-structure-table td {
        text-align: center;
        vertical-align: middle;
        height: 45px;
        font-size: 14px;
    }

    .fee-structure-table td:first-child {
        font-weight: 600;
        /* text-align: left; */
        padding-left: 12px;
    }
</style>


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
<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">Fee Estimation Report</h4>
    </header>

    <div class="panel-body">

        <!-- Fee Structure Table -->
        <div class="table-responsive">
            <table class="table table-bordered fee-structure-table" id="reportTable">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Last Balance</th>
                        <?php foreach ($fee_types as $ft): ?>
                            <th><?= $ft['fee_name'] ?></th>
                        <?php endforeach; ?>
                        <th>Transport Fee</th>
                        <?php foreach ($sets as $set): ?>
                            <th><?= $set['set_name'] ?></th>
                        <?php endforeach; ?>

                        <th>Total Assigned</th>
                        <th>Total Recieved</th>
                        <th>Remaining Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $columnTotals   = [];
                    $grandAssigned  = 0;
                    $grandReceived  = 0;
                    $grandRemaining = 0;
                    ?>

                    <?php foreach ($classes as $class): ?>
                        <tr data-class="<?= $class['id'] ?>">
                            <td><strong><?= $class['name'] ?></strong></td>

                            <?php
                            $classFees     = $fee_structure[$class['id']] ?? [];
                            $rowTotal      = 0;
                            $materialTotal = 0;

                            $lastBalance = $class_last_balance[$class['id']] ?? 0;
                            $columnTotals['last_balance'] =
                                ($columnTotals['last_balance'] ?? 0) + $lastBalance;
                            ?>

                            <!-- LAST BALANCE -->
                            <td><strong><?= number_format($lastBalance, 2) ?></strong></td>

                            <!-- NORMAL FEES -->
                            <?php foreach ($fee_types as $ft): ?>
                                <?php
                                $key = strtolower($ft['fee_name']);
                                $key = preg_replace('/\s+/', '_', $key);
                                $key = preg_replace('/_+/', '_', $key);
                                $key = trim($key, '_');

                                $amount = $classFees[$key] ?? '-';

                                if ($amount !== '-') {
                                    $rowTotal += $amount;
                                    $columnTotals[$key] = ($columnTotals[$key] ?? 0) + $amount;
                                }
                                ?>
                                <td><?= $amount ?></td>
                            <?php endforeach; ?>

                            <!-- TRANSPORT -->
                            <?php
                            $transportFee = $transportFees[$class['id']] ?? '-';

                            if ($transportFee !== '-') {
                                $rowTotal += $transportFee;
                                $columnTotals['transport_fee'] =
                                    ($columnTotals['transport_fee'] ?? 0) + $transportFee;
                            }
                            ?>
                            <td><?= $transportFee ?></td>

                            <!-- MATERIAL (SET-WISE) -->
                            <?php foreach ($sets as $set): ?>
                                <?php
                                $setAmount = $classSetAmounts[$class['id']][$set['id']] ?? '-';

                                if ($setAmount !== '-') {
                                    $rowTotal      += $setAmount;
                                    $materialTotal += $setAmount;

                                    $columnTotals['set_' . $set['id']] =
                                        ($columnTotals['set_' . $set['id']] ?? 0) + $setAmount;
                                }
                                ?>
                                <td><?= $setAmount !== '-' ? number_format($setAmount, 2) : '-' ?></td>
                            <?php endforeach; ?>

                            <!-- ASSIGNED / RECEIVED / REMAINING -->
                            <?php
                            $assigned = ($class_assigned[$class['id']] ?? 0)
                                + $materialTotal
                                + $lastBalance;

                            $received  = $class_received[$class['id']] ?? 0;
                            $remaining = $assigned - $received;

                            $grandAssigned  += $assigned;
                            $grandReceived  += $received;
                            $grandRemaining += $remaining;
                            ?>

                            <td><strong><?= number_format($assigned, 2) ?></strong></td>
                            <td><strong><?= number_format($received, 2) ?></strong></td>
                            <td>
                                <strong style="color:<?= $remaining > 0 ? 'red' : 'green' ?>">
                                    <?= number_format($remaining, 2) ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- 🔥 GRAND TOTAL -->
                    <tr style="background:#f5f7fa;">
                        <td style="font-size:17px;font-weight:800;">GRAND TOTAL</td>

                        <!-- LAST BALANCE TOTAL -->
                        <td style="font-weight:800;">
                            <?= number_format($columnTotals['last_balance'] ?? 0, 2) ?>
                        </td>

                        <!-- NORMAL FEE TOTALS -->
                        <?php foreach ($fee_types as $ft): ?>
                            <?php
                            $key = strtolower($ft['fee_name']);
                            $key = preg_replace('/\s+/', '_', $key);
                            $key = preg_replace('/_+/', '_', $key);
                            $key = trim($key, '_');
                            ?>
                            <td style="font-weight:700;">
                                <?= number_format($columnTotals[$key] ?? 0, 2) ?>
                            </td>
                        <?php endforeach; ?>

                        <!-- TRANSPORT TOTAL -->
                        <td style="font-weight:700;">
                            <?= number_format($columnTotals['transport_fee'] ?? 0, 2) ?>
                        </td>

                        <!-- MATERIAL SET TOTALS -->
                        <?php foreach ($sets as $set): ?>
                            <td style="font-weight:700;">
                                <?= number_format($columnTotals['set_' . $set['id']] ?? 0, 2) ?>
                            </td>
                        <?php endforeach; ?>

                        <!-- FINAL TOTALS -->
                        <td style="font-size:18px;font-weight:800;">
                            <?= number_format($grandAssigned, 2) ?>
                        </td>
                        <td style="font-size:18px;font-weight:800;">
                            <?= number_format($grandReceived, 2) ?>
                        </td>
                        <td style="font-size:18px;font-weight:800;color:red;">
                            <?= number_format($grandRemaining, 2) ?>
                        </td>
                    </tr>
                </tbody>



            </table>
        </div>

    </div>
</section>
<?php
    endif;
endif;
?>
<script>
    $(document).ready(function() {

        var table = $('#reportTable').DataTable({
            dom: '<"row mb-2"' +
                '<"col-md-6"B>' +
                '<"col-md-6 text-right"f>' +
                '>' +
                'rt' +
                '<"row"' +
                '<"col-md-6"i>' +
                '<"col-md-6"p>' +
                '>',
            buttons: [
                
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy"></i>',
                    className: 'btn btn-default btn-sm'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i>',
                    titleAttr: 'Export Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i>',
                    titleAttr: 'Export PDF',
                    className: 'btn btn-danger btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Fee Estimation Report'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    titleAttr: 'Print',
                    className: 'btn btn-primary btn-sm'
                },
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-columns"></i>',
                    titleAttr: 'Column Visibility',
                    className: 'btn btn-default btn-sm'
                }
            ],
            pageLength: 25,
            ordering: true,
            order: [],
            searching: true,
            autoWidth: false
        });

        // ❌ reinitialize kabhi mat karna
        window.defaulterTable = table;
    });
</script>
<!-- <pre>
<?php print_r($fee_structure); ?>
</pre> -->

<style>
    .fee-structure-table th {
        background: #f5f7fa;
        text-align: center;
        font-weight: 600;
    }

    .fee-structure-table td {
        text-align: center;
        vertical-align: middle;
        /* height: 45px; */
        font-size: 14px;
    }

    .fee-structure-table td:first-child {
        font-weight: 600;
        text-align: left;
        padding-left: 12px;
    }

    .rotate {
        transform: rotate(180deg);
        transition: 0.2s ease;
    }
</style>

<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/media/css/dataTables.bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/buttons.dataTables.min.css') ?>">
<script src="<?= asset_url('assets/vendor/datatables/media/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/media/js/dataTables.bootstrap.min.js') ?>"></script>

<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.colVis.min.js') ?>"></script>


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
        <h4 class="panel-title">Fee Structure</h4>
    </header>

    <div class="panel-body">

        <!-- Payment Type -->
        <div class="mb-md">
            <label class="mr-md">
                <input type="radio" name="fee_type" value="annually"> Yearly
            </label>
            <label class="mr-md">
                <input type="radio" name="fee_type" value="half-yearly"> Half Yearly
            </label>
            <label class="mr-md">
                <input type="radio" name="fee_type" value="quarterly"> Quarterly
            </label>
            <label>
                <input type="radio" name="fee_type" value="monthly" checked> Monthly
            </label>
        </div>


        <!-- Fee Structure Table -->
        <div class="table-responsive">
            <table class="table table-bordered fee-structure-table" id="structureTable">
                <thead>
                    <tr>
                        <th>Class</th>
                        <?php foreach ($fee_types as $ft): ?>
                            <th><?= $ft['fee_name'] ?></th>
                        <?php endforeach; ?>
                        <th>Transport Fee</th>
                        <?php foreach ($sets as $set): ?>
                            <th class="set-toggle"
                                data-set-id="<?= $set['id'] ?>"
                                style="cursor:pointer;">
                                <?= $set['set_name'] ?>
                                <i class="fa fa-chevron-down ml-1"></i>
                            </th>
                        <?php endforeach; ?>

                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // initialize column totals
                    $columnTotals = [];
                    $grandTotalAll = 0;
                    ?>

                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('fees_structure/class_students/' . $class['id']) ?>">
                                    <?= $class['name'] ?>
                                </a>
                            </td>

                            <?php
                            $classFees = $fee_structure[$class['id']] ?? [];
                            $rowTotal = 0;
                            ?>

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

                                <td class="fee-cell"
                                    <?php if (is_numeric($amount)): ?>
                                    data-base="<?= $amount ?>"
                                    <?php endif; ?>>
                                    <?= $amount ?>
                                </td>


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


                            <!-- MATERIAL -->
                            <?php foreach ($sets as $set): ?>

                                <?php
                                $key       = 'set_' . $set['id'];
                                $setAmount = $classSetTotals[$class['id']][$key]['amount'] ?? 0;

                                if ($setAmount > 0) {
                                    $columnTotals[$key] = ($columnTotals[$key] ?? 0) + $setAmount;
                                    $rowTotal += $setAmount;
                                }
                                ?>

                                <td class="set-cell" data-set-id="<?= $set['id'] ?>">

                                    <?php if ($setAmount > 0): ?>

                                        <!-- Visible total -->
                                        <div class="set-total fee-cell">
                                            <?= number_format($setAmount, 2) ?>
                                        </div>

                                        <!-- Products ONLY when amount exists -->
                                        <div class="set-products" style="display:none;font-size:12px;color:#555;">
                                            <?php foreach ($setProducts[$set['id']] ?? [] as $item): ?>
                                                <div>
                                                    <?= $item['name'] ?> = <?= number_format($item['amount'], 2) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?>

                                </td>

                            <?php endforeach; ?>



                            <!-- ROW TOTAL -->
                            <td><strong class="row-total"><?= $rowTotal ?></strong></td>
                        </tr>

                        <?php $grandTotalAll += $rowTotal; ?>

                    <?php endforeach; ?>

                    <!-- 🔥 GRAND TOTAL ROW -->
                    <!-- <tr style="background:#f5f7fa;">
                        <td style="font-size:16px;font-weight:700;">GRAND TOTAL</td>

                        <?php foreach ($fee_types as $ft): ?>
                            <?php
                            $key = strtolower($ft['fee_name']);
                            $key = preg_replace('/\s+/', '_', $key);
                            $key = preg_replace('/_+/', '_', $key);
                            $key = trim($key, '_');
                            ?>
                            <td style="font-size:16px;font-weight:700;">
                                <?= $columnTotals[$key] ?? '-' ?>
                            </td>
                        <?php endforeach; ?>

                        <td style="font-size:16px;font-weight:700;">
                            <?= $columnTotals['transport_fee'] ?? '-' ?>
                        </td>

                        <?php foreach ($sets as $set): ?>
                            <?php $key = 'set_' . $set['id']; ?>
                            <td style="font-size:16px;font-weight:700;">
                                <?= $columnTotals[$key] ?? '-' ?>
                            </td>
                        <?php endforeach; ?>
                        <td style="font-size:18px;font-weight:800;color:#000;">
                            <?= $grandTotalAll ?>
                        </td>
                    </tr> -->

                </tbody>





            </table>
        </div>

    </div>
</section>
<?php endif;
endif; ?>
<script>
    $(document).ready(function() {

        const multiplierMap = {
            'monthly': 1,
            'quarterly': 3,
            'half-yearly': 6,
            'annually': 12
        };

        $('input[name="fee_type"]').on('change', function() {
            recalculate($(this).val());
        });

        function recalculate(type) {

            let multiplier = multiplierMap[type] || 1;

            $('tbody tr').each(function() {

                let rowTotal = 0;

                // normal + transport fees
                $(this).find('td.fee-cell').each(function() {

                    if (!$(this).data('base')) return;

                    let base = parseFloat($(this).data('base'));
                    let value = base * multiplier;

                    $(this).text(value);
                    rowTotal += value;
                });

                // set fees
                $(this).find('.set-fee').each(function() {

                    let base = parseFloat($(this).data('base')) || 0;
                    let value = base * multiplier;

                    $(this).text(value.toFixed(2));
                    rowTotal += value;
                });

                $(this).find('.row-total').text(rowTotal.toFixed(2));
            });
        }

        // default load
        recalculate('monthly');

    });
</script>


<!-- <script>
document.querySelectorAll('input[name="fee_type"]').forEach(radio => {
    radio.addEventListener('change', function () {
        recalculateFees(this.value);
    });
});

function recalculateFees(selectedFrequency) {

    const multiplierMap = {
        'monthly': 1,
        'quarterly': 3,
        'half-yearly': 6,
        'annually': 12
    };

    document.querySelectorAll('tbody tr').forEach(row => {

        let rowTotal = 0;

        row.querySelectorAll('.fee-cell').forEach(cell => {

            let base = parseFloat(cell.dataset.base) || 0;
            let feeFrequency = cell.dataset.frequency;

            let amount = base;

            // ✔ multiply only if frequency matches
            if (feeFrequency === selectedFrequency) {
                amount = base * multiplierMap[selectedFrequency];
            }

            cell.innerText = amount > 0 ? amount.toFixed(2) : '-';
            rowTotal += amount;
        });

        // update total column
        let totalCell = row.querySelector('.total-cell strong');
        if (totalCell) {
            totalCell.innerText = rowTotal > 0 ? rowTotal.toFixed(2) : '-';
        }
    });
}
</script> -->
<script>
    $(document).ready(function() {

        $(document).on('click', '.set-toggle', function() {

            let setId = $(this).data('set-id');

            // Toggle products inside SAME column for ALL rows
            $('.set-cell[data-set-id="' + setId + '"]')
                .find('.set-products')
                .slideToggle(150);

            // Optional: rotate arrow
            $(this).find('i').toggleClass('rotate');

        });

    });
</script>
<script>
    $(document).ready(function() {

        var table = $('#structureTable').DataTable({
            dom: '<"row mb-2"' +
                '<"col-md-6"B>' +
                '<"col-md-6 text-right"f>' +
                '>' +
                'rt' +
                '<"row"' +
                '<"col-md-6"i>' +
                '<"col-md-6"p>' +
                '>',
            buttons: [{
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
                    title: 'Fee Structure Report'
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
        window.defaulterTable = table;
    });
</script>
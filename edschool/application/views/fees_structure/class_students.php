<!-- <pre>
<?php print_r($fee_types); ?>
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


<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">Fee Structure</h4>
    </header>

    <div class="panel-body">


        <!-- Fee Structure Table -->
        <div class="table-responsive">
            <table class="table table-bordered fee-structure-table" id="structureTable">
                <thead>
                    <tr>
                        <th>Student Name</th>

                        <?php foreach ($fee_types as $ft): ?>
                            <?php
                            $feeKey = strtolower($ft['fee_name']);
                            $feeKey = preg_replace('/\s+/', '_', $feeKey);
                            $feeKey = trim($feeKey, '_');
                            ?>
                            <th class="fee-toggle" data-fee-key="<?= $feeKey ?>">
                                <?= $ft['fee_name'] ?>
                                <i class="fa fa-chevron-down ml-1"></i>
                            </th>
                        <?php endforeach; ?>

                        <th>Transport Fee</th>

                        <?php foreach ($groupedSets as $setName => $setGroup): ?>
                            <th><?= $setName ?></th>
                        <?php endforeach; ?>

                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>

                        <tr>
                            <td><?= $student['first_name'] . ' ' . $student['last_name'] ?></td>

                            <?php
                            // SAME class fees
                            $classFees = $fee_structure[$student['class_id']] ?? [];
                            $rowTotal = 0;
                            ?>

                            <?php foreach ($fee_types as $ft): ?>

                                <?php
                                $monthNames = [
                                    1 => 'Jan',
                                    2 => 'Feb',
                                    3 => 'Mar',
                                    4 => 'Apr',
                                    5 => 'May',
                                    6 => 'Jun',
                                    7 => 'Jul',
                                    8 => 'Aug',
                                    9 => 'Sep',
                                    10 => 'Oct',
                                    11 => 'Nov',
                                    12 => 'Dec'
                                ];

                                $feeKey = strtolower($ft['fee_name']);
                                $feeKey = preg_replace('/\s+/', '_', $feeKey);
                                $feeKey = trim($feeKey, '_');

                                $amount = $classFees[$feeKey] ?? '-';

                                if ($amount !== '-') {
                                    $rowTotal += $amount;
                                }

                                // MONTH TEXT
                                $monthsText = '-';
                                if (!empty($ft['month'])) {
                                    $monthsArr = array_map('intval', explode(',', $ft['month']));
                                    $monthsText = count($monthsArr) === 12
                                        ? 'All Months'
                                        : implode(', ', array_map(fn($m) => $monthNames[$m], $monthsArr));
                                }
                                ?>

                                <td class="fee-cell" data-fee-key="<?= $feeKey ?>">
                                    <?php if (is_numeric($amount)): ?>
                                        <div class="fee-total"><?= number_format($amount, 2) ?></div>
                                        <div class="fee-months" style="display:none;font-size:12px;color:green;">
                                            <?= $monthsText ?>
                                        </div>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                            <?php endforeach; ?>

                            <!-- Transport -->
                            <?php
                            $transport = $transportFees[$student['class_id']] ?? '-';
                            if ($transport !== '-') $rowTotal += $transport;
                            ?>
                            <td><?= $transport ?></td>

                            <!-- Sets -->
                            <?php foreach ($groupedSets as $setName => $setGroup): ?>
                                <?php
                                $groupTotal = 0;
                                foreach ($setGroup as $set) {
                                    $key = 'set_' . $set['id'];
                                    $groupTotal += $classSetTotals[$student['class_id']][$key]['amount'] ?? 0;
                                }
                                if ($groupTotal > 0) $rowTotal += $groupTotal;
                                ?>
                                <td><?= $groupTotal > 0 ? number_format($groupTotal, 2) : '-' ?></td>
                            <?php endforeach; ?>

                            <td><strong><?= number_format($rowTotal, 2) ?></strong></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>





            </table>
        </div>

    </div>
</section>
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

<script>
    $(document).on('click', '.fee-toggle', function() {

        let feeKey = $(this).data('fee-key');

        $('.fee-cell[data-fee-key="' + feeKey + '"]')
            .find('.fee-months')
            .slideToggle(150);

        $(this).find('i').toggleClass('rotate');
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
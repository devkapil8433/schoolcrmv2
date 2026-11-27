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
        text-align: left;
        padding-left: 12px;
    }
</style>

<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">
            Fee Estimation Report — <?= html_escape($class['name']) ?>
            <a href="<?= base_url('fees_structure/fee_estimation_report') ?>" class="btn btn-default btn-xs pull-right">
                <i class="fas fa-arrow-left"></i> Back to Classes
            </a>
        </h4>
    </header>

    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered fee-structure-table" id="reportTable">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Last Balance</th>
                        <?php foreach ($fee_types as $ft): ?>
                            <th><?= html_escape($ft['fee_name']) ?></th>
                        <?php endforeach; ?>
                        <th>Transport Fee</th>
                        <?php foreach ($sets as $set): ?>
                            <th><?= html_escape($set['set_name']) ?></th>
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

                    <?php foreach ($students as $student): ?>
                        <?php
                        $studentId = $student['id'];
                        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
                        if (!empty($student['section_name'])) {
                            $studentName .= ' (' . $student['section_name'] . ')';
                        }
                        $studentFees   = $fee_structure[$studentId] ?? [];
                        $materialTotal = 0;

                        $lastBalance = $student_last_balance[$studentId] ?? 0;
                        $columnTotals['last_balance'] =
                            ($columnTotals['last_balance'] ?? 0) + $lastBalance;
                        ?>
                        <tr data-student="<?= (int) $studentId ?>">
                            <td>
                                <strong><?= html_escape($studentName) ?></strong>
                                <?php if (!empty($student['register_no'])): ?>
                                    <div class="text-muted" style="font-size:12px;font-weight:400;">
                                        Reg: <?= html_escape($student['register_no']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td><strong><?= number_format($lastBalance, 2) ?></strong></td>

                            <?php foreach ($fee_types as $ft): ?>
                                <?php
                                $key = strtolower($ft['fee_name']);
                                $key = preg_replace('/\s+/', '_', $key);
                                $key = preg_replace('/_+/', '_', $key);
                                $key = trim($key, '_');

                                $amount = isset($studentFees[$key]) ? (float) $studentFees[$key] : 0;
                                $columnTotals[$key] = ($columnTotals[$key] ?? 0) + $amount;
                                ?>
                                <td><?= number_format($amount, 2) ?></td>
                            <?php endforeach; ?>

                            <?php
                            $transportFee = isset($transportFees[$studentId]) ? (float) $transportFees[$studentId] : 0;
                            $columnTotals['transport_fee'] =
                                ($columnTotals['transport_fee'] ?? 0) + $transportFee;
                            ?>
                            <td><?= number_format($transportFee, 2) ?></td>

                            <?php foreach ($sets as $set): ?>
                                <?php
                                $setAmount = $studentSetAmounts[$studentId][$set['id']] ?? '-';
                                if ($setAmount !== '-') {
                                    $materialTotal += $setAmount;
                                    $columnTotals['set_' . $set['id']] =
                                        ($columnTotals['set_' . $set['id']] ?? 0) + $setAmount;
                                }
                                ?>
                                <td><?= $setAmount !== '-' ? number_format($setAmount, 2) : '-' ?></td>
                            <?php endforeach; ?>

                            <?php
                            $assigned = ($student_assigned[$studentId] ?? 0)
                                + $materialTotal
                                + $lastBalance;
                            $received  = $student_received[$studentId] ?? 0;
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
                </tbody>
                <tfoot>
                    <tr style="background:#f5f7fa;">
                        <td style="font-size:17px;font-weight:800;">GRAND TOTAL</td>
                        <td style="font-weight:800;">
                            <?= number_format($columnTotals['last_balance'] ?? 0, 2) ?>
                        </td>
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
                        <td style="font-weight:700;">
                            <?= number_format($columnTotals['transport_fee'] ?? 0, 2) ?>
                        </td>
                        <?php foreach ($sets as $set): ?>
                            <td style="font-weight:700;">
                                <?= number_format($columnTotals['set_' . $set['id']] ?? 0, 2) ?>
                            </td>
                        <?php endforeach; ?>
                        <td style="font-size:18px;font-weight:800;">
                            <?= number_format($grandAssigned, 2) ?>
                        </td>
                        <td style="font-size:18px;font-weight:800;">
                            <?= number_format($grandReceived, 2) ?>
                        </td>
                        <td style="font-size:18px;font-weight:800;color:<?= $grandRemaining > 0 ? 'red' : 'green' ?>;">
                            <?= number_format($grandRemaining, 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        $('#reportTable').DataTable({
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
                    title: 'Fee Estimation - <?= html_escape($class['name']) ?>'
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
    });
</script>

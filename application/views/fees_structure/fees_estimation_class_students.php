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
        background: #eef4ff;
        text-align: center;
        font-weight: 600;
        white-space: normal;
        font-size: 12px;
    }

    .fee-structure-table td {
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
    }

    .fee-structure-table td:first-child {
        font-weight: 600;
        text-align: left;
        padding-left: 7px;
    }

    .fee-structure-table {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100%;
        table-layout: fixed;
        font-size: 13px;
    }

    .fee-structure-table th,
    .fee-structure-table td {
        padding: 3px 2px !important;
        line-height: 1.25;
        overflow-wrap: anywhere;
        word-break: normal;
    }

    #reportTable th,
    #reportTable td {
        padding: 4px 3px !important;
    }

    .fee-structure-table th:first-child,
    .fee-structure-table td:first-child {
        width: 12%;
    }

    .fee-structure-table td:not(:first-child) {
        white-space: nowrap;
        overflow-wrap: normal;
        font-variant-numeric: tabular-nums;
    }

    .fee-structure-table tbody tr:last-child td[style*="font-size"] {
        font-size: 13px !important;
    }

    .fee-structure-table th,
    .fee-structure-table td {
        line-height: 1.3;
    }

    @media (max-width: 767px) {
        .fee-structure-table {
            min-width: 1050px !important;
        }
    }

    .estimation-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .estimation-summary-card {
        flex: 1 1 190px;
        padding: 16px 18px;
        color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(20, 40, 80, .12);
    }

    .estimation-summary-card span {
        display: block;
        opacity: .9;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .estimation-summary-card strong {
        display: block;
        margin-top: 6px;
        font-size: 22px;
    }

    .estimation-summary-card small {
        display: block;
        margin-top: 5px;
        color: rgba(255, 255, 255, .92);
        line-height: 1.4;
    }

    .fee-overdue-alert {
        border-left: 4px solid #d63649;
        border-radius: 7px;
    }

    .fee-performance-panel {
        margin: 8px 0 22px;
        padding: 18px 20px 12px;
        border: 1px solid #e5eaf2;
        border-radius: 12px;
        background: linear-gradient(135deg, #ffffff, #f7faff);
        box-shadow: 0 5px 18px rgba(30, 55, 90, .06);
    }

    .fee-performance-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .fee-performance-heading h4 {
        margin: 0 0 5px;
        color: #24364b;
        font-weight: 700;
    }

    .fee-performance-heading p {
        margin: 0;
        color: #728096;
        font-size: 13px;
    }

    .fee-performance-period {
        padding: 5px 10px;
        color: #365f9e;
        background: #eaf2ff;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .fee-chart-legend {
        display: flex;
        gap: 18px;
        margin: 14px 0 2px 35px;
        color: #526176;
        font-size: 12px;
    }

    .fee-chart-legend span {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .fee-chart-legend i {
        width: 18px;
        height: 3px;
        display: inline-block;
        border-radius: 3px;
    }

    .fee-legend-assigned,
    .fee-chart-line-assigned {
        background: #4677e8;
        stroke: #4677e8;
    }

    .fee-legend-received,
    .fee-chart-line-received {
        background: #21a675;
        stroke: #21a675;
    }

    .fee-chart-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .fee-line-chart {
        display: block;
        width: 100%;
        min-width: 600px;
        height: auto;
    }

    .fee-chart-axis {
        stroke: #dce4ef;
        stroke-width: 1;
    }

    .fee-chart-line {
        fill: none;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .fee-chart-point {
        stroke: #fff;
        stroke-width: 2;
    }

    .fee-chart-point-assigned {
        fill: #4677e8;
    }

    .fee-chart-point-received {
        fill: #21a675;
    }

    .fee-chart-label {
        fill: #728096;
        font-size: 12px;
    }

    .fee-chart-empty {
        padding: 38px 12px;
        color: #728096;
        text-align: center;
    }

    .fee-overdue-row > td:first-child {
        background: #fff3f3 !important;
        border-left: 4px solid #d63649;
    }

    .fee-status-badge {
        display: inline-block;
        margin-top: 6px;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        white-space: normal;
    }

    .fee-status-overdue {
        color: #a92334;
        background: #ffe1e4;
    }

    .fee-status-ok {
        color: #16724f;
        background: #ddf5e9;
    }

    @media (max-width: 767px) {
        .fee-performance-heading {
            flex-direction: column;
        }
    }
</style>

<?php
$displayFeeTypes = array_values(array_filter($fee_types, function ($feeType) {
    return !preg_match('/^class\s*-?\s*1$/i', trim($feeType['fee_name']));
}));
$summaryAssigned = $summaryReceived = $summaryRemaining = 0;
$formatTableAmount = function ($amount) {
    $amount = (float) $amount;
    return number_format($amount, abs($amount - round($amount)) < 0.005 ? 0 : 2);
};
$totalOverdue = 0;
$overdueStudentCount = 0;
foreach ($students as $summaryStudent) {
    $studentId = $summaryStudent['id'];
    $materials = $student_material_totals[$studentId] ?? array('assigned' => 0, 'received' => 0, 'balance' => 0);
    $transport = $student_transport_totals[$studentId] ?? array('assigned' => 0, 'received' => 0, 'balance' => 0);
    $oldBalance = (float) ($student_last_balance[$studentId] ?? 0);
    $oldBalancePaid = (float) ($student_last_balance_received[$studentId] ?? 0);
    $summaryAssigned += (float) ($student_assigned[$studentId] ?? 0) + $materials['assigned'] + $transport['assigned'] + $oldBalance + $oldBalancePaid;
    $summaryReceived += (float) ($student_received[$studentId] ?? 0) + $materials['received'] + $transport['received'] + $oldBalancePaid;
    $summaryRemaining += max(0, (float) ($student_assigned[$studentId] ?? 0) - (float) ($student_received[$studentId] ?? 0))
        + $materials['balance'] + $transport['balance'] + $oldBalance;
    $monthlyStatus = $feePerformance['students'][$studentId] ?? array('due' => 0, 'months' => 0);
    $studentOverdue = (float) $monthlyStatus['due'];
    if ($studentOverdue > 0) {
        $overdueStudentCount++;
        $totalOverdue += $studentOverdue;
    }
}
$chartMonths = $feePerformance['months'] ?? array();
$chartMax = 1;
foreach ($chartMonths as $chartMonth) {
    $chartMax = max($chartMax, (float) $chartMonth['assigned'], (float) $chartMonth['received']);
}
$chartWidth = 900;
$chartHeight = 220;
$chartLeft = 48;
$chartTop = 20;
$chartPlotWidth = 820;
$chartPlotHeight = 155;
$assignedPoints = array();
$receivedPoints = array();
foreach ($chartMonths as $index => $chartMonth) {
    $x = $chartLeft + (count($chartMonths) > 1 ? $index * $chartPlotWidth / (count($chartMonths) - 1) : $chartPlotWidth / 2);
    $assignedY = $chartTop + $chartPlotHeight - ((float) $chartMonth['assigned'] / $chartMax * $chartPlotHeight);
    $receivedY = $chartTop + $chartPlotHeight - ((float) $chartMonth['received'] / $chartMax * $chartPlotHeight);
    $assignedPoints[] = round($x, 1) . ',' . round($assignedY, 1);
    $receivedPoints[] = round($x, 1) . ',' . round($receivedY, 1);
}
?>
<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">
            Fee Estimation Report — <?= html_escape($class['name']) ?>
            <a href="<?= base_url('fees_structure/fee_estimation_report?branch_id=' . urlencode($branch_id)) ?>" class="btn btn-default btn-xs pull-right">
                <i class="fas fa-arrow-left"></i> Back to Classes
            </a>
        </h4>
    </header>

    <div class="panel-body">
        <div class="alert alert-info">
            Materials shows the net amount actually sold to this student. Received is the amount paid on those inventory sales; remaining is assigned minus received.
        </div>
        <?php if ($overdueStudentCount > 0): ?>
            <div class="alert alert-danger fee-overdue-alert">
                <strong><i class="fas fa-exclamation-triangle"></i> Admin attention required:</strong>
                <?= $overdueStudentCount ?> student<?= $overdueStudentCount === 1 ? '' : 's' ?> have overdue amounts
                (<?= number_format($totalOverdue, 2) ?> total).
            </div>
        <?php else: ?>
            <div class="alert alert-success fee-overdue-alert">
                <i class="fas fa-check-circle"></i> No students have unpaid fee months past their due dates.
            </div>
        <?php endif; ?>
        <div class="estimation-summary">
            <div class="estimation-summary-card" style="background:linear-gradient(120deg,#3867d6,#4b7bec);">
                <span>Total Assigned</span><strong><?= number_format($summaryAssigned, 2) ?></strong>
                <small>In words: <?= html_escape(amountToWords($summaryAssigned)) ?></small>
            </div>
            <div class="estimation-summary-card" style="background:linear-gradient(120deg,#20bf6b,#26de81);">
                <span>Total Received</span><strong><?= number_format($summaryReceived, 2) ?></strong>
                <small>In words: <?= html_escape(amountToWords($summaryReceived)) ?></small>
            </div>
            <div class="estimation-summary-card" style="background:linear-gradient(120deg,#eb3b5a,#fc5c65);">
                <span>Remaining Balance</span><strong><?= number_format($summaryRemaining, 2) ?></strong>
                <small>In words: <?= html_escape(amountToWords($summaryRemaining)) ?></small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover fee-structure-table" id="reportTable">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Last Balance Due</th>
                        <?php foreach ($displayFeeTypes as $ft): ?>
                            <th><?= html_escape($ft['fee_name']) ?></th>
                        <?php endforeach; ?>
                        <th>Transport Fee</th>
                        <th>Materials</th>
                        <th>Total Assigned</th>
                        <th>Total Received</th>
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
                        $lastBalance = $student_last_balance[$studentId] ?? 0;
                        $transport = $student_transport_totals[$studentId] ?? array('assigned' => 0, 'received' => 0, 'balance' => 0);
                        $material = $student_material_totals[$studentId] ?? array('assigned' => 0, 'received' => 0, 'balance' => 0);
                        $columnTotals['last_balance'] =
                            ($columnTotals['last_balance'] ?? 0) + $lastBalance;
                        ?>
                        <?php
                        $monthlyStatus = $feePerformance['students'][$studentId] ?? array('due' => 0, 'months' => 0);
                        $studentOverdue = (float) $monthlyStatus['due'];
                        ?>
                        <tr data-student="<?= (int) $studentId ?>" class="<?= $studentOverdue > 0 ? 'fee-overdue-row' : 'fee-ontrack-row' ?>">
                            <td>
                                <strong><?= html_escape($studentName) ?></strong>
                                <?php if (!empty($student['register_no'])): ?>
                                    <div class="text-muted" style="font-size:12px;font-weight:400;">
                                        Reg: <?= html_escape($student['register_no']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($studentOverdue > 0): ?>
                                    <div class="fee-status-badge fee-status-overdue">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Overdue <?= number_format($studentOverdue, 2) ?>
                                        <?php if ($monthlyStatus['months'] > 0): ?>· <?= (int) $monthlyStatus['months'] ?> due month<?= $monthlyStatus['months'] === 1 ? '' : 's' ?><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="fee-status-badge fee-status-ok"><i class="fas fa-check-circle"></i> On track</div>
                                <?php endif; ?>
                            </td>

                            <td><strong><?= $formatTableAmount($lastBalance) ?></strong></td>

                            <?php foreach ($displayFeeTypes as $ft): ?>
                                <?php
                                $key = strtolower($ft['fee_name']);
                                $key = preg_replace('/\s+/', '_', $key);
                                $key = preg_replace('/_+/', '_', $key);
                                $key = trim($key, '_');

                                $amount = isset($studentFees[$key]) ? (float) $studentFees[$key] : 0;
                                $columnTotals[$key] = ($columnTotals[$key] ?? 0) + $amount;
                                ?>
                                <td><?= $formatTableAmount($amount) ?></td>
                            <?php endforeach; ?>

                            <?php
                            $columnTotals['transport_fee'] = ($columnTotals['transport_fee'] ?? 0) + $transport['assigned'];
                            $columnTotals['materials'] = ($columnTotals['materials'] ?? 0) + $material['assigned'];
                            ?>
                            <td><?= $formatTableAmount($transport['assigned']) ?></td>
                            <td><?= $formatTableAmount($material['assigned']) ?></td>

                            <?php
                            $lastBalancePaid = (float) ($student_last_balance_received[$studentId] ?? 0);
                            $assigned = (float) ($student_assigned[$studentId] ?? 0)
                                + $material['assigned'] + $transport['assigned'] + $lastBalance + $lastBalancePaid;
                            $received = (float) ($student_received[$studentId] ?? 0)
                                + $material['received'] + $transport['received'] + $lastBalancePaid;
                            $remaining = max(0, $assigned - $received);

                            $grandAssigned  += $assigned;
                            $grandReceived  += $received;
                            $grandRemaining += $remaining;
                            ?>

                            <td><strong><?= $formatTableAmount($assigned) ?></strong></td>
                            <td><strong><?= $formatTableAmount($received) ?></strong></td>
                            <td>
                                <strong style="color:<?= $remaining > 0 ? 'red' : 'green' ?>">
                                    <?= $formatTableAmount($remaining) ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr style="background:#f5f7fa;">
                        <td style="font-size:17px;font-weight:800;">GRAND TOTAL</td>
                        <td style="font-weight:800;">
                            <?= $formatTableAmount($columnTotals['last_balance'] ?? 0) ?>
                        </td>
                        <?php foreach ($displayFeeTypes as $ft): ?>
                            <?php
                            $key = strtolower($ft['fee_name']);
                            $key = preg_replace('/\s+/', '_', $key);
                            $key = preg_replace('/_+/', '_', $key);
                            $key = trim($key, '_');
                            ?>
                            <td style="font-weight:700;">
                                <?= $formatTableAmount($columnTotals[$key] ?? 0) ?>
                            </td>
                        <?php endforeach; ?>
                        <td style="font-weight:700;">
                            <?= $formatTableAmount($columnTotals['transport_fee'] ?? 0) ?>
                        </td>
                        <td style="font-weight:700;"><?= $formatTableAmount($columnTotals['materials'] ?? 0) ?></td>
                        <td style="font-size:18px;font-weight:800;">
                            <?= $formatTableAmount($grandAssigned) ?>
                        </td>
                        <td style="font-size:18px;font-weight:800;">
                            <?= $formatTableAmount($grandReceived) ?>
                        </td>
                        <td style="font-size:18px;font-weight:800;color:red;">
                            <?= $formatTableAmount($grandRemaining) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <section class="fee-performance-panel" aria-label="Monthly fee performance">
            <div class="fee-performance-heading">
                <div>
                    <h4>Monthly Fee Performance — <?= html_escape($class['name']) ?></h4>
                    <p>Assigned vs received for fee months due through this month</p>
                </div>
                <span class="fee-performance-period"><?= count($chartMonths) ?> due month<?= count($chartMonths) === 1 ? '' : 's' ?></span>
            </div>
            <div class="fee-chart-legend">
                <span><i class="fee-legend-assigned"></i> Assigned</span>
                <span><i class="fee-legend-received"></i> Received</span>
            </div>
            <div class="fee-chart-wrap">
                <?php if (!empty($chartMonths)): ?>
                    <svg class="fee-line-chart" viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>" role="img" aria-label="Monthly assigned and received fees">
                        <line x1="<?= $chartLeft ?>" y1="<?= $chartTop + $chartPlotHeight ?>" x2="<?= $chartLeft + $chartPlotWidth ?>" y2="<?= $chartTop + $chartPlotHeight ?>" class="fee-chart-axis"></line>
                        <line x1="<?= $chartLeft ?>" y1="<?= $chartTop ?>" x2="<?= $chartLeft ?>" y2="<?= $chartTop + $chartPlotHeight ?>" class="fee-chart-axis"></line>
                        <polyline points="<?= html_escape(implode(' ', $assignedPoints)) ?>" class="fee-chart-line fee-chart-line-assigned"></polyline>
                        <polyline points="<?= html_escape(implode(' ', $receivedPoints)) ?>" class="fee-chart-line fee-chart-line-received"></polyline>
                        <?php foreach ($chartMonths as $index => $chartMonth): ?>
                            <?php
                            $x = $chartLeft + (count($chartMonths) > 1 ? $index * $chartPlotWidth / (count($chartMonths) - 1) : $chartPlotWidth / 2);
                            $assignedY = $chartTop + $chartPlotHeight - ((float) $chartMonth['assigned'] / $chartMax * $chartPlotHeight);
                            $receivedY = $chartTop + $chartPlotHeight - ((float) $chartMonth['received'] / $chartMax * $chartPlotHeight);
                            ?>
                            <circle cx="<?= round($x, 1) ?>" cy="<?= round($assignedY, 1) ?>" r="4" class="fee-chart-point fee-chart-point-assigned">
                                <title><?= html_escape($chartMonth['label']) ?> assigned: <?= number_format($chartMonth['assigned'], 2) ?></title>
                            </circle>
                            <circle cx="<?= round($x, 1) ?>" cy="<?= round($receivedY, 1) ?>" r="4" class="fee-chart-point fee-chart-point-received">
                                <title><?= html_escape($chartMonth['label']) ?> received: <?= number_format($chartMonth['received'], 2) ?></title>
                            </circle>
                            <text x="<?= round($x, 1) ?>" y="202" text-anchor="middle" class="fee-chart-label"><?= html_escape($chartMonth['label']) ?></text>
                        <?php endforeach; ?>
                    </svg>
                <?php else: ?>
                    <div class="fee-chart-empty">No monthly fee allocations are available for this session.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>

<script src="<?php echo base_url('assets/js/fee_report_export.js'); ?>"></script>
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
                    className: 'btn btn-default btn-sm',
                    exportOptions: { columns: function() { return true; }, modifier: { page: 'all' } }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i>',
                    titleAttr: 'Export Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: { columns: function() { return true; }, modifier: { page: 'all' } }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i>',
                    titleAttr: 'Export PDF',
                    className: 'btn btn-danger btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Fee Estimation - <?= html_escape($class['name']) ?>',
                    exportOptions: { columns: function() { return true; }, modifier: { page: 'all' } },
                    customize: function(doc) {
                        doc.pageSize = 'A4';
                        doc.pageOrientation = 'landscape';
                        doc.pageMargins = [8, 10, 8, 10];
                        doc.defaultStyle.fontSize = $('#reportTable thead th').length > 13 ? 5 : 6;
                        doc.styles.tableHeader.fontSize = doc.defaultStyle.fontSize;
                        var tableContent = doc.content.find(function(item) { return item.table; });
                        if (tableContent && tableContent.table.body.length) {
                            tableContent.table.widths = Array(tableContent.table.body[0].length).fill('*');
                        }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    titleAttr: 'Print',
                    className: 'btn btn-primary btn-sm',
                    exportOptions: { columns: function() { return true; }, modifier: { page: 'all' } },
                    customize: function(win) {
                        var printFontSize = $('#reportTable thead th').length > 13 ? '5pt' : '6pt';
                        $(win.document.head).append('<style>@page{size:A4 landscape;margin:5mm}html,body{width:100%!important;margin:0!important;padding:0!important}table{width:100%!important;table-layout:fixed!important;border-collapse:collapse!important}th,td{font-size:' + printFontSize + '!important;padding:2px!important;white-space:normal!important;overflow-wrap:anywhere;word-break:break-word}thead{display:table-header-group}tr{page-break-inside:avoid}</style>');
                    }
                },
                {
                    text: '<i class="fab fa-whatsapp"></i> PDF',
                    titleAttr: 'Share PDF to WhatsApp',
                    className: 'btn btn-success btn-sm',
                    action: function(event, dt) {
                        shareFeeReportPdf(dt, 'Fee Estimation - <?= html_escape($class['name']) ?>');
                    }
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

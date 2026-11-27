<!-- <pre>
<?php print_r($fee_structure); ?>
</pre> -->

<style>
    .fee-structure-panel .panel-body {
        padding: 20px;
    }

    .fee-structure-table th {
        background: #f2f4f7;
        text-align: center;
        font-weight: 700;
        color: #475467;
        white-space: nowrap;
    }

    .fee-structure-table td {
        text-align: center;
        vertical-align: middle;
        font-size: 14px;
        color: #344054;
    }

    .fee-structure-table td:first-child {
        font-weight: 600;
        text-align: left;
        padding-left: 12px;
    }

    .fee-frequency-switch {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 5px;
        border: 1px solid #e4e7ec;
        border-radius: 9px;
        background: #f8f9fb;
    }

    .fee-frequency-switch label {
        position: relative;
        margin: 0;
        padding: 8px 14px;
        border-radius: 6px;
        color: #475467;
        cursor: pointer;
        font-weight: 600;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }

    .fee-frequency-switch input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .fee-frequency-switch label.selected {
        background: #f0ad4e;
        color: #fff;
        box-shadow: 0 2px 5px rgba(152, 91, 5, .18);
    }

    .fee-admission-switch {
        margin: 15px 0 0;
    }

    .fee-admission-switch .fee-switch-title {
        margin: 0 0 7px;
        color: #475467;
        font-size: 14px;
        font-weight: 700;
    }

    .fee-frequency-switch label:focus-within {
        outline: 2px solid #c77e12;
        outline-offset: 2px;
    }

    .fee-period-caption {
        display: inline-block;
        margin-left: 12px;
        color: #667085;
        font-size: 13px;
    }

    .fee-structure-table {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100%;
        table-layout: fixed;
        margin-top: 15px;
        border-color: #e4e7ec;
        background: #fff;
        font-size: 13px;
    }

    .fee-structure-panel .table-responsive {
        overflow-x: hidden;
    }

    .fee-structure-table th,
    .fee-structure-table td {
        padding: 7px 5px !important;
        line-height: 1.35;
        overflow-wrap: break-word;
        word-break: normal;
    }

    #structureTable th,
    #structureTable td {
        padding: 7px 5px !important;
    }

    .fee-structure-table th {
        font-size: 13px;
        font-weight: 700;
        white-space: normal;
    }

    .fee-structure-table td {
        font-size: 14px;
        font-weight: 500;
    }

    .fee-structure-table th:first-child,
    .fee-structure-table td:first-child {
        width: 11%;
    }

    .fee-structure-table td:not(:first-child) {
        white-space: nowrap;
        overflow-wrap: normal;
        font-variant-numeric: tabular-nums;
    }

    .fee-structure-table tbody tr:hover {
        background: #fffaf1;
    }

    .fee-structure-table tbody tr td:first-child a {
        color: #1871b8;
        font-weight: 700;
    }

    .fee-structure-table .row-total {
        color: #253247;
        font-size: 16px;
        font-weight: 700;
        white-space: nowrap;
    }

    .fee-structure-panel .dataTables_info,
    .fee-structure-panel .dataTables_paginate,
    .fee-structure-panel .dataTables_filter,
    .fee-structure-panel .dt-buttons {
        font-size: 13px;
    }

    #structureTable.admission-mode-old .admission-fee-column {
        display: none;
    }

    .fee-structure-panel .dataTables_filter input {
        min-width: 210px;
        border: 1px solid #d0d5dd;
        border-radius: 6px;
        box-shadow: none;
    }

    .fee-structure-panel .dt-buttons .btn {
        margin-right: 4px;
        border-radius: 5px;
    }

    @media (max-width: 767px) {
        .fee-structure-panel .table-responsive {
            overflow-x: auto;
        }

        .fee-structure-panel .panel-body {
            padding: 12px;
        }

        .fee-frequency-switch label {
            padding: 7px 10px;
            font-size: 12px;
        }

        .fee-period-caption {
            display: block;
            margin: 8px 0 0;
        }
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
<?php $formatTableAmount = function ($amount) {
    $amount = (float) $amount;
    return number_format($amount, abs($amount - round($amount)) < 0.005 ? 0 : 2);
}; ?>
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
                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control'
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
<section class="panel fee-structure-panel">
    <header class="panel-heading">
        <h4 class="panel-title">Fee Structure</h4>
    </header>

    <div class="panel-body">

        <!-- Payment Type -->
        <div class="fee-frequency-switch fee-period-switch" role="radiogroup" aria-label="Fee period">
            <label><input type="radio" name="fee_type" value="annually"> Yearly</label>
            <label><input type="radio" name="fee_type" value="half-yearly"> Half Yearly</label>
            <label><input type="radio" name="fee_type" value="quarterly"> Quarterly</label>
            <label><input type="radio" name="fee_type" value="monthly" checked> Monthly</label>
        </div>
        <span class="fee-period-caption" id="feePeriodCaption" aria-live="polite"></span>
        <div class="fee-admission-switch">
            <div class="fee-switch-title">Student Admission Type</div>
            <div class="fee-frequency-switch" role="radiogroup" aria-label="Student admission type">
                <label class="selected"><input type="radio" name="admission_type" value="old" checked> Old Admission</label>
                <label><input type="radio" name="admission_type" value="new"> New Admission</label>
            </div>
        </div>
        <!-- Fee Structure Table -->
        <div class="table-responsive">
            <table class="table table-bordered fee-structure-table" id="structureTable">
                <thead>
                    <tr>
                        <th>Class</th>
                        <?php foreach ($fee_types as $ft): ?>
                            <th class="fee-type-heading<?= stripos($ft['fee_name'], 'admission') !== false ? ' admission-fee-column' : '' ?>">
                                <?= html_escape($ft['fee_name']) ?>
                            </th>
                        <?php endforeach; ?>
                        <th>Transport Fee</th>
                        <th>Materials (Set / Student)</th>
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
                                <a href="<?= base_url('fees_structure/class_students/' . $class['id'] . '?branch_id=' . urlencode($branch_id)) ?>">
                                    <?= html_escape($class['name']) ?>
                                </a>
                            </td>

                            <?php
                            $classFees = $fee_structure_details[$class['id']] ?? [];
                            $rowTotal = 0;
                            ?>

                            <!-- NORMAL FEES -->
                            <?php foreach ($fee_types as $ft): ?>

                                <?php
                                $key = strtolower($ft['fee_name']);
                                $key = preg_replace('/\s+/', '_', $key);
                                $key = preg_replace('/_+/', '_', $key);
                                $key = trim($key, '_');

                                $feeConfigs = $classFees[$key] ?? [];
                                $amount = 0;
                                $currentAcademicMonth = 4;
                                foreach ($feeConfigs as $feeConfig) {
                                    if (in_array($currentAcademicMonth, $feeConfig['months'], true)) {
                                        $amount += $feeConfig['amount'];
                                    }
                                }
                                if ($amount > 0) {
                                    $rowTotal += $amount;
                                    $columnTotals[$key] = ($columnTotals[$key] ?? 0) + $amount;
                                }
                                ?>

                                <td class="fee-cell<?= stripos($ft['fee_name'], 'admission') !== false ? ' admission-fee-column' : '' ?>"
                                    data-fees="<?= html_escape(json_encode($feeConfigs)) ?>">
                                    <?= $amount > 0 ? $formatTableAmount($amount) : '-' ?>
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
                            <td class="transport-cell" <?php if (is_numeric($transportFee)): ?>data-base="<?= $transportFee ?>"<?php endif; ?>>
                                <?= is_numeric($transportFee) ? $formatTableAmount($transportFee) : '-' ?>
                            </td>


                            <?php
                            $materialSetAmount = (float) ($classMaterialSetAmounts[$class['id']] ?? 0);
                            $rowTotal += $materialSetAmount;
                            ?>
                            <td class="material-cell" data-total="<?= $materialSetAmount ?>">
                                <?= $materialSetAmount > 0 ? $formatTableAmount($materialSetAmount) : '-' ?>
                            </td>



                            <!-- ROW TOTAL -->
                            <td><strong class="row-total"><?= $rowTotal ?></strong></td>
                        </tr>

                        <?php $grandTotalAll += $rowTotal; ?>

                    <?php endforeach; ?>

                </tbody>





            </table>
        </div>

    </div>
</section>
<?php endif; ?>
<script src="<?php echo base_url('assets/js/fee_report_export.js'); ?>"></script>
<script>
    $(document).ready(function() {
        const academicMonths = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3];
        const formatAmount = function(amount) {
            return amount.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        function getPeriodMonths(type) {
            if (type === 'annually') {
                return academicMonths;
            }

            const periodLength = type === 'half-yearly' ? 6 : (type === 'quarterly' ? 3 : 1);
            return academicMonths.slice(0, periodLength);
        }

        function getFeeAmount(configs, periodMonths) {
            return configs.reduce(function(total, config) {
                const assignedMonths = Array.isArray(config.months) ? config.months : [];
                const amount = Number(config.amount) || 0;
                return total + amount * assignedMonths.filter(function(month) {
                    return periodMonths.indexOf(Number(month)) !== -1;
                }).length;
            }, 0);
        }

        function recalculate(type) {
            const periodMonths = getPeriodMonths(type);
            const periodLength = periodMonths.length;
            const admissionType = $('input[name="admission_type"]:checked').val() || 'old';
            const periodLabels = {
                monthly: 'Monthly total',
                quarterly: 'Quarterly total',
                'half-yearly': 'Half-yearly total',
                annually: 'Full academic-year total'
            };
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            const periodLabel = periodMonths.map(function(month) {
                return monthNames[month - 1];
            }).join(', ');
            $('#feePeriodCaption').text(periodLabels[type] + ' · ' + periodLabel);

            $('#structureTable tbody tr').each(function() {
                let rowTotal = 0;
                const $row = $(this);

                $row.find('td.fee-cell').each(function() {
                    const $cell = $(this);
                    const configs = JSON.parse($cell.attr('data-fees') || '[]');
                    const isAdmissionFee = $cell.hasClass('admission-fee-column');
                    const amount = isAdmissionFee && admissionType === 'old'
                        ? 0
                        : getFeeAmount(configs, periodMonths);
                    $cell.text(amount > 0 ? formatAmount(amount) : '-');
                    rowTotal += amount;
                });

                const $transport = $row.find('td.transport-cell[data-base]');
                if ($transport.length) {
                    const transportAmount = (Number($transport.attr('data-base')) || 0) * periodLength;
                    $transport.text(transportAmount > 0 ? formatAmount(transportAmount) : '-');
                    rowTotal += transportAmount;
                }

                $row.find('td.material-cell').each(function() {
                    const materialAmount = Number($(this).attr('data-total')) || 0;
                    $(this).text(materialAmount > 0 ? formatAmount(materialAmount) : '-');
                    rowTotal += materialAmount;
                });

                $row.find('.row-total').text(formatAmount(rowTotal));
            });
        }

        $('input[name="fee_type"]').on('change', function() {
            $('.fee-period-switch label').removeClass('selected');
            $(this).closest('label').addClass('selected');
            recalculate($(this).val());
        });

        $('input[name="admission_type"]').on('change', function() {
            $('.fee-admission-switch .fee-frequency-switch label').removeClass('selected');
            $(this).closest('label').addClass('selected');
            $('#structureTable').toggleClass('admission-mode-old', $(this).val() === 'old');
            recalculate($('input[name="fee_type"]:checked').val() || 'monthly');
        });

        $('.fee-period-switch input:checked').closest('label').addClass('selected');
        $('#structureTable').addClass('admission-mode-old');
        recalculate($('.fee-period-switch input:checked').val() || 'monthly');

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
                    title: 'Fee Structure Report',
                    exportOptions: { columns: function() { return true; }, modifier: { page: 'all' } },
                    customize: function(doc) {
                        doc.pageSize = 'A4';
                        doc.pageOrientation = 'landscape';
                        doc.pageMargins = [8, 10, 8, 10];
                        doc.defaultStyle.fontSize = $('#structureTable thead th').length > 13 ? 5 : 6;
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
                        var printFontSize = $('#structureTable thead th').length > 13 ? '5pt' : '6pt';
                        $(win.document.head).append('<style>@page{size:A4 landscape;margin:5mm}html,body{width:100%!important;margin:0!important;padding:0!important}table{width:100%!important;table-layout:fixed!important;border-collapse:collapse!important}th,td{font-size:' + printFontSize + '!important;padding:2px!important;white-space:normal!important;overflow-wrap:anywhere;word-break:break-word}thead{display:table-header-group}tr{page-break-inside:avoid}</style>');
                    }
                },
                {
                    text: '<i class="fab fa-whatsapp"></i> PDF',
                    titleAttr: 'Share PDF to WhatsApp',
                    className: 'btn btn-success btn-sm',
                    action: function(event, dt) {
                        shareFeeReportPdf(dt, 'Fee Structure Report');
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
        window.defaulterTable = table;
    });
</script>
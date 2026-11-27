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
?>
        <div class="content-wrapper panel">
            <div class="container-fluid">
                <div class="defaulter-page-heading">
                    <div>
                        <h3><?= html_escape($title) ?></h3>
                        <p>Filter monthly fee balances, review outstanding amounts, and share reminders.</p>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="defaulter-filter-card">
                        <form id="filterForm">
                            <input type="hidden" name="branch_id" value="<?= html_escape($branch_id) ?>">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
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
                                        onchange="getSectionByClass(this.value, 0)">
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
                                    <select class="form-control" name="feeTypes[]" id="feeTypes" multiple>
                                        <?php foreach ($feeTypes as $feeType): ?>
                                            <option value="<?= (int) $feeType['id'] ?>"><?= html_escape($feeType['fee_name']) ?></option>
                                        <?php endforeach; ?>
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

                                <div class="col-md-6 d-flex align-items-end fee-filter-actions">
                                    <button type="submit" id="fetchDefaulters" class="btn btn-primary shadow-sm">
                                        <i class="fa fa-search me-2"></i> Fetch Defaulters
                                    </button>
                                    <button type="reset" id="resetDefaulterFilters" class="btn btn-default shadow-sm">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                </div>

                <!-- Results Area -->
                <div class="defaulter-results-card">
                        <div id="resultArea" class="text-muted">
                            <div class="defaulter-results-heading">
                                <div>
                                    <h3>Fee Defaulters</h3>
                                    <p id="monthHeading">Choose filters and fetch to see outstanding fees.</p>
                                </div>
                                <span class="defaulter-count" id="defaulterCount">0 students</span>
                            </div>
                            <div class="defaulter-summary">
                                <div class="defaulter-summary-card assigned">
                                    <span>Assigned</span><strong id="summaryAssigned">₹0</strong>
                                </div>
                                <div class="defaulter-summary-card fine">
                                    <span>Fine Due</span><strong id="summaryFine">₹0</strong>
                                </div>
                                <div class="defaulter-summary-card paid">
                                    <span>Fee Paid</span><strong id="summaryPaid">₹0</strong>
                                </div>
                                <div class="defaulter-summary-card due">
                                    <span>Outstanding</span><strong id="summaryDue">₹0</strong>
                                </div>
                            </div>
                            <div class="table-responsive mt-4">
                                <div class="defaulter-actions">
                                    <span class="defaulter-selection-count" id="selectedStudentCount">0 selected</span>
                                    <button id="sendMessageBtn" type="button" class="btn btn-success">
                                        <i class="fab fa-whatsapp"></i> Prepare WhatsApp Reminders
                                    </button>
                                </div>

                                <table id="feesTable" class="table table-bordered w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th><input type="checkbox" id="checkAll" aria-label="Select or deselect all students in the report" title="Select or deselect all students in the report"></th>
                                            <th>Sr.no</th>
                                            <th>Register No</th>
                                            <th>Student Name</th>
                                            <th>Roll No</th>
                                            <th>Class & Section</th>
                                            <th>Fee Heads</th>
                                            <th>Assigned Amount</th>
                                            <th>Fine Due</th>
                                            <th>Fee Paid</th>
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
                        <div id="whatsappDialog" class="whatsapp-dialog" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="whatsappDialogTitle">
                            <div class="whatsapp-dialog-card">
                                <button type="button" class="whatsapp-dialog-close" id="closeWhatsappDialog" aria-label="Close">&times;</button>
                                <h3 id="whatsappDialogTitle"><i class="fab fa-whatsapp"></i> WhatsApp fee reminders</h3>
                                <p>Open each reminder in WhatsApp, review it, and send it individually. Messages are not sent automatically.</p>
                                <div id="whatsappRecipientList"></div>
                            </div>
                        </div>
                        <div id="feeHeadPopup" class="fee-head-popup" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="feeHeadPopupTitle">
                            <div class="fee-head-popup-card">
                                <button type="button" class="fee-head-popup-close" id="closeFeeHeadPopup" aria-label="Close">&times;</button>
                                <div class="fee-head-popup-heading">
                                    <span class="fee-head-popup-icon"><i class="fas fa-eye"></i></span>
                                    <div>
                                        <h3 id="feeHeadPopupTitle">Fee head details</h3>
                                        <p id="feeHeadPopupStudent"></p>
                                    </div>
                                </div>
                                <div id="feeHeadPopupContent" class="fee-head-popup-content"></div>
                                <div class="fee-head-popup-footer">
                                    <button type="button" class="btn btn-default" id="doneFeeHeadPopup">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
<?php endif; ?>


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

    .fee-row-total {
        font-weight: bold;
        display: block;
    }

    .content-wrapper.panel {
        margin: 18px 0;
        padding: 0;
        border: 1px solid #e7ebf2;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(27, 49, 78, .07);
        background: #fff;
    }

    .defaulter-page-heading,
    .defaulter-results-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .defaulter-page-heading {
        padding: 18px 22px 12px;
        color: #233650;
    }

    .defaulter-page-heading h3,
    .defaulter-results-heading h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
    }

    .defaulter-page-heading p,
    .defaulter-results-heading p {
        margin: 5px 0 0;
        color: #6b778c;
        font-size: 13px;
    }

    .defaulter-filter-card,
    .defaulter-results-card {
        margin: 0 16px 16px;
        padding: 18px;
        border: 1px solid #e7ebf2;
        border-radius: 10px;
        background: #fff;
    }

    .defaulter-filter-card {
        background: linear-gradient(135deg, #fff, #f7faff);
    }

    .defaulter-filter-card label {
        display: block;
        margin: 0 0 6px;
        color: #34445b;
        font-size: 12px;
        font-weight: 700;
    }

    .defaulter-filter-card .form-control {
        min-height: 38px;
        border-color: #d9e1ec;
        border-radius: 7px;
        box-shadow: none;
    }

    .fee-filter-actions {
        gap: 9px;
        padding-top: 20px;
    }

    .fee-filter-actions .btn {
        min-height: 38px;
        padding: 8px 16px;
        border-radius: 7px;
        font-weight: 600;
    }

    .defaulter-results-heading {
        margin-bottom: 14px;
    }

    .defaulter-count {
        flex: 0 0 auto;
        padding: 7px 12px;
        color: #365d93;
        background: #eaf2ff;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .defaulter-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin: 12px 0 18px;
    }

    .defaulter-summary-card {
        padding: 12px 15px;
        border: 1px solid #e7ebf2;
        border-left: 4px solid #4779d3;
        border-radius: 8px;
        background: #fff;
    }

    .defaulter-summary-card span {
        display: block;
        color: #68768a;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .defaulter-summary-card strong {
        display: block;
        margin-top: 5px;
        color: #25364f;
        font-size: 19px;
    }

    .defaulter-summary-card.fine { border-left-color: #e49a24; }
    .defaulter-summary-card.paid { border-left-color: #25a675; }
    .defaulter-summary-card.due { border-left-color: #d94755; }

    .defaulter-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin: 0 0 10px;
    }

    .defaulter-selection-count {
        color: #68768a;
        font-size: 12px;
        font-weight: 600;
    }

    #sendMessageBtn {
        padding: 9px 15px;
        border: 0;
        border-radius: 8px;
        background: linear-gradient(135deg, #20b866, #128c51);
        box-shadow: 0 3px 9px rgba(18, 140, 81, .2);
        font-weight: 700;
        transition: box-shadow .15s ease, transform .15s ease;
    }

    #sendMessageBtn:hover,
    #sendMessageBtn:focus {
        background: linear-gradient(135deg, #1ba85c, #0e7844);
        box-shadow: 0 5px 13px rgba(18, 140, 81, .28);
        transform: translateY(-1px);
    }

    #feesTable {
        width: 100% !important;
        border: 1px solid #e1e7ef;
        border-radius: 8px;
        background: #fff;
    }

    #feesTable th {
        padding: 9px 7px;
        background: #f0f5fc;
        color: #34445b;
        font-size: 12px;
        white-space: nowrap;
    }

    #feesTable td {
        padding: 8px 7px;
        color: #3b4758;
        font-size: 13px;
        vertical-align: middle;
    }

    #feesTable tbody tr:hover { background: #f7faff; }
    #feesTable tfoot td {
        padding: 10px 7px;
        background: #f2f6fc;
        color: #233650;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .fee-head-view-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 32px;
        border: 1px solid #cfe0f7;
        border-radius: 7px;
        background: #f1f6ff;
        color: #2864b4;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }

    .fee-head-view-button:hover,
    .fee-head-view-button:focus {
        border-color: #2864b4;
        background: #2864b4;
        color: #fff;
        box-shadow: 0 3px 9px rgba(40, 100, 180, .2);
        outline: none;
    }

    .fee-head-popup {
        position: fixed;
        z-index: 20001;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(20, 32, 48, .58);
    }

    .fee-head-popup-card {
        position: relative;
        width: min(620px, 100%);
        max-height: 85vh;
        overflow: auto;
        padding: 22px;
        border: 1px solid #e6ebf2;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 18px 55px rgba(18, 34, 56, .25);
    }

    .fee-head-popup-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 28px 16px 0;
        border-bottom: 1px solid #edf0f5;
    }

    .fee-head-popup-icon {
        display: flex;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: #eaf2ff;
        color: #2864b4;
        font-size: 17px;
    }

    .fee-head-popup-heading h3 {
        margin: 0;
        color: #26374e;
        font-size: 18px;
        font-weight: 700;
    }

    .fee-head-popup-heading p {
        margin: 4px 0 0;
        color: #718096;
        font-size: 13px;
    }

    .fee-head-popup-close {
        position: absolute;
        top: 14px;
        right: 17px;
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #667085;
        font-size: 25px;
        line-height: 1;
    }

    .fee-head-popup-close:hover { background: #f2f4f7; }
    .fee-head-popup-content { padding-top: 8px; }

    .fee-head-popup-item {
        padding: 13px 0;
        border-bottom: 1px solid #edf0f5;
    }

    .fee-head-popup-item:last-child { border-bottom: 0; }

    .fee-head-popup-item-name {
        display: block;
        margin-bottom: 9px;
        color: #2d405c;
        font-size: 14px;
        font-weight: 700;
    }

    .fee-head-popup-values {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .fee-head-popup-value {
        padding: 8px 9px;
        border-radius: 7px;
        background: #f6f8fb;
    }

    .fee-head-popup-value span {
        display: block;
        color: #758196;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .fee-head-popup-value strong {
        display: block;
        margin-top: 3px;
        color: #34445b;
        font-size: 12px;
        white-space: nowrap;
    }

    .fee-head-popup-value.due strong { color: #c83e4d; }

    .fee-head-popup-footer {
        display: flex;
        justify-content: flex-end;
        padding-top: 14px;
        border-top: 1px solid #edf0f5;
    }

    #feesTable .fee-row-total { white-space: nowrap; }

    .dataTables_wrapper .dt-buttons .btn {
        margin: 0 5px 7px 0;
        border-radius: 6px !important;
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_filter input {
        min-height: 35px;
        margin-left: 6px;
        padding: 6px 10px;
        border: 1px solid #d5deea;
        border-radius: 6px;
        box-shadow: none;
    }

    .whatsapp-dialog {
        position: fixed;
        z-index: 20000;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(20, 32, 48, .55);
    }

    .whatsapp-dialog-card {
        position: relative;
        width: min(560px, 100%);
        max-height: 80vh;
        overflow: auto;
        padding: 22px;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .2);
    }

    .whatsapp-dialog-card h3 { margin: 0 0 7px; color: #16834f; font-size: 20px; }
    .whatsapp-dialog-card p { color: #667085; font-size: 13px; }
    #whatsappRecipientList { margin-top: 14px; }
    .whatsapp-dialog-close {
        position: absolute;
        top: 8px;
        right: 12px;
        border: 0;
        background: transparent;
        color: #667085;
        font-size: 26px;
    }
    .whatsapp-recipient {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-top: 1px solid #edf0f5;
    }

    .whatsapp-recipient-name { color: #26374e; font-weight: 700; }
    .whatsapp-recipient-meta { display: block; margin-top: 3px; color: #758196; font-size: 12px; }
    .whatsapp-recipient .btn {
        flex: 0 0 auto;
        border-radius: 7px;
        font-weight: 600;
    }
    .whatsapp-unavailable { color: #a04a4a; font-size: 12px; }

    @media (max-width: 767px) {
        .defaulter-filter-card,
        .defaulter-results-card { margin-right: 8px; margin-left: 8px; padding: 12px; }
        .defaulter-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .defaulter-page-heading,
        .defaulter-results-heading { align-items: flex-start; }
        .fee-filter-actions { padding-top: 4px; }
        .defaulter-actions { justify-content: space-between; }
        #sendMessageBtn { padding: 8px 10px; font-size: 12px; }
        .whatsapp-recipient { align-items: flex-start; flex-direction: column; }
        .fee-head-popup-card { padding: 17px; }
        .fee-head-popup-values { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #feesTable { min-width: 1050px; }
    }
</style>


<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#feeTypes, #month').select2({
                placeholder: "Select one or more",
                allowClear: true,
                width: '100%'
            });
        }

        var selectedStudents = {};
        var defaulterRows = {};
        function updateSelectionUI() {
            const reportCheckboxes = table.rows().nodes().to$().find('.student-check');
            let matchingSelected = 0;
            reportCheckboxes.each(function() {
                if (selectedStudents[this.value]) matchingSelected++;
            });
            $('#checkAll')
                .prop('checked', reportCheckboxes.length > 0 && matchingSelected === reportCheckboxes.length)
                .prop('indeterminate', matchingSelected > 0 && matchingSelected < reportCheckboxes.length);

            const selectedCount = Object.keys(selectedStudents).filter(function(studentID) {
                return selectedStudents[studentID] && defaulterRows[studentID];
            }).length;
            $('#selectedStudentCount').text(selectedCount + ' selected');
        }

        function getExportCellText(data, node) {
            const feeHeads = $(node).find('.fee-head-export');
            if (feeHeads.length) {
                return feeHeads.text().trim();
            }
            if (node && typeof node.textContent === 'string') {
                return node.textContent.replace(/\s+/g, ' ').trim();
            }
            return $('<div>').html(data == null ? '' : String(data)).text().replace(/\s+/g, ' ').trim();
        }

        const reportExportOptions = {
            columns: ':visible:not(:first-child)',
            modifier: { search: 'applied', page: 'all' },
            format: {
                body: function(data, row, column, node) {
                    return getExportCellText(data, node);
                }
            }
        };
        const printExportOptions = $.extend(true, {}, reportExportOptions, {
            format: {
                body: function(data, row, column, node) {
                    return escapeHtml(getExportCellText(data, node));
                }
            }
        });

        var table = $('#feesTable').DataTable({
            dom: '<"row fee-table-toolbar"<"col-sm-8"B><"col-sm-4"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="fas fa-copy"></i> Copy',
                    className: 'btn btn-default btn-sm',
                    exportOptions: reportExportOptions
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Fee Defaulters Report',
                    exportOptions: reportExportOptions
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Fee Defaulters Report',
                    exportOptions: reportExportOptions,
                    customize: function(doc) {
                        doc.pageMargins = [24, 35, 24, 30];
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 8;
                        doc.styles.title.fontSize = 14;
                        doc.content.forEach(function(item) {
                            if (item.table) {
                                item.table.widths = [30, 58, 95, 34, 70, 130, 75, 60, 60, 80];
                            }
                        });
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-primary btn-sm',
                    title: 'Fee Defaulters Report',
                    exportOptions: printExportOptions,
                    customize: function(win) {
                        const printStyles = `
                            @page { size: landscape; margin: 6mm; }
                            body.dt-print-view { margin: 0; color: #222; font-family: Arial, sans-serif; }
                            body.dt-print-view h1 { margin: 0 0 8px; font-size: 14pt; text-align: center; }
                            body.dt-print-view table { width: 100% !important; table-layout: fixed; border-collapse: collapse; }
                            body.dt-print-view thead { display: table-header-group; }
                            body.dt-print-view tr { page-break-inside: avoid; break-inside: avoid; }
                            body.dt-print-view th,
                            body.dt-print-view td {
                                padding: 3px 4px !important;
                                border: 1px solid #b9c1cc !important;
                                font-size: 7.5pt !important;
                                line-height: 1.15 !important;
                                vertical-align: top !important;
                            }
                            body.dt-print-view th { background: #e9eef5 !important; color: #1f2d3d !important; }
                            body.dt-print-view th:nth-child(1),
                            body.dt-print-view td:nth-child(1) { width: 4%; }
                            body.dt-print-view th:nth-child(2),
                            body.dt-print-view td:nth-child(2) { width: 9%; }
                            body.dt-print-view th:nth-child(3),
                            body.dt-print-view td:nth-child(3) { width: 15%; }
                            body.dt-print-view th:nth-child(4),
                            body.dt-print-view td:nth-child(4) { width: 6%; }
                            body.dt-print-view th:nth-child(5),
                            body.dt-print-view td:nth-child(5) { width: 9%; }
                            body.dt-print-view th:nth-child(6),
                            body.dt-print-view td:nth-child(6) { width: 20%; overflow-wrap: anywhere; }
                            body.dt-print-view th:nth-child(7),
                            body.dt-print-view td:nth-child(7) { width: 10%; }
                            body.dt-print-view th:nth-child(8),
                            body.dt-print-view td:nth-child(8) { width: 8%; }
                            body.dt-print-view th:nth-child(9),
                            body.dt-print-view td:nth-child(9) { width: 8%; }
                            body.dt-print-view th:nth-child(10),
                            body.dt-print-view td:nth-child(10) { width: 11%; }
                        `;
                        $('<style>').text(printStyles).appendTo(win.document.head);
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-columns"></i> Columns',
                    className: 'btn btn-info btn-sm',
                    columns: ':not(:first-child)'
                }
            ],
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[3, 'asc']],
            language: { emptyTable: 'Choose filters and fetch to see fee defaulters.' },
            drawCallback: function() {
                $('#feesTable .student-check').each(function() {
                    this.checked = !!selectedStudents[this.value];
                });
            }
        });
        table.on('draw', updateSelectionUI);
        updateSelectionUI();

        function getMonthName(monthValue) {
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            return months[parseInt(monthValue) - 1];
        }



        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            let month = $('#month').val();
            let class_id = $('#class_id').val();
            let section_id = $('#section_id').val();

            let hasMonth = Array.isArray(month) ? month.length > 0 : !!month;
            if (!hasMonth && (!class_id || !section_id)) {
                alert('Select at least one month, or select both a class and section.');
                return;
            }

            $('#fetchDefaulters').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

            $.ajax({
                url: "<?= base_url('fees/fetch_defaulters') ?>",
                type: "POST",
                data: $('#filterForm').serialize(),
                dataType: "json",
                success: function(response) {
                    let selectedMonths = $('#month').val();
                    if (selectedMonths && selectedMonths.length > 0) {
                        let monthNames = selectedMonths.map(m => getMonthName(m)).join(", ");
                        $('#monthHeading').text(`Outstanding fees for: ${monthNames}`);
                    } else {
                        $('#monthHeading').text('Outstanding fees for the selected class and section.');
                    }

                    table.clear();
                    selectedStudents = {};
                    defaulterRows = {};
                    let total_fee = 0, total_paid = 0, total_due = 0, total_fine = 0;
                    const responseRows = Array.isArray(response.data) ? response.data : [];

                    responseRows.forEach(function(row, index) {
                        const feeItems = Array.isArray(row.fee_items) ? row.fee_items : [];
                        const feeTotal = Number(row.total_fee) || 0;
                        const paidTotal = Number(row.total_paid) || 0;
                        const fineTotal = Number(row.fine_total) || 0;
                        const dueTotal = Number(row.outstanding_total) || 0;
                        const feeHeadNames = feeItems.map(function(item) {
                            return escapeHtml(item.name);
                        }).join(', ');
                        const detailHtml = feeItems.map(function(item) {
                            return `<div class="fee-head-popup-item"><span class="fee-head-popup-item-name">${escapeHtml(item.name)}</span><div class="fee-head-popup-values"><div class="fee-head-popup-value"><span>Assigned</span><strong>₹${money(item.amount)}</strong></div><div class="fee-head-popup-value"><span>Paid</span><strong>₹${money(item.paid)}</strong></div><div class="fee-head-popup-value"><span>Fine Due</span><strong>₹${money(item.fine)}</strong></div><div class="fee-head-popup-value due"><span>Outstanding</span><strong>₹${money(item.due)}</strong></div></div></div>`;
                        }).join('');
                        const studentName = `${row.first_name || ''} ${row.last_name || ''}`.trim();
                        const nameCell = `<button type="button" class="fee-head-view-button" aria-label="View fee heads for ${escapeHtml(studentName)}" title="View fee heads"><i class="fas fa-eye" aria-hidden="true"></i></button><span class="fee-head-export" style="display:none">${feeHeadNames}</span><div class="fee-breakdown" style="display:none">${detailHtml}</div>`;
                        const studentKey = String(row.student_id);
                        defaulterRows[studentKey] = row;
                        total_fee += feeTotal;
                        total_paid += paidTotal;
                        total_fine += fineTotal;
                        total_due += dueTotal;

                        table.row.add([
                            `<input type="checkbox" class="student-check" value="${escapeHtml(studentKey)}">`,
                            index + 1,
                            escapeHtml(row.register_no || '—'),
                            escapeHtml(`${row.first_name || ''} ${row.last_name || ''}`.trim()),
                            escapeHtml(row.roll_no || '—'),
                            escapeHtml(`${row.class_name || ''}${row.section_name ? ' (' + row.section_name + ')' : ''}`),
                            nameCell,
                            `<span class="fee-row-total" style="color:#2166c2">₹${money(feeTotal)}</span>`,
                            `<span class="fee-row-total" style="color:#bf7513">₹${money(fineTotal)}</span>`,
                            `<span class="fee-row-total" style="color:#178258">₹${money(paidTotal)}</span>`,
                            `<span class="fee-row-total" style="color:#c83e4d">₹${money(dueTotal)}</span>`
                        ]);
                    });

                    table.draw();
                    $('#totalFee').text('₹' + money(total_fee));
                    $('#totalFine').text('₹' + money(total_fine));
                    $('#totalPaid').text('₹' + money(total_paid));
                    $('#totalDue').text('₹' + money(total_due));
                    $('#summaryAssigned').text('₹' + money(total_fee));
                    $('#summaryFine').text('₹' + money(total_fine));
                    $('#summaryPaid').text('₹' + money(total_paid));
                    $('#summaryDue').text('₹' + money(total_due));
                    $('#defaulterCount').text(responseRows.length + (responseRows.length === 1 ? ' student' : ' students'));

                    if (responseRows.length === 0) {
                        $('#monthHeading').text('No students have an outstanding balance for the selected filters.');
                    }
                },
                error: function(xhr) {
                    selectedStudents = {};
                    defaulterRows = {};
                    table.clear().draw();
                    $('#monthHeading').text(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to load the report. Please try again.');
                    $('#totalFee, #totalFine, #totalPaid, #totalDue, #summaryAssigned, #summaryFine, #summaryPaid, #summaryDue').text('₹0.00');
                    $('#defaulterCount').text('0 students');
                },
                complete: function() {
                    $('#fetchDefaulters').prop('disabled', false).html('<i class="fa fa-search me-2"></i> Fetch Defaulters');
                }
            });
        });

        function money(value) {
            return (Number(value) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        $('#checkAll').on('change', function() {
            const shouldSelect = this.checked;
            table.rows().nodes().to$().find('.student-check').each(function() {
                if (shouldSelect) {
                    selectedStudents[this.value] = true;
                } else {
                    delete selectedStudents[this.value];
                }
            });
            table.draw(false);
        });

        $('#feesTable').on('change', '.student-check', function() {
            if (this.checked) {
                selectedStudents[this.value] = true;
            } else {
                delete selectedStudents[this.value];
            }
            updateSelectionUI();
        });

        let feeHeadPopupTrigger = null;
        function closeFeeHeadPopup() {
            $('#feeHeadPopup').fadeOut(120, function() {
                $('#feeHeadPopupContent').empty();
                if (feeHeadPopupTrigger) feeHeadPopupTrigger.trigger('focus');
                feeHeadPopupTrigger = null;
            });
        }

        $('#feesTable').on('click', '.fee-head-view-button', function() {
            feeHeadPopupTrigger = $(this);
            const $cell = $(this).closest('td');
            const studentName = $cell.siblings().eq(3).text().trim();
            $('#feeHeadPopupStudent').text(studentName);
            $('#feeHeadPopupContent').html($cell.find('.fee-breakdown').html());
            $('#feeHeadPopup').fadeIn(120);
            $('#closeFeeHeadPopup').trigger('focus');
        });

        $('#closeFeeHeadPopup, #doneFeeHeadPopup').on('click', closeFeeHeadPopup);
        $('#feeHeadPopup').on('click', function(event) {
            if (event.target === this) closeFeeHeadPopup();
        });
        $(document).on('keydown', function(event) {
            if (event.key === 'Escape' && $('#feeHeadPopup').is(':visible')) {
                closeFeeHeadPopup();
            }
        });

        $('#sendMessageBtn').on('click', function() {
            const recipients = Object.keys(selectedStudents)
                .filter(function(studentID) { return selectedStudents[studentID] && defaulterRows[studentID]; })
                .map(function(studentID) { return defaulterRows[studentID]; });
            if (recipients.length === 0) {
                alert('Select at least one student from the report first.');
                return;
            }

            const recipientHtml = recipients.map(function(row) {
                const phone = normalizeWhatsAppPhone(row.guardian_mobile);
                const studentName = `${row.first_name || ''} ${row.last_name || ''}`.trim();
                const months = ($('#month option:selected').map(function() { return $(this).text(); }).get()).join(', ');
                const message = `Namaste Parent/Guardian,\n\nThis is a friendly reminder that ₹${money(row.outstanding_total)} is outstanding for ${studentName}${months ? ' for ' + months : ''}.\n\nKindly clear the pending fees at your convenience. If you have already paid, please ignore this reminder or contact the school office.\n\nRegards,\nSchool Office`;
                const href = phone ? `https://wa.me/${phone}?text=${encodeURIComponent(message)}` : '';
                return `<div class="whatsapp-recipient"><div><span class="whatsapp-recipient-name">${escapeHtml(studentName)}</span><span class="whatsapp-recipient-meta">${phone ? '+' + escapeHtml(phone) : 'Parent mobile number unavailable'} · Due ₹${money(row.outstanding_total)}</span></div>${href ? `<a class="btn btn-success btn-sm" href="${href}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> Open WhatsApp</a>` : '<span class="whatsapp-unavailable">Cannot message: check parent mobile number</span>'}</div>`;
            }).join('');
            $('#whatsappRecipientList').html(recipientHtml);
            $('#whatsappDialog').fadeIn(120);
        });

        $('#closeWhatsappDialog').on('click', function() {
            $('#whatsappDialog').fadeOut(120);
        });
        $('#whatsappDialog').on('click', function(event) {
            if (event.target === this) $(this).fadeOut(120);
        });

        function normalizeWhatsAppPhone(value) {
            let phone = String(value || '').replace(/\D/g, '');
            if (phone.indexOf('00') === 0) phone = phone.substring(2);
            if (phone.length === 11 && phone.charAt(0) === '0') phone = '91' + phone.substring(1);
            if (phone.length === 10) phone = '91' + phone;
            return phone.length >= 11 && phone.length <= 15 ? phone : '';
        }

        $('#resetDefaulterFilters').on('click', function() {
            window.setTimeout(function() {
                $('#month, #feeTypes').val(null).trigger('change');
                $('#class_id').val('');
                $('#section_id').html('<option value="">-- Select Class First --</option>');
                selectedStudents = {};
                defaulterRows = {};
                table.clear().draw();
                updateSelectionUI();
                $('#monthHeading').text('Choose filters and fetch to see outstanding fees.');
                $('#defaulterCount').text('0 students');
                $('#totalFee, #totalFine, #totalPaid, #totalDue, #summaryAssigned, #summaryFine, #summaryPaid, #summaryDue').text('₹0.00');
            }, 0);
        });
    });
</script>
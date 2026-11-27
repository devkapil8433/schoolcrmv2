<!-- DataTables -->
<script src="<?= asset_url('assets/vendor/datatables/media/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/media/js/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.bootstrap.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.html5.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.print.min.js') ?>"></script>
<script src="<?= asset_url('assets/vendor/datatables/extras/TableTools/Buttons-1.4.2/js/buttons.colVis.min.js') ?>"></script>
<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/media/css/dataTables.bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset_url('assets/vendor/datatables/buttons.dataTables.min.css') ?>">

<?php
$selectedBranch = !empty($branch_id) ? $branch_id : set_value('branch_id');
if (empty($selectedBranch) && !is_superadmin_loggedin()) {
	$selectedBranch = get_loggedin_branch_id();
}
?>
<div class="fees-register-wrap">
	<section class="panel fees-register-filter">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-receipt"></i> <?= html_escape($title) ?></h4>
		</header>
		<form id="filterForm">
			<div class="panel-body">
				<div class="fees-register-filters">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="fees-register-field">
						<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
						<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown(
							"branch_id",
							$arrayBranch,
							$selectedBranch,
							"class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'"
						);
						?>
					</div>
					<?php else: ?>
					<input type="hidden" name="branch_id" id="branch_id" value="<?= html_escape($selectedBranch) ?>">
					<?php endif; ?>

					<div class="fees-register-field">
						<label class="control-label">Date Range <span class="required">*</span></label>
						<select class="form-control" name="date_period" id="date_period" data-plugin-selectTwo data-width="100%" required>
							<option value="today" selected>Today</option>
							<option value="yesterday">Yesterday</option>
							<option value="last_7_days">Last 7 Days</option>
							<option value="last_30_days">Last 30 Days</option>
							<option value="custom">Custom Date Range</option>
						</select>
					</div>

					<div class="fees-register-field custom-date-fields" style="display:none;">
						<label class="control-label">From Date</label>
						<input type="date" name="from_date" id="from_date" class="form-control">
					</div>
					<div class="fees-register-field custom-date-fields" style="display:none;">
						<label class="control-label">To Date</label>
						<input type="date" name="to_date" id="to_date" class="form-control">
					</div>

					<div class="fees-register-field fees-register-field--wide">
						<label class="control-label">Fees Type</label>
						<select class="form-control fees-register-fee-types" name="fee_types[]" id="feeTypes" multiple></select>
					</div>
				</div>
			</div>
		</form>
	</section>

	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-list"></i> Paid Receipts</h4>
		</header>
		<div class="panel-body">
			<div class="fees-register-overview" id="feesSummary" style="display:none;">
				<div class="fees-register-chart-card">
					<div class="fees-register-chart-head">
						<strong>Collection Mix</strong>
						<small>Collection · Fine · Discount</small>
					</div>
					<div class="fees-register-chart-wrap">
						<canvas id="feesSummaryChart" height="180"></canvas>
						<div class="fees-register-chart-center">
							<em id="summaryChartLabel">Total</em>
							<span id="summaryChartValue">₹0.00</span>
						</div>
					</div>
					<ul class="fees-register-chart-legend">
						<li class="is-paid"><i></i> Collection</li>
						<li class="is-fine"><i></i> Fine</li>
						<li class="is-discount"><i></i> Discount</li>
					</ul>
				</div>
				<div class="fees-register-stat-grid">
					<div class="summary-chip is-receipts">
						<div class="summary-chip__icon"><i class="fas fa-receipt"></i></div>
						<div class="summary-chip__body">
							<strong>Receipts</strong>
							<span id="summaryCount">0</span>
						</div>
					</div>
					<div class="summary-chip is-fine">
						<div class="summary-chip__icon"><i class="fas fa-exclamation-circle"></i></div>
						<div class="summary-chip__body">
							<strong>Total Fine</strong>
							<span id="summaryFine">₹0.00</span>
						</div>
					</div>
					<div class="summary-chip is-discount">
						<div class="summary-chip__icon"><i class="fas fa-percent"></i></div>
						<div class="summary-chip__body">
							<strong>Total Discount</strong>
							<span id="summaryDiscount">₹0.00</span>
						</div>
					</div>
					<div class="summary-chip is-paid">
						<div class="summary-chip__icon"><i class="fas fa-rupee-sign"></i></div>
						<div class="summary-chip__body">
							<strong>Total Collection</strong>
							<span id="summaryPaid">₹0.00</span>
						</div>
					</div>
				</div>
			</div>

			<div id="resultArea">
				<div class="fees-register-empty" id="emptyState">
					<i class="fas fa-inbox"></i>
					<span>Select a branch to load today's payments.</span>
				</div>
				<div class="table-responsive" id="feesTableWrap">
					<table id="feesTable" class="table table-bordered table-condensed table-hover mb-none">
						<thead>
							<tr>
								<th style="width:36px;"></th>
								<th>Sr.no</th>
								<th>Submitted Date</th>
								<th>Receipt No</th>
								<th>Register No</th>
								<th>Student Name</th>
								<th>Class & Section</th>
								<th>Roll No</th>
								<th>Fee Type (Head)</th>
								<th>Mode</th>
								<th>Fine</th>
								<th>Paid Amount</th>
								<th class="hidden">Items</th>
								<th class="hidden">Item Count</th>
							</tr>
						</thead>
						<tbody></tbody>
						<tfoot>
							<tr>
								<td colspan="10" class="text-right">Totals</td>
								<td>₹0.00</td>
								<td>₹0.00</td>
								<td class="hidden"></td>
								<td class="hidden"></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</section>
</div>

<!-- Payment details modal -->
<div class="modal fade" id="studentPaymentsModal" tabindex="-1" role="dialog" aria-labelledby="studentPaymentsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fas fa-receipt"></i> Payment details</h4>
			</div>
			<div class="modal-body">
				<div id="studentPaymentsInfo" class="fees-register-modal-info mb-sm"></div>
				<div class="table-responsive">
					<table class="table table-bordered table-condensed mb-none" id="studentPaymentsTable">
						<thead>
							<tr>
								<th>#</th>
								<th>Fee Head</th>
								<th>Fine</th>
								<th>Paid Amount</th>
							</tr>
						</thead>
						<tbody></tbody>
						<tfoot>
							<tr>
								<td colspan="3" class="text-right"><strong>Total Collection:</strong></td>
								<td id="modalTotalPaid">₹0.00</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	const baseUrl = "<?= base_url(); ?>";

	function getSelectedBranchId() {
		return ($('#branch_id').val() || '').toString();
	}

	let filtersReady = false;

	function initFeeTypesSelect(optionsHtml) {
		let $feeTypes = $('#feeTypes');
		if ($feeTypes.hasClass('select2-hidden-accessible')) {
			$feeTypes.off('change.feesRegister');
			$feeTypes.select2('destroy');
		}
		$feeTypes.html(optionsHtml || '');
		$feeTypes.select2({
			placeholder: 'Select one or more fee types',
			allowClear: true,
			width: '100%',
			closeOnSelect: false
		});
		$feeTypes.on('change.feesRegister', function() {
			if (filtersReady) {
				fetchRecords();
			}
		});
	}

	function loadFeeTypes() {
		return $.ajax({
			url: baseUrl + "fees/fetch",
			method: "GET",
			dataType: "json",
			success: function(data) {
				let options = '';
				$.each(data || [], function(index, feeTypes) {
					options += `<option value="${feeTypes.id}">${feeTypes.fee_name}</option>`;
				});
				initFeeTypesSelect(options);
			},
			error: function() {
				initFeeTypesSelect('');
				console.error("Failed to load fee Types");
			}
		});
	}
	initFeeTypesSelect('');

	var table;
	if ($.fn.DataTable.isDataTable('#feesTable')) {
		$('#feesTable').DataTable().clear().destroy();
	}
	function stripExportText(data) {
		return $('<div>').html(data == null ? '' : data).text().replace(/\s+/g, ' ').trim();
	}

	function getPdfMeta() {
		let branchName = $('#branch_id option:selected').text() || $('#branch_id').val() || '-';
		if (branchName === 'Select' || branchName === '') {
			branchName = '-';
		}
		let periodLabel = $('#date_period option:selected').text() || 'Today';
		if ($('#date_period').val() === 'custom') {
			periodLabel = ($('#from_date').val() || '-') + ' to ' + ($('#to_date').val() || '-');
		}
		let feeLabel = 'All Fee Types';
		try {
			let feeTypes = $('#feeTypes').hasClass('select2-hidden-accessible')
				? ($('#feeTypes').select2('data') || [])
				: [];
			if (feeTypes.length) {
				feeLabel = feeTypes.map(function(item) { return item.text; }).join(', ');
			}
		} catch (err) {}
		return {
			branchName: branchName,
			periodLabel: periodLabel,
			feeLabel: feeLabel,
			receipts: $('#summaryCount').text() || '0',
			fine: $('#summaryFine').text() || '₹0.00',
			discount: $('#summaryDiscount').text() || '₹0.00',
			collection: $('#summaryPaid').text() || '₹0.00'
		};
	}

	let exportColumns = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
	let exportFormat = {
		body: function(data) { return stripExportText(data); },
		footer: function(data) { return stripExportText(data); },
		header: function(data) { return stripExportText(data); }
	};

	table = $('#feesTable').DataTable({
		dom: '<"row mb-sm"<"col-md-6"B><"col-md-6"f>>tipr',
		buttons: [{
				extend: 'excelHtml5',
				text: '<i class="fas fa-file-excel"></i> Excel',
				className: 'btn btn-default btn-sm',
				title: 'Fees Register',
				exportOptions: {
					columns: exportColumns,
					format: exportFormat
				}
			},
			{
				extend: 'pdfHtml5',
				text: '<i class="fas fa-file-pdf"></i> PDF',
				className: 'btn btn-default btn-sm',
				orientation: 'landscape',
				pageSize: 'A4',
				title: '',
				filename: function() {
					let d = new Date();
					let m = String(d.getMonth() + 1).padStart(2, '0');
					let day = String(d.getDate()).padStart(2, '0');
					return 'Fees_Register_' + d.getFullYear() + '-' + m + '-' + day;
				},
				footer: false,
				exportOptions: {
					columns: exportColumns,
					format: exportFormat
				},
				customize: function(doc) {
					let meta = getPdfMeta();
					let generatedAt = new Date().toLocaleString();
					let pdfSafe = function(value) {
						return String(value == null ? '' : value).replace(/₹/g, 'Rs.');
					};

					doc.pageMargins = [28, 36, 28, 40];
					doc.defaultStyle = {
						fontSize: 8,
						color: '#1f2937'
					};
					doc.styles.tableHeader = {
						bold: true,
						fontSize: 8,
						color: '#ffffff',
						fillColor: '#0f766e',
						alignment: 'left',
						margin: [0, 2, 0, 2]
					};
					doc.styles.tableBodyOdd = {
						fillColor: '#f8fafc'
					};
					doc.styles.tableBodyEven = {
						fillColor: '#ffffff'
					};
					doc.styles.tableFooter = {
						bold: true,
						fontSize: 8,
						color: '#ffffff',
						fillColor: '#115e59'
					};

					// Find the data table node (DataTables may prepend a title).
					let tableNode = null;
					for (let i = 0; i < doc.content.length; i++) {
						if (doc.content[i] && doc.content[i].table && doc.content[i].table.body && doc.content[i].table.body.length) {
							tableNode = doc.content[i];
						}
					}

					if (tableNode && tableNode.table) {
						let colCount = tableNode.table.body[0] ? tableNode.table.body[0].length : 0;
						// pdfMake 0.1.x requires numeric widths; string numbers break layout math.
						let widthMap = {
							11: [28, 70, 70, 55, '*', 70, 40, '*', 45, 45, 70]
						};
						tableNode.table.widths = widthMap[colCount] || Array(colCount).fill('*');
						tableNode.layout = {
							hLineWidth: function() { return 0.4; },
							vLineWidth: function() { return 0.4; },
							hLineColor: function() { return '#d0d5dd'; },
							vLineColor: function() { return '#d0d5dd'; },
							paddingLeft: function() { return 4; },
							paddingRight: function() { return 4; },
							paddingTop: function() { return 3; },
							paddingBottom: function() { return 3; }
						};
						tableNode.table.body.forEach(function(row, rowIndex) {
							row.forEach(function(cell) {
								if (cell && typeof cell === 'object' && cell.text != null) {
									cell.text = pdfSafe(cell.text);
								}
							});
							if (colCount >= 11) {
								[0, 6, 9, 10].forEach(function(ci) {
									if (row[ci] && typeof row[ci] === 'object') {
										row[ci].alignment = 'right';
									}
								});
							}
							if (rowIndex === 0) {
								row.forEach(function(cell) {
									if (cell && typeof cell === 'object') {
										cell.alignment = cell.alignment || 'left';
									}
								});
							}
						});
					}

					doc.content.unshift(
						{
							columns: [
								{
									width: '*',
									stack: [
										{ text: 'FEES REGISTER', fontSize: 16, bold: true, color: '#0f766e', margin: [0, 0, 0, 2] },
										{ text: 'Paid receipts report', fontSize: 9, color: '#667085' }
									]
								},
								{
									width: 'auto',
									stack: [
										{ text: 'Generated', fontSize: 8, color: '#98a2b3', alignment: 'right' },
										{ text: generatedAt, fontSize: 9, color: '#344054', alignment: 'right', margin: [0, 2, 0, 0] }
									]
								}
							],
							margin: [0, 0, 0, 10]
						},
						{
							columns: [
								{ text: 'Branch: ' + pdfSafe(meta.branchName), fontSize: 9, color: '#344054' },
								{ text: 'Period: ' + pdfSafe(meta.periodLabel), fontSize: 9, color: '#344054', alignment: 'center' },
								{ text: 'Fee Types: ' + pdfSafe(meta.feeLabel), fontSize: 9, color: '#344054', alignment: 'right' }
							],
							margin: [0, 0, 0, 6]
						},
						{
							table: {
								widths: ['*', '*', '*', '*'],
								body: [[
									{ text: 'Receipts\n' + pdfSafe(meta.receipts), alignment: 'center', fillColor: '#eff6ff', color: '#1d4ed8', bold: true, margin: [4, 6, 4, 6] },
									{ text: 'Total Fine\n' + pdfSafe(meta.fine), alignment: 'center', fillColor: '#fffbeb', color: '#b45309', bold: true, margin: [4, 6, 4, 6] },
									{ text: 'Total Discount\n' + pdfSafe(meta.discount), alignment: 'center', fillColor: '#f0fdfa', color: '#0f766e', bold: true, margin: [4, 6, 4, 6] },
									{ text: 'Total Collection\n' + pdfSafe(meta.collection), alignment: 'center', fillColor: '#f0fdf4', color: '#15803d', bold: true, margin: [4, 6, 4, 6] }
								]]
							},
							layout: {
								hLineWidth: function() { return 0; },
								vLineWidth: function() { return 4; },
								vLineColor: function() { return '#ffffff'; },
								paddingLeft: function() { return 2; },
								paddingRight: function() { return 2; }
							},
							margin: [0, 0, 0, 12]
						}
					);

					doc.footer = function(currentPage, pageCount) {
						return {
							margin: [28, 0, 28, 0],
							columns: [
								{ text: 'Fees Register', fontSize: 8, color: '#98a2b3' },
								{ text: 'Page ' + currentPage + ' of ' + pageCount, alignment: 'right', fontSize: 8, color: '#98a2b3' }
							]
						};
					};
				}
			}
		],
		pageLength: 25,
		language: {
			search: "_INPUT_",
			searchPlaceholder: "Search student, receipt, class...",
			emptyTable: "No payment records to display",
			zeroRecords: "No matching payment records"
		},
		columnDefs: [
			{ targets: [12, 13], visible: false, searchable: false },
			{ targets: 0, orderable: false, className: 'text-center' }
		]
	});

	function formatPaidCell(paid, discount) {
		let html = '<div class="paid-cell"><span class="amount-paid">₹' + Number(paid || 0).toFixed(2) + '</span>';
		if (Number(discount || 0) > 0) {
			html += '<span class="fee-discount-line">Discount: ₹' + Number(discount).toFixed(2) + '</span>';
		}
		html += '</div>';
		return html;
	}

	function normalizeItems(items) {
		if (typeof items === 'string') {
			try {
				items = JSON.parse(items);
			} catch (err) {
				return [];
			}
		}
		return Array.isArray(items) ? items : [];
	}

	function uniqueJoined(value, separator) {
		separator = separator || '<br>';
		let parts = (value || '').toString().split(/[\n,]+/).map(function(v) {
			return v.trim();
		}).filter(Boolean);
		let unique = [];
		parts.forEach(function(part) {
			if (unique.indexOf(part) === -1) {
				unique.push(part);
			}
		});
		return unique.join(separator);
	}

	let feesSummaryChart = null;

	function ensureSummaryChart() {
		let canvas = document.getElementById('feesSummaryChart');
		if (!canvas || typeof Chart === 'undefined') {
			return null;
		}
		if (feesSummaryChart) {
			return feesSummaryChart;
		}
		feesSummaryChart = new Chart(canvas.getContext('2d'), {
			type: 'doughnut',
			data: {
				labels: ['Collection', 'Fine', 'Discount'],
				datasets: [{
					data: [0, 0, 0],
					backgroundColor: ['#16a34a', '#f59e0b', '#0d9488'],
					borderWidth: 0,
					hoverBorderWidth: 0
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				cutoutPercentage: 72,
				legend: {
					display: false
				},
				tooltips: {
					callbacks: {
						label: function(tooltipItem, data) {
							let value = data.datasets[0].data[tooltipItem.index] || 0;
							return ' ' + data.labels[tooltipItem.index] + ': ₹' + Number(value).toFixed(2);
						}
					}
				}
			}
		});
		return feesSummaryChart;
	}

	function setSummary(count, fine, discount, paid) {
		fine = Number(fine || 0);
		discount = Number(discount || 0);
		paid = Number(paid || 0);

		$('#summaryCount').text(count);
		$('#summaryFine').text('₹' + fine.toFixed(2));
		$('#summaryDiscount').text('₹' + discount.toFixed(2));
		$('#summaryPaid').text('₹' + paid.toFixed(2));
		$('#summaryChartValue').text('₹' + paid.toFixed(2));
		$('#summaryChartLabel').text('Collection');
		$('#feesSummary').toggle(count > 0);

		let chart = ensureSummaryChart();
		if (chart) {
			let collectionSlice = Math.max(paid, 0);
			let fineSlice = Math.max(fine, 0);
			let discountSlice = Math.max(discount, 0);
			if (collectionSlice === 0 && fineSlice === 0 && discountSlice === 0) {
				chart.data.datasets[0].data = [1, 0, 0];
				chart.data.datasets[0].backgroundColor = ['#e5e7eb', '#e5e7eb', '#e5e7eb'];
			} else {
				chart.data.datasets[0].data = [collectionSlice, fineSlice, discountSlice];
				chart.data.datasets[0].backgroundColor = ['#16a34a', '#f59e0b', '#0d9488'];
			}
			chart.update();
		}
	}

	function setFooter(fine, discount, paid) {
		let footerCells = $('#feesTable tfoot tr td');
		footerCells.eq(1).html('₹' + Number(fine || 0).toFixed(2));
		footerCells.eq(2).html(formatPaidCell(paid, discount));
	}

	function showEmpty(message, isLoading) {
		let icon = isLoading ? 'fas fa-spinner fa-spin' : 'fas fa-inbox';
		$('#emptyState').html('<i class="' + icon + '"></i><span>' + message + '</span>').show();
		$('#feesTableWrap').hide();
		if (!isLoading) {
			setSummary(0, 0, 0, 0);
			setFooter(0, 0, 0);
		}
	}

	function showTable() {
		$('#emptyState').hide();
		$('#feesTableWrap').show();
		if ($.fn.DataTable.isDataTable('#feesTable')) {
			table.columns.adjust();
		}
	}

	function openPaymentsModal(rowData) {
		let items = normalizeItems(rowData[12]);
		let studentName = rowData[5] || '';
		let classSection = rowData[6] || '';
		let registerNo = rowData[4] || '';
		let receiptNo = uniqueJoined(rowData[3] || '', ', ');
		let mode = uniqueJoined(rowData[9] || '', ', ');
		let submittedDate = uniqueJoined(rowData[2] || '', ', ');
		if (!submittedDate && items.length) {
			submittedDate = uniqueJoined(items.map(function(f) { return f.date || ''; }).join(','), ', ');
		}

		$('#studentPaymentsInfo').html(`
			<div class="row">
				<div class="col-md-4"><strong>Student:</strong> ${studentName}</div>
				<div class="col-md-4"><strong>Class:</strong> ${classSection}</div>
				<div class="col-md-4"><strong>Register No:</strong> ${registerNo}</div>
			</div>
			<div class="row mt-sm">
				<div class="col-md-4"><strong>Date:</strong> ${submittedDate || '-'}</div>
				<div class="col-md-4"><strong>Receipt:</strong> ${receiptNo || '-'}</div>
				<div class="col-md-4"><strong>Mode:</strong> ${mode || '-'}</div>
			</div>
		`);

		let tbody = '';
		let totalPaid = 0;
		let totalDiscount = 0;
		items.forEach(function(f, idx) {
			let paid = Number(f.paid_amount || 0);
			let discount = Number(f.discount || 0);
			totalPaid += paid;
			totalDiscount += discount;
			tbody += `
				<tr>
					<td>${idx + 1}</td>
					<td>${f.fee_name || ''}</td>
					<td>${Number(f.fine || 0).toFixed(2)}</td>
					<td>${formatPaidCell(paid, discount)}</td>
				</tr>`;
		});

		if (!items.length) {
			tbody = '<tr><td colspan="4" class="text-center text-muted">No payment items found.</td></tr>';
		}

		$('#studentPaymentsTable tbody').html(tbody);
		$('#modalTotalPaid').html(formatPaidCell(totalPaid, totalDiscount));
		$('#studentPaymentsModal').modal('show');
	}

	$('#feesTable tbody').on('click', 'button.view-details', function() {
		let rowData = table.row($(this).closest('tr')).data();
		openPaymentsModal(rowData);
	});

	function formatDateYMD(dateObj) {
		let y = dateObj.getFullYear();
		let m = String(dateObj.getMonth() + 1).padStart(2, '0');
		let d = String(dateObj.getDate()).padStart(2, '0');
		return y + '-' + m + '-' + d;
	}

	function getDateRangeFromPeriod(period) {
		let today = new Date();
		today.setHours(0, 0, 0, 0);
		let from = new Date(today);
		let to = new Date(today);

		if (period === 'yesterday') {
			from.setDate(today.getDate() - 1);
			to.setDate(today.getDate() - 1);
		} else if (period === 'last_7_days') {
			from.setDate(today.getDate() - 6);
		} else if (period === 'last_30_days') {
			from.setDate(today.getDate() - 29);
		} else if (period === 'custom') {
			return {
				from_date: $('#from_date').val() || '',
				to_date: $('#to_date').val() || ''
			};
		}

		return {
			from_date: formatDateYMD(from),
			to_date: formatDateYMD(to)
		};
	}

	function toggleCustomDateFields() {
		let isCustom = $('#date_period').val() === 'custom';
		$('.custom-date-fields').toggle(isCustom);
		$('#from_date, #to_date').prop('required', isCustom);
	}

	let fetchRequest = null;

	function fetchRecords() {
		let period = $('#date_period').val();
		let range = getDateRangeFromPeriod(period);
		let from_date = range.from_date;
		let to_date = range.to_date;
		let feeTypes = $('#feeTypes').val();
		let branch_id = getSelectedBranchId();

		if (!branch_id) {
			showEmpty('Select a branch to load today\'s payments.');
			return;
		}

		if (period === 'custom' && (from_date === '' || to_date === '')) {
			showEmpty('Select From Date and To Date.');
			return;
		}

		if (from_date === '' || to_date === '') {
			showEmpty('Select a date range.');
			return;
		}

		if (from_date > to_date) {
			showEmpty('From Date cannot be greater than To Date.');
			return;
		}

		if (fetchRequest && fetchRequest.readyState !== 4) {
			fetchRequest.abort();
		}

		showEmpty('Loading payments...', true);

		fetchRequest = $.ajax({
			url: "<?= base_url('fees/fetch_paid_students') ?>",
			type: "POST",
			data: {
				from_date: from_date,
				to_date: to_date,
				feeTypes: feeTypes,
				branch_id: branch_id
			},
			dataType: "json",
			success: function(response) {
				table.clear().draw();
				let rows = response.data || [];
				let grandTotal = 0;
				let grandFine = 0;
				let grandDiscount = 0;

				if (rows.length > 0) {
					rows.forEach(function(row, index) {
						let toggleIcon = `<button type="button" class="view-details" title="View details"><i class="fas fa-eye"></i></button>`;

						let paidTotal = parseFloat(row.total_paid_sum || 0);
						let fineTotal = parseFloat(row.total_fine_sum || 0);
						let discountTotal = parseFloat(row.total_discount_sum || 0);
						grandTotal += paidTotal;
						grandFine += fineTotal;
						grandDiscount += discountTotal;

						table.row.add([
							toggleIcon,
							index + 1,
							uniqueJoined(row.date, '<br>'),
							uniqueJoined(row.receipt_no, ', '),
							row.register_no ?? '',
							((row.first_name || '') + ' ' + (row.last_name || '')).trim(),
							(row.class_name || '') + ' (' + (row.section_name || '') + ')',
							row.roll_no ?? '',
							uniqueJoined(row.fee_name, '<br>'),
							uniqueJoined(row.payment_method, ', '),
							fineTotal.toFixed(2),
							formatPaidCell(paidTotal, discountTotal),
							JSON.stringify(normalizeItems(row.items)),
							row.item_count || 0
						]);
					});

					table.draw();
					setFooter(grandFine, grandDiscount, grandTotal);
					setSummary(rows.length, grandFine, grandDiscount, grandTotal);
					showTable();
				} else {
					showEmpty('No payment records found for the selected filters.');
				}
			},
			error: function(xhr, status) {
				if (status === 'abort') {
					return;
				}
				showEmpty('Something went wrong while fetching records.');
			}
		});
	}

	$('#date_period').on('change', function() {
		toggleCustomDateFields();
		if (!filtersReady) {
			return;
		}
		if ($(this).val() !== 'custom') {
			fetchRecords();
		}
	});
	$('#from_date, #to_date').on('change', function() {
		if (filtersReady) {
			fetchRecords();
		}
	});
	$('#branch_id').on('change', function() {
		if (filtersReady) {
			fetchRecords();
		}
	});

	$('#filterForm').on('submit', function(e) {
		e.preventDefault();
		fetchRecords();
	});

	toggleCustomDateFields();

	$.when(loadFeeTypes()).always(function() {
		filtersReady = true;
		if (getSelectedBranchId()) {
			fetchRecords();
		} else {
			showEmpty('Select a branch to load today\'s payments.');
		}
	});
});
</script>

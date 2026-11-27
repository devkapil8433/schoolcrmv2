<style>
	#invoice_print {
		padding-bottom: 72px;
	}

	.fees-student-card {
		display: flex;
		align-items: stretch;
		gap: 18px;
		padding: 16px 18px;
		margin-bottom: 16px;
		border: 1px solid #e6e8ec;
		border-radius: 10px;
		background: linear-gradient(180deg, #fbfcfd 0%, #f4f6f8 100%);
	}

	.fees-student-card__photo {
		flex: 0 0 88px;
		width: 88px;
		height: 88px;
		border-radius: 10px;
		overflow: hidden;
		border: 1px solid #d7dbe2;
		background: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.fees-student-card__photo img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.fees-student-card__body {
		flex: 1;
		min-width: 0;
	}

	.fees-student-card__name {
		margin: 0 0 4px;
		font-size: 20px;
		font-weight: 700;
		color: #222;
		line-height: 1.25;
	}

	.fees-student-card__class {
		margin: 0 0 12px;
		color: #667085;
		font-size: 13px;
	}

	.fees-student-meta {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 8px 16px;
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.fees-student-meta li {
		min-width: 0;
		font-size: 13px;
		color: #333;
		line-height: 1.35;
	}

	.fees-student-meta > li > span:first-child {
		display: block;
		font-size: 11px;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #8a94a6;
		margin-bottom: 2px;
	}

	.fees-invoice-toolbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 10px;
		flex-wrap: wrap;
	}

	.fees-invoice-toolbar__hint {
		margin: 0;
		font-size: 12px;
		color: #8a94a6;
	}

	#studentPaymentReceipts {
		margin-bottom: 0;
		border: 1px solid #e4e7ec;
		border-radius: 8px;
		overflow: hidden;
	}

	#studentPaymentReceipts thead th {
		background: #f6f8fb;
		color: #475467;
		font-size: 12px;
		white-space: nowrap;
		border-bottom: 1px solid #e4e7ec;
	}

	#studentPaymentReceipts tbody td {
		vertical-align: middle;
	}

	.receipt-action-group {
		display: inline-flex;
		gap: 4px;
	}

	.receipt-correction-amount {
		max-width: 150px;
	}

	.invoice-summary--cards {
		margin: 0 0 18px;
		padding: 0;
		border: 0;
		background: transparent;
	}

	.invoice-summary--cards .amounts {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 12px;
	}

	.invoice-summary--cards .amounts li {
		position: relative;
		min-width: 0;
		overflow: hidden;
		padding: 15px 16px 16px 19px;
		border: 1px solid transparent;
		border-radius: 12px;
		box-shadow: 0 4px 14px rgba(16, 24, 40, .06);
		white-space: normal;
	}

	.invoice-summary--cards .amounts li::before {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		width: 5px;
		content: "";
		background: currentColor;
		opacity: .85;
	}

	.invoice-summary--cards .amounts li strong {
		display: flex;
		align-items: center;
		gap: 7px;
		margin-bottom: 8px;
		font-size: 11px;
		letter-spacing: .06em;
		color: inherit;
		opacity: .82;
	}

	.invoice-summary--cards .amounts li span {
		display: block;
		font-size: 22px;
		font-weight: 750;
		line-height: 1.2;
		color: #172033;
	}

	.invoice-summary--cards .summary-total {
		color: #1d4ed8;
		border-color: #bfdbfe !important;
		background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
	}

	.invoice-summary--cards .summary-paid {
		color: #047857;
		border-color: #a7f3d0 !important;
		background: linear-gradient(135deg, #ecfdf5, #d1fae5) !important;
	}

	.invoice-summary--cards .summary-fine {
		color: #b45309;
		border-color: #fde68a !important;
		background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
	}

	.invoice-summary--cards .summary-balance {
		color: #b91c1c;
		border-color: #fecaca !important;
		background: linear-gradient(135deg, #fff1f2, #ffe4e6) !important;
	}

	#invoiceSummary {
		font-size: 13px;
		min-width: 1100px;
	}

	#invoiceSummary thead th {
		white-space: nowrap;
		background: #f7f8fa;
		border-bottom: 1px solid #e4e7ec !important;
		vertical-align: middle;
	}

	#invoiceSummary tbody td {
		vertical-align: middle;
		white-space: nowrap;
	}

	#invoiceSummary tbody td:nth-child(7) {
		white-space: normal;
		min-width: 120px;
		max-width: 220px;
	}

	#invoiceSummary .month-row {
		cursor: pointer;
	}

	#invoiceSummary .month-row > td {
		font-weight: 700 !important;
		background: #e8eef5 !important;
		border-top: 1px solid #cfd8e3 !important;
		border-bottom: 1px solid #cfd8e3 !important;
	}

	#invoiceSummary .month-row .month-toggle {
		display: inline-flex;
		align-items: center;
		gap: 8px;
	}

	#invoiceSummary .month-row .month-toggle i {
		width: 16px;
		color: #667085;
		transition: transform .15s ease;
	}

	#invoiceSummary .month-row.is-open .month-toggle i {
		transform: rotate(90deg);
	}

	/* Nested fee items under month — keep as table-row, never display:block */
	#invoiceSummary .fee-row {
		display: none;
	}

	#invoiceSummary .fee-row.is-visible {
		display: table-row;
	}

	#invoiceSummary .fee-row > td {
		font-weight: 400 !important;
		color: #555 !important;
		border-top: none !important;
		border-bottom: 1px solid #eef1f5 !important;
		background: #f7f9fb !important;
		font-size: 12.5px;
	}

	#invoiceSummary .fee-row > td:first-child {
		box-shadow: inset 3px 0 0 #8fa6bf;
	}

	#invoiceSummary .fee-row > td:nth-child(4) {
		padding-left: 48px !important;
		position: relative;
	}

	#invoiceSummary .fee-row > td:nth-child(4)::before {
		content: '';
		position: absolute;
		left: 22px;
		top: 0;
		bottom: 50%;
		width: 1px;
		background: #b7c4d4;
	}

	#invoiceSummary .fee-row > td:nth-child(4)::after {
		content: '';
		position: absolute;
		left: 22px;
		top: 50%;
		width: 14px;
		height: 1px;
		background: #b7c4d4;
	}

	#invoiceSummary .fee-row .num-col {
		color: #98a2b3;
		font-size: 12px;
	}

	#invoiceSummary .cb-col,
	#invoiceSummary .collect-col,
	#invoiceSummary .num-col {
		width: 36px;
		min-width: 36px;
		max-width: 36px;
		text-align: center;
		vertical-align: middle !important;
		white-space: nowrap;
		padding-left: 4px !important;
		padding-right: 4px !important;
	}

	#invoiceSummary .cb-col .checkbox-replace,
	#invoiceSummary .cb-col .i-checks {
		display: inline-block;
		margin: 0;
		vertical-align: middle;
	}

	#invoiceSummary .cb-col input[type="checkbox"] {
		margin: 0;
		vertical-align: middle;
	}

	#invoiceSummary .num-col {
		font-weight: 600;
	}

	#invoiceSummary .collect-col .collect-fee-row {
		padding: 2px 5px;
		line-height: 1;
		border-radius: 4px;
		color: #b45309;
		border-color: #f0c36d;
		background: #fffbeb;
	}

	#invoiceSummary .collect-col .collect-fee-row:hover {
		background: #fef3c7;
		color: #92400e;
	}

	#invoiceSummary .paid-cell {
		white-space: nowrap;
		line-height: 1.25;
	}

	#invoiceSummary .paid-cell .fee-discount-line {
		display: block;
		margin-top: 3px;
		font-size: 11px;
		font-weight: 600;
		color: #0d9488;
	}

	.invoice-summary .amounts li.summary-discount {
		border-color: #99f6e4;
		background: #f0fdfa;
		color: #0f766e;
	}

	.invoice-summary .amounts li.summary-discount strong {
		color: #0d9488;
	}

	.invoice-summary {
		background: #f8f9fa;
		padding: 14px 16px;
		border-radius: 8px;
		border: 1px solid #e4e7ec;
		margin-bottom: 16px;
	}

	.invoice-summary .amounts {
		list-style: none;
		padding: 0;
		margin: 0;
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
	}

	.invoice-summary .amounts li {
		flex: 1 1 140px;
		white-space: nowrap;
		font-weight: 600;
		color: #222;
		background: #fff;
		border: 1px solid #e4e7ec;
		border-radius: 8px;
		padding: 10px 12px;
	}

	.invoice-summary .amounts li strong {
		display: block;
		font-size: 11px;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #8a94a6;
		margin-bottom: 2px;
		min-width: 0;
		font-weight: 600;
	}

	@media (max-width: 991px) {
		.fees-student-meta {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.invoice-summary--cards .amounts {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 640px) {
		.fees-student-card {
			flex-direction: column;
			align-items: flex-start;
		}

		.fees-student-meta {
			grid-template-columns: 1fr;
		}

		.invoice-summary--cards .amounts li span {
			font-size: 19px;
		}
	}

	@media print {
		.fees-student-card {
			background: #fff;
			border-color: #ccc;
		}

		#invoiceSummary {
			min-width: 0;
			font-size: 11px;
		}

		#invoiceSummary .fee-row,
		#invoiceSummary .fee-row.is-visible {
			display: table-row !important;
		}

		#invoiceSummary .fee-row td:nth-child(4) {
			padding-left: 16px;
		}

		#invoiceSummary .fee-row td:nth-child(4)::before,
		#invoiceSummary .fee-row td:nth-child(4)::after {
			display: none;
		}
	}
</style>

<?php
$currency_symbol = $global_config['currency_symbol'];
$extINTL = extension_loaded('intl');
if ($extINTL == true) {
	$spellout = new NumberFormatter("en", NumberFormatter::SPELLOUT);
}
?>
<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#invoice" data-toggle="tab"><i class="far fa-credit-card"></i> <?= translate('invoice') ?></a>
			</li>
			<li>
				<a href="#history" data-toggle="tab"><i class="fas fa-dollar-sign"></i> <?= translate('payment_history') ?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div id="invoice" class="tab-pane <?= empty($this->session->flashdata('pay_tab')) ? 'active' : ''; ?>">
				<div id="invoice_print">
					<div class="invoice">
						<!-- <header class="clearfix">
							<div class="row">
								<div class="col-xs-6">
									<div class="ib">
										<img src="<?= $this->application_model->getBranchImage($basic['branch_id'], 'printing-logo') ?>" alt="School Logo" />
									</div>
								</div>
								<div class="col-md-6 text-right">
									<h4 class="mt-none mb-none text-dark">Fee Account Ref: <?= html_escape($invoice['invoice_no']) ?></h4>
									<p class="mb-none">
										<span class="text-dark"><?= translate('date') ?> : </span>
										<span class="value"><?= _d(date('Y-m-d')) ?></span>
									</p>
								</div>
							</div>
						</header> -->
						<!-- <div class="bill-info">
							<div class="row">
								<div class="col-xs-6">
									<div class="bill-data">
										<p class="h5 mb-xs text-dark text-weight-semibold">Invoice To :</p>
										<address>
											<?php
											echo $basic['first_name'] . ' ' . $basic['last_name'] . '<br>';
											echo translate('register_no') . ' : ' . $basic['register_no'] . '<br>';
											echo (empty($basic['student_address']) ? "" : nl2br($basic['student_address']) . '<br>');
											echo translate('class') . ' : ' . $basic['class_name'] . " (" . $basic['section_name'] . ')<br>';
											if (!empty($basic['father_name'])) {
												echo translate('father_name') . ' : ' . $basic['father_name'];
											}
											?>
										</address>
									</div>
								</div>
								<div class="col-xs-6">
									<div class="bill-data text-right">
										<p class="h5 mb-xs text-dark text-weight-semibold">Academic :</p>
										<address>
											<?php
											echo $basic['school_name'] . "<br/>";
											echo $basic['school_address'] . "<br/>";
											echo $basic['school_mobileno'] . "<br/>";
											echo $basic['school_email'] . "<br/>";
											?>
										</address>
									</div>
								</div>
							</div>
						</div> -->
						<div class="bill-info">
							<div class="fees-student-card">
								<div class="fees-student-card__photo">
									<img src="<?= get_image_url('student', $basic['photo'] ?? '') ?>" alt="<?= html_escape(trim(($basic['first_name'] ?? '') . ' ' . ($basic['last_name'] ?? ''))) ?>">
								</div>
								<div class="fees-student-card__body">
									<h3 class="fees-student-card__name"><?= html_escape(trim(($basic['first_name'] ?? '') . ' ' . ($basic['last_name'] ?? ''))) ?></h3>
									<p class="fees-student-card__class"><?= html_escape(($basic['class_name'] ?? '') . ' (' . ($basic['section_name'] ?? '') . ')') ?></p>
									<ul class="fees-student-meta">
										<li><span><?= translate('father_name') ?></span><?= html_escape($basic['father_name'] ?? '—') ?></li>
										<li><span><?= translate('register_no') ?></span><?= html_escape($basic['register_no'] ?? '—') ?></li>
										<li><span><?= translate('roll') ?></span><?= html_escape($basic['roll'] ?? '—') ?></li>
										<li><span><?= translate('category') ?></span><?= html_escape($basic['category'] ?? '—') ?></li>
										<li><span><?= translate('mobile_no') ?></span><?= html_escape($basic['mobileno'] ?? '—') ?></li>
										<li><span><?= translate('status') ?></span>
											<?php
											$invStatus = $invoice['status'] ?? '';
											if ($invStatus == 'unpaid') {
												echo '<span class="label-danger-custom">' . translate('unpaid') . '</span>';
											} elseif ($invStatus == 'partly') {
												echo '<span class="label-info-custom">' . translate('partly_paid') . '</span>';
											} elseif ($invStatus == 'total') {
												echo '<span class="label-success-custom">' . translate('total_paid') . '</span>';
											} else {
												echo html_escape($invStatus ?: '—');
											}
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="fees-invoice-toolbar hidden-print">
							<?php if (get_permission('collect_fees', 'is_add')) { ?>
								<button type="button" class="btn btn-default btn-sm" id="collectFees" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<i class="fas fa-coins fa-fw"></i> Selected Fees Collect
								</button>
							<?php } else { ?>
								<span></span>
							<?php } ?>
							<p class="fees-invoice-toolbar__hint">Expand a month to view fee items. Use checkboxes to collect selected fees.</p>
						</div>
						<div class="table-responsive br-none fees-invoice-table-wrap">
							<table class="table invoice-items table-hover mb-none" id="invoiceSummary">
								<thead>
									<tr class="text-dark">
										<th class="text-weight-semibold hidden-print cb-col">
											<div class="checkbox-replace">
												<label class="i-checks" data-toggle="tooltip" data-original-title="Show / Hide All">
													<input type="checkbox" class="fee-selectAll"><i></i>
												</label>
											</div>
										</th>
										<th class="text-weight-semibold hidden-print collect-col"></th>
										<th class="text-weight-semibold hidden-print num-col">#</th>
										<th class="text-weight-semibold"><?= translate("fees_type") ?></th>
										<th class="text-weight-semibold"><?= translate("due_date") ?></th>
										<th class="text-weight-semibold"><?= translate("status") ?></th>
										<th class="text-weight-semibold"><?= translate("amount") ?></th>
										<th class="text-weight-semibold"><?= translate("reciept") ?></th>
										<th class="text-weight-semibold"><?= translate("mode") ?></th>
										<th class="text-weight-semibold"><?= translate("fine") ?></th>
										<th class="text-weight-semibold"><?= translate("paid") ?></th>
										<th class="text-center text-weight-semibold"><?= translate("balance") ?></th>
									</tr>
								</thead>




								<tbody>
									<?php
									$count = 1;
									$total_fine = $total_discount = $total_paid = $total_balance = $total_amount = 0;
									$fully_total_fine = 0;
									$typeData = array('' => translate('select'));

									$allMonths = [
										4 => 'April',
										5 => 'May',
										6 => 'June',
										7 => 'July',
										8 => 'August',
										9 => 'September',
										10 => 'October',
										11 => 'November',
										12 => 'December',
										1 => 'January',
										2 => 'February',
										3 => 'March'
									];


									$allocations = $this->fees_model->getStudentFeeDetails($basic['enroll_id']);
									$transportFees = $transport_fees;
									$salesBills = $salesBills ?? [];
									$monthlyCollections = $this->fees_model->getStudentMonthlyCollections($basic['id'] ?? null);

									$monthTotals = array();
									foreach ($allMonths as $monthNo => $monthName) {
										$monthTotals[$monthNo] = array(
											'amount' => 0,
											'discount_due' => 0,
											'discount_paid' => 0,
											'fine_due' => 0,
											'fine_paid' => 0,
											'paid' => 0,
											'balance' => 0,
										);
									}

									$lastBalance = !empty($allocations[0]['previous_last_balance'])
										? (float) $allocations[0]['previous_last_balance']
										: 0;
									if ($lastBalance > 0) {
										$monthTotals[4]['amount'] += $lastBalance;
										$monthTotals[4]['balance'] += $lastBalance;
									}

									foreach ($allocations as $row) {
										$assignedMonths = array_filter(array_map('intval', explode(',', (string) ($row['month'] ?? ''))));
										$fee_amount = (float) ($row['amount'] ?? 0);
										$type_fine = (float) ($row['fine_due'] ?? $this->fees_model->resolveConfiguredFine($row));
										$type_discount = (float) ($row['discount_due'] ?? 0);
										$type_paid = (float) ($row['paid_amount'] ?? 0);
										$paid_discount = (float) ($row['discount'] ?? 0);
										$paid_fine = (float) ($row['fine'] ?? 0);
										$balance = (float) ($row['balance'] ?? 0);
										if ($balance < 0) {
											$balance = 0;
										}

										foreach ($assignedMonths as $assignedMonth) {
											if (!isset($monthTotals[$assignedMonth])) {
												continue;
											}
											$monthTotals[$assignedMonth]['amount'] += $fee_amount;
											$monthTotals[$assignedMonth]['discount_due'] += $type_discount;
											$monthTotals[$assignedMonth]['discount_paid'] += $paid_discount;
											$monthTotals[$assignedMonth]['fine_due'] += $type_fine;
											$monthTotals[$assignedMonth]['fine_paid'] += $paid_fine;
											$monthTotals[$assignedMonth]['paid'] += $type_paid;
											$monthTotals[$assignedMonth]['balance'] += $balance;
										}
									}

									if (!empty($transportFees)) {
										foreach ($transportFees as $t) {
											$tMonth = (int) $t->month;
											if (!isset($monthTotals[$tMonth])) {
												continue;
											}
											$tf_amount = (float) $t->route_fare;
											$tfDeposit = $this->fees_model->getStudentTransportFeeDeposit($t->id);
											$tfPaid = !empty($tfDeposit) ? ((float) $tfDeposit['total_amount']) : 0;
											$tfDiscount = !empty($tfDeposit) ? ((float) $tfDeposit['total_discount']) : 0;
											$tfFine = !empty($tfDeposit) ? ((float) $tfDeposit['total_fine']) : 0;
											$monthTotals[$tMonth]['amount'] += $tf_amount;
											$monthTotals[$tMonth]['paid'] += $tfPaid;
											$monthTotals[$tMonth]['discount_paid'] += $tfDiscount;
											$monthTotals[$tMonth]['fine_paid'] += $tfFine;
											$monthTotals[$tMonth]['balance'] += max(0, $tf_amount - $tfPaid - $tfDiscount);
										}
									}

									if (!empty($salesBills)) {
										foreach ($salesBills as $bill) {
											$billMonth = (int) date('n', strtotime($bill->date));
											if (!isset($monthTotals[$billMonth])) {
												continue;
											}
											$monthTotals[$billMonth]['amount'] += (float) $bill->total;
											$monthTotals[$billMonth]['discount_paid'] += (float) $bill->discount;
											$monthTotals[$billMonth]['paid'] += (float) $bill->paid;
											$monthTotals[$billMonth]['balance'] += (float) $bill->due;
										}
									}

									// Merge month-level collect totals (covers last_balance / edge cases)
									foreach ($monthlyCollections as $mNo => $collectRow) {
										if (!isset($monthTotals[$mNo])) {
											continue;
										}
										// Prefer allocation-based paid already summed; only fill discount/fine if collect has more
										if ((float) $monthTotals[$mNo]['discount_paid'] <= 0 && !empty($collectRow['discount'])) {
											$monthTotals[$mNo]['discount_paid'] = (float) $collectRow['discount'];
										}
										if ((float) $monthTotals[$mNo]['fine_paid'] <= 0 && !empty($collectRow['fine'])) {
											$monthTotals[$mNo]['fine_paid'] = (float) $collectRow['fine'];
										}
									}

									foreach ($allMonths as $monthNo => $monthName):
										$monthSummary = $monthTotals[$monthNo];
									?>

										<!-- Month Row -->
										<tr class="month-row" data-month="<?= $monthNo ?>">
											<td class="hidden-print cb-col">
												<div class="checkbox-replace">
													<label class="i-checks">
														<input type="checkbox" class="month-checkbox" data-month="<?= $monthNo ?>">
														<i></i>
													</label>
												</div>
											</td>
											<td class="hidden-print collect-col"></td>
											<td class="hidden-print num-col"><?= $count ?></td>
											<td class="text-dark">
												<span class="month-toggle"><i class="fas fa-caret-right"></i><strong><?= $monthName ?></strong></span>
											</td>
											<?php $count++; ?>
											<td>01-<?= date('M', mktime(0, 0, 0, $monthNo, 1)) ?>-<?= date('y') ?></td>
											<td>
												<?php
												$monthPaid = (float) $monthSummary['paid'];
												$monthBalance = (float) $monthSummary['balance'];
												if ($monthPaid == 0 && $monthBalance <= 0 && (float) $monthSummary['amount'] == 0) {
													$monthStatus = '-';
													$monthLabel = '';
												} elseif ($monthPaid == 0) {
													$monthStatus = translate('unpaid');
													$monthLabel = 'label-danger-custom';
												} elseif ($monthBalance <= 0) {
													$monthStatus = translate('total_paid');
													$monthLabel = 'label-success-custom';
												} else {
													$monthStatus = translate('partly_paid');
													$monthLabel = 'label-info-custom';
												}
												?>
												<?php if ($monthLabel !== ''): ?>
													<span class="<?= $monthLabel ?>"><?= $monthStatus ?></span>
												<?php else: ?>
													<?= $monthStatus ?>
												<?php endif; ?>
											</td>
											<td><strong><?= currencyFormat($monthSummary['amount']) ?></strong></td>
											<td>
												<?php
												$monthReceipts = $monthlyCollections[$monthNo]['receipts'] ?? [];
												echo !empty($monthReceipts) ? html_escape(implode(', ', $monthReceipts)) : '-';
												?>
											</td>
											<td>-</td>
											<td>
												<strong><?= currencyFormat($monthSummary['fine_paid']) ?></strong>
												<span class="text-muted"> / <?= currencyFormat($monthSummary['fine_due']) ?></span>
											</td>
											<td class="paid-cell">
												<strong><?= currencyFormat($monthSummary['paid']) ?></strong>
												<?php
												$mDiscPaid = (float) $monthSummary['discount_paid'];
												if ($mDiscPaid > 0):
												?>
													<span class="fee-discount-line"><?= translate('discount') ?>: <?= currencyFormat($mDiscPaid) ?></span>
												<?php endif; ?>
											</td>
											<td><strong><?= currencyFormat($monthSummary['balance']) ?></strong></td>
										</tr>
										<?php if ($monthNo == 4 && $lastBalance > 0): ?>
											<?php
											// Add to totals
											$total_amount  += $lastBalance;
											$total_balance += $lastBalance;
											?>
											<tr class="fee-row" data-month="4">
												<td class="hidden-print cb-col">
													<div class="checkbox-replace">
														<label class="i-checks">
															<input type="checkbox"
																name="cb_invoice"
																data-fee-type="last_balance"
																data-month="4"
																data-amount="<?= $lastBalance ?>"
																data-discount="0"
																data-fine="0"
																data-balance="<?= $lastBalance ?>">
															<i></i>
														</label>
													</div>
												</td>
												<td class="hidden-print collect-col">
													<?php if (get_permission('collect_fees', 'is_add')): ?>
														<button type="button" class="btn btn-default btn-xs collect-fee-row" title="<?= translate('collect') ?>" data-loading-text="<i class='fas fa-spinner fa-spin'></i>">
															<i class="fas fa-coins"></i>
														</button>
													<?php endif; ?>
												</td>
												<td class="hidden-print num-col"></td>
												<td class="text-dark"><strong>Last Year Balance</strong></td>
												<td>-</td>
												<td><span class="label-danger-custom">Unpaid</span></td>
												<td><?= currencyFormat($lastBalance) ?></td>
												<td>-</td>
												<td>-</td>
												<td><?= currencyFormat(0) ?></td>
												<td class="paid-cell"><?= currencyFormat(0) ?></td>
												<td><?= currencyFormat($lastBalance) ?></td>
											</tr>
										<?php endif; ?>


										<?php
										/* -------------------- GENERAL FEE ROWS ---------------------- */
										foreach ($allocations as $row):
											$assignedMonths = array_filter(array_map('intval', explode(',', (string) ($row['month'] ?? ''))));
											if (in_array((int) $monthNo, $assignedMonths, true)):

												$fee_amount = (float) ($row['amount'] ?? 0);
												$type_fine = (float) ($row['fine_due'] ?? $this->fees_model->resolveConfiguredFine($row));
												$type_discount = (float) ($row['discount_due'] ?? 0);
												$paid_discount = (float) ($row['discount'] ?? 0);
												$paid_fine = (float) ($row['fine'] ?? 0);
												$fine_remaining = (float) ($row['fine_remaining'] ?? max(0, $type_fine - $paid_fine));
												$discount_remaining = (float) ($row['discount_remaining'] ?? max(0, $type_discount - $paid_discount));

												$type_amount = (float) ($row['paid_amount'] ?? $row['paid'] ?? 0);
												$balance     = (float) ($row['balance'] ?? 0);
												if ($balance < 0) {
													$balance = 0;
												}

												if ($balance > 0) {
													$typeData[$row['allocation_id'] . "|" . $row['fee_type_id']] = $row['fee_name'];
												}

												if ($type_amount == 0) {
													$status = translate('unpaid');
													$label  = 'label-danger-custom';
												} elseif ($balance <= 0) {
													$status = translate('total_paid');
													$label  = 'label-success-custom';
												} else {
													$status = translate('partly_paid');
													$label  = 'label-info-custom';
												}

												$total_amount  += $fee_amount;
												$total_fine    += $type_fine;
												$total_discount += $type_discount;
												$total_paid    += $type_amount;
												$total_balance += $balance;
										?>
												<tr class="fee-row" data-month="<?= $monthNo ?>">
													<td class="hidden-print cb-col">
														<div class="checkbox-replace">
															<label class="i-checks">
																<input type="checkbox" name="cb_invoice"
																	value="<?= $row['allocation_id'] . '' . $row['fee_type_id'] . '' . $monthNo ?>"
																	data-allocation-id="<?= $row['allocation_id'] ?>"
																	data-fee-type-id="<?= $row['fee_type_id'] ?>"
																	data-fee-type="general"
																	data-transport-fd-id="0"
																	data-month="<?= $monthNo ?>"
																	data-amount="<?= $fee_amount ?>"
																	data-discount="<?= $discount_remaining ?>"
																	data-fine="<?= $fine_remaining ?>"
																	data-balance="<?= $balance ?>">
																<i></i>
															</label>
														</div>
													</td>
													<td class="hidden-print collect-col">
														<?php if (get_permission('collect_fees', 'is_add') && $balance > 0): ?>
															<button type="button" class="btn btn-default btn-xs collect-fee-row" title="<?= translate('collect') ?>" data-loading-text="<i class='fas fa-spinner fa-spin'></i>">
																<i class="fas fa-coins"></i>
															</button>
														<?php endif; ?>
													</td>
													<td class="hidden-print num-col"></td>
													<td class="text-dark"><?= html_escape($row['fee_name']) ?></td>
													<td><?= _d($row['due_date']) ?></td>
													<td><span class="<?= $label ?>"><?= $status ?></span></td>
													<td><?= currencyFormat($fee_amount) ?></td>
													<td><?= html_escape($row['receipt_no'] ?? '-') ?></td>
													<td><?= html_escape($row['payment_method_name'] ?? '-') ?></td>
													<td>
														<?= currencyFormat($paid_fine) ?>
														<span class="text-muted"> / <?= currencyFormat($type_fine) ?></span>
													</td>
													<td class="paid-cell">
														<?= currencyFormat($type_amount) ?>
														<?php if ($paid_discount > 0): ?>
															<span class="fee-discount-line"><?= translate('discount') ?>: <?= currencyFormat($paid_discount) ?></span>
														<?php endif; ?>
													</td>
													<td><?= currencyFormat($balance) ?></td>
												</tr>
												<?php
											endif;
										endforeach;

										/* -------------------- TRANSPORT FEE ROWS ---------------------- */
										if (!empty($transportFees)) {
											foreach ($transportFees as $t) {

												if ((int)$t->month == (int)$monthNo) {
													$tf_amount = (float) $t->route_fare;
													$tfDeposit = $this->fees_model->getStudentTransportFeeDeposit($t->id);
													$tf_discount = !empty($tfDeposit) ? (float) $tfDeposit['total_discount'] : 0;
													$tf_fine = !empty($tfDeposit) ? (float) $tfDeposit['total_fine'] : 0;
													$tf_paid = !empty($tfDeposit) ? (float) $tfDeposit['total_amount'] : 0;
													$tf_balance = max(0, $tf_amount - $tf_paid - $tf_discount);

													if ($tf_paid == 0) {
														$status = translate('unpaid');
														$label  = 'label-danger-custom';
													} elseif ($tf_balance <= 0) {
														$status = translate('total_paid');
														$label  = 'label-success-custom';
													} else {
														$status = translate('partly_paid');
														$label  = 'label-info-custom';
													}

													$total_amount  += $tf_amount;
													$total_discount += $tf_discount;
													$total_fine    += $tf_fine;
													$total_paid    += $tf_paid;
													$total_balance += $tf_balance;

													if ($tf_balance > 0) {
														$typeData["transport|" . $t->id . "|" . $t->month] = translate('transport_fees') . " - " . $monthName;
													}
												?>
													<tr class="fee-row transport-row" data-month="<?= $monthNo ?>">
														<td class="hidden-print cb-col">
															<div class="checkbox-replace">
																<label class="i-checks">
																	<input type="checkbox" name="cb_invoice"
																		data-allocation-id="0"
																		data-fee-type-id="0"
																		data-fee-type="transport"
																		data-transport-fd-id="<?= $t->id ?>"
																		data-month="<?= $monthNo ?>"
																		data-amount="<?= $tf_amount ?>"
																		data-discount="0"
																		data-fine="0"
																		data-balance="<?= $tf_balance ?>">
																	<i></i>
																</label>
															</div>
														</td>
														<td class="hidden-print collect-col">
															<?php if (get_permission('collect_fees', 'is_add') && $tf_balance > 0): ?>
																<button type="button" class="btn btn-default btn-xs collect-fee-row" title="<?= translate('collect') ?>" data-loading-text="<i class='fas fa-spinner fa-spin'></i>">
																	<i class="fas fa-coins"></i>
																</button>
															<?php endif; ?>
														</td>
														<td class="hidden-print num-col"></td>
														<td class="text-dark">Transport Fee</td>
														<td><?= _d($t->due_date) ?></td>
														<td><span class="<?= $label ?>"><?= $status ?></span></td>
														<td><?= currencyFormat($tf_amount) ?></td>
														<td>-</td>
														<td>-</td>
														<td>
															<?= currencyFormat($tf_fine) ?>
															<span class="text-muted"> / <?= currencyFormat(0) ?></span>
														</td>
														<td class="paid-cell">
															<?= currencyFormat($tf_paid) ?>
															<?php if ($tf_discount > 0): ?>
																<span class="fee-discount-line"><?= translate('discount') ?>: <?= currencyFormat($tf_discount) ?></span>
															<?php endif; ?>
														</td>
														<td><?= currencyFormat($tf_balance) ?></td>
													</tr>
												<?php
												}
											}
										}
										/* -------------------- BOOKS / SALES BILL ROWS ---------------------- */
										if (!empty($salesBills)) {
											foreach ($salesBills as $bill) {

												// Extract month number from bill date
												$billMonth = (int)date("n", strtotime($bill->date));

												// If bill month matches current loop month, show it
												if ($billMonth == (int)$monthNo) {

													$billStatus = $bill->payment_status == 1 ? 'Paid' : 'Unpaid';
													$billLabel  = $bill->payment_status == 1 ? 'label-success-custom' : 'label-danger-custom';

													// Add totals
													$total_amount  += $bill->total;
													$total_discount += $bill->discount;
													$total_paid    += $bill->paid;
													$total_balance += $bill->due;
												?>
													<tr class="fee-row book-row" data-month="<?= $monthNo ?>">
														<td class="hidden-print cb-col">
															<div class="checkbox-replace">
																<label class="i-checks">
																	<input type="checkbox"
																		name="cb_invoice"
																		data-fee-type="books"
																		data-month="<?= $monthNo ?>"
																		data-amount="<?= $bill->total ?>"
																		data-discount="<?= $bill->discount ?>"
																		data-fine="0"
																		data-balance="<?= $bill->due ?>"
																		data-bill-id="<?= $bill->id ?>">
																	<i></i>
																</label>
															</div>
														</td>
														<td class="hidden-print collect-col">
															<?php if (get_permission('collect_fees', 'is_add') && $bill->due > 0): ?>
																<button type="button" class="btn btn-default btn-xs collect-fee-row" title="<?= translate('collect') ?>" data-loading-text="<i class='fas fa-spinner fa-spin'></i>">
																	<i class="fas fa-coins"></i>
																</button>
															<?php endif; ?>
														</td>
														<td class="hidden-print num-col"></td>
														<td class="text-dark">
															<?= html_escape($bill->product_names ?: 'Sales Bill') ?> - <?= html_escape($bill->bill_no) ?>
														</td>
														<td><?= _d($bill->date) ?></td>
														<td><span class="<?= $billLabel ?>"><?= $billStatus ?></span></td>
														<td><?= currencyFormat($bill->total) ?></td>
														<td>-</td>
														<td>-</td>
														<td>
															<?= currencyFormat(0) ?>
															<span class="text-muted"> / <?= currencyFormat(0) ?></span>
														</td>
														<td class="paid-cell">
															<?= currencyFormat($bill->paid) ?>
															<?php if ((float) $bill->discount > 0): ?>
																<span class="fee-discount-line"><?= translate('discount') ?>: <?= currencyFormat($bill->discount) ?></span>
															<?php endif; ?>
														</td>
														<td><?= currencyFormat($bill->due) ?></td>
													</tr>
									<?php
												}
											}
										}


									endforeach;
									?>

								</tbody>





							</table>
						</div>




						<div class="invoice-summary invoice-summary--cards mt-lg hidden-print" id="invoiceTotalsSummary">
							<ul class="amounts">
								<li class="summary-total">
									<strong><i class="fas fa-wallet"></i> <?= translate('grand_total') ?></strong>
									<span><?= currencyFormat($total_amount + $total_fine - $total_discount); ?></span>
								</li>
								<li class="summary-paid">
									<strong><i class="fas fa-check-circle"></i> <?= translate('paid') ?> / Submitted</strong>
									<span><?= currencyFormat($total_paid); ?></span>
								</li>
								<li class="summary-fine">
									<strong><i class="fas fa-exclamation-triangle"></i> <?= translate('fine') ?></strong>
									<span><?= currencyFormat($total_fine); ?></span>
								</li>
								<li class="summary-balance">
									<strong><i class="fas fa-hourglass-half"></i> <?= translate('balance') ?></strong>
									<span><?= currencyFormat($total_balance); ?></span>
								</li>
							</ul>
						</div>
						<div class="invoice-summary text-right mt-lg visible-print-block" id="invDetailsPrint"></div>
					</div>
					<div class="text-right mr-lg hidden-print">
						<button id="invoicePrint" class="btn btn-default ml-sm" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><i class="fas fa-print"></i> <?= translate('print') ?></button>
					</div>
				</div>
			</div>
				<div class="tab-pane" id="history">
					<div id="payment_print">
						<div class="invoice payment">
							<header class="clearfix">
								<div class="row">
									<div class="col-xs-6">
										<div class="ib">
											<img src="<?= $this->application_model->getBranchImage($basic['branch_id'], 'printing-logo') ?>" alt="School Logo" />
										</div>
									</div>
									<div class="col-md-6 text-right">
										<h4 class="mt-none mb-none text-dark">Fee Account Ref: <?= html_escape($invoice['invoice_no']) ?></h4>
										<p class="mb-none">
											<span class="text-dark"><?= translate('date') ?> : </span>
											<span class="value"><?php echo _d(date('Y-m-d')); ?></span>
										</p>
										<p class="mb-none">
											<span class="text-dark"><?= translate('status') ?> : </span>
											<?php
											$labelmode = '';
											if ($invoice['status'] == 'unpaid') {
												$status = translate('unpaid');
												$labelmode = 'label-danger-custom';
											} elseif ($invoice['status'] == 'partly') {
												$status = translate('partly_paid');
												$labelmode = 'label-info-custom';
											} elseif ($invoice['status'] == 'total') {
												$status = translate('total_paid');
												$labelmode = 'label-success-custom';
											}
											echo "<span class='value label " . $labelmode . " '>" . $status . "</span>";
											?>
										</p>
									</div>
								</div>
							</header>
							<div class="bill-info">
								<div class="row">
									<div class="col-xs-6">
										<div class="bill-data">
											<p class="h5 mb-xs text-dark text-weight-semibold">Invoice To :</p>
											<address>
												<?php
												echo $basic['first_name'] . ' ' . $basic['last_name'] . '<br>';
												echo translate('register_no') . ' : ' . $basic['register_no'] . '<br>';
												echo (empty($basic['student_address']) ? "" : nl2br($basic['student_address']) . '<br>');
												echo translate('class') . ' : ' . $basic['class_name'] . " (" . $basic['section_name'] . ')<br>';
												if (!empty($basic['father_name'])) {
													echo translate('father_name') . ' : ' . $basic['father_name'];
												}
												?>
											</address>
										</div>
									</div>
									<div class="col-xs-6">
										<div class="bill-data text-right">
											<p class="h5 mb-xs text-dark text-weight-semibold">Academic :</p>
											<address>
												<?php
												echo $basic['school_name'] . "<br/>";
												echo $basic['school_address'] . "<br/>";
												echo $basic['school_mobileno'] . "<br/>";
												echo $basic['school_email'] . "<br/>";
												?>
											</address>
										</div>
									</div>
								</div>
							</div>
							<div class="fees-invoice-toolbar hidden-print">
								<div>
									<label for="receiptPageSize">Paper size</label>
									<select id="receiptPageSize" class="form-control input-sm" style="display:inline-block;width:110px;margin-left:6px">
										<option value="A4" selected>A4</option>
										<option value="A5">A5</option>
										<option value="Letter">Letter</option>
										<option value="Legal">Legal</option>
									</select>
								</div>
								<button type="button" class="btn btn-default btn-sm" id="printAllReceipts" <?= empty($payment_receipts) ? 'disabled' : '' ?>>
									<i class="fas fa-print"></i> Print all receipts
								</button>
								<button type="button" class="btn btn-default btn-sm" id="printSelectedReceipts">
									<i class="fas fa-print"></i> Print selected receipts
								</button>
							</div>
							<div class="table-responsive">
								<table class="table table-hover table-bordered" id="studentPaymentReceipts">
									<thead>
										<tr>
											<th class="hidden-print"><input type="checkbox" id="selectAllReceipts" aria-label="Select all receipts"></th>
											<th>Receipt No.</th>
											<th><?= translate('date') ?></th>
											<th>Fee details</th>
											<th><?= translate('method') ?></th>
											<th class="text-right"><?= translate('amount') ?></th>
											<th class="text-right"><?= translate('discount') ?></th>
											<th class="text-right"><?= translate('fine') ?></th>
											<th class="text-right">Received</th>
											<th>Status</th>
											<th class="hidden-print">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($payment_receipts as $receipt): ?>
											<?php
											$receiptMessage = (!empty($receipt['is_reversed']) ? 'VOID - reversed fee receipt ' : 'Fee receipt ') . $receipt['receipt_label']
												. ' | Student: ' . trim(($basic['first_name'] ?? '') . ' ' . ($basic['last_name'] ?? ''))
												. ' | Class: ' . trim(($basic['class_name'] ?? '') . ' ' . ($basic['section_name'] ?? ''))
												. ' | Date: ' . _d($receipt['date'])
												. ' | Amount: ' . currencyFormat($receipt['total_amount'])
												. ' | Discount: ' . currencyFormat($receipt['total_discount'])
												. ' | Fine: ' . currencyFormat($receipt['total_fine'])
												. ' | Received: ' . currencyFormat($receipt['total_paid'] + $receipt['total_fine']);
											$receiptFeeNames = array();
											foreach ($receipt['items'] as $receiptItem) {
												$feeName = trim((string) $receiptItem['name']);
												$feeNameKey = strtolower(preg_replace('/\s+/', ' ', $feeName));
												if ($feeName !== '' && !isset($receiptFeeNames[$feeNameKey])) {
													$receiptFeeNames[$feeNameKey] = $feeName;
												}
											}
											$whatsAppNumber = preg_replace('/\D+/', '', (string) ($basic['guardian_mobileno'] ?? ''));
											if ($whatsAppNumber === '') {
												$whatsAppNumber = preg_replace('/\D+/', '', (string) ($basic['mobileno'] ?? ''));
											}
											if (strlen($whatsAppNumber) === 10) {
												$whatsAppNumber = '91' . $whatsAppNumber;
											}
											$correctionItems = array();
											foreach ($receipt['items'] as $receiptItem) {
												$correctionItems[] = array(
													'id' => (int) $receiptItem['id'],
													'name' => $receiptItem['name'],
													'amount' => number_format((float) $receiptItem['amount'], 2, '.', ''),
												);
											}
											?>
											<tr>
												<td class="hidden-print"><input type="checkbox" class="receipt-select" value="<?= html_escape($receipt['receipt_key']) ?>"></td>
												<td><strong><?= html_escape($receipt['receipt_label']) ?></strong></td>
												<td><?= html_escape(_d($receipt['date'])) ?></td>
												<td>
													<?php foreach (array_values($receiptFeeNames) as $itemIndex => $feeName): ?>
														<?= $itemIndex > 0 ? '<br>' : '' ?><?= html_escape($feeName) ?>
													<?php endforeach; ?>
												</td>
												<td><?= html_escape($receipt['payment_method'] ?: '—') ?></td>
												<td class="text-right"><?= currencyFormat($receipt['total_amount']) ?></td>
												<td class="text-right"><?= currencyFormat($receipt['total_discount']) ?></td>
												<td class="text-right"><?= currencyFormat($receipt['total_fine']) ?></td>
												<td class="text-right"><strong><?= currencyFormat($receipt['total_paid'] + $receipt['total_fine']) ?></strong></td>
												<td>
													<?php if (!empty($receipt['is_reversed'])): ?>
														<span class="label label-danger" title="<?= html_escape($receipt['reversal_reason']) ?>">Reversed</span>
													<?php else: ?>
														<span class="label label-success">Paid</span>
													<?php endif; ?>
												</td>
												<td class="hidden-print text-nowrap">
													<button type="button" class="btn btn-xs btn-default print-one-receipt" data-receipt-key="<?= html_escape($receipt['receipt_key']) ?>" title="Print receipt"><i class="fas fa-print"></i></button>
													<?php if ($whatsAppNumber !== ''): ?>
														<a class="btn btn-xs btn-success" target="_blank" rel="noopener" href="https://wa.me/<?= html_escape($whatsAppNumber) ?>?text=<?= rawurlencode($receiptMessage) ?>" title="Send receipt details to parent on WhatsApp" aria-label="Send receipt details to parent on WhatsApp"><i class="fab fa-whatsapp"></i></a>
													<?php endif; ?>
													<?php if (get_permission('fees_revert', 'is_delete') && !empty($receipt['can_correct'])): ?>
														<button type="button" class="btn btn-xs btn-info correct-fee-receipt" data-receipt-no="<?= (int) preg_replace('/\D+/', '', $receipt['receipt_key']) ?>" data-items="<?= html_escape(json_encode($correctionItems)) ?>" title="Correct amount and issue a replacement receipt"><i class="fas fa-edit"></i></button>
													<?php endif; ?>
													<?php if (get_permission('fees_revert', 'is_delete') && !empty($receipt['can_reverse'])): ?>
														<button type="button" class="btn btn-xs btn-warning reverse-fee-receipt" data-receipt-no="<?= (int) preg_replace('/\D+/', '', $receipt['receipt_key']) ?>" title="Reverse this receipt"><i class="fas fa-undo"></i></button>
													<?php endif; ?>
												</td>
											</tr>
										<?php endforeach; ?>
										<?php if (empty($payment_receipts)): ?>
											<tr><td colspan="11" class="text-center text-muted">No payment receipts have been recorded for this student.</td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
							<p class="text-muted hidden-print">WhatsApp opens a pre-filled receipt summary for the parent/guardian. To send the receipt as a PDF, print/save it first and attach it in WhatsApp.</p>
							<?php if (false): ?>
							<?php if (get_permission('fees_revert', 'is_delete')): ?>
								<button type="button" class="btn btn-default btn-sm mb-sm hidden-print" id="selected_revert" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<i class="fas fa-trash-restore-alt"></i> <?php echo translate('selected_revert'); ?>
								</button>
							<?php endif; ?>
							<div class="table-responsive">
								<table class="table invoice-items" id="paymentHistory">
									<thead>
										<tr class="h5 text-dark">
											<th id="cell-count" class="text-weight-semibold hidden-print">
												<div class="checkbox-replace">
													<label class="i-checks" data-toggle="tooltip" data-original-title="Print Show / Hidden">
														<input type="checkbox" class="fee-selectAll" checked> <i></i>
													</label>
												</div>
											</th>
											<th id="cell-item" class="text-weight-semibold"><?= translate('fees_type') ?></th>
											<th id="cell-item" class="text-weight-semibold"><?= translate('fees_code') ?></th>
											<th id="cell-item" class="text-weight-semibold"><?= translate('date') ?></th>
											<th id="cell-item" class="text-weight-semibold">Payment Id</th>
											<th id="cell-item" class="text-weight-semibold hidden-print"><?= translate('collect_by') ?></th>
											<th id="cell-desc" class="text-weight-semibold"><?= translate('remarks') ?></th>
											<th id="cell-qty" class="text-weight-semibold"><?= translate('method') ?></th>
											<th id="cell-price" class="text-weight-semibold"><?= translate('amount') ?></th>
											<th id="cell-price" class="text-weight-semibold"><?= translate('discount') ?></th>
											<th id="cell-price" class="text-weight-semibold"><?= translate('fine') ?></th>
											<th id="cell-price" class="text-weight-semibold"><?= translate('paid') ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$allocations = $this->db->where(array('student_id' => $basic['enroll_id'], 'session_id' => get_session_id()))->get('fee_allocation')->result_array();
										foreach ($allocations as $allRow) {
											$historys = $this->fees_model->getPaymentHistory($allRow['id'], $allRow['group_id']);
											foreach ($historys as $row) {
										?>
												<tr>
													<td class="hidden-print checked-area">
														<div class="checkbox-replace">
															<label class="i-checks"><input type="checkbox" name="cb_feePay" value="<?php echo $row['id']; ?>" checked><i></i></label>
														</div>
													</td>
													<td class="text-weight-semibold text-dark"><?php echo $row['name']; ?></td>
													<td><?php echo $row['fee_code']; ?></td>
													<td><?php echo _d($row['date']); ?></td>
													<td><?php echo $row['id']; ?></td>
													<td class="hidden-print">
														<?php
														if ($row['collect_by'] == 'online') {
															echo translate('online');
														} else {
															echo get_type_name_by_id('staff', $row['collect_by']);
														}
														?>
													</td>
													<td><?php echo $row['remarks']; ?></td>
													<td><?php echo $row['payvia']; ?></td>
													<td><?php echo currencyFormat($row['amount'] + $row['discount']); ?></td>
													<td><?php echo currencyFormat($row['discount']); ?></td>
													<td><?php echo currencyFormat($row['fine']); ?></td>
													<td><?php echo currencyFormat($row['amount']); ?></td>
												</tr>
												<?php }
										}
										if (moduleIsEnabled('transport')) {
											$this->db->select('transport_fee_details.id,transport_fee_fine.month');
											$this->db->from('transport_fee_details');
											$this->db->join('transport_fee_fine', 'transport_fee_fine.id = transport_fee_details.transport_fee_fine_id', 'inner');
											$this->db->where('transport_fee_details.enroll_id', $basic['enroll_id']);
											$this->db->order_by('transport_fee_details.id', 'asc');
											$transport_fee_details =  $this->db->get()->result();
											foreach ($transport_fee_details as $trans) {
												$month = $this->app_lib->getMonthslist($trans->month);
												$historys = $this->fees_model->getTransportPaymentHistory($trans->id);
												foreach ($historys as $row) {
												?>
													<tr>
														<td class="hidden-print checked-area">
															<div class="checkbox-replace">
																<label class="i-checks"><input type="checkbox" name="cb_feePay" value="<?php echo $row['id']; ?>" checked><i></i></label>
															</div>
														</td>
														<td class="text-weight-semibold text-dark"><?php echo translate('transport_fees') ?></td>
														<td><?php echo strtolower($month); ?></td>
														<td><?php echo _d($row['date']); ?></td>
														<td><?php echo $row['id']; ?></td>
														<td class="hidden-print">
															<?php
															if ($row['collect_by'] == 'online') {
																echo translate('online');
															} else {
																echo get_type_name_by_id('staff', $row['collect_by']);
															}
															?>
														</td>
														<td><?php echo $row['remarks']; ?></td>
														<td><?php echo $row['payvia']; ?></td>
														<td><?php echo currencyFormat($row['amount'] + $row['discount']); ?></td>
														<td><?php echo currencyFormat($row['discount']); ?></td>
														<td><?php echo currencyFormat($row['fine']); ?></td>
														<td><?php echo currencyFormat($row['amount']); ?></td>
													</tr>
											<?php }
											} ?>
										<?php } ?>
									</tbody>
								</table>
							</div>
							<div class="invoice-summary text-right mt-lg hidden-print">
								<div class="row">
									<div class="col-md-5 col-xs-12 pull-right">
										<ul class="amounts">
											<li><strong><?= translate('sub_total') ?> :</strong> <?= currencyFormat($total_paid + $total_discount); ?></li>
											<li><strong><?= translate('discount') ?> :</strong> <?= currencyFormat($total_discount); ?></li>
											<li><strong><?= translate('paid') ?> :</strong> <?= currencyFormat($total_paid); ?></li>
											<li><strong><?= translate('fine') ?> :</strong> <?= currencyFormat($total_fine); ?></li>
											<li>
												<strong><?= translate('total_paid') ?> (<?= translate('with_fine') ?>) : </strong>
												<?php
												$numberSPELL = "";
												$grand_paid = number_format($total_paid + $total_fine, 2, '.', '');
												if ($extINTL == true) {
													$numberSPELL = ' </br>( ' . ucwords($spellout->format($grand_paid)) . ' )';
												}
												echo currencyFormat($total_paid + $total_fine) . $numberSPELL;
												?>
											</li>
										</ul>
									</div>
								</div>
							</div>
							<div class="invoice-summary text-right mt-lg visible-print-block" id="invPaymentHistory"></div>
						</div>
							<?php endif; ?>
					</div>
				</div>

			<?php if (false): ?>
			<!-- Legacy standalone collection tabs are intentionally disabled. -->
			<!-- add fees form -->
			<?php if ($invoice['status'] != 'total'): ?>
				<div id="collect_fees" class="tab-pane">
					<?php echo form_open('fees/fee_add', array('class' => 'form-horizontal frm-submit')); ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('fees_type') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							echo form_dropdown("fees_type", $typeData, set_value('fees_type'), "class='form-control' id='fees_type'
								data-plugin-selectTwo data-width='100%' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('date') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" data-plugin-datepicker
								data-plugin-options='{"todayHighlight" : true, "endDate": "today"}' name="date" value="<?= date('Y-m-d') ?>" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('amount') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="amount" id="feeAmount" value="" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('discount') ?></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="discount_amount" value="0" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('fine') ?></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="fine_amount" id="fineAmount" value="0" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('payment_method') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$payvia_list = $this->app_lib->getSelectList('payment_types');
							echo form_dropdown("pay_via", $payvia_list, set_value('pay_via', 1), "class='form-control' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<?php
					$links = $this->fees_model->get('transactions_links', array('branch_id' => $basic['branch_id']), true);
					if ($links['status'] == 1) {
					?>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('account'); ?> <span class="required">*</span></label>
							<div class="col-md-6">
								<?php
								$accounts_list = $this->app_lib->getSelectByBranch('accounts', $basic['branch_id']);
								echo form_dropdown("account_id", $accounts_list, $links['deposit'], "class='form-control' id='account_id' required data-plugin-selectTwo data-width='100%'");
								?>
							</div>
						</div>
					<?php } ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('remarks') ?></label>
						<div class="col-md-6 mb-md">
							<textarea name="remarks" rows="2" class="form-control" placeholder="<?= translate('write_your_remarks') ?>"></textarea>
							<div class="checkbox-replace mt-lg">
								<label class="i-checks">
									<input type="checkbox" name="guardian_sms" checked> <i></i> Guardian Confirmation Sms
								</label>
							</div>
						</div>
					</div>
					<input type="hidden" name="branch_id" value="<?= $basic['branch_id'] ?>">
					<input type="hidden" name="student_id" value="<?= $basic['id'] ?>">
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-3">
								<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<?= translate('fee_payment') ?>
								</button>
							</div>
						</div>
					</footer>
					<?php echo form_close(); ?>
				</div>
			<?php endif; ?>
			<!--fully paid form-->
			<?php if ($invoice['status'] != 'total'): ?>
				<div id="fully_paid" class="tab-pane">
					<?php echo form_open('fees/fee_fully_paid', array('class' => 'form-horizontal frm-submit')); ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('date') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" data-plugin-datepicker
								data-plugin-options='{"todayHighlight" : true, "endDate":"today"}' name="date" value="<?= date('Y-m-d') ?>" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('amount') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="amount" id="feeAmount" value="<?= number_format($total_balance, 2, '.', '') ?>" autocomplete="off" disabled />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('fine') ?></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="fine_amount" id="fineAmount" value="<?= number_format($fully_total_fine, 2, '.', '') ?>" autocomplete="off" disabled />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('payment_method') ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$payvia_list = $this->app_lib->getSelectList('payment_types');
							echo form_dropdown("pay_via", $payvia_list, set_value('pay_via'), "class='form-control' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<?php
					$links = $this->fees_model->get('transactions_links', array('branch_id' => $basic['branch_id']), true);
					if ($links['status'] == 1) {
					?>
						<div class="form-group">
							<label class="col-md-3 control-label"><?php echo translate('account'); ?> <span class="required">*</span></label>
							<div class="col-md-6">
								<?php
								$accounts_list = $this->app_lib->getSelectByBranch('accounts', $basic['branch_id']);
								echo form_dropdown("account_id", $accounts_list, $links['deposit'], "class='form-control' id='account_id' required data-plugin-selectTwo data-width='100%'");
								?>
							</div>
						</div>
					<?php } ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('remarks') ?></label>
						<div class="col-md-6 mb-md">
							<textarea name="remarks" rows="2" class="form-control" placeholder="<?= translate('write_your_remarks') ?>"></textarea>
							<div class="checkbox-replace mt-lg">
								<label class="i-checks">
									<input type="checkbox" name="guardian_sms" checked> <i></i> Guardian Confirmation Sms
								</label>
							</div>
						</div>
					</div>
					<input type="hidden" name="invoice_id" value="<?php echo $basic['enroll_id']; ?>">
					<input type="hidden" name="branch_id" value="<?= $basic['branch_id'] ?>">
					<input type="hidden" name="student_id" value="<?= $basic['id'] ?>">
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-3">
								<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<?= translate('fee_payment') ?>
								</button>
							</div>
						</div>
					</footer>
					<?php echo form_close(); ?>
				</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</section>

<div class="modal fade" id="receiptCorrectionModal" tabindex="-1" role="dialog" aria-labelledby="receiptCorrectionTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<?php echo form_open('fees/correct_receipt', array('id' => 'receiptCorrectionForm')); ?>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="receiptCorrectionTitle">Correct receipt amount</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" name="enroll_id" value="<?= (int) $basic['enroll_id'] ?>">
				<input type="hidden" name="receipt_no" id="correctionReceiptNo">
				<p class="text-muted">Enter the correct gross amount for each item. The original receipt will remain in history as reversed, and a replacement receipt will be issued.</p>
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead><tr><th>Fee item</th><th class="text-right">Correct amount</th></tr></thead>
						<tbody id="receiptCorrectionItems"></tbody>
					</table>
				</div>
				<div class="form-group">
					<label for="receiptCorrectionReason">Correction reason <span class="required">*</span></label>
					<textarea class="form-control" name="reason" id="receiptCorrectionReason" rows="3" maxlength="500" required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary" id="submitReceiptCorrection">Correct and reissue</button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<div class="zoom-anim-dialog modal-block mfp-hide modal-block-full" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-coins fa-fw"></i> <?= translate('collect_fees') ?>
				<button type="button" class="close modal-dismiss" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</h4>
		</header>
		<?php echo form_open('fees/selectedFeesPay', array('class' => 'frm-submit')); ?>
		<div class="panel-body">
			<div id="printResult" class="pt-sm pb-sm">
				<div class="table-responsive">
					<table class="table table-bordered table-condensed text-dark" id="feeCollect">

					</table>
				</div>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="submit" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">Submit Fee Payment</button>
				</div>
			</div>
		</footer>
		<?php echo form_close(); ?>
	</section>
</div>
<script type="text/javascript">
	var branchID = "<?= $basic['branch_id']; ?>";
	var studentID = "<?= $basic['enroll_id']; ?>";

	$(document).ready(function() {
		var $invoiceSummary = $('#invoiceTotalsSummary');
		if ($invoiceSummary.length) {
			$invoiceSummary.insertBefore($('.fees-invoice-toolbar').first());
		}

		function submitReceiptPrint(receiptKeys) {
			if (!receiptKeys.length) {
				popupMsg("Select at least one receipt to print.", "error");
				return;
			}
			var $form = $('<form>', {
				method: 'post',
				action: base_url + 'fees/receipt_print',
				target: '_blank'
			});
			$form.append($('<input>', { type: 'hidden', name: 'enroll_id', value: studentID }));
			$form.append($('<input>', {
				type: 'hidden',
				name: '<?= $this->security->get_csrf_token_name(); ?>',
				value: '<?= $this->security->get_csrf_hash(); ?>'
			}));
			$form.append($('<input>', {
				type: 'hidden',
				name: 'page_size',
				value: $('#receiptPageSize').val() || 'A4'
			}));
			receiptKeys.forEach(function(key) {
				$form.append($('<input>', { type: 'hidden', name: 'receipt_keys[]', value: key }));
			});
			$('body').append($form);
			$form.trigger('submit');
			$form.remove();
		}

		$('#selectAllReceipts').on('change', function() {
			$('.receipt-select').prop('checked', this.checked);
		});
		$('.receipt-select').on('change', function() {
			$('#selectAllReceipts').prop(
				'checked',
				$('.receipt-select').length > 0 && $('.receipt-select:not(:checked)').length === 0
			);
		});
		$('#printSelectedReceipts').on('click', function() {
			submitReceiptPrint($('.receipt-select:checked').map(function() {
				return this.value;
			}).get());
		});
		$('#printAllReceipts').on('click', function() {
			submitReceiptPrint($('.receipt-select').map(function() {
				return this.value;
			}).get());
		});
		$(document).on('click', '.print-one-receipt', function() {
			submitReceiptPrint([$(this).data('receipt-key')]);
		});
		$(document).on('click', '.reverse-fee-receipt', function() {
			var receiptNo = $(this).data('receipt-no');
			var reason = window.prompt('Enter the reason for reversing receipt RCP-' + receiptNo + '. The original receipt will remain in history.');
			if (reason === null || !reason.trim()) {
				return;
			}
			$.ajax({
				url: base_url + 'fees/reverse_receipt',
				type: 'POST',
				dataType: 'json',
				data: {
					enroll_id: studentID,
					receipt_no: receiptNo,
					reason: reason.trim(),
					'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
				},
				success: function(response) {
					popupMsg(response.message || 'Receipt reversal completed.', response.status === 'success' ? 'success' : 'error');
					if (response.status === 'success') {
						window.location.reload();
					}
				},
				error: function() {
					popupMsg('The receipt could not be reversed. No changes should be assumed.', 'error');
				}
			});
		});
		$(document).on('click', '.correct-fee-receipt', function() {
			var items;
			try {
				items = JSON.parse($(this).attr('data-items') || '[]');
			} catch (error) {
				popupMsg('Receipt items could not be loaded. Refresh the page and try again.', 'error');
				return;
			}
			var $items = $('#receiptCorrectionItems').empty();
			items.forEach(function(item) {
				var $row = $('<tr>');
				$row.append($('<td>').text(item.name));
				var $amount = $('<input>', {
					type: 'number',
					name: 'corrected_amounts[' + item.id + ']',
					class: 'form-control input-sm receipt-correction-amount',
					min: '0.01',
					step: '0.01',
					required: true,
					value: item.amount
				});
				$row.append($('<td>').append($amount));
				$items.append($row);
			});
			$('#correctionReceiptNo').val($(this).data('receipt-no'));
			$('#receiptCorrectionReason').val('');
			$('#receiptCorrectionModal').modal('show');
		});
		$('#receiptCorrectionForm').on('submit', function(event) {
			event.preventDefault();
			var $button = $('#submitReceiptCorrection').prop('disabled', true);
			$.ajax({
				url: base_url + 'fees/correct_receipt',
				type: 'POST',
				dataType: 'json',
				data: $(this).serialize(),
				success: function(response) {
					popupMsg(response.message || 'Receipt correction completed.', response.status === 'success' ? 'success' : 'error');
					if (response.status === 'success') {
						$('#receiptCorrectionModal').modal('hide');
						window.location.reload();
					}
				},
				error: function() {
					popupMsg('The receipt could not be corrected. No changes should be assumed.', 'error');
				},
				complete: function() {
					$button.prop('disabled', false);
				}
			});
		});
		function setMonthOpen(monthNo, isOpen) {
			var $monthRow = $("#invoiceSummary tr.month-row[data-month='" + monthNo + "']");
			var $feeRows = $("#invoiceSummary tr.fee-row[data-month='" + monthNo + "']");
			$monthRow.toggleClass('is-open', isOpen);
			$feeRows.toggleClass('is-visible', isOpen);
		}

		// --- Click month label to expand/collapse ---
		$(document).on('click', '#invoiceSummary tr.month-row', function(e) {
			if ($(e.target).closest('input, label, a, button').length) {
				return;
			}
			var monthNo = $(this).data('month');
			setMonthOpen(monthNo, !$(this).hasClass('is-open'));
		});

		// --- Month Selection: Show/Hide and Check/Uncheck rows ---
		$(document).on('change', '.month-checkbox', function() {
			var monthNo = $(this).data('month');
			var isChecked = $(this).is(':checked');
			var $feeRows = $("#invoiceSummary tr.fee-row[data-month='" + monthNo + "']");

			setMonthOpen(monthNo, isChecked);
			$feeRows.find("input[name='cb_invoice']").prop('checked', isChecked);

			var allChecked = $("#invoiceSummary input[name='cb_invoice']").length ===
				$("#invoiceSummary input[name='cb_invoice']:checked").length;
			$(".fee-selectAll").prop('checked', allChecked);
		});

		// --- Header Select/Deselect All ---
		$(".fee-selectAll").on("change", function() {
			var checked = $(this).is(':checked');

			$("#invoiceSummary input[name='cb_invoice']").prop('checked', checked);
			$("#invoiceSummary tr.fee-row").toggleClass('is-visible', checked);
			$("#invoiceSummary tr.month-row").toggleClass('is-open', checked);

			$("#invoiceSummary .month-checkbox").each(function() {
				var monthNo = $(this).data('month');
				var monthAllChecked = $("#invoiceSummary tr.fee-row[data-month='" + monthNo + "'] input[name='cb_invoice']:not(:checked)").length === 0;
				$(this).prop('checked', checked && monthAllChecked);
			});
		});

		// --- Collect Fees Modal ---
		function openCollectFeesModal($checkboxes, $btn) {
			var selectedData = $checkboxes.map(function() {
				var $cb = $(this);
				return {
					allocationID: $cb.data("allocation-id") || 0,
					feeTypeID: $cb.data("fee-type-id") || 0,
					trans_fd_id: $cb.data("transport-fd-id") || 0,
					feeType: $cb.data("fee-type") || '',
					month: $cb.data("month") || '',
					feeAmount: parseFloat($cb.data('amount')) || 0,
					discount: parseFloat($cb.data('discount')) || 0,
					fine: parseFloat($cb.data('fine')) || 0,
					balance: parseFloat($cb.data('balance')) || 0
				};
			}).get();

			if (selectedData.length === 0) {
				alert("No Rows Selected.");
				if ($btn) {
					$btn.button('reset');
				}
				return;
			}

			$.ajax({
				url: base_url + "fees/selectedFeesCollect",
				type: 'POST',
				data: {
					data: JSON.stringify(selectedData),
					branch_id: branchID,
					student_id: studentID
				},
				dataType: "html",
				cache: false,
				success: function(response) {
					$("#feeCollect").html(response);

					selectedData.forEach(function(item, index) {
						var $row = $("#feeCollect").find("input[name='collect_fees[" + index + "][amount]']").closest("tr");

						if ($row.length) {
							$row.find("input[name*='[amount]']").val(item.feeAmount.toFixed(2));
							$row.find("input[name*='[discount_amount]']").val(item.discount.toFixed(2));
							$row.find("input[name*='[fine_amount]']").val(item.fine.toFixed(2));
						}
					});
				},
				complete: function() {
					$(".selectTwo").each(function() {
						$(this).themePluginSelect2({});
					});
					$(".datepicker").each(function() {
						$(this).themePluginDatePicker({
							todayHighlight: true,
							endDate: "today"
						});
					});
					mfp_modal('#modal');
					if ($btn) {
						$btn.button('reset');
					}
				}
			});
		}

		$('#collectFees').on('click', function() {
			var $btn = $(this);
			$btn.button('loading');
			openCollectFeesModal($("#invoiceSummary input[name='cb_invoice']:checked"), $btn);
		});

		$(document).on('click', '.collect-fee-row', function() {
			var $btn = $(this);
			$btn.button('loading');
			openCollectFeesModal($(this).closest('tr.fee-row').find("input[name='cb_invoice']"), $btn);
		});

		// --- Invoice Print ---
		$('#invoicePrint').on('click', function() {
			var $btn = $(this);
			$btn.button('loading');
			var arrayData = [];

			$("#invoiceSummary input[name='cb_invoice']:checked").each(function() {
				var $cb = $(this);
				arrayData.push({
					allocationID: $cb.data("allocation-id") || 0,
					feeTypeID: $cb.data("fee-type-id") || 0,
					trans_fd_id: $cb.data("transport-fd-id") || 0,
					feeType: $cb.data("fee-type") || '',
					month: $cb.data('month') || '',
					feeAmount: parseFloat($cb.data('amount')) || 0,
					discount: parseFloat($cb.data('discount')) || 0,
					fine: parseFloat($cb.data('fine')) || 0
				});
			});

			if (arrayData.length === 0) {
				popupMsg("<?= translate('no_row_are_selected') ?>", "error");
				$btn.button('reset');
				return;
			}

			$("#invDetailsPrint").html("");
			$.ajax({
				url: base_url + "fees/printFeesInvoice",
				type: 'POST',
				data: {
					data: JSON.stringify(arrayData)
				},
				dataType: "html",
				cache: false,
				success: function(response) {
					$("#invDetailsPrint").html(response);
				},
				complete: function() {
					fn_printElem('invoice_print');
					$btn.button('reset');
				}
			});
		});

		// --- Fee Type Change: Update balance/fine ---
		$('#fees_type').on("change", function() {
			var typeID = $(this).val();
			$.ajax({
				url: base_url + 'fees/getBalanceByType',
				type: 'POST',
				data: {
					typeID: typeID
				},
				dataType: "json",
				success: function(data) {
					$('#feeAmount').val(data.balance.toFixed(2));
					$('#fineAmount').val(data.fine.toFixed(2));
				}
			});
		});

		// --- Initialize datepickers ---
		$(".datepicker").datepicker({
			format: "yyyy-mm-dd",
			autoclose: true,
			orientation: "bottom",
			endDate: "today"
		});

	});
</script>
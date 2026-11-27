<style>
	.invoice-summary {
		background: #f8f9fa;
		/* light gray background */
		padding: 15px 20px;
		border-radius: 8px;
		border: 1px solid #ddd;
		margin-bottom: 20px;
	}

	.invoice-summary .amounts {
		list-style: none;
		padding: 0;
		margin: 0;
		display: flex;
		flex-wrap: wrap;
		justify-content: flex-end;
	}

	.invoice-summary .amounts li {
		margin-right: 25px;
		white-space: nowrap;
		font-weight: 500;
		color: #333;
	}

	/* 🔹 Table styling for clean alignment */
	.invoice-details {
		width: 100%;
		border-collapse: collapse;
		font-size: 15px;
		color: #333;
	}

	.invoice-details td {
		padding: 6px 10px;
		border: none;
		vertical-align: top;
	}

	.invoice-details tr:nth-child(odd) {
		background-color: #fff;
	}

	.invoice-details tr:nth-child(even) {
		background-color: #fdfdfd;
	}

	/* 🔹 Align all strong labels */
	.invoice-details strong {
		display: inline-block;
		min-width: 140px;
		/* consistent label width */
		color: #222;
		font-weight: 600;
	}

	/* 🔹 Optional hover row highlight (for interactivity) */
	.invoice-details tr:hover {
		background-color: #f1f3f5;
		transition: background-color 0.2s;
	}

	.table strong {
		display: inline-block;
		min-width: 130px;
		/* adjust this value until it aligns perfectly */
		font-weight: 600;
		color: #222;
	}

	.fee-row {
		transition: all 0.3s ease-in-out;
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
			<?php if ($invoice['status'] != 'unpaid'): ?>
				<li>
					<a href="#history" data-toggle="tab"><i class="fas fa-dollar-sign"></i> <?= translate('payment_history') ?></a>
				</li>
			<?php endif; ?>
			<?php if (get_permission('collect_fees', 'is_add') && $invoice['status'] != 'total'): ?>
				<li>
					<a href="#collect_fees" data-toggle="tab"><i class="fas fa-hand-holding-usd"></i> <?= translate('collect_fees') ?></a>
				</li>
			<?php endif; ?>
			<?php if (get_permission('collect_fees', 'is_add') && $invoice['status'] != 'total'): ?>
				<li>
					<a href="#fully_paid" data-toggle="tab"><i class="far fa-credit-card"></i> Fully Paid</a>
				</li>
			<?php endif; ?>
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
									<h4 class="mt-none mb-none text-dark">Invoice No #<?= $invoice['invoice_no'] ?></h4>
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
							<div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
								<div style="flex: 1; margin-right: 20px;">
									<!-- Your original table code here -->
									<table class="table table-bordered" style="width:100%; font-size: 15px;">
										<tbody>
											<!-- <tr>
												<td><strong>Date :</strong> <?= date('d-m-Y') ?></td>
												<td><strong>Rec No :</strong> <?= $basic['invoice_no'] ?? '' ?></td>
											</tr> -->
											<tr>
												<td><strong>Name :</strong> <?= $basic['first_name'] . ' ' . $basic['last_name'] ?></td>
												<td><strong>Class :</strong> <?= $basic['class_name'] . " (" . $basic['section_name'] . ")" ?></td>
											</tr>
											<tr>
												<td><strong>Father’s Name :</strong> <?= $basic['father_name'] ?? '' ?></td>
												<td><strong>Registration No :</strong> <?= $basic['register_no'] ?></td>
											</tr>
											<tr>
												<td><strong>Category :</strong> <?= $basic['category'] ?? '' ?></td>
												<td><strong>Roll No :</strong> <?= $basic['roll'] ?? '' ?></td>
											</tr>
											<tr>
												<td><strong>Mobile :</strong> <?= $basic['mobileno'] ?? '' ?></td>
											</tr>
										</tbody>
									</table>
								</div>
								<div style="width: 170px; text-align: center;">
									<?php if (!empty($basic['photo'])): ?>
										<img src="<?= base_url('uploads/images/student/' . $basic['photo']); ?>" style="width:190px; height:190px; border:1px solid #000; padding:3px;">
									<?php else: ?>
										<div style="width:190px; height:190px; border:1px solid #000; display:flex; align-items:center; justify-content:center;">
											<span>No Photo</span>
										</div>
									<?php endif; ?>
								</div>
							</div>

						</div>

						<?php if (get_permission('collect_fees', 'is_add')) { ?>
							<button type="button" class="btn btn-default btn-sm mb-sm hidden-print" id="collectFees" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-coins fa-fw"></i> Selected Fees Collect
							</button>
						<?php } ?>
						<div class="table-responsive br-none">
							<table class="table invoice-items table-hover mb-none" id="invoiceSummary">
								<thead>
									<tr class="text-dark">
										<th id="cell-count" class="text-weight-semibold hidden-print">
											<div class="checkbox-replace">
												<label class="i-checks" data-toggle="tooltip" data-original-title="Print Show / Hidden">
													<input type="checkbox" class="fee-selectAll"><i></i>
												</label>
											</div>
										</th>
										<th id="cell-count" class="text-weight-semibold hidden-print">#</th>
										<th id="cell-item" class="text-weight-semibold"><?= translate("fees_type") ?></th>
										<th id="cell-id" class="text-weight-semibold"><?= translate("due_date") ?></th>
										<th id="cell-price" class="text-weight-semibold"><?= translate("status") ?></th>
										<th id="cell-price" class="text-weight-semibold"><?= translate("amount") ?></th>
										<th id="cell-price" class="text-weight-semibold"><?= translate("reciept") ?></th>
										<th id="cell-price" class="text-weight-semibold"><?= translate("mode") ?></th>
										<th id="cell-price" class="text-weight-semibold"><?= translate("discount") ?></th>
										<th id="cell-price" class="text-weight-semibold"><?= translate("fine") ?></th>
										<th id="cell-price" class="text-weight-semibold"><?= translate("paid") ?></th>
										<th id="cell-total" class="text-center text-weight-semibold"><?= translate("balance") ?></th>
									</tr>
								</thead>




								<tbody id="invoiceSummary">
									<?php
									$count = 1;
									$total_fine = $total_discount = $total_paid = $total_balance = $total_amount = 0;

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

									foreach ($allMonths as $monthNo => $monthName): ?>

										<!-- Month Row -->
										<tr class="month-row">
											<td class="hidden-print checked-area" colspan="13">
												<div class="checkbox-replace">
													<label class="i-checks">
														<input type="checkbox" class="month-checkbox" data-month="<?= $monthNo ?>">
														<i></i>
														<strong><?= $monthName ?></strong>
													</label>
												</div>
											</td>
										</tr>
										<?php if ($monthNo == 4): ?>
											<?php
											// Get last balance from first allocation row (same for all)
											$lastBalance = !empty($allocations[0]['previous_last_balance'])
												? $allocations[0]['previous_last_balance']
												: 0;

											// Add to totals
											$total_amount  += $lastBalance;
											$total_balance += $lastBalance;
											?>
											<tr class="fee-row" data-month="4">
												<td></td>
												<td class="hidden-print">
													<!-- This checkbox ensures it can be collected -->
													<input type="checkbox"
														name="cb_invoice"
														data-fee-type="last_balance"
														data-month="4"
														data-amount="<?= $lastBalance ?>"
														data-discount="0"
														data-fine="0"
														data-balance="<?= $lastBalance ?>">
												</td>
												<td class="text-dark"><strong>Last Year Balance</strong></td>
												<td>-</td>
												<td><span class="label-danger-custom">Unpaid</span></td>
												<td><?= currencyFormat($lastBalance) ?></td>
												<td>-</td>
												<td>-</td>
												<td><?= currencyFormat(0) ?></td>
												<td><?= currencyFormat(0) ?></td>
												<td><?= currencyFormat(0) ?></td>
												<td><?= currencyFormat($lastBalance) ?></td>
											</tr>
										<?php endif; ?>


										<?php
										/* -------------------- GENERAL FEE ROWS ---------------------- */
										foreach ($allocations as $row):
											$assignedMonths = explode(',', $row['month']);
											if (in_array($monthNo, $assignedMonths)):

												$fee_amount = $row['amount'] ?? 0;

												if ($row['fine_type'] == 'percentage') {
													$type_fine = ($fee_amount * $row['per_val']) / 100;
												} elseif ($row['fine_type'] == 'fixed') {
													$type_fine = $row['amt_val'];
												} else {
													$type_fine = 0;
												}

												$type_discount = 0;

												if (!empty($row['discount_type'])) {
													if ($row['discount_type'] === 'percentage') {
														$type_discount = ($fee_amount * $row['discount_percentage']) / 100;
													} elseif ($row['discount_type'] === 'fixed') {
														$type_discount = $row['discount_fixed'];
													}
												}


												$type_amount = $row['paid'];
												$balance     = $row['balance'];
												if ($balance < 0) {
													$balance = 0;
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
													<td></td>
													<td class="hidden-print">
														<input type="checkbox" name="cb_invoice"
															value="<?= $row['allocation_id'] . '' . $row['fee_type_id'] . '' . $monthNo ?>"
															data-allocation-id="<?= $row['allocation_id'] ?>"
															data-fee-type-id="<?= $row['fee_type_id'] ?>"
															data-fee-type="general"
															data-transport-fd-id="0"
															data-month="<?= $monthNo ?>"
															data-amount="<?= $fee_amount ?>"
															data-discount="<?= $type_discount ?>"
															data-fine="<?= $type_fine ?>"
															data-balance="<?= $balance ?>">
													</td>
													<td class="text-dark"><?= html_escape($row['fee_name']) ?></td>
													<td><?= _d($row['due_date']) ?></td>
													<td><span class="<?= $label ?>"><?= $status ?></span></td>
													<td><?= currencyFormat($fee_amount) ?></td>
													<td><?= $row['receipt_no'] ?></td>
													<td><?= $row['payment_method_name'] ?></td>
													<td><?= currencyFormat($type_discount) ?></td>
													<td><?= currencyFormat($type_fine) ?></td>
													<td><?= currencyFormat($row['paid_amount'] + $row['fine'] - $row['discount']) ?></td>
													<td><?= currencyFormat($balance) ?></td>
												</tr>
												<?php
											endif;
										endforeach;

										/* -------------------- TRANSPORT FEE ROWS ---------------------- */
										if (!empty($transportFees)) {
											foreach ($transportFees as $t) {

												if ((int)$t->month == (int)$monthNo) {
													$tf_amount = $t->route_fare;
													$tf_discount = 0;
													$tf_fine = 0;
													$tf_paid = 0;
													$tf_balance = $tf_amount;

													$status = translate('unpaid');
													$label  = 'label-danger-custom';

													$total_amount  += $tf_amount;
													$total_discount += 0;
													$total_fine    += 0;
													$total_paid    += 0;
													$total_balance += $tf_balance;
												?>
													<tr class="fee-row transport-row" data-month="<?= $monthNo ?>">
														<td></td>
														<td class="hidden-print">
															<input type="checkbox" name="cb_invoice"
																data-allocation-id="0"
																data-fee-type-id="0"
																data-fee-type="transport"
																data-transport-fd-id="<?= $t->transport_fee_fine_id ?>"
																data-month="<?= $monthNo ?>"
																data-amount="<?= $tf_amount ?>"
																data-discount="0"
																data-fine="0"
																data-balance="<?= $tf_balance ?>">
														</td>
														<td class="text-dark">Transport Fee</td>
														<td><?= _d($t->due_date) ?></td>
														<td><span class="<?= $label ?>"><?= $status ?></span></td>
														<td><?= currencyFormat($tf_amount) ?></td>
														<td>-</td>
														<td>-</td>
														<td><?= currencyFormat(0) ?></td>
														<td><?= currencyFormat(0) ?></td>
														<td><?= currencyFormat(0) ?></td>
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
														<td></td>
														<td class="hidden-print">
															<input type="checkbox"
																name="cb_invoice"
																data-fee-type="books"
																data-month="<?= $monthNo ?>"
																data-amount="<?= $bill->total ?>"
																data-discount="<?= $bill->discount ?>"
																data-fine="0"
																data-balance="<?= $bill->due ?>"
																data-bill-id="<?= $bill->id ?>">
														</td>
														<td class="text-dark">
															<?= $bill->product_names ?: 'Sales Bill' ?> - <?= $bill->bill_no ?>
														</td>
														<td><?= _d($bill->date) ?></td>
														<td><span class="<?= $billLabel ?>"><?= $billStatus ?></span></td>
														<td><?= currencyFormat($bill->total) ?></td>
														<td>-</td>
														<td>-</td>
														<td><?= currencyFormat($bill->discount) ?></td>
														<td><?= currencyFormat(0) ?></td>
														<td><?= currencyFormat($bill->paid) ?></td>
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




						<div class="invoice-summary text-right mt-lg hidden-print" style="width:100%; font-size: 15px;">
							<div class="row">
								<div class="col-md-12">
									<ul class="amounts d-flex flex-wrap justify-content-end">
										<li><strong><?= translate('grand_total') ?> :</strong> <?= currencyFormat($total_amount + $total_fine - $total_discount); ?></li>
										<li><strong><?= translate('discount') ?> :</strong> <?= currencyFormat($total_discount); ?></li>
										<li><strong><?= translate('paid') ?> :</strong> <?= currencyFormat($total_paid); ?></li>
										<li><strong><?= translate('fine') ?> :</strong> <?= currencyFormat($total_fine); ?></li>
										<?php if ($total_balance != 0): ?>
											<li><strong><?= translate('total_paid') ?> (with fine) :</strong> <?= currencyFormat($total_paid); ?></li>
											<li>
												<strong><?= translate('balance') ?> : </strong>
												<?php
												$numberSPELL = "";
												if ($extINTL == true) {
													$numberSPELL = ' </br>( ' . ucwords($spellout->format(number_format($total_balance, 2, '.', ''))) . ' )';
												}
												echo currencyFormat($total_balance) . $numberSPELL;
												?>
											</li>
										<?php else:
											$paidWithFine = ($total_paid);
										?>
											<li>
												<strong><?= translate('total_paid') ?> (<?= translate('with_fine') ?>) : </strong>
												<?php
												$numberSPELL = "";
												if ($extINTL == true) {
													$numberSPELL = ' </br>( ' . ucwords($spellout->format(number_format($paidWithFine, 2, '.', ''))) . ' )';
												}
												echo currencyFormat($paidWithFine) . $numberSPELL;
												?>
											</li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
						</div>
						<div class="invoice-summary text-right mt-lg visible-print-block" id="invDetailsPrint"></div>
					</div>
					<div class="text-right mr-lg hidden-print">
						<button id="invoicePrint" class="btn btn-default ml-sm" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><i class="fas fa-print"></i> <?= translate('print') ?></button>
					</div>
				</div>
			</div>
			<?php if ($invoice['status'] != 'unpaid'): ?>
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
										<h4 class="mt-none mb-none text-dark">Invoice No #<?php echo $invoice['invoice_no'] ?></h4>
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
						<div class="text-right mr-lg hidden-print">
							<button id="payReceiptPrint" class="btn btn-default mr-xs" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><i class="fas fa-print"></i> Selected Pay Receipt</button>
							<button id="paymentPrint" class="btn btn-default" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><i class="fas fa-print"></i> <?= translate('print') ?></button>
						</div>
					</div>
				</div>
			<?php endif; ?>

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
		</div>
	</div>
</section>

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
<!-- <script>
$(document).ready(function() {
    // Hide all fee rows initially
    $('.fee-row').hide();

    // When month checkbox changes
    $('.month-checkbox').on('change', function() {
        const month = $(this).data('month');
        if ($(this).is(':checked')) {
            // Show all rows for this month
            $('.fee-row[data-month="' + month + '"]').slideDown(200);
        } else {
            // Hide rows for this month
            $('.fee-row[data-month="' + month + '"]').slideUp(200);
            // Uncheck all fee checkboxes for that month
            $('.fee-row[data-month="' + month + '"] input[type="checkbox"]').prop('checked', false);
        }
    });
});
</script> -->
<script>
	$(document).ready(function() {
		// Hide all rows initially
		$('.fee-row').hide();

		// Function to get month number from a fee row
		function getMonth(row) {
			return $(row).data('month');
		}

		// Auto-show unpaid and partly paid rows
		$('.fee-row').each(function() {
			const statusText = $(this).find('span').text().trim().toLowerCase();

			// Check if unpaid or partly paid
			if (statusText === 'unpaid' || statusText === 'partly paid') {
				const month = getMonth(this);

				// Show that row and mark fee checkbox selected
				$(this).slideDown(0);
				$(this).find('input[type="checkbox"]').prop('checked', true);

				// Check the month header checkbox too
				$('.month-checkbox[data-month="' + month + '"]').prop('checked', true);
			}
		});

		// When month checkbox changes
		$('.month-checkbox').on('change', function() {
			const month = $(this).data('month');
			if ($(this).is(':checked')) {
				$('.fee-row[data-month="' + month + '"]').slideDown(200);
			} else {
				$('.fee-row[data-month="' + month + '"]').slideUp(200);
				$('.fee-row[data-month="' + month + '"] input[type="checkbox"]').prop('checked', false);
			}
		});
	});
</script>


<script>
	$(document).on('submit', '.frm-submit', function(e) {
		e.preventDefault();


		var $btn = $(this).find('[type=submit]');
		$btn.button('loading');

		// $.ajax({
		// 	url: $(this).attr('action'),
		// 	type: "POST",
		// 	data: $(this).serialize(),
		// 	dataType: "json",
		// 	success: function(res) {
		// 		if (res.status === 'success') {
		// 			alert(res.message);
		// 			location.reload(); // reload to update balances
		// 		} else {
		// 			alert("Error: " + res.message);
		// 		}
		// 		$btn.button('reset');
		// 	},
		// 	error: function() {
		// 		alert("Something went wrong!");
		// 		$btn.button('reset');
		// 	}
		// });
	});
</script>





<script type="text/javascript">
	var branchID = "<?= $basic['branch_id']; ?>";
	var studentID = "<?= $basic['enroll_id']; ?>";

	$(document).ready(function() {

		// --- Month Selection: Show/Hide and Check/Uncheck rows ---
		$(document).on('change', '.month-checkbox', function() {
			var monthNo = $(this).data('month');
			var isChecked = $(this).is(':checked');

			$("#invoiceSummary tr.fee-row[data-month='" + monthNo + "']").each(function() {
				$(this).toggle(isChecked);
				$(this).find("input[name='cb_invoice']").prop('checked', isChecked);
			});

			var allChecked = $("#invoiceSummary input[name='cb_invoice']").length ===
				$("#invoiceSummary input[name='cb_invoice']:checked").length;
			$(".fee-selectAll").prop('checked', allChecked);
		});

		// --- Header Select/Deselect All ---
		$(".fee-selectAll").on("change", function() {
			var checked = $(this).is(':checked');

			$("#invoiceSummary input[name='cb_invoice']").prop('checked', checked);
			$("#invoiceSummary tr.fee-row").toggle(checked);

			$("#invoiceSummary .month-checkbox").each(function() {
				var monthNo = $(this).data('month');
				var monthAllChecked = $("#invoiceSummary tr.fee-row[data-month='" + monthNo + "'] input[name='cb_invoice']:not(:checked)").length === 0;
				$(this).prop('checked', monthAllChecked);
			});
		});

		// --- Collect Fees Modal ---
		$('#collectFees').on('click', function() {
			var $btn = $(this);
			$btn.button('loading');

			var selectedData = $("#invoiceSummary input[name='cb_invoice']:checked").map(function() {
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
				$btn.button('reset');
				return;
			}

			// AJAX to get modal HTML
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

					// Fill modal inputs with exact amounts from main table
					selectedData.forEach(function(item, index) {
						var $row;
						if (item.feeType == 'general') {
							$row = $("#feeCollect").find(
								"input[name='collect_fees[" + index + "][allocation_id]']"
							).closest("tr");
						} else {
							$row = $("#feeCollect").find(
								"input[name='collect_fees[" + index + "][trans_fd_id]']"
							).closest("tr");
						}

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
					$btn.button('reset');
				}
			});
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

		// --- Payment History Print ---
		$('#paymentPrint').on('click', function() {
			var $btn = $(this);
			$btn.button('loading');
			var arrayData = [];

			$("#paymentHistory tbody input[name='cb_feePay']:checked").each(function() {
				arrayData.push({
					payment_id: $(this).val()
				});
			});

			if (arrayData.length === 0) {
				popupMsg("<?= translate('no_row_are_selected') ?>", "error");
				$btn.button('reset');
				return;
			}

			$("#invPaymentHistory").html("");
			$.ajax({
				url: base_url + "fees/printFeesPaymentHistory",
				type: 'POST',
				data: {
					data: JSON.stringify(arrayData)
				},
				dataType: "html",
				cache: false,
				success: function(response) {
					$("#invPaymentHistory").html(response);
				},
				complete: function() {
					fn_printElem('payment_print');
					$btn.button('reset');
				}
			});
		});

		// --- Pay Receipt Print ---
		$('#payReceiptPrint').on('click', function() {
			var $btn = $(this);
			$btn.button('loading');
			var arrayData = [];

			$("#paymentHistory tbody input[name='cb_feePay']:checked").each(function() {
				arrayData.push({
					payment_id: $(this).val(),
					allocationID: $(this).data("allocation-id"),
					feeTypeID: $(this).data("fee-type-id")
				});
			});

			if (arrayData.length === 0) {
				alert("No Rows Selected.");
				$btn.button('reset');
				return;
			}

			$("#invDetailsPrint").html("");
			$.ajax({
				url: base_url + "fees/payReceiptPrint",
				type: 'POST',
				data: {
					student_id: studentID,
					data: JSON.stringify(arrayData)
				},
				dataType: "html",
				cache: false,
				success: function(response) {
					fn_printElem(response, true);
				},
				complete: function() {
					$btn.button('reset');
				}
			});
		});

		// --- Selected Revert ---
		$('#selected_revert').on('click', function() {
			var $this = $(this);
			var paymentID = [];
			$("#paymentHistory tbody input[name='cb_feePay']:checked").each(function() {
				paymentID.push($(this).val());
			});

			if (paymentID.length === 0) {
				alert("No payment selected.");
				return;
			}

			swal({
				title: "<?= translate('are_you_sure') ?>",
				text: "<?= translate('delete_this_information') ?>",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn btn-default swal2-btn-default",
				cancelButtonClass: "btn btn-default swal2-btn-default",
				confirmButtonText: "<?= translate('yes_continue') ?>",
				cancelButtonText: "<?= translate('cancel') ?>",
				buttonsStyling: false,
				footer: "<?= translate('deleted_note') ?>"
			}).then((result) => {
				if (result.value) {
					$.ajax({
						url: base_url + 'fees/paymentRevert',
						type: "POST",
						data: {
							id: paymentID
						},
						dataType: "JSON",
						beforeSend: function() {
							$this.button('loading');
						},
						success: function(data) {
							swal({
								title: "<?= translate('deleted') ?>",
								text: data.message,
								buttonsStyling: false,
								showCloseButton: true,
								focusConfirm: false,
								confirmButtonClass: "btn btn-default swal2-btn-default",
								type: data.status
							}).then(() => {
								location.reload();
							});
						},
						complete: function() {
							$this.button('reset');
						}
					});
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
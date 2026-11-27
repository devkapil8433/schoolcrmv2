<thead>
	<th><?= translate('fees_type') ?> <span class="required">*</span></th>
	<th><?= translate('date') ?> <span class="required">*</span></th>
	<th><?= translate('amount') ?> <span class="required">*</span></th>
	<th><?= translate('discount') ?> <span class="required">*</span></th>
	<th><?= translate('fine') ?> <span class="required">*</span></th>
	<th><?= translate('payment_method') ?> <span class="required">*</span></th>
	<?php
	$colspan = 7;
	$links   = $this->fees_model->get('transactions_links', array('branch_id' => $branch_id), true);
	if ($links['status'] == 1) {
		$colspan += 1;
	?>
		<th><?= translate('account') ?> <span class="required">*</span></th>
	<?php } ?>
	<th><?= translate('remarks') ?></th>
</thead>
<tbody>
	<input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">
	<input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
	<?php
	$count = 0;
	foreach ($record_array as $key => $value) {
		if ($value->feeType == 'general') {
			// $b       = $this->fees_model->getBalance($value->allocationID, $value->feeTypeID);
			// $balance = isset($b['balance']) ? $b['balance'] : 0; // ✅ default 0
			$fee_amount    = $value->feeAmount;
			$type_discount = $value->discount;
			$type_fine     = $value->fine;
			$type_amount   = 0; // because no 'paid' in your array yet
			// $balance       = ($fee_amount + $type_fine) - $type_discount;
			$balance = isset($value->balance) ? (float) $value->balance : 0;
			$fine    = isset($value->fine) ? (float) $value->fine : 0;

			// Skip if balance is zero (already fully paid)
			if ($balance <= 0) {
				continue;
			}

			$typeDetails = $this->db->select('fee_name')->where('id', $value->feeTypeID)->get('fees_type')->row();
			$feeName     = $typeDetails ? $typeDetails->fee_name : translate('fees');
			$count++;
	?>
			<tr>
				<input type="hidden" name="collect_fees[<?php echo $key ?>][allocation_id]" value="<?php echo $value->allocationID; ?>">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][type_id]" value="<?php echo $value->feeTypeID; ?>">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][trans_fd_id]" value="0">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][fee_type]" value="<?php echo $value->feeType; ?>">
				<td class="fee-modal">
					<p style="margin-bottom: 2px; margin-left:5px"><?php echo html_escape($feeName); ?></p>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control datepicker" name="collect_fees[<?php echo $key ?>][date]" value="<?= date('Y-m-d') ?>" autocomplete="off" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control"
							name="collect_fees[<?= $key ?>][amount]"
							value="<?= number_format($balance, 2, '.', '') ?>"
							autocomplete="off" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][discount_amount]" value="0" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][fine_amount]" value="<?= number_format($fine, 2, '.', '') ?>" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<?php
						$payvia_list = $this->app_lib->getSelectList('payment_types');
						echo form_dropdown("collect_fees[$key][pay_via]", $payvia_list, 1, "class='form-control selectTwo' data-width='100%'");
						?>
					</div>
				</td>
				<?php if ($links['status'] == 1) { ?>
					<td class="fee-modal">
						<div class="form-group">
							<?php
							$accounts_list = $this->app_lib->getSelectByBranch('accounts', $branch_id);
							echo form_dropdown("collect_fees[$key][account_id]", $accounts_list, $links['deposit'], "class='form-control selectTwo' data-width='100%'");
							?>
						</div>
					</td>
				<?php } ?>
				<td class="fee-modal">
					<textarea name="collect_fees[<?php echo $key ?>][remarks]" rows="1" class="form-control"></textarea>
				</td>
			</tr>
		<?php
		} elseif ($value->feeType == 'transport') {
			$balance = isset($value->balance) ? (float) $value->balance : 0;
			$fine    = isset($value->fine) ? (float) $value->fine : 0;

			if ($balance <= 0) {
				continue;
			}

			$monthNum = (int) ($value->month ?? 0);
			if ($monthNum <= 0 && !empty($value->trans_fd_id)) {
				$monthRow = $this->db->select('month')->where('id', $value->trans_fd_id)->get('transport_fee_details')->row();
				$monthNum = $monthRow ? (int) $monthRow->month : 0;
			}
			$monthLabel = $this->app_lib->getMonthslist(str_pad((string) $monthNum, 2, '0', STR_PAD_LEFT));
			if (is_array($monthLabel)) {
				$monthLabel = $monthNum > 0 ? date('F', mktime(0, 0, 0, $monthNum, 1)) : '';
			}
			$count++;
		?>
			<tr>
				<input type="hidden" name="collect_fees[<?php echo $key ?>][allocation_id]" value="0">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][type_id]" value="0">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][trans_fd_id]" value="<?php echo $value->trans_fd_id; ?>">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][fee_type]" value="<?php echo $value->feeType; ?>">
				<td class="fee-modal">
					<p style="margin-bottom: 2px; margin-left:5px"><?= translate('transport_fees') ?></p>
					<span style="color: #606060; margin-left: 8px;">- <?php echo html_escape($monthLabel); ?></span>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control datepicker" name="collect_fees[<?php echo $key ?>][date]" value="<?= date('Y-m-d') ?>" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][amount]" value="<?= number_format($balance, 2, '.', '') ?>" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][discount_amount]" value="0" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][fine_amount]" value="<?= number_format($fine, 2, '.', '') ?>" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<?php
						$payvia_list = $this->app_lib->getSelectList('payment_types');
						echo form_dropdown("collect_fees[$key][pay_via]", $payvia_list, 1, "class='form-control selectTwo' data-width='100%'");
						?>
					</div>
				</td>
				<?php if ($links['status'] == 1) { ?>
					<td class="fee-modal">
						<div class="form-group">
							<?php
							$accounts_list = $this->app_lib->getSelectByBranch('accounts', $branch_id);
							echo form_dropdown("collect_fees[$key][account_id]", $accounts_list, $links['deposit'], "class='form-control selectTwo' data-width='100%'");
							?>
						</div>
					</td>
				<?php } ?>
				<td class="fee-modal">
					<textarea name="collect_fees[<?php echo $key ?>][remarks]" rows="1" class="form-control"></textarea>
				</td>
			</tr>
	<?php
		} elseif ($value->feeType == 'last_balance') {
			$balance = isset($value->balance) ? (float) $value->balance : 0;
			$fine    = isset($value->fine) ? (float) $value->fine : 0;

			if ($balance <= 0) {
				continue;
			}
			$count++;
		?>
			<tr>
				<input type="hidden" name="collect_fees[<?php echo $key ?>][allocation_id]" value="0">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][type_id]" value="0">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][trans_fd_id]" value="0">
				<input type="hidden" name="collect_fees[<?php echo $key ?>][fee_type]" value="last_balance">
				<td class="fee-modal">
					<p style="margin-bottom: 2px; margin-left:5px">Last Year Balance</p>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control datepicker" name="collect_fees[<?php echo $key ?>][date]" value="<?= date('Y-m-d') ?>" autocomplete="off" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][amount]" value="<?= number_format($balance, 2, '.', '') ?>" autocomplete="off" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][discount_amount]" value="0" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<input type="text" class="form-control" name="collect_fees[<?php echo $key ?>][fine_amount]" value="<?= number_format($fine, 2, '.', '') ?>" />
					</div>
				</td>
				<td class="fee-modal">
					<div class="form-group">
						<?php
						$payvia_list = $this->app_lib->getSelectList('payment_types');
						echo form_dropdown("collect_fees[$key][pay_via]", $payvia_list, 1, "class='form-control selectTwo' data-width='100%'");
						?>
					</div>
				</td>
				<?php if ($links['status'] == 1) { ?>
					<td class="fee-modal">
						<div class="form-group">
							<?php
							$accounts_list = $this->app_lib->getSelectByBranch('accounts', $branch_id);
							echo form_dropdown("collect_fees[$key][account_id]", $accounts_list, $links['deposit'], "class='form-control selectTwo' data-width='100%'");
							?>
						</div>
					</td>
				<?php } ?>
				<td class="fee-modal">
					<textarea name="collect_fees[<?php echo $key ?>][remarks]" rows="1" class="form-control"></textarea>
				</td>
			</tr>
	<?php
		}
	}
	?>
</tbody>
<tfoot>
  <tr>
    <td colspan="<?= $colspan ?>" class="text-end fw-bold" style="text-align:end;">
      <span style="font-size:20px;">Total Payable Amount:</span>
      <span id="totalAmount" class="text-success" style="font-size:17px; margin-left:8px;">₹0.00</span>
    </td>
  </tr>
</tfoot>
<script>
$(document).ready(function() {
    function calculateTotal() {
        let total = 0;

        $('input[name^="collect_fees"]').each(function() {
            const name = $(this).attr('name');

            if (name.includes('[amount]')) {
                const rowIndex = name.match(/\[(\d+)\]/)[1];
                const amount = parseFloat($(`input[name="collect_fees[${rowIndex}][amount]"]`).val()) || 0;
                const discount = parseFloat($(`input[name="collect_fees[${rowIndex}][discount_amount]"]`).val()) || 0;
                const fine = parseFloat($(`input[name="collect_fees[${rowIndex}][fine_amount]"]`).val()) || 0;

                const totalRow = (amount + fine) - discount;
                total += totalRow;
            }
        });

        $('#totalAmount').text('₹' + total.toFixed(2));
    }

    // Recalculate when any value changes
    $(document).on('input', 'input[name^="collect_fees"]', function() {
        calculateTotal();
    });

    // Run once on modal load
    calculateTotal();
});
</script>


<script type="text/javascript">
	$(function() {
		$(".datepicker").datepicker({
			format: "yyyy-mm-dd",
			autoclose: true,
			orientation: "bottom",
			endDate: "today"
		});
	});
</script>
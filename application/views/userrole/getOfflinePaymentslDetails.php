<?php
$row = $this->userrole_model->getOfflinePaymentsList(array('op.id' => $payments_id), true);
if (empty($row['transport_fee_details_id'])) {
    $groupID = $this->db->select('group_id')->where('id', $row['fees_allocation_id'])->get('fee_allocation')->row();
} else {
    $month = get_type_name_by_id('transport_fee_details', $row['transport_fee_details_id'], 'month');
    $month = $this->app_lib->getMonthslist($month);
}
$statusLabel = '';
$statusBadge = '';
if ($row['status'] == 1) {
    $statusLabel = translate('pending');
    $statusBadge = 'label label-info';
} elseif ($row['status'] == 2) {
    $statusLabel = translate('approved');
    $statusBadge = 'label label-success';
} elseif ($row['status'] == 3) {
    $statusLabel = translate('suspended');
    $statusBadge = 'label label-danger';
}
?>
<header class="panel-heading">
	<h4 class="panel-title"><i class="fas fa-receipt"></i> <?php echo translate('payment_details'); ?></h4>
</header>
<div class="panel-body">
	<table class="table table-bordered table-condensed mb-none">
		<tbody>
			<tr><th><?php echo translate('trx_id'); ?></th><td><?php echo $row['id']; ?></td></tr>
			<tr><th><?= translate('reviewed_by') ?></th><td><?php echo !empty($row['approved_by']) ? get_type_name_by_id('staff', $row['approved_by']) : translate('unreviewed'); ?></td></tr>
			<tr><th><?php echo translate('payment_method'); ?></th><td><?php echo get_type_name_by_id('offline_payment_types', $row['payment_method']); ?></td></tr>
<?php if (empty($row['transport_fee_details_id'])) { ?>
			<tr><th><?php echo translate('fees_group'); ?></th><td><?php echo !empty($groupID) ? get_type_name_by_id('fee_groups', $groupID->group_id) : '—'; ?></td></tr>
			<tr><th><?php echo translate('fees_type'); ?></th><td><?php echo get_type_name_by_id('fees_type', $row['fees_type_id']); ?></td></tr>
<?php } else { ?>
			<tr><th><?php echo translate('transport_fees') . ' ' . translate('month'); ?></th><td><?php echo $month; ?></td></tr>
<?php } ?>
			<tr><th><?php echo translate('date_of_submission '); ?></th><td><?php echo _d($row['submit_date']); ?></td></tr>
			<tr><th><?php echo translate('date_of_payment'); ?></th><td><?php echo _d($row['payment_date']); ?></td></tr>
			<tr><th><?= translate('approve') . ' / ' . translate('reject') . ' ' . translate('date') ?></th><td><?php echo empty($row['approve_date']) ? '—' : _d($row['approve_date']); ?></td></tr>
			<tr><th><?php echo translate('reference'); ?></th><td><?php echo empty($row['reference']) ? 'N/A' : html_escape($row['reference']); ?></td></tr>
			<tr><th><?php echo translate('user') . ' ' . translate('note'); ?></th><td><?php echo empty($row['note']) ? 'N/A' : html_escape($row['note']); ?></td></tr>
<?php if (!empty($row['enc_file_name'])) { ?>
			<tr><th><?php echo translate('proof_of_payment'); ?></th><td><a class="btn btn-default btn-sm" target="_blank" href="<?= base_url('offline_payments/download/' . $row['id'] . '/' . $row['enc_file_name']) ?>"><i class="far fa-arrow-alt-circle-down"></i> <?php echo translate('download'); ?></a></td></tr>
<?php } ?>
<?php if ($row['fine'] != 0) { ?>
			<tr><th><?php echo translate('fee') . ' ' . translate('amount'); ?></th><td><strong><?php echo currencyFormat($row['amount']); ?></strong></td></tr>
			<tr><th><?php echo translate('fine') . ' ' . translate('amount'); ?></th><td><strong><?php echo currencyFormat($row['fine']); ?></strong></td></tr>
			<tr><th><?php echo translate('total_paid'); ?></th><td><strong><?php echo currencyFormat($row['amount'] + $row['fine']); ?></strong></td></tr>
<?php } else { ?>
			<tr><th><?php echo translate('paid') . ' ' . translate('amount'); ?></th><td><strong><?php echo currencyFormat($row['amount']); ?></strong></td></tr>
<?php } ?>
			<tr><th><?php echo translate('status'); ?></th><td><span class="<?php echo $statusBadge; ?>"><?php echo $statusLabel; ?></span></td></tr>
			<tr><th><?php echo translate('comments'); ?> / <?php echo translate('reason'); ?></th><td><?php echo html_escape($row['comments']); ?></td></tr>
		</tbody>
	</table>
<footer class="panel-footer">
	<button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
</footer>

<input type="hidden" name="exam_id" id="examID" value="<?php echo $exam->id ?>">
<div class="form-group">
	<h4 ><?php echo translate('payment_amount')?>: <span ><?php echo currencyFormat($exam->fee) ?></span></h4>
</div>
<div class="form-group">
	<label class="control-label"><?=translate('payment_method')?> <span class="required">*</span></label>
	<?php
		$payvia_list = array('' => translate('select_payment_method'));
		if ($config['paypal_status'] == 1) $payvia_list['paypal'] = 'Paypal';
		if ($config['stripe_status'] == 1) $payvia_list['stripe'] = 'Stripe';
		if ($config['payumoney_status'] == 1) $payvia_list['payumoney'] = 'PayUmoney';
		if ($config['paystack_status'] == 1) $payvia_list['paystack'] = 'Paystack';
		if ($config['razorpay_status'] == 1) $payvia_list['razorpay'] = 'Razorpay';
		if ($config['sslcommerz_status'] == 1) $payvia_list['sslcommerz'] = 'SSLcommerz';
		if ($config['jazzcash_status'] == 1) $payvia_list['jazzcash'] = 'Jazzcash';
		if ($config['midtrans_status'] == 1) $payvia_list['midtrans'] = 'Midtrans';
		if ($config['flutterwave_status'] == 1) $payvia_list['flutterwave'] = 'Flutter Wave';
		if ($config['paytm_status'] == 1) $payvia_list['paytm'] = 'Paytm';
		if ($config['toyyibpay_status'] == 1) $payvia_list['toyyibpay'] = 'toyyibPay';
		if ($config['payhere_status'] == 1) $payvia_list['payhere'] = 'Payhere';
		if ($config['nepalste_status'] == 1) $payvia_list['nepalste'] = 'Nepalste';
		if ($config['bkash_status'] == 1) $payvia_list['bkash'] = 'bKash';
		echo form_dropdown("pay_via", $payvia_list, set_value('pay_via'), "class='form-control' data-width='100%' id='payVia' data-minimum-results-for-search='Infinity' ");
	?>
	<span class="error"></span>
</section>
<?php
$paymentFields = array(
	'payu' => array(
		array('name' => 'payer_name', 'label' => 'Name', 'type' => 'text', 'value' => $getUser['name']),
		array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $getUser['email']),
		array('name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'value' => $getUser['mobileno']),
	),
	'toyyibpay' => array(
		array('name' => 'toyyibpay_email', 'label' => 'Email', 'type' => 'email', 'value' => $getUser['email']),
		array('name' => 'toyyibpay_phone', 'label' => 'Phone', 'type' => 'text', 'value' => $getUser['mobileno']),
	),
	'sslcommerz' => array(
		array('name' => 'sslcommerz_name', 'label' => 'Name', 'type' => 'text', 'value' => $getUser['name']),
		array('name' => 'sslcommerz_email', 'label' => 'Email', 'type' => 'email', 'value' => $getUser['email']),
		array('name' => 'sslcommerz_address', 'label' => 'Address', 'type' => 'text', 'value' => $getUser['address']),
		array('name' => 'sslcommerz_postcode', 'label' => 'Post Code', 'type' => 'text', 'value' => ''),
		array('name' => 'sslcommerz_state', 'label' => 'State', 'type' => 'text', 'value' => $getUser['state']),
		array('name' => 'sslcommerz_phone', 'label' => 'Phone', 'type' => 'text', 'value' => $getUser['mobileno']),
	),
);
foreach ($paymentFields as $groupClass => $fields):
	foreach ($fields as $field):
?>
<div class="form-group <?php echo $groupClass; ?> hidden">
	<label class="control-label"><?php echo $field['label']; ?> <span class="required">*</span></label>
	<input type="<?php echo $field['type']; ?>" class="form-control" name="<?php echo $field['name']; ?>" value="<?php echo html_escape($field['value']); ?>" autocomplete="off" />
	<span class="error"></span>
</section>
<?php
	endforeach;
endforeach;
?>

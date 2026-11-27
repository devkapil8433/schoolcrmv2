<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#productlist" data-toggle="tab"><i class="fas fa-list-ul"></i> <?php echo translate('product') . ' ' . translate('list'); ?></a>
			</li>
			<?php if (get_permission('product', 'is_add')) { ?>
				<li>
					<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?php echo translate('create') . ' ' . translate('product'); ?></a>
				</li>
			<?php } ?>
		</ul>
		<div class="tab-content">
			<?php if (get_permission('product', 'is_add')): ?>
				<button class="btn btn-success mb-sm" data-toggle="modal" data-target="#importProductModal">
					<i class="fas fa-file-import"></i> Import Products
				</button>
			<?php endif; ?>

			<div id="productlist" class="tab-pane active mb-md">
				<div class="export_title"><?php echo translate('product') . " " . translate('list'); ?></div>
				<table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php echo translate('sl'); ?></th>
							<?php if (is_superadmin_loggedin()): ?>
								<th><?= translate('branch') ?></th>
							<?php endif; ?>
							<th><?php echo translate('name'); ?></th>
							<th><?php echo translate('code'); ?></th>
							<th><?php echo translate('category'); ?></th>
							<th><?php echo translate('purchase_unit'); ?></th>
							<th><?php echo translate('sale_unit'); ?></th>
							<th><?php echo translate('unit_ratio'); ?></th>
							<th><?php echo translate('purchase_price'); ?></th>
							<th><?php echo translate('sales_price'); ?></th>
							<th><?php echo translate('remarks'); ?></th>
							<th><?php echo translate('action'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						if (!empty($productlist)) {
							foreach ($productlist as $row):
						?>
								<tr>
									<td><?php echo $count++; ?></td>
									<?php if (is_superadmin_loggedin()): ?>
										<td><?php echo get_type_name_by_id('branch', $row['branch_id']); ?></td>
									<?php endif; ?>
									<td><?php echo html_escape($row['name']); ?></td>
									<td><?php echo html_escape($row['code']); ?></td>
									<td><?php echo html_escape($row['category_name']); ?></td>
									<td><?php echo html_escape($row['p_unit_name']); ?></td>
									<td><?php echo html_escape($row['s_unit_name']); ?></td>
									<td><?php echo html_escape($row['unit_ratio']); ?></td>
									<td><?php echo html_escape(currencyFormat($row['purchase_price'])); ?></td>
									<td><?php echo html_escape(currencyFormat($row['sales_price'])); ?></td>
									<td><?php echo html_escape($row['remarks']); ?></td>
									<td class="min-w-xs">
										<!-- 👁 View Usage -->
										<button class="btn btn-circle icon btn-info"
											onclick="viewProductUsage(<?= $row['id'] ?>)"
											data-toggle="tooltip"
											title="View Usage">
											<i class="fas fa-eye"></i>
										</button>
										<?php if (get_permission('product', 'is_edit')): ?>
											<a href="<?php echo base_url('inventory/product_edit/' . $row['id']); ?>" class="btn btn-circle icon btn-default" data-placement="top" data-toggle="tooltip" data-original-title="<?php echo translate('edit'); ?>">
												<i class="fas fa-pen-nib"></i>
											</a>
										<?php endif;
										if (get_permission('product', 'is_delete')): ?>
											<!-- <?php echo btn_delete('inventory/product_delete/' . $row['id']); ?> -->
											<button class="btn btn-circle icon btn-danger"
												onclick="deleteProduct(<?= $row['id'] ?>)">
												<i class="fas fa-trash"></i>
											</button>

										<?php endif; ?>
									</td>
								</tr>
						<?php endforeach;
						} ?>
					</tbody>
				</table>
			</div>
			<?php if (get_permission('product', 'is_add')) { ?>
				<div id="create" class="tab-pane">
					<?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit')); ?>

					<?php if (is_superadmin_loggedin()): ?>
						<div class="form-group">
							<label class="control-label col-md-3"><?= translate('branch') ?> <span class="required">*</span></label>
							<div class="col-md-6">
								<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' data-width='100%' id='branch_id'
									data-plugin-selectTwo  data-minimum-results-for-search='Infinity'");
								?>
								<span class="error"></span>
							</div>
						</div>
					<?php endif; ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('product') . " " . translate('name'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="product_name" value="" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('product') . " " . translate('code'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="product_code" value="" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('product') . " " . translate('category'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$categorylist = $this->app_lib->getSelectByBranch('product_category', $branch_id);
							echo form_dropdown("product_category", $categorylist, set_value("product_category"), "class='form-control' data-plugin-selectTwo id='productCategory'
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('purchase_unit'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							echo form_dropdown("purchase_unit", $unitlist, set_value("purchase_unit"), "class='form-control prounit' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('sales_unit'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							echo form_dropdown("sales_unit", $unitlist, set_value("sales_unit"), "class='form-control prounit' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('unit_ratio'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="unit_ratio" id="unit_ratio" value="" placeholder="Eg. Purchase Unit : KG & Sales Unit : Gram = Ratio : 1000" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('purchase_price'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="purchase_price" id="purchase_price" value="" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('sales_price'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="sales_price" id="sales_price" value="" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('remarks'); ?></label>
						<div class="col-md-6 mb-lg">
							<input type="text" class="form-control" name="remarks" id="remarks" value="" />
						</div>
					</div>

					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-2">
								<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><i class="fas fa-plus-circle"></i> <?php echo translate('save'); ?></button>
							</div>
						</div>
					</footer>
					<?php echo form_close(); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
<div class="modal fade" id="importProductModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<?php echo form_open_multipart('Inventory/import_products'); ?>
			<div class="modal-header">
				<h5 class="modal-title">Import Products (CSV)</h5>
			</div>
			<div class="modal-body">
				<input type="file" name="csv_file" class="form-control" required accept=".csv">
				<small class="text-muted">
					CSV format only. First row must be header.
				</small>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<i class="fas fa-upload"></i> Import
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">
					Cancel
				</button>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	$('#branch_id').on('change', function() {
		var branchID = $(this).val();
		$.ajax({
			url: "<?= base_url('ajax/getDataByBranch') ?>",
			type: 'POST',
			data: {
				branch_id: branchID,
				table: 'product_category'
			},
			success: function(data) {
				$('#productCategory').html(data);
			}
		});

		$.ajax({
			url: "<?= base_url('ajax/getDataByBranch') ?>",
			type: 'POST',
			data: {
				branch_id: branchID,
				table: 'product_unit'
			},
			success: function(data) {
				$('.prounit').html(data);
			}
		});
	});
</script>
<script>
	function deleteProduct(id) {
		if (!confirm('Are you sure you want to delete this product?')) return;

		$.ajax({
			url: base_url + 'inventory/product_delete/' + id,
			type: 'POST',
			dataType: 'json',
			success: function(res) {
				if (res.status === 'success') {
					alert(res.message);
					location.reload();
				} else {
					Swal.fire('Cannot Delete', res.message, 'warning');
				}
			},
			error: function() {
				alert('Something went wrong');
			}
		});
	}
</script>
<script>
function viewProductUsage(productId) {
	$.ajax({
		url: base_url + 'inventory/product_usage/' + productId,
		type: 'GET',
		dataType: 'json',
		success: function (res) {

			let html = '<ul style="text-align:left; font-size:14px;">';

			if (res.purchase > 0) {
				html += `<li>📦 Used in <b>Purchases</b> (${res.purchase})</li>`;
			}
			if (res.sales > 0) {
				html += `<li>💰 Used in <b>Sales</b> (${res.sales})</li>`;
			}
			if (res.sets > 0) {
				html += `<li>🧩 Used in <b>Sets</b> (${res.sets})</li>`;
			}

			if (res.purchase == 0 && res.sales == 0 && res.sets == 0) {
				html += `<li>✅ This product is not used anywhere</li>`;
			}

			html += '</ul>';

			Swal.fire({
				title: 'Product Usage',
				html: html,
				icon: 'info',
				confirmButtonText: 'Close'
			});
		},
		error: function () {
			Swal.fire('Error', 'Unable to fetch product usage', 'error');
		}
	});
}
</script>

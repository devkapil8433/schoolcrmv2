<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin()): ?>
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body">
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				<a href="<?=base_url('digital_diary/notebook_teaching_activity')?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
			</div>
		</section>
		<?php else: ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('branch')?></h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(current_url(), array('method' => 'get', 'class' => 'form-inline')); ?>
				<div class="form-group">
					<label class="control-label"><?=translate('branch')?></label>
					<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown("branch_id", $arrayBranch, $branch_id, "class='form-control' id='branch_id' data-plugin-selectTwo data-width='200px'");
					?>
				</div>
				<button type="submit" class="btn btn-default"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
				<?php echo form_close(); ?>
			</div>
		</section>
		<?php endif; ?>
		<?php endif; ?>

		<section class="panel" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-book-reader"></i> Note Book Type</h4>
				<div class="panel-btn">
					<a href="<?=base_url('digital_diary/notebook_type_add') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-circle">
						<i class="fas fa-plus-circle"></i> Add Note Book Type
					</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if (!$this->db->table_exists('digital_diary_notebook_type')): ?>
				<div class="alert alert-warning">
					<strong>Setup required.</strong> Run the SQL script to create the Note Book Type table: <code>application/sql/digital_diary_notebook_type.sql</code>
				</div>
				<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-hover" id="notebookTypeTable" width="100%">
						<thead>
							<tr>
								<th><?=translate('sl')?></th>
								<th>Name</th>
								<th>Code</th>
								<th>Description</th>
								<th>Status</th>
								<th><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
<?php if ($this->db->table_exists('digital_diary_notebook_type')): ?>
<script type="text/javascript">
	$(document).ready(function () {
		var filter = function (d) {
			d.branch_id = '<?=isset($branch_id) ? (int)$branch_id : ""?>';
		};
		var cusDataTable = initDatatable("#notebookTypeTable", "digital_diary/getNotebookTypeDT", filter);
	});
</script>
<?php endif; ?>

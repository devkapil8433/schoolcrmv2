<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin()): ?>
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body">
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				<a href="<?=base_url('digital_diary/teaching_activity_types')?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
			</div>
		</section>
		<?php else: ?>
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('branch')?></h4>
			</header>
			<div class="panel-body">
				<?php echo form_open(base_url('digital_diary/teaching_activity_types'), array('method' => 'get', 'class' => 'form-inline')); ?>
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
				<h4 class="panel-title"><i class="fas fa-list"></i> Teaching Activity</h4>
				<div class="panel-btn">
					<a href="<?=base_url('digital_diary/teaching_activity_add') . (is_superadmin_loggedin() && !empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '')?>" class="btn btn-default btn-circle">
						<i class="fas fa-plus-circle"></i> <?=translate('add')?> Teaching Activity
					</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if (!$this->db->table_exists('digital_diary_teaching_activity')): ?>
				<div class="alert alert-warning">
					<strong>Setup required.</strong> Run the SQL script to create the table: <code>application/sql/digital_diary_teaching_activity.sql</code>
				</div>
				<?php else: ?>
				<p class="text-muted mb-md">These types are used for <strong>class work</strong> and <strong>home work</strong> on <a href="<?=base_url('digital_diary/today_work')?>">Today's Work</a>. Enable <strong>Coordinator</strong> per type, then use <i class="fas fa-user-plus"></i> to assign a teacher to <strong>multiple classes</strong> for that activity.</p>
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-hover" id="teachingActivityTable" width="100%">
						<thead>
							<tr>
								<th><?=translate('activity')?></th>
								<th><?=translate('note')?></th>
								<th>Notebook Type</th>
								<th><?=translate('remarks')?></th>
								<th class="no-sort no-export">Coordinator</th>
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
<?php if ($this->db->table_exists('digital_diary_teaching_activity')): ?>
<script type="text/javascript">
	$(document).ready(function () {
		var filter = function (d) {
			d.branch_id = '<?=isset($branch_id) ? (int)$branch_id : ""?>';
		};
		initDatatable("#teachingActivityTable", "digital_diary/getTeachingActivityDT", filter);
		$(document).on('change', '.ta-role-switch', function () {
			var $el = $(this);
			var state = $el.prop('checked');
			$.ajax({
				type: 'POST',
				url: base_url + 'digital_diary/teaching_activity_role_toggle',
				data: {
					id: $el.data('id'),
					role: $el.data('role'),
					status: state
				},
				dataType: 'json',
				success: function (data) {
					if (data && data.status == true) {
						if (typeof alertMsg === 'function') {
							alertMsg(data.msg);
						}
					} else {
						$el.prop('checked', !state);
					}
				},
				error: function () {
					$el.prop('checked', !state);
				}
			});
		});
	});
</script>
<?php endif; ?>

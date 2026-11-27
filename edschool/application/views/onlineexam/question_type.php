<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin()): ?>
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body clearfix">
				<a href="<?=base_url('onlineexam/question_type')?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
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
						echo form_dropdown("branch_id", $arrayBranch, isset($branch_id) ? $branch_id : '', "class='form-control' id='branch_id' data-plugin-selectTwo data-width='200px'");
					?>
				</div>
				<button type="submit" class="btn btn-default"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
				<?php echo form_close(); ?>
			</div>
		</section>
		<?php endif; ?>
		<?php endif; ?>

		<div class="row" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<div class="col-md-4">
				<section class="panel qt-panel">
					<header class="panel-heading">
						<h4 class="panel-title">Update Question Type</h4>
					</header>
					<div class="panel-body">
						<?php echo form_open(base_url('onlineexam/save_question_type'), array('class' => 'frm-submit', 'id' => 'questionTypeAddForm')); ?>
							<input type="hidden" name="qt_id" id="qt_id" value="">
							<input type="hidden" name="branch_id" value="<?=isset($branch_id) ? (int)$branch_id : ''?>">
							<div class="form-group">
								<label class="control-label">Question Type</label>
								<input type="text" class="form-control" name="question_type" id="question_type" placeholder="" readonly>
								<span class="error"></span>
							</div>
							<div class="form-group">
								<label class="control-label"><?=translate('description')?></label>
								<textarea class="form-control" rows="3" name="description" id="description"></textarea>
								<span class="error"></span>
							</div>
							<div class="form-group">
								<label class="control-label">Question Marks</label>
								<input type="number" min="0" step="1" class="form-control" name="marks" id="marks" value="1">
								<span class="error"></span>
							</div>
							<div class="text-right qt-form-actions">
								<button type="button" class="btn btn-default" id="qtCancelEdit" style="display:none;">Cancel</button>
								<button type="submit" class="btn btn-info" id="qtSubmitBtn" disabled>Update</button>
							</div>
						<?php echo form_close(); ?>
					</div>
				</section>
			</div>
			<div class="col-md-8">
				<section class="panel qt-panel">
					<header class="panel-heading">
						<h4 class="panel-title">Question Type List</h4>
					</header>
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-bordered table-condensed table-hover" id="questionTypeTable" width="100%">
								<thead>
									<tr>
										<th>Question Type</th>
										<th>Description</th>
										<th class="text-center">Question Marks</th>
										<th class="text-center no-sort"><?=translate('action')?></th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	.qt-panel .panel-heading {
		background: #fff;
		border-bottom: 1px solid #e9e9e9;
	}

	.qt-panel .panel-title {
		font-weight: 600;
	}

	#questionTypeTable th,
	#questionTypeTable td {
		vertical-align: middle;
	}

	#questionTypeTable td:nth-child(3),
	#questionTypeTable th:nth-child(3) {
		width: 140px;
		text-align: center;
	}

	#questionTypeTable td:nth-child(4),
	#questionTypeTable th:nth-child(4) {
		width: 100px;
		text-align: center;
	}

	#questionTypeTable td .btn.icon {
		margin: 0 2px;
	}

	.qt-form-actions .btn {
		min-width: 70px;
	}
</style>
<script type="text/javascript">
	$(document).ready(function () {
		var filter = function (d) {
			d.branch_id = '<?=isset($branch_id) ? (int)$branch_id : ""?>';
		};
		initDatatable("#questionTypeTable", "onlineexam/getQuestionTypeDT", filter, 10, true, false, [
			{"orderable": false, "targets": 'no-sort'},
			{"orderable": false, "targets": [3], 'class':'action'}
		]);

		$(document).on('click', '.qt-edit', function () {
			var $btn = $(this);
			$('#qt_id').val($btn.data('id') || '');
			$('#question_type').val($btn.data('name') || '');
			$('#description').val($btn.data('description') || '');
			$('#marks').val($btn.data('marks') || 1);
			$('#qtSubmitBtn').prop('disabled', false);
			$('#qtCancelEdit').show();
			$('#question_type').focus();
		});

		$('#qtCancelEdit').on('click', function () {
			$('#qt_id').val('');
			$('#question_type').val('');
			$('#description').val('');
			$('#marks').val(1);
			$('#qtSubmitBtn').prop('disabled', true);
			$('#qtCancelEdit').hide();
		});
	});
</script>

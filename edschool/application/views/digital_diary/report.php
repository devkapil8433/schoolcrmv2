<?php $widget = (is_superadmin_loggedin() ? 3 : 4); ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
		<?php echo form_open(base_url('digital_diary/report'), array('class' => 'search_form'));?>
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<div class="panel-body">
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<?php endif; ?>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('class')?></label>
							<?php
								$arrayClass = $this->app_lib->getClass($branch_id);
								echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
								data-plugin-selectTwo data-width='100%'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('section')?></label>
							<?php
								$arraySection = $this->app_lib->getSections(set_value('class_id'), false);
								echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="col-md-<?php echo $widget; ?>">
						<div class="form-group">
							<label class="control-label"><?=translate('date')?></label>
							<input type="text" class="form-control" name="date" id="date" value="<?=set_value('date', date('Y-m-d'))?>" data-plugin-datepicker data-plugin-options='{"format": "yyyy-mm-dd"}' placeholder="<?=translate('date')?>">
							<span class="error"></span>
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="search" value="1" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing" class="btn btn btn-default btn-block"> <i class="fas fa-filter"></i> <?=translate('filter')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>

		<section class="panel appear-animation" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-chart-bar"></i> <?=isset($this->lang->language['diary_report']) ? translate('diary_report') : 'Diary Report'?></h4>
			</header>
			<div class="panel-body">
				<div class="export_title"><?=isset($this->lang->language['diary_entries']) ? translate('diary_entries') : 'Diary Entries'?></div>
				<table class="table table-bordered table-condensed" id="digitalDiaryReportList" width="100%">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('date')?></th>
							<th><?=translate('class')?></th>
							<th><?=translate('section')?></th>
							<th><?=isset($this->lang->language['student']) ? translate('student') : 'Student'?></th>
							<th><?=isset($this->lang->language['entry_title']) ? translate('entry_title') : 'Entry Title'?></th>
							<th><?=isset($this->lang->language['status']) ? translate('status') : 'Status'?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
	var searchBtn = "";
	var cusDataTable = "";
	$(document).ready(function () {
		var filter = function (d) {
			d.branch_id = $('#branch_id').val();
			d.class_id = $('#class_id').val();
			d.section_id = $('#section_id').val();
			d.date = $('#date').val();
			d.submit_btn = searchBtn;
		};
		cusDataTable = initDatatable("#digitalDiaryReportList", "digital_diary/getDiaryListDT", filter);
		// Load existing data on page load
		searchBtn = 1;
		$(".appear-animation").show();
		cusDataTable.draw();
		$("form.search_form").on('submit', function(e) {
			var $this = $(this);
			e.preventDefault();
			var btn = $this.find('[type="submit"]');
			$.ajax({
				url: $(this).attr('action'),
				type: "POST",
				data: $(this).serialize(),
				dataType: 'json',
				beforeSend: function () {
					btn.button('loading');
				},
				success: function (data) {
					$('.error').html("");
					if (data.status == "fail") {
						$('.appear-animation').hide();
						$.each(data.error, function (index, value) {
							$this.find("[name='" + index + "']").parents('.form-group').find('.error').html(value);
						});
					} else {
						$(".export_title").html(data.export_title || '<?=isset($this->lang->language["diary_report"]) ? translate("diary_report") : "Diary Report"?>');
						searchBtn = 1;
						$(".appear-animation").show();
						cusDataTable.draw();
					}
				},
				complete: function () {
					btn.button('reset');
				},
				error: function () {
					btn.button('reset');
				}
			});
		});

		$('#branch_id').on('change', function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
		});
	});
</script>

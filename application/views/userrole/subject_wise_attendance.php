<section class="panel">
	<?php echo form_open($this->uri->uri_string());?>
	<header class="panel-heading">
		<h4 class="panel-title"><?=translate('select_ground')?></h4>
	</header>
	<div class="panel-body">
		<div class="row mb-sm">
			<div class="col-md-6 mb-sm">
				<div class="form-group">
					<label class="control-label"><?=translate('month')?> <span class="required">*</span></label>
					<div class="input-group">
						<input type="text" class="form-control" name="timestamp" value="<?=set_value('timestamp', date('Y-F'))?>" data-plugin-datepicker required
						data-plugin-options='{ "format": "yyyy-MM", "minViewMode": "months", "orientation": "bottom"}' />
						<span class="input-group-addon"><i class="icon-event icons"></i></span>
					</div>
					<span class="error"><?=form_error('month')?></span>
				</div>
			</div>
			<div class="col-md-6 mb-sm">
				<div class="form-group">
					<label class="control-label"><?=translate('subject')?> <span class="required">*</span></label>
					<?php
						$arraySubject = array("" => translate('select'));
						$query = $this->subject_model->getSubjectByClassSection($class_id, $section_id);
						$subjects = $query->result_array();
						foreach ($subjects as $row){
							$subjectID = $row['subject_id'];
							$arraySubject[$subjectID] = $row['subjectname'] . " (" . $row['subject_code'] . ")";
						}
						echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='form-control' id='subject_id'
						data-plugin-selectTwo data-width='100%' ");
					?>
					<span class="error"><?=form_error('subject_id')?></span>
				</div>
			</div>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-offset-10 col-md-2">
				<button type="submit" name="search" value="1" class="btn btn-default btn-block"><i class="fas fa-filter"></i> <?=translate('filter')?></button>
			</div>
		</div>
	</footer>
	<?php echo form_close();?>
</section>

<?php 
if (isset($studentDetails)): 
	$subject_name = get_type_name_by_id('subject', $subject_id);
	$class_name = get_type_name_by_id('class', $class_id);
	$section_name = get_type_name_by_id('section', $section_id);
?>
<section class="panel mt-sm">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-users"></i> <?=translate('attendance_report')?></h4>
	</header>
	<div class="panel-body">
		<div class="export_title">Period Attendance Sheet on <?php echo $subject_name; ?> - <?=date("F Y", strtotime($year.'-'.$month)); ?> <?php 
			echo translate('class') .' : '. $class_name;
			echo ' ( ' .translate('section'). ' : ' . $section_name . ' )';
		?></div>
		<div class="row mb-md">
			<div class="col-md-12">
				<p class="mb-xs"><strong><?php echo translate('subject'); ?>:</strong> <?php echo html_escape($subject_name); ?></p>
				<p class="mb-xs"><strong><?php echo translate('class'); ?>:</strong> <?php echo html_escape($class_name); ?> · <strong><?php echo translate('section'); ?>:</strong> <?php echo html_escape($section_name); ?></p>
			</div>
			<div class="col-md-offset-6 col-md-6">
				<table class="table table-condensed table-bordered text-dark text-center mb-none">
					<tbody>
						<tr>
							<td><strong>Present :</strong> <i class="far fa-check-circle hidden-print text-success"></i><span class="visible-print">P</span></td>
							<td><strong>Absent : </strong> <i class="far fa-times-circle hidden-print text-danger"></i><span class="visible-print">A</span></td>
							<td><strong>Late : </strong> <i class="far fa-clock hidden-print text-tertiary"></i><span class="visible-print">L</span></td>
							<td><strong>Half Day : </strong> <i class="fas fa-star-half-alt text-tertiary"></i><span class="visible-print">HD</span></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="table-responsive">
			<table class="table table-bordered table-hover table-condensed table-export text-dark mb-none">
				<thead>
					<tr>
						<th><?=translate('student_name')?></th>
<?php
$weekends = $this->attendance_model->getWeekendDaysSession($branch_id);
$getHolidays = $this->attendance_model->getHolidays($branch_id);
$getHolidays = explode('","', $getHolidays);
for ($i = 1; $i <= $days; $i++) {
	$date = date('Y-m-d', strtotime($year . '-' . $month . '-' . $i));
?>
						<th class="text-center no-sort"><?php echo date('D', strtotime($date)); ?><br><?php echo date('d', strtotime($date)); ?></th>
<?php } ?>
						<th class="text-center">(%)</th>
						<th class="text-center">W</th>
						<th class="text-center text-success">P</th>
						<th class="text-center text-danger">A</th>
						<th class="text-center text-tertiary">L</th>
						<th class="text-center text-tertiary">HD</th>
					</tr>
				</thead>
				<tbody>
<?php
$total_present = 0;
$total_absent = 0;
$total_late = 0;
$total_half_day = 0;
$total_weekends = 0;
$studentID = $studentDetails['enroll_id'];
?>
					<tr>
						<td><?php echo $studentDetails['fullname']; ?> <div class="visible-print"> / <?php echo translate('register_no') . " " .  $studentDetails['register_no'] ?></div></td>
<?php
for ($i = 1; $i <= $days; $i++) { 
	$date = date('Y-m-d', strtotime($year . '-' . $month . '-' . $i));
	$day = date('l', strtotime($date));
	$atten = $this->attendance_period_model->get_attendance_by_subjectID($studentID, $class_id, $section_id, $date, $day, $subject_id);
?>
						<td class="center">
<?php if (!empty($atten)) { ?>
							<span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?php echo $atten['remark']; ?>">
								<?php echo $atten['time'] ?><br>
<?php if ($atten['status'] == 'A') { $total_absent++; ?>
								<i class="far fa-times-circle text-danger"></i><span class="visible-print">- A</span>
<?php } if ($atten['status'] == 'P') { $total_present++; ?>
								<i class="far fa-check-circle text-success"></i><span class="visible-print">- P</span>
<?php } if ($atten['status'] == 'L') { $total_late++; ?>
								<i class="far fa-clock text-info"></i><span class="visible-print">-L</span>
<?php } if ($atten['status'] == 'H'){ ?>
								<i class="fas fa-hospital-symbol text-tertiary"></i><span class="visible-print">- H</span>
<?php } if ($atten['status'] == 'HD'){ $total_half_day++; ?>
								<i class="fas fa-star-half-alt text-tertiary"></i><span class="visible-print">- HD</span>
<?php } ?>
							</span>
<?php } else {
	if (in_array($date, $getHolidays)) {
		echo '<i class="fas fa-hospital-symbol text-tertiary"></i><span class="visible-print">H</span>';
	} else {
		if (in_array($date, $weekends)) {
			$total_weekends++;
			echo '<span class="text-success">W</span>';
		}
	}
} ?>
						</td>
<?php } ?>
						<td class="center"><?php 
						$total_working_days = ($total_present + $total_absent + $total_late + $total_half_day);
						if ($total_working_days == 0) {
							echo "-";
						} else {
							$total_present_calc = ($total_present + $total_late + $total_half_day);
							$percentage = ($total_present_calc / $total_working_days) * 100;
							echo round($percentage);
						}
						?></td>
						<td class="center"><?=$total_weekends?></td>
						<td class="center"><?=($total_present + $total_late + $total_half_day)?></td>
						<td class="center"><?=$total_absent?></td>
						<td class="center"><?=$total_late?></td>
						<td class="center"><?=$total_half_day?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</section>
<?php endif; ?>

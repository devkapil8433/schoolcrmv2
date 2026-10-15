<?php
$marksheet_template = $this->marksheet_template_model->getTemplate($templateID, $branchID);
$markPadding = $marksheet_template['top_space'] . 'px ' . $marksheet_template['right_space'] . 'px ' . $marksheet_template['bottom_space'] . 'px ' . $marksheet_template['left_space'] . 'px';
$markBgStyle = empty($marksheet_template['background'])
    ? 'background:#fff;'
    : 'background-image:url("' . base_url('uploads/marksheet/' . $marksheet_template['background']) . '");background-repeat:no-repeat;background-size:100% 100%;';
?>

<?php
$extINTL = extension_loaded('intl');
if (!empty($studentID)) {
		$result = $this->exam_model->getStudentReportCard($studentID, $examID, $sessionID, $class_id, $section_id);
		$student = $result['student'];
		$getMarksList = $result['exam'];

		$rankDetail = $this->db->where(array('exam_id ' => $examID, 'enroll_id  ' => $student['enrollID']))->get('exam_rank')->row();
		$getExam = $this->db->where(array('id' => $examID))->get('exam')->row_array();
		$schoolYear = get_type_name_by_id('schoolyear', $sessionID, 'school_year');

		$extendsData = [];
		$extendsData['print_date'] = $print_date;
		$extendsData['schoolYear'] = $schoolYear;
		$extendsData['exam_name'] = $getExam['name'];
		$extendsData['teacher_comments'] = empty($rankDetail->teacher_comments) ? '' : $rankDetail->teacher_comments;
		$extendsData['principal_comments'] = empty($rankDetail->principal_comments) ? '' : $rankDetail->principal_comments;
		$header_content = $this->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'header_content');
		$footer_content = $this->marksheet_template_model->tagsReplace($student, $marksheet_template, $extendsData, 'footer_content');

		?>
<div class="relative">
	<div class="absolute inset-0" style="<?php echo $markBgStyle; ?>"></div>
	<div class="relative z-[2] mx-auto min-w-[1000px] [&_table]: [&_table]:mx-auto print:[&_th]:! print:[&_td]:! print:[&_th]:!bg-transparent print:[&_td]:!bg-transparent" style="padding: <?php echo $markPadding; ?>;">
		<?php echo $header_content ?>
		<table class="table table-bordered table-hover table-condensed table-export">
			<thead>
				<tr>
					<th>Subjects</th>
				<?php 
				$markDistribution = json_decode($getExam['mark_distribution'], true);
				foreach ($markDistribution as $id) {
					?>
					<th><?php echo get_type_name_by_id('exam_mark_distribution',$id)  ?></th>
				<?php } ?>
				<?php if ($getExam['type_id'] == 1) { ?>
					<th>Total</th>
				<?php } elseif($getExam['type_id'] == 2) { ?>
					<th>Grade</th>
					<th>Point</th>
<?php if ($marksheet_template['remark'] == 1) { ?>
					<th>Remark</th>
<?php } ?>
				<?php } elseif ($getExam['type_id'] == 3) { ?>
					<th>Total</th>
					<th>Grade</th>
					<th>Point</th>
<?php if ($marksheet_template['remark'] == 1) { ?>
					<th>Remark</th>
<?php } ?>
				<?php } ?>
<?php if ($marksheet_template['subject_position'] == 1) { ?>
					<th>Subject Position</th>
<?php } ?>

				</tr>
			</thead>
			<tbody>
			<?php
			$colspan = count($markDistribution) + 1;
			$total_grade_point = 0;
			$grand_obtain_marks = 0;
			$grand_full_marks = 0;
			$result_status = 1;
			foreach ($getMarksList as $row) {
				?>
				<tr>
					<td valign="middle" width="35%"><?=$row['subject_name']?></td>
				<?php 
				$total_obtain_marks = 0;
				$total_full_marks = 0;
				$fullMarkDistribution = json_decode($row['mark_distribution'], true);
				$obtainedMark = json_decode($row['get_mark'], true);
				foreach ($fullMarkDistribution as $i => $val) {
					$obtained_mark = floatval($obtainedMark[$i]);
					$fullMark = floatval($val['full_mark']);
					$passMark = floatval($val['pass_mark']);
					if ($obtained_mark < $passMark) {
						$result_status = 0;
					}

					$total_obtain_marks += $obtained_mark;
					$obtained = $row['get_abs'] == 'on' ? 'Absent' : $obtained_mark;
					$total_full_marks += $fullMark;
					?>
				<?php if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3){ ?>
					<td valign="middle">
						<?php 
							if ($row['get_abs'] == 'on') {
								echo 'Absent';
							} else {
								echo $obtained_mark . '/' . $fullMark;
							}
						?>
					</td>
				<?php } if ($getExam['type_id'] == 2){ ?>
					<td valign="middle">
						<?php 
							if ($row['get_abs'] == 'on') {
								echo 'Absent';
							} else {
								$percentage_grade = ($obtained_mark * 100) / $fullMark;
								$grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
								echo $grade['name'];
							}
						?>
					</td>
				<?php } ?>
				<?php
				}
				$grand_obtain_marks += $total_obtain_marks;
				$grand_full_marks += $total_full_marks;
				?>
				<?php if($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
					<td valign="middle"><?=$total_obtain_marks . "/" . $total_full_marks?></td>
				<?php } if($getExam['type_id'] == 2) { 
					$colspan += 1;
					$percentage_grade = ($total_obtain_marks * 100) / $total_full_marks;
					$grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
					$total_grade_point += $grade['grade_point'];
					?>
					<td valign="middle"><?=$grade['name']?></td>
					<td valign="middle"><?=number_format($grade['grade_point'], 2, '.', '')?></td>
<?php if ($marksheet_template['remark'] == 1) { ?>
					<td valign="middle"><?=$grade['remark']?></td>
<?php } ?>
				<?php } if ($getExam['type_id'] == 3) {
					$colspan += 2;
					$percentage_grade = ($total_obtain_marks * 100) / $total_full_marks;
					$grade = $this->exam_model->get_grade($percentage_grade, $getExam['branch_id']);
					$total_grade_point += $grade['grade_point'];
					?>
					<td valign="middle"><?=$grade['name']?></td>
					<td valign="middle"><?=number_format($grade['grade_point'], 2, '.', '')?></td>
<?php if ($marksheet_template['remark'] == 1) { ?>
					<td valign="middle"><?=$grade['remark']?></td>
<?php } ?>
				<?php } ?>
<?php if ($marksheet_template['subject_position'] == 1) {?>
					<td valign="middle"><?php echo $this->exam_progress_model->getSubjectPosition($student['class_id'], $student['section_id'], [$examID], $sessionID, $row['subject_id'], $total_obtain_marks); ?></td>
<?php } ?>
				</tr>
			<?php } ?>
			<?php if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
				<tr class="text-weight-semibold">
					<td valign="top" >GRAND TOTAL :</td>
					<td valign="top" colspan="<?=$colspan?>"><?=$grand_obtain_marks . '/' . $grand_full_marks; ?>, Average : <?php $percentage = ($grand_obtain_marks * 100) / $grand_full_marks; echo number_format($percentage, 2, '.', '')?>%</td>
				</tr>
			<?php if ($extINTL == true) { ?>
				<tr class="text-weight-semibold">
					<td valign="top" >GRAND TOTAL IN WORDS :</td>
					<td valign="top" colspan="<?=$colspan?>">
						<?php
						$f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
						echo ucwords($f->format($grand_obtain_marks));
						?>
					</td>
				</tr>
			<?php } ?>
			<?php } if ($getExam['type_id'] == 2) { ?>
				<tr class="text-weight-semibold">
					<td valign="top" >GPA :</td>
					<td valign="top" colspan="<?=$colspan+1?>"><?=number_format(($total_grade_point / count($getMarksList)), 2, '.', '')?></td>
				</tr>
			<?php } if ($getExam['type_id'] == 3) { ?>
				<tr class="text-weight-semibold">
					<td valign="top" >GPA :</td>
					<td valign="top" colspan="<?=$colspan?>"><?=number_format(($total_grade_point / count($getMarksList)), 2, '.', '')?></td>
				</tr>
			<?php } if ($getExam['type_id'] == 1 || $getExam['type_id'] == 3) { ?>
<?php if ($marksheet_template['result'] == 1) { ?>
				<tr class="text-weight-semibold">
					<td valign="top" >RESULT :</td>
					<td valign="top" colspan="<?=$colspan?>"><?=$result_status == 0 ? 'Fail' : 'Pass'; ?></td>
				</tr>
			<?php } } ?>
<?php if ($marksheet_template['position'] == 1) { ?>
				<tr class="text-weight-semibold">
					<td valign="top">Position :</td>
					<td valign="top" colspan="<?=$colspan?>"> <?php echo (!empty($rankDetail->rank) ? $rankDetail->rank : translate("not_generated"));?></td>
				</tr>
<?php } ?>
			</tbody>
		</table>
		
		<div class="/2">
<?php
if ($marksheet_template['attendance_percentage'] == 1) {
					$year = explode('-', $schoolYear);
					$getTotalWorking = $this->db->where(array('enroll_id' => $student['enrollID'], 'year(date)' => $year[0]))->get('student_attendance')->num_rows();
					$getTotalAttendance = $this->db->where(array('enroll_id' => $student['enrollID'], 'status' => 'P', 'year(date)' => $year[0]))->get('student_attendance')->num_rows();
					$attenPercentage = empty($getTotalWorking) ? '0.00' : ($getTotalAttendance * 100) / $getTotalWorking;
					?>
				<table class="table table-bordered table-hover table-condensed table-export">
					<tbody>
						<tr>
							<th colspan="2" >Attendance</th>
						</tr>
						<tr>
							<th class="w-[65%]">No. of working days</th>
							<td><?=$getTotalWorking?></td>
						</tr>
						<tr>
							<th class="w-[65%]">No. of days attended</th>
							<td><?=$getTotalAttendance?></td>
						</tr>
						<tr>
							<th class="w-[65%]">Attendance Percentage</th>
							<td><?=number_format($attenPercentage, 2, '.', '') ?>%</td>
						</tr>
					</tbody>
				</table>
	</div>
</section>
<?php } ?>
			</section>
<?php
	if ($marksheet_template['grading_scale'] == 1) {
		if ($getExam['type_id'] != 1) {
			?>
			<div class="/2">
				<table class="table table-bordered table-hover table-condensed table-export">
					<tbody>
						<tr>
							<th colspan="3" >Grading Scale</th>
						</tr>
						<tr>
							<th>Grade</th>
							<th>Min Percentage</th>
							<th>Max Percentage</th>
						</tr>
					<?php 
					$grade = $this->db->where('branch_id', $getExam['branch_id'])->get('grade')->result_array();
					foreach ($grade as $key => $row) {
					?>
						<tr>
							<td class="w-[30%]"><?=$row['name']?></td>
							<td class="w-[30%]"><?=$row['lower_mark']?>%</td>
							<td class="w-[30%]"><?=$row['upper_mark']?>%</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
	</div>
</section>
<?php } } ?>
		</section>
<?php echo $footer_content; ?>
	</div>
</section>
<?php } ?>

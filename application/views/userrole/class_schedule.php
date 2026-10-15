<section class="panel">
	<header class="panel-heading">
		<div class="panel-actions">
			<button onclick="fn_printElem('printResult')" class="btn btn-default btn-circle icon"><i class="fas fa-print"></i></button>
		</div>
		<h4 class="panel-title"><i class="fas fa-user-clock"></i> <?=translate('schedule') . " " . translate('list');?></h4>
	</header>
	<div class="panel-body">
		<?php if (count($timetables) > 0) { ?>
		<div class="table-responsive">
			<div id="printResult">
				<div class="visible-print">
					<center>
						<h4 class="text-dark text-weight-bold"><?=$student['school_name']?></h4>
						<h5 class="text-dark"><?=$student['school_address']?></h5>
						<h5 class="text-weight-bold text-dark">Class Schedule</h5>
						<h5 class="text-dark">
							<?php
							echo translate('class') . ' : ' . $student['class_name'];
							echo ' (' . translate('section') . ' : ' .  $student['section_name'] .  ' )';
							?>
						</h5>
						<hr>
					</center>
				</div>
				<table class="table table-bordered table-hover table-condensed table-export">
					<tbody>
					<?php
					$days = array(
						'sunday',
						'monday',
						'tuesday',
						'wednesday',
						'thursday',
						'friday',
						'saturday'
					);
					$mapfunction = function($s) {return $s->day;};
					$count = array_count_values(array_map($mapfunction, $timetables));
					$max = max($count);
					foreach ($days as $key => $day):
						echo '<tr>';
							echo '<td class="timetable">' . strtoupper($day) . '</td>';
							$row_count = 0;
							foreach ($timetables as $timetable){
								if($timetable->day == $day) {
									$row_count ++;
									echo '<td class="center">';
									if($timetable->break == 0){
										echo '<strong>' . get_type_name_by_id('subject', $timetable->subject_id) . '</strong><br>';
									} else{
										echo '<strong>BREAK</strong><br>';
									}
									echo '<small> (' . date("g:i A", strtotime($timetable->time_start)) . ' - ' . date("g:i A", strtotime($timetable->time_end)) . ')</small><br>';
									if($timetable->break == 0)
										echo '<small>' . translate('teacher') . ' : ' . get_type_name_by_id('staff', $timetable->teacher_id) . '</small>';
									echo ($timetable->class_room != '' ? '<br>' . translate('class_room') . ' : ' . $timetable->class_room : '');
									echo '</td>';
								}
							}
							while($row_count<$max) {
								echo '<td class="center">N/A</td>';
								$row_count++;
							}
						echo '</tr>';
					endforeach;
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php } else { ?>
		<p class="text-center text-muted mb-none"><?=translate('no_information_available')?></p>
		<?php } ?>
	</div>
</section>

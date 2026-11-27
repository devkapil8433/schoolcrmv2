<?php
$animation = isset($global_config['animations']) ? $global_config['animations'] : 'fadeIn';
?>
<?php if (empty($student_id)): ?>
<div class="dashboard-page">
	<div class="row appear-animation" data-appear-animation="<?php echo $animation; ?>" data-appear-animation-delay="100">
		<div class="col-md-12 mb-lg">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fab fa-slideshare"></i> <?php echo translate('my_children'); ?></h4>
				</header>
				<div class="panel-body">
					<div class="row">
						<?php
						$sessionID = get_session_id();
						$this->db->select('s.id,s.first_name,s.last_name,s.photo,s.register_no,s.birthday,e.class_id,e.section_id,e.id as enroll_id,e.roll,e.session_id,c.name as class_name,se.name as section_name');
						$this->db->from('enroll as e');
						$this->db->join('student as s', 'e.student_id = s.id', 'left');
						$this->db->join('class as c', 'e.class_id = c.id', 'left');
						$this->db->join('section as se', 'e.section_id = se.id', 'left');
						$this->db->where('s.parent_id', get_loggedin_user_id());
						$this->db->where('e.session_id', $sessionID);
						$query = $this->db->get();
						if ($query->num_rows() > 0) {
							$students = $query->result();
							foreach ($students as $row):
						?>
						<div class="col-md-6 mb-lg">
							<div class="profile-head">
								<div class="col-md-12 col-lg-4">
									<div class="image-content-center user-pro">
										<div class="preview">
											<img src="<?php echo get_image_url('student', $row->photo); ?>" alt="">
										</div>
									</div>
								</div>
								<div class="col-md-12 col-lg-8">
									<h5><?php echo html_escape($row->first_name . ' ' . $row->last_name); ?></h5>
									<p><?php echo translate('student'); ?> / <?php echo html_escape($row->class_name); ?> (<?php echo html_escape($row->section_name); ?>)</p>
									<ul>
										<li>
											<div class="icon-holder" data-toggle="tooltip" data-original-title="<?php echo translate('roll'); ?>"><i class="fas fa-sort-numeric-down"></i></div> <?php echo html_escape($row->roll); ?>
										</li>
										<li>
											<div class="icon-holder" data-toggle="tooltip" data-original-title="<?php echo translate('register_no'); ?>"><i class="fas fa-id-card"></i></div> <?php echo html_escape($row->register_no); ?>
										</li>
										<?php if (!empty($row->birthday)) { ?>
										<li>
											<div class="icon-holder" data-toggle="tooltip" data-original-title="<?php echo translate('birthday'); ?>"><i class="fas fa-birthday-cake"></i></div> <?php echo _d($row->birthday); ?>
										</li>
										<?php } ?>
									</ul>
									<a href="<?php echo base_url('parents/select_child/' . $row->enroll_id); ?>" class="btn btn-default btn-sm mt-sm">
										<i class="fas fa-tachometer-alt"></i> <?php echo translate('dashboard'); ?>
									</a>
								</div>
							</div>
						</div>
						<?php endforeach; } else { ?>
						<div class="col-md-12">
							<div class="well well-lg text-center mb-none">
								<i class="fas fa-child fa-3x text-muted mb-md"></i>
								<p class="mb-none"><?php echo translate('no_child_was_found'); ?></p>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>
<?php
else :
	$book_issued = $this->dashboard_model->getMonthlyBookIssued($student_id);
	$get_monthly_payment = $this->dashboard_model->getMonthlyPayment($student_id);
	$fees_summary = $this->dashboard_model->annualFeessummaryCharts($school_id, $student_id);
	$get_student_attendance = $this->dashboard_model->getStudentAttendance($student_id);
	$get_monthly_attachments = $this->dashboard_model->get_monthly_attachments($student_id);

	$student = $this->application_model->getStudentDetails($student_id);
	$parent_name = !empty($student['parent_id']) ? get_type_name_by_id('parent', $student['parent_id'], 'name') : '';
	$present_total = array_sum($get_student_attendance['total_present']);
	$absent_total = array_sum($get_student_attendance['total_absent']);
	$late_total = array_sum($get_student_attendance['total_late']);
	$attendance_total = $present_total + $absent_total + $late_total;
	$attendance_rate = $attendance_total > 0 ? round(($present_total / $attendance_total) * 100) : 0;
	$fees_collected_ytd = array_sum($fees_summary['total_paid']);
	$fees_due_ytd = array_sum($fees_summary['total_due']);

	$this->db->from('event');
	$this->db->where('start_date BETWEEN DATE_SUB(CURDATE() ,INTERVAL 1 MONTH) AND CURDATE() AND branch_id = "' . get_loggedin_branch_id() . '"');
	$monthly_events = $this->db->get()->num_rows();
?>

<div class="dashboard-page">
	<div class="row appear-animation" data-appear-animation="<?php echo $animation; ?>" data-appear-animation-delay="100">
		<div class="col-md-12 mb-lg">
			<div class="profile-head">
				<div class="col-md-12 col-lg-4 col-xl-3">
					<div class="image-content-center user-pro">
						<div class="preview">
							<img src="<?php echo get_image_url('student', $student['photo']); ?>" alt="">
						</div>
					</div>
				</div>
				<div class="col-md-12 col-lg-5 col-xl-5">
					<h5><?php echo html_escape($student['first_name'] . ' ' . $student['last_name']); ?></h5>
					<p><?php echo translate('student'); ?> / <?php echo html_escape($student['class_name']); ?> (<?php echo html_escape($student['section_name']); ?>)</p>
					<ul>
						<li>
							<div class="icon-holder" data-toggle="tooltip" data-original-title="<?php echo translate('roll'); ?>"><i class="fas fa-sort-numeric-down"></i></div> <?php echo html_escape($student['roll']); ?>
						</li>
						<li>
							<div class="icon-holder" data-toggle="tooltip" data-original-title="<?php echo translate('register_no'); ?>"><i class="fas fa-id-card"></i></div> <?php echo html_escape($student['register_no']); ?>
						</li>
						<?php if (!empty($parent_name)) { ?>
						<li>
							<div class="icon-holder" data-toggle="tooltip" data-original-title="<?php echo translate('guardian_name'); ?>"><i class="fas fa-users"></i></div> <?php echo html_escape($parent_name); ?>
						</li>
						<?php } ?>
						<?php if (!empty($student['mobileno'])) { ?>
						<li>
							<div class="icon-holder" data-toggle="tooltip" data-original-title="<?php echo translate('mobile_no'); ?>"><i class="fas fa-phone-volume"></i></div> <?php echo html_escape($student['mobileno']); ?>
						</li>
						<?php } ?>
					</ul>
				</div>
				<div class="col-md-12 col-lg-3 col-xl-4">
					<div class="row">
						<div class="col-xs-6 mb-sm">
							<section class="panel pg-fw mb-none">
								<div class="panel-body text-center">
									<h3 class="text-primary mt-none mb-xs"><?php echo $attendance_rate; ?>%</h3>
									<p class="mb-none text-muted text-uppercase"><?php echo translate('attendance'); ?></p>
								</div>
							</section>
						</div>
						<div class="col-xs-6 mb-sm">
							<section class="panel pg-fw mb-none">
								<div class="panel-body text-center">
									<h3 class="text-success mt-none mb-xs"><?php echo currencyFormat($fees_collected_ytd); ?></h3>
									<p class="mb-none text-muted text-uppercase"><?php echo translate('collected'); ?></p>
								</div>
							</section>
						</div>
						<div class="col-xs-12">
							<div class="progress progress-sm progress-half-rounded m-none">
								<div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="<?php echo $attendance_rate; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $attendance_rate; ?>%;">
									<span class="sr-only"><?php echo $attendance_rate; ?>%</span>
								</div>
							</div>
							<p class="text-muted text-center mt-xs mb-none">
								<?php echo translate('remaining'); ?>: <strong class="text-danger"><?php echo currencyFormat($fees_due_ytd); ?></strong>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row appear-animation" data-appear-animation="<?php echo $animation; ?>" data-appear-animation-delay="200">
		<div class="col-md-12">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-th-large"></i> <?php echo translate('quick_links'); ?></h4>
				</header>
				<div class="panel-body p-none">
					<div class="row widget-row-in m-none">
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6">
							<a href="<?php echo base_url('userrole/homework'); ?>" class="text-dark">
								<div class="panel-body text-center">
									<div class="widget-col-in">
										<i class="fas fa-book-reader text-primary"></i>
										<h5 class="mt-sm mb-none"><?php echo translate('homework'); ?></h5>
									</div>
								</div>
							</a>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6">
							<a href="<?php echo base_url('userrole/attendance'); ?>" class="text-dark">
								<div class="panel-body text-center">
									<div class="widget-col-in">
										<i class="fas fa-chart-bar text-success"></i>
										<h5 class="mt-sm mb-none"><?php echo translate('attendance'); ?></h5>
									</div>
								</div>
							</a>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6">
							<a href="<?php echo base_url('userrole/invoice'); ?>" class="text-dark">
								<div class="panel-body text-center">
									<div class="widget-col-in">
										<i class="fas fa-calculator text-warning"></i>
										<h5 class="mt-sm mb-none"><?php echo translate('fees_history'); ?></h5>
									</div>
								</div>
							</a>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6">
							<a href="<?php echo base_url('userrole/exam_schedule'); ?>" class="text-dark">
								<div class="panel-body text-center">
									<div class="widget-col-in">
										<i class="fas fa-calendar-alt text-info"></i>
										<h5 class="mt-sm mb-none"><?php echo translate('exam') . ' ' . translate('schedule'); ?></h5>
									</div>
								</div>
							</a>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6">
							<a href="<?php echo base_url('communication/mailbox/inbox'); ?>" class="text-dark">
								<div class="panel-body text-center">
									<div class="widget-col-in">
										<i class="fas fa-envelope text-primary"></i>
										<h5 class="mt-sm mb-none"><?php echo translate('message'); ?></h5>
									</div>
								</div>
							</a>
						</div>
						<?php if (is_student_loggedin()) { ?>
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-6">
							<a href="<?php echo base_url('profile'); ?>" class="text-dark">
								<div class="panel-body text-center">
									<div class="widget-col-in">
										<i class="far fa-user-circle text-muted"></i>
										<h5 class="mt-sm mb-none"><?php echo translate('profile'); ?></h5>
									</div>
								</div>
							</a>
						</div>
						<?php } ?>
					</div>
				</div>
			</section>
		</div>
	</div>

	<div class="row widget-1 appear-animation" data-appear-animation="<?php echo $animation; ?>" data-appear-animation-delay="300">
		<div class="col-md-12">
			<div class="panel">
				<div class="row widget-row-in">
					<div class="col-lg-3 col-sm-6">
						<div class="panel-body">
							<div class="widget-col-in row">
								<div class="col-md-6 col-sm-6 col-xs-6">
									<i class="fas fa-book-reader"></i>
									<h5><?php echo translate('book_issued'); ?></h5>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-6">
									<h3 class="counter text-right mt-md text-primary"><?php echo $book_issued; ?></h3>
								</div>
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="box-top-line line-color-primary">
										<span class="text-uppercase"><?php echo translate('this_month'); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-sm-6">
						<div class="panel-body">
							<div class="widget-col-in row">
								<div class="col-md-6 col-sm-6 col-xs-6">
									<i class="fas fa-cloud-download-alt"></i>
									<h5><?php echo translate('attachments'); ?></h5>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-6">
									<h3 class="counter text-right mt-md text-success"><?php echo $get_monthly_attachments; ?></h3>
								</div>
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="box-top-line line-color-primary">
										<span class="text-uppercase"><?php echo translate('this_month'); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-sm-6">
						<div class="panel-body">
							<div class="widget-col-in row">
								<div class="col-md-6 col-sm-6 col-xs-6">
									<i class="far fa-money-bill-alt"></i>
									<h5><?php echo translate('fees_payment'); ?></h5>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-6">
									<h3 class="counter text-right mt-md text-warning"><?php echo currencyFormat($get_monthly_payment); ?></h3>
								</div>
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="box-top-line line-color-primary">
										<span class="text-uppercase"><?php echo translate('this_month'); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 col-sm-6">
						<div class="panel-body">
							<div class="widget-col-in row">
								<div class="col-md-6 col-sm-6 col-xs-6">
									<i class="fas fa-bullhorn"></i>
									<h5><?php echo translate('events'); ?></h5>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-6">
									<h3 class="counter text-right mt-md text-info"><?php echo $monthly_events; ?></h3>
								</div>
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="box-top-line line-color-primary">
										<span class="text-uppercase"><?php echo translate('this_month'); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row appear-animation" data-appear-animation="<?php echo $animation; ?>" data-appear-animation-delay="400">
		<div class="col-md-12 col-lg-4 col-xl-3">
			<section class="panel pg-fw">
				<div class="panel-body">
					<h4 class="chart-title mb-md"><i class="fas fa-user-check"></i> <?php echo translate('attendance'); ?></h4>
					<ul class="list-group list-group-bordered mb-md">
						<li class="list-group-item">
							<span class="badge badge-success"><?php echo $present_total; ?></span>
							<?php echo translate('total_present'); ?>
						</li>
						<li class="list-group-item">
							<span class="badge badge-danger"><?php echo $absent_total; ?></span>
							<?php echo translate('total_absent'); ?>
						</li>
						<li class="list-group-item">
							<span class="badge badge-info"><?php echo $late_total; ?></span>
							<?php echo translate('total_late'); ?>
						</li>
					</ul>
					<div class="text-center">
						<h2 class="text-primary mt-none mb-xs"><?php echo $attendance_rate; ?>%</h2>
						<p class="text-muted mb-none text-uppercase"><?php echo translate('attendance'); ?></p>
					</div>
					<div class="round-overlap"><i class="fas fa-chart-pie"></i></div>
				</div>
			</section>
		</div>
		<div class="col-md-12 col-lg-8 col-xl-9">
			<section class="panel">
				<div class="panel-body">
					<h4 class="chart-title mb-md"><i class="fas fa-chart-line"></i> <?php echo translate('my_annual_fee_summary'); ?></h4>
					<p class="mb-sm">
						<?php echo translate('collected'); ?>: <strong class="text-success"><?php echo currencyFormat($fees_collected_ytd); ?></strong>
						&nbsp;&middot;&nbsp;
						<?php echo translate('remaining'); ?>: <strong class="text-danger"><?php echo currencyFormat($fees_due_ytd); ?></strong>
					</p>
					<div class="pe-chart">
						<canvas id="fees_graph" style="height: 322px;"></canvas>
					</div>
				</div>
			</section>
		</div>
	</div>

	<div class="row appear-animation" data-appear-animation="<?php echo $animation; ?>" data-appear-animation-delay="500">
		<div class="col-md-12">
			<section class="panel">
				<div class="panel-body">
					<h4 class="chart-title mb-md"><i class="fas fa-chart-bar"></i> <?php echo translate('my_annual_attendance_overview'); ?></h4>
					<div class="pe-chart">
						<canvas id="attendance_overview" style="height: 322px;"></canvas>
					</div>
				</div>
			</section>
		</div>
	</div>

	<div class="row appear-animation" data-appear-animation="<?php echo $animation; ?>" data-appear-animation-delay="600">
		<div class="col-md-12">
			<section class="panel">
				<header class="panel-heading">
					<div class="panel-actions">
						<a href="<?php echo base_url('userrole/event'); ?>" class="btn btn-default btn-sm"><i class="fas fa-external-link-alt"></i> <?php echo translate('view'); ?></a>
					</div>
					<h4 class="panel-title"><i class="fas fa-calendar"></i> <?php echo translate('events'); ?> &middot; <?php echo translate('calendar'); ?></h4>
				</header>
				<div class="panel-body">
					<div id="event_calendar"></div>
				</div>
			</section>
		</div>
	</div>
</div>

<section class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<div class="panel-actions">
				<button id="print" class="btn btn-default btn-sm"><i class="fas fa-print"></i></button>
			</div>
			<h4 class="panel-title"><i class="fas fa-info-circle"></i> <?php echo translate('event_details'); ?></h4>
		</header>
		<div class="panel-body">
			<div id="printResult" class="table-responsive">
				<table class="table table-bordered table-condensed" id="ev_table"></table>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12">
					<button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
				</div>
			</div>
		</footer>
	</section>
</section>

<script type="application/javascript">
$(document).ready(function() {
	"use strict";

	if (typeof Chart !== 'undefined') {
		var feesCanvas = document.getElementById('fees_graph');
		if (feesCanvas) {
			new Chart(feesCanvas.getContext('2d'), {
				type: 'line',
				data: {
					labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
					datasets: [
						{ label: '<?php echo translate("total"); ?>', data: <?php echo json_encode($fees_summary['total_fee']); ?>, borderColor: '#0088cc', backgroundColor: 'rgba(0, 136, 204, 0.1)', borderWidth: 2, fill: true, lineTension: 0.3 },
						{ label: '<?php echo translate("collected"); ?>', data: <?php echo json_encode($fees_summary['total_paid']); ?>, borderColor: '#47a447', backgroundColor: 'rgba(71, 164, 71, 0.1)', borderWidth: 2, fill: true, lineTension: 0.3 },
						{ label: '<?php echo translate("remaining"); ?>', data: <?php echo json_encode($fees_summary['total_due']); ?>, borderColor: '#d2322d', backgroundColor: 'rgba(210, 50, 45, 0.1)', borderWidth: 2, fill: true, lineTension: 0.3 }
					]
				},
				options: { responsive: true, maintainAspectRatio: false, legend: { position: 'bottom' } }
			});
		}

		var attendanceCanvas = document.getElementById('attendance_overview');
		if (attendanceCanvas) {
			new Chart(attendanceCanvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
					datasets: [
						{ label: '<?php echo translate("total_present"); ?>', data: <?php echo json_encode($get_student_attendance['total_present']); ?>, backgroundColor: '#47a447' },
						{ label: '<?php echo translate("total_absent"); ?>', data: <?php echo json_encode($get_student_attendance['total_absent']); ?>, backgroundColor: '#d2322d' },
						{ label: '<?php echo translate("total_late"); ?>', data: <?php echo json_encode($get_student_attendance['total_late']); ?>, backgroundColor: '#5bc0de' }
					]
				},
				options: { responsive: true, maintainAspectRatio: false, legend: { position: 'bottom' }, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } }
			});
		}
	}

	if ($('#event_calendar').length && $.fn.fullCalendar) {
		$('#event_calendar').fullCalendar({
			header: { left: 'prev,next today', center: 'title', right: 'month,agendaWeek,agendaDay,listWeek' },
			firstDay: 1,
			height: 620,
			droppable: false,
			editable: false,
			events: { url: "<?php echo base_url('event/get_events_list'); ?>" },
			buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day', list: 'List' },
			eventRender: function(event, element) {
				$(element).on("click", function() { dashboardViewEvent(event.id); });
				if (event.icon) element.find(".fc-title").prepend("<i class='fas fa-" + event.icon + "'></i> ");
			}
		});
	}
});

function dashboardViewEvent(id) {
	$.ajax({
		url: "<?php echo base_url('event/getDetails'); ?>",
		type: 'POST',
		data: { event_id: id },
		success: function(data) {
			$('#ev_table').html(data);
			mfp_modal('#modal');
		}
	});
}
</script>
<?php endif; ?>

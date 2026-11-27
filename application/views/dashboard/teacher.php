<?php
$dashboard = isset($teacher_dashboard) && is_array($teacher_dashboard) ? $teacher_dashboard : array();
$teacherName = isset($teacher_name) ? trim((string) $teacher_name) : '';
$todayLabel = date('l, d M Y');
$pendingLeaves = isset($dashboard['pending_leaves']) ? (int) $dashboard['pending_leaves'] : 0;
$complaintStats = isset($dashboard['complaint_stats']) && is_array($dashboard['complaint_stats']) ? $dashboard['complaint_stats'] : array();
$studentCount = isset($dashboard['student_count']) ? (int) $dashboard['student_count'] : 0;
$allocatedStudents = isset($dashboard['allocated_students']) && is_array($dashboard['allocated_students']) ? $dashboard['allocated_students'] : array();
$coordinatorTasks = isset($dashboard['coordinator_tasks']) ? (int) $dashboard['coordinator_tasks'] : 0;
$nonPerformerTracker = isset($dashboard['non_performer_tracker']) && is_array($dashboard['non_performer_tracker']) ? $dashboard['non_performer_tracker'] : array();
$nonPerformerStudentCount = isset($dashboard['non_performer_student_count']) ? (int) $dashboard['non_performer_student_count'] : 0;
$nonPerformerRangeStart = isset($dashboard['non_performer_range_start']) ? (string) $dashboard['non_performer_range_start'] : date('Y-m-d', strtotime('-29 days'));
$upcomingClasses = isset($dashboard['upcoming_classes']) && is_array($dashboard['upcoming_classes']) ? $dashboard['upcoming_classes'] : array();
$upcomingExams = isset($dashboard['upcoming_exams']) && is_array($dashboard['upcoming_exams']) ? $dashboard['upcoming_exams'] : array();
$syllabusSubjects = isset($dashboard['syllabus_subjects']) && is_array($dashboard['syllabus_subjects']) ? $dashboard['syllabus_subjects'] : array();
$schoolCalendar = isset($dashboard['school_calendar']) && is_array($dashboard['school_calendar']) ? $dashboard['school_calendar'] : array();
$schoolYear = isset($dashboard['school_year']) ? (string) $dashboard['school_year'] : '';
$teacherRank = isset($dashboard['teacher_rank']) && is_array($dashboard['teacher_rank']) ? $dashboard['teacher_rank'] : null;
$workReportAvailable = !empty($dashboard['work_report_available']);
$syllabusAvailable = !empty($dashboard['syllabus_available']);
$complaintModuleEnabled = !empty($complaint_module_enabled);
$complaintTypes = isset($complaint_types) && is_array($complaint_types) ? $complaint_types : array();
$complaintRecipients = isset($complaint_recipients) && is_array($complaint_recipients) ? $complaint_recipients : array();
$canViewLeaveRequests = get_permission('leave_request', 'is_view');
?>
<style>
.teacher-dashboard .td-welcome { padding: 22px 24px; margin-bottom: 18px; border: 0; border-radius: 12px; color: #fff; background: linear-gradient(120deg, #123f52 0%, #167d87 100%); box-shadow: 0 8px 24px rgba(18, 63, 82, .16); }
.teacher-dashboard .td-welcome h3 { margin: 0 0 5px; color: #fff; font-weight: 700; }
.teacher-dashboard .td-welcome p { margin: 0; opacity: .84; }
.teacher-dashboard .td-card { min-height: 132px; border: 1px solid #e8edf1; border-radius: 10px; background: #fff; box-shadow: 0 3px 12px rgba(32, 54, 72, .05); margin-bottom: 18px; }
.teacher-dashboard .td-card a { color: inherit; text-decoration: none; display: block; min-height: 132px; padding: 19px; }
.teacher-dashboard .td-card-inner { min-height: 132px; padding: 19px; }
.teacher-dashboard .td-card-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.teacher-dashboard .td-card-label { color: #667580; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
.teacher-dashboard .td-card-value { margin-top: 12px; color: #183849; font-size: 28px; font-weight: 700; line-height: 1.15; }
.teacher-dashboard .td-card-note { margin-top: 6px; color: #87949d; font-size: 12px; }
.teacher-dashboard .td-card-icon { display: inline-flex; width: 42px; height: 42px; align-items: center; justify-content: center; border-radius: 12px; background: #edf7f7; color: #167d87; font-size: 17px; }
.teacher-dashboard .td-section { border-radius: 10px; overflow: hidden; }
.teacher-dashboard .td-section .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.teacher-dashboard .td-section .panel-title { font-weight: 700; }
.teacher-dashboard .td-period { padding: 12px 0; border-bottom: 1px solid #edf0f2; }
.teacher-dashboard .td-period:last-child { border-bottom: 0; }
.teacher-dashboard .td-period-title { color: #223f4c; font-weight: 700; }
.teacher-dashboard .td-period-meta { color: #82909a; font-size: 12px; margin-top: 3px; }
.teacher-dashboard .td-status { text-align: right; white-space: nowrap; }
.teacher-dashboard .td-status strong { color: #b94a48; }
.teacher-dashboard .td-status small { display: block; color: #87949d; margin-top: 3px; }
.teacher-dashboard .td-empty { padding: 24px 12px; text-align: center; color: #87949d; }
.teacher-dashboard .td-empty i { display: block; margin-bottom: 9px; font-size: 24px; color: #b8c5cc; }
.teacher-dashboard .td-list-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 0; border-bottom: 1px solid #edf0f2; }
.teacher-dashboard .td-list-row:last-child { border-bottom: 0; }
.teacher-dashboard .td-list-title { color: #263f4c; font-weight: 600; }
.teacher-dashboard .td-list-meta { color: #87949d; font-size: 12px; margin-top: 4px; }
.teacher-dashboard .td-section { margin-bottom: 24px; }
.teacher-dashboard .td-section .panel-body { padding: 22px; }
.teacher-dashboard .td-stat-strip { display: flex; flex-wrap: wrap; gap: 12px; margin: 0 0 18px; }
.teacher-dashboard .td-stat { flex: 1 1 130px; padding: 14px 16px; border: 1px solid #edf0f2; border-radius: 9px; background: #fbfcfd; }
.teacher-dashboard .td-stat strong { display: block; color: #183849; font-size: 23px; }
.teacher-dashboard .td-stat span { display: block; margin-top: 3px; color: #7d8b94; font-size: 12px; }
.teacher-dashboard .td-data-scroll { max-height: 520px; overflow: auto; }
.teacher-dashboard .td-table th { position: sticky; top: 0; z-index: 1; background: #f7f9fa; }
.teacher-dashboard .td-work-detail { margin-top: 12px; padding: 12px 14px; border: 1px solid #edf0f2; border-radius: 8px; background: #fbfcfd; }
.teacher-dashboard .td-work-detail summary { color: #167d87; cursor: pointer; font-weight: 600; }
.teacher-dashboard .td-work-list { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 0; padding: 0; list-style: none; }
.teacher-dashboard .td-work-list li { padding: 6px 10px; border: 1px solid #f0d7d9; border-radius: 999px; background: #fff7f7; color: #934e54; font-size: 12px; }
.teacher-dashboard .td-syllabus-card { padding: 18px; margin-bottom: 14px; border: 1px solid #e8edf1; border-radius: 10px; background: #fff; }
.teacher-dashboard .td-syllabus-title { color: #203d4b; font-size: 15px; font-weight: 700; }
.teacher-dashboard .td-progress { height: 8px; margin: 10px 0 5px; border-radius: 8px; background: #edf1f3; }
.teacher-dashboard .td-progress > span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #167d87, #49b7a5); }
.teacher-dashboard .td-lesson-list { margin: 12px 0 0; padding-left: 20px; color: #61717b; }
.teacher-dashboard .td-lesson-list li { padding: 4px 0; }
.teacher-dashboard .td-complaint-note { max-width: 500px; overflow-wrap: anywhere; white-space: pre-line; color: #64737c; font-size: 12px; }
.teacher-dashboard .td-calendar { min-height: 560px; }
.teacher-dashboard .td-subheading { margin: 0 0 14px; color: #244351; font-size: 15px; font-weight: 700; }
.teacher-dashboard .td-form-actions { margin-top: 16px; }
@media (max-width: 767px) {
    .teacher-dashboard .td-welcome { padding: 18px; }
    .teacher-dashboard .td-status { white-space: normal; }
    .teacher-dashboard .td-section .panel-heading { align-items: flex-start; }
    .teacher-dashboard .td-calendar { min-height: 430px; }
}
</style>

<div class="dashboard-page teacher-dashboard">
    <div class="td-welcome">
        <h3><?php echo html_escape(translate('welcome_to') . ' ' . ($teacherName !== '' ? $teacherName : translate('teacher'))); ?></h3>
        <p><?php echo html_escape($todayLabel); ?> <span class="hidden-xs">· Your classes, work updates and school activities at a glance.</span></p>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-6">
            <div class="td-card">
                <a href="#allocated-students" aria-label="View allocated student list">
                    <div class="td-card-top">
                        <span class="td-card-label">Allocated students</span>
                        <span class="td-card-icon"><i class="fas fa-user-graduate"></i></span>
                    </div>
                    <div class="td-card-value"><?php echo $studentCount; ?></div>
                    <div class="td-card-note">Students in your assigned classes and subjects · Click to view list</div>
                </a>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="td-card">
                <?php if ($canViewLeaveRequests): ?><a href="<?php echo base_url('leave/request'); ?>"><?php else: ?><div class="td-card-inner"><?php endif; ?>
                    <div class="td-card-top">
                        <span class="td-card-label">Leave requests</span>
                        <span class="td-card-icon"><i class="fas fa-calendar-minus"></i></span>
                    </div>
                    <div class="td-card-value"><?php echo $pendingLeaves; ?></div>
                    <div class="td-card-note">Pending approval</div>
                <?php if ($canViewLeaveRequests): ?></a><?php else: ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="td-card">
                <div class="td-card-inner">
                    <div class="td-card-top">
                        <span class="td-card-label">Salary this month</span>
                        <span class="td-card-icon"><i class="fas fa-wallet"></i></span>
                    </div>
                    <div class="td-card-value"><?php echo isset($dashboard['salary']) && $dashboard['salary'] !== null ? currencyFormat($dashboard['salary']) : '—'; ?></div>
                    <div class="td-card-note"><?php echo isset($dashboard['salary']) && $dashboard['salary'] !== null ? html_escape(date('F Y')) : 'Payslip not processed yet'; ?></div>
                </div>
            </div>
        </div>
        <?php if (!empty($dashboard['coordinator_assigned'])): ?>
        <div class="col-md-4 col-sm-6">
            <div class="td-card">
                <a href="<?php echo base_url('digital_diary/coordinator_tasks'); ?>">
                    <div class="td-card-top">
                        <span class="td-card-label">Coordinator tasks</span>
                        <span class="td-card-icon"><i class="fas fa-clipboard-check"></i></span>
                    </div>
                    <div class="td-card-value"><?php echo $coordinatorTasks; ?></div>
                    <div class="td-card-note">Unread tasks for your assigned classes</div>
                </a>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($complaintModuleEnabled): ?>
        <div class="col-md-4 col-sm-6">
            <div class="td-card">
                <a href="#teacher-complaints">
                    <div class="td-card-top">
                        <span class="td-card-label">Assigned complaints</span>
                        <span class="td-card-icon"><i class="fas fa-comment-dots"></i></span>
                    </div>
                    <div class="td-card-value"><?php echo isset($complaintStats['assigned_pending']) ? (int) $complaintStats['assigned_pending'] : 0; ?></div>
                    <div class="td-card-note"><?php echo isset($complaintStats['assigned_total']) ? (int) $complaintStats['assigned_total'] : 0; ?> total · <?php echo isset($complaintStats['assigned_resolved']) ? (int) $complaintStats['assigned_resolved'] : 0; ?> resolved</div>
                </a>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-md-4 col-sm-6">
            <div class="td-card">
                <div class="td-card-inner">
                    <div class="td-card-top">
                        <span class="td-card-label">Teacher work rank</span>
                        <span class="td-card-icon"><i class="fas fa-trophy"></i></span>
                    </div>
                    <div class="td-card-value"><?php echo $teacherRank ? '#' . (int) $teacherRank['rank'] : '—'; ?></div>
                    <div class="td-card-note">
                        <?php if ($teacherRank): ?>
                            <?php echo (int) $teacherRank['score']; ?>% work completion · among <?php echo (int) $teacherRank['total']; ?> teachers today
                        <?php else: ?>
                            Today's work report has not been recorded yet
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <section class="panel td-section">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-chalkboard-teacher text-primary"></i> Today's upcoming classes</h4>
                    <?php if (get_permission('teacher_timetable', 'is_view')): ?>
                    <a class="btn btn-default btn-xs" href="<?php echo base_url('timetable/teacherview'); ?>">Full timetable</a>
                    <?php endif; ?>
                </header>
                <div class="panel-body">
                    <?php if (empty($upcomingClasses)): ?>
                    <div class="td-empty"><i class="far fa-calendar-check"></i>No more classes scheduled for today.</div>
                    <?php else: ?>
                        <?php foreach ($upcomingClasses as $period): ?>
                        <div class="td-list-row">
                            <div>
                                <div class="td-list-title"><?php echo html_escape($period['subject_name'] . ' · ' . $period['class_name'] . ' (' . $period['section_name'] . ')'); ?></div>
                                <div class="td-list-meta"><?php echo html_escape(date('g:i A', strtotime($period['time_start'])) . ' – ' . date('g:i A', strtotime($period['time_end']))); ?><?php if (!empty($period['class_room'])): ?> · Room <?php echo html_escape($period['class_room']); ?><?php endif; ?></div>
                            </div>
                            <span class="label label-primary">Today</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <div class="col-md-6">
            <section class="panel td-section">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-file-alt text-warning"></i> Upcoming exams</h4>
                    <?php if (get_permission('exam', 'is_view')): ?>
                    <a class="btn btn-default btn-xs" href="<?php echo base_url('exam'); ?>">View exams</a>
                    <?php endif; ?>
                </header>
                <div class="panel-body">
                    <?php if (empty($upcomingExams)): ?>
                    <div class="td-empty"><i class="far fa-calendar"></i>No upcoming exams found for your assigned subjects.</div>
                    <?php else: ?>
                        <?php foreach ($upcomingExams as $exam): ?>
                        <div class="td-list-row">
                            <div>
                                <div class="td-list-title"><?php echo html_escape($exam['exam_name'] . ' · ' . $exam['subject_name']); ?></div>
                                <div class="td-list-meta"><?php echo html_escape($exam['class_name'] . ' (' . $exam['section_name'] . ')'); ?> · <?php echo html_escape(_d($exam['exam_date'])); ?><?php if (!empty($exam['time_start'])): ?> · <?php echo html_escape(date('g:i A', strtotime($exam['time_start']))); ?><?php endif; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <section class="panel td-section" id="teacher-non-performer-tracker">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-user-clock text-danger"></i> Non-performer tracker · assigned classes and subjects</h4>
            <a class="btn btn-primary btn-xs" href="<?php echo base_url('digital_diary/student_work_report_entry'); ?>">Open work report</a>
        </header>
        <div class="panel-body">
            <p class="text-muted">
                Track students with incomplete work from <?php echo html_escape(_d($nonPerformerRangeStart)); ?> to <?php echo html_escape(_d(date('Y-m-d'))); ?>.
                Review the missed work areas and open the work report to add today's guidance or follow-up.
            </p>
            <?php if (!$workReportAvailable): ?>
            <div class="alert alert-warning mb-none">
                Student work reporting is not set up yet. Please ask the administrator to configure the Student Work Report module.
            </div>
            <?php elseif (empty($nonPerformerTracker)): ?>
            <div class="td-empty"><i class="fas fa-user-check"></i>No incomplete work recorded for your assigned classes and subjects in the last 30 days.</div>
            <?php else: ?>
            <?php
                $nonPerformerScopes = array();
                $nonPerformerSubjects = array();
                foreach ($nonPerformerTracker as $trackerRow) {
                    $scopeKey = (int) $trackerRow['class_id'] . ':' . (int) $trackerRow['section_id'];
                    $nonPerformerScopes[$scopeKey] = $trackerRow['class_name'] . ' (' . $trackerRow['section_name'] . ')';
                    $nonPerformerSubjects[(int) $trackerRow['subject_id']] = $trackerRow['subject_name'];
                }
            ?>
            <div class="td-stat-strip">
                <div class="td-stat">
                    <strong><?php echo $nonPerformerStudentCount; ?></strong>
                    <span>students needing follow-up</span>
                </div>
                <div class="td-stat">
                    <strong><?php echo count($nonPerformerTracker); ?></strong>
                    <span>student-subject records</span>
                </div>
            </div>
            <div class="row mb-md">
                <div class="col-md-4 col-sm-6">
                    <label class="sr-only" for="td_non_performer_scope">Filter by class and section</label>
                    <select class="form-control" id="td_non_performer_scope">
                        <option value="">All assigned classes and sections</option>
                        <?php foreach ($nonPerformerScopes as $scopeKey => $scopeLabel): ?>
                        <option value="<?php echo html_escape($scopeKey); ?>"><?php echo html_escape($scopeLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-sm-6">
                    <label class="sr-only" for="td_non_performer_subject">Filter by subject</label>
                    <select class="form-control" id="td_non_performer_subject">
                        <option value="">All assigned subjects</option>
                        <?php foreach ($nonPerformerSubjects as $subjectID => $subjectName): ?>
                        <option value="<?php echo (int) $subjectID; ?>"><?php echo html_escape($subjectName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-sm-12 text-muted" id="td_non_performer_matches" aria-live="polite">
                    Showing <?php echo count($nonPerformerTracker); ?> student-subject records
                </div>
            </div>
            <div class="table-responsive td-data-scroll">
                <table class="table table-hover td-table mb-none">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class / Section</th>
                            <th>Subject</th>
                            <th>Incomplete reports</th>
                            <th>Work areas to support</th>
                            <th>Last recorded</th>
                            <th>Follow-up</th>
                        </tr>
                    </thead>
                    <tbody id="td_non_performer_rows">
                        <?php foreach ($nonPerformerTracker as $trackerRow): ?>
                        <tr
                            data-class-section="<?php echo (int) $trackerRow['class_id'] . ':' . (int) $trackerRow['section_id']; ?>"
                            data-subject-id="<?php echo (int) $trackerRow['subject_id']; ?>">
                            <td>
                                <?php echo html_escape(trim($trackerRow['first_name'] . ' ' . $trackerRow['last_name'])); ?>
                                <small class="text-muted bs-block">
                                    <?php if (!empty($trackerRow['roll'])): ?>Roll <?php echo html_escape($trackerRow['roll']); ?><?php endif; ?>
                                    <?php if (!empty($trackerRow['register_no'])): ?> · <?php echo html_escape($trackerRow['register_no']); ?><?php endif; ?>
                                </small>
                            </td>
                            <td><?php echo html_escape($trackerRow['class_name'] . ' / ' . $trackerRow['section_name']); ?></td>
                            <td><?php echo html_escape($trackerRow['subject_name']); ?></td>
                            <td><span class="label label-danger"><?php echo (int) $trackerRow['incomplete_days']; ?></span></td>
                            <td>
                                <?php if ((int) $trackerRow['fair_copy_missed'] > 0): ?>
                                <span class="label label-warning">Fair copy · <?php echo (int) $trackerRow['fair_copy_missed']; ?></span>
                                <?php endif; ?>
                                <?php if ((int) $trackerRow['writing_work_missed'] > 0): ?>
                                <span class="label label-warning">Writing · <?php echo (int) $trackerRow['writing_work_missed']; ?></span>
                                <?php endif; ?>
                                <?php if ((int) $trackerRow['learning_work_missed'] > 0): ?>
                                <span class="label label-warning">Learning · <?php echo (int) $trackerRow['learning_work_missed']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo html_escape(_d($trackerRow['last_incomplete_date'])); ?></td>
                            <td><a class="btn btn-default btn-xs" href="<?php echo base_url('digital_diary/student_work_report_entry'); ?>">Review work</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="td-empty" id="td_non_performer_empty" style="display:none;">
                <i class="fas fa-filter"></i>No students match these filters.
            </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="row">
        <div class="col-md-12">
            <section class="panel td-section" id="allocated-students">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-users text-primary"></i> My allocated students <span class="text-muted">(<?php echo $studentCount; ?>)</span></h4>
                </header>
                <div class="panel-body">
                    <?php if (empty($allocatedStudents)): ?>
                    <div class="td-empty"><i class="fas fa-user-graduate"></i>No active students are allocated to your classes or subjects.</div>
                    <?php else: ?>
                    <div class="row mb-md">
                        <div class="col-md-4 col-sm-6">
                            <input type="search" class="form-control" id="td_student_search" placeholder="Search name, class, roll or register no">
                        </div>
                    </div>
                    <div class="table-responsive td-data-scroll">
                        <table class="table table-hover td-table mb-none">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Register no.</th>
                                    <th>Roll no.</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                </tr>
                            </thead>
                            <tbody id="td_student_rows">
                                <?php foreach ($allocatedStudents as $student): ?>
                                <tr>
                                    <td><?php echo html_escape(trim($student['first_name'] . ' ' . $student['last_name'])); ?></td>
                                    <td><?php echo html_escape($student['register_no']); ?></td>
                                    <td><?php echo html_escape($student['roll']); ?></td>
                                    <td><?php echo html_escape($student['class_name']); ?></td>
                                    <td><?php echo html_escape($student['section_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <section class="panel td-section" id="teacher-syllabus">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-book text-success"></i> My assigned subject syllabus</h4>
            <span class="text-muted"><?php echo count($syllabusSubjects); ?> assigned subjects</span>
        </header>
        <div class="panel-body">
            <?php if (!$syllabusAvailable): ?>
            <div class="alert alert-warning mb-none">The syllabus module is not set up yet. Please ask the administrator to configure it.</div>
            <?php elseif (empty($syllabusSubjects)): ?>
            <div class="td-empty"><i class="fas fa-book"></i>No syllabus subjects are assigned to you yet.</div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($syllabusSubjects as $subject): ?>
                <div class="col-md-6">
                    <div class="td-syllabus-card">
                        <div class="td-syllabus-title"><?php echo html_escape($subject['subject_name']); ?></div>
                        <div class="td-list-meta"><?php echo html_escape($subject['class_name'] . ' · ' . $subject['section_name']); ?></div>
                        <?php if ($subject['percentage'] !== null): ?>
                        <div class="td-progress"><span style="width: <?php echo (int) $subject['percentage']; ?>%"></span></div>
                        <div class="td-list-meta"><?php echo (int) $subject['completed_count']; ?> of <?php echo (int) $subject['total_count']; ?> lessons completed · <?php echo (int) $subject['percentage']; ?>%</div>
                        <?php else: ?>
                        <div class="td-progress"><span style="width: 0%"></span></div>
                        <div class="td-list-meta">No lessons have been added to this subject yet.</div>
                        <?php endif; ?>
                        <?php if (!empty($subject['lessons'])): ?>
                        <details class="td-work-detail">
                            <summary>View syllabus lessons (<?php echo count($subject['lessons']); ?>)</summary>
                            <ol class="td-lesson-list">
                                <?php foreach ($subject['lessons'] as $lesson): ?>
                                <li>
                                    <?php if (!empty($lesson['lesson_no'])): ?>Lesson <?php echo html_escape($lesson['lesson_no']); ?> · <?php endif; ?>
                                    <?php echo html_escape($lesson['lesson_name']); ?>
                                    <span class="label <?php echo !empty($lesson['is_completed']) ? 'label-success' : 'label-default'; ?>">
                                        <?php echo !empty($lesson['is_completed']) ? 'Completed' : 'Pending'; ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ol>
                        </details>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($complaintModuleEnabled): ?>
    <section class="panel td-section" id="teacher-complaints">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-comment-medical text-danger"></i> Complaints & support</h4>
        </header>
        <div class="panel-body">
            <div class="td-stat-strip">
                <div class="td-stat">
                    <strong><?php echo (int) $complaintStats['assigned_total']; ?></strong>
                    <span>Assigned to you</span>
                </div>
                <div class="td-stat">
                    <strong><?php echo (int) $complaintStats['assigned_pending']; ?></strong>
                    <span>Pending action</span>
                </div>
                <div class="td-stat">
                    <strong><?php echo (int) $complaintStats['assigned_resolved']; ?></strong>
                    <span>Resolved</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <h5 class="td-subheading">Complaints assigned to you</h5>
                    <?php if (empty($complaintStats['assigned_recent'])): ?>
                    <div class="td-empty"><i class="far fa-check-circle"></i>No complaints have been assigned to you.</div>
                    <?php else: ?>
                        <?php foreach ($complaintStats['assigned_recent'] as $complaint): ?>
                        <?php $resolved = !empty($complaint['date_of_solution']) && $complaint['date_of_solution'] !== '0000-00-00'; ?>
                        <div class="td-list-row">
                            <div>
                                <div class="td-list-title"><?php echo html_escape($complaint['type_name']); ?> · <?php echo html_escape($complaint['name']); ?></div>
                                <div class="td-list-meta"><?php echo html_escape(_d($complaint['date'])); ?><?php if ($resolved): ?> · Resolved <?php echo html_escape(_d($complaint['date_of_solution'])); ?><?php endif; ?></div>
                                <?php if (!empty($complaint['note'])): ?><div class="td-complaint-note mt-xs"><?php echo html_escape($complaint['note']); ?></div><?php endif; ?>
                            </div>
                            <span class="label <?php echo $resolved ? 'label-success' : 'label-warning'; ?>"><?php echo $resolved ? 'Resolved' : 'Pending'; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-5">
                    <h5 class="td-subheading">Submit your issue</h5>
                    <?php if (count($complaintTypes) > 1 && count($complaintRecipients) > 1): ?>
                    <?php echo form_open('dashboard/submit_teacher_complaint', array('class' => 'form')); ?>
                        <div class="form-group">
                            <label for="teacher_complaint_type">Complaint type</label>
                            <?php echo form_dropdown('type_id', $complaintTypes, set_value('type_id'), "class='form-control' id='teacher_complaint_type' required"); ?>
                        </div>
                        <div class="form-group">
                            <label for="teacher_complaint_recipient">Send to</label>
                            <?php echo form_dropdown('staff_id', $complaintRecipients, set_value('staff_id'), "class='form-control' id='teacher_complaint_recipient' required"); ?>
                        </div>
                        <div class="form-group">
                            <label for="teacher_complaint_subject">Subject</label>
                            <input type="text" name="subject" id="teacher_complaint_subject" class="form-control" maxlength="120" value="<?php echo html_escape(set_value('subject')); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="teacher_complaint_note">Describe your issue</label>
                            <textarea name="note" id="teacher_complaint_note" class="form-control" rows="4" maxlength="1000" required><?php echo html_escape(set_value('note')); ?></textarea>
                        </div>
                        <div class="td-form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit complaint</button>
                        </div>
                    <?php echo form_close(); ?>
                    <?php else: ?>
                    <div class="alert alert-info mb-none">Complaint submission needs an active complaint type and a school staff recipient. Please contact the administrator.</div>
                    <?php endif; ?>
                </div>
            </div>

            <hr>
            <h5 class="td-subheading">Issues submitted by you</h5>
            <div class="td-stat-strip">
                <div class="td-stat">
                    <strong><?php echo (int) $complaintStats['submitted_total']; ?></strong>
                    <span>Submitted</span>
                </div>
                <div class="td-stat">
                    <strong><?php echo (int) $complaintStats['submitted_pending']; ?></strong>
                    <span>Awaiting resolution</span>
                </div>
                <div class="td-stat">
                    <strong><?php echo (int) $complaintStats['submitted_resolved']; ?></strong>
                    <span>Resolved</span>
                </div>
            </div>
            <?php if (empty($complaintStats['submitted_recent'])): ?>
            <div class="td-empty"><i class="far fa-comment"></i>You have not submitted any issues yet.</div>
            <?php else: ?>
                <?php foreach ($complaintStats['submitted_recent'] as $complaint): ?>
                <?php $resolved = !empty($complaint['date_of_solution']) && $complaint['date_of_solution'] !== '0000-00-00'; ?>
                <div class="td-list-row">
                    <div>
                        <div class="td-list-title"><?php echo html_escape($complaint['type_name']); ?> · <?php echo html_escape(_d($complaint['date'])); ?></div>
                        <div class="td-complaint-note"><?php echo html_escape($complaint['note']); ?></div>
                        <?php if ($resolved): ?><div class="td-list-meta">Resolved <?php echo html_escape(_d($complaint['date_of_solution'])); ?></div><?php endif; ?>
                    </div>
                    <span class="label <?php echo $resolved ? 'label-success' : 'label-warning'; ?>"><?php echo $resolved ? 'Resolved' : 'Pending'; ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="panel td-section">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="far fa-calendar-alt text-primary"></i> School annual calendar<?php if ($schoolYear !== ''): ?> · <?php echo html_escape($schoolYear); ?><?php endif; ?></h4>
            <span class="text-muted"><?php echo count($schoolCalendar); ?> school events & holidays</span>
        </header>
        <div class="panel-body">
            <p class="text-muted">Browse school events and holidays for the selected academic year. Use the calendar arrows to move between months.</p>
            <div id="teacher_school_calendar" class="td-calendar"></div>
            <?php if (empty($schoolCalendar)): ?>
            <div class="td-empty"><i class="far fa-calendar"></i>No school events or holidays are scheduled for this academic year.</div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
(function ($) {
    $(function () {
        var $calendar = $('#teacher_school_calendar');
        if ($calendar.length && $.fn.fullCalendar) {
            $calendar.fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,listWeek'
                },
                defaultView: 'month',
                firstDay: 1,
                height: 'auto',
                editable: false,
                droppable: false,
                eventLimit: true,
                events: <?php echo json_encode($schoolCalendar, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
            });
        }

        var $nonPerformerRows = $('#td_non_performer_rows tr');
        if ($nonPerformerRows.length) {
            var applyNonPerformerFilters = function () {
                var scope = $('#td_non_performer_scope').val();
                var subject = $('#td_non_performer_subject').val();
                var visibleCount = 0;
                $nonPerformerRows.each(function () {
                    var $row = $(this);
                    var matches = (!scope || $row.attr('data-class-section') === scope)
                        && (!subject || $row.attr('data-subject-id') === subject);
                    $row.toggle(matches);
                    if (matches) {
                        visibleCount++;
                    }
                });
                $('#td_non_performer_matches').text('Showing ' + visibleCount + ' of ' + $nonPerformerRows.length + ' student-subject records');
                $('#td_non_performer_empty').toggle(visibleCount === 0);
            };
            $('#td_non_performer_scope, #td_non_performer_subject').on('change', applyNonPerformerFilters);
        }

        $('#td_student_search').on('input', function () {
            var search = String($(this).val() || '').toLowerCase();
            $('#td_student_rows tr').each(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(search) !== -1);
            });
        });
    });
})(jQuery);
</script>

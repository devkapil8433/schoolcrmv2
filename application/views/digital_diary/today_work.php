<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$today_date = isset($today_date) && !empty($today_date) ? $today_date : date('Y-m-d');
$filter_class_id = isset($filter_class_id) ? (int) $filter_class_id : 0;
$filter_section_id = isset($filter_section_id) ? (int) $filter_section_id : 0;
$filter_subject_id = isset($filter_subject_id) ? (int) $filter_subject_id : 0;
$filter_subject_group = isset($filter_subject_group) ? $filter_subject_group : '';
$period_scoped = !empty($period_scoped);
$timetable_id = isset($timetable_id) ? (int) $timetable_id : 0;
$is_class_teacher_for_section = !empty($is_class_teacher_for_section);
$class_options = isset($class_options) ? $class_options : array('' => translate('select'));
$section_options = isset($section_options) ? $section_options : array('' => translate('select_class_first'));
$subject_group_options = isset($subject_group_options) ? $subject_group_options : array('' => translate('all'));
$report_rows = isset($report_rows) ? $report_rows : array();
$activity_type_options = isset($activity_type_options) ? $activity_type_options : array('' => translate('select'));
$notebook_type_options = isset($notebook_type_options) ? $notebook_type_options : array('' => translate('select'));
$notebook_types_multiselect = isset($notebook_types_multiselect) ? $notebook_types_multiselect : array();
$today_work_saved_json = isset($today_work_saved_json) ? $today_work_saved_json : '{}';
$is_today_work_day_final = !empty($is_today_work_day_final);
$class_name = !empty($filter_class_id) ? get_type_name_by_id('class', $filter_class_id) : '';
$tables_ok = $this->db->table_exists('digital_diary_teacher_activity_log') && $this->db->table_exists('digital_diary_lesson');
$today_work_table_ok = $this->db->table_exists('digital_diary_today_work');
?>

<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-tasks"></i> Today's Work — Step 2: Add Today's Work</h4>
			</header>
			<div class="panel-body">
				<?php if (!empty($filter_class_id) && !empty($filter_section_id)): ?>
				<div class="alert alert-success mb-md">
					<strong>Step 2 of 2:</strong> Student Work Report is done for this day. Now add class work / home work / learning<?= $period_scoped ? ' for your assigned subject.' : ' for each subject.' ?>
				</div>
				<?php else: ?>
				<div class="alert alert-info mb-md">
					<strong>Step 2 of 2:</strong> Select date, class and section to add Today's Work. If Student Work Report is not submitted yet, you will be taken to Step 1 first.
				</div>
				<?php endif; ?>
				<?php if (!$tables_ok): ?>
					<div class="alert alert-warning">
						<strong>Setup required.</strong> Run <code>application/sql/digital_diary_teacher_activity_log.sql</code> and <code>application/sql/digital_diary.sql</code>.
					</div>
				<?php elseif (!$today_work_table_ok): ?>
					<div class="alert alert-warning">
						<strong>Setup required.</strong> Run <code>application/sql/digital_diary_today_work.sql</code> to enable saving today's work.
					</div>
				<?php endif; ?>

				<?php if ($tables_ok): ?>
					<?php if (is_superadmin_loggedin() && empty($branch_id)): ?>
					<section class="panel mb-md">
						<header class="panel-heading">
							<h4 class="panel-title"><?= translate('branch') ?></h4>
						</header>
						<div class="panel-body">
							<?php echo form_open(base_url('digital_diary/today_work'), array('method' => 'get', 'class' => 'form-inline')); ?>
							<div class="form-group">
								<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
								<?php
									$arrayBranch = $this->app_lib->getSelectList('branch');
									echo form_dropdown('branch_id', $arrayBranch, $branch_id, "class='form-control' id='branch_pick' data-plugin-selectTwo data-width='280px' required");
								?>
							</div>
							<button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
							<?php echo form_close(); ?>
							<p class="text-muted mb-none mt-sm"><?= translate('select') ?> <?= translate('branch') ?> <?= translate('to') ?> <?= translate('continue') ?>.</p>
						</div>
					</section>
					<?php else: ?>
					<?php if (is_superadmin_loggedin() && !empty($branch_id)): ?>
					<section class="panel mb-md">
						<div class="panel-body">
							<strong><?= translate('branch') ?>:</strong> <?= html_escape(!empty($branch_name) ? $branch_name : get_type_name_by_id('branch', $branch_id)) ?>
							<a href="<?= base_url('digital_diary/today_work') ?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> <?= translate('change') ?></a>
						</div>
					</section>
					<?php endif; ?>

					<?php echo form_open(base_url('digital_diary/today_work'), array('method' => 'get', 'class' => 'mb-md', 'id' => 'frm_today_work_filter')); ?>
					<?php if (!empty($branch_id)): ?>
					<input type="hidden" name="branch_id" id="filter_branch_id" value="<?= (int) $branch_id ?>">
					<?php endif; ?>
					<?php if ($period_scoped): ?>
					<input type="hidden" name="timetable_id" value="<?= (int) $timetable_id ?>">
					<?php endif; ?>
					<div class="row mb-md">
						<div class="col-md-2">
							<label><?= translate('date') ?></label>
							<input type="text" class="form-control" name="date" id="date" value="<?= html_escape($today_date) ?>" <?= $period_scoped ? 'readonly' : 'data-plugin-datepicker data-plugin-options=\'{"format":"yyyy-mm-dd"}\'' ?>>
						</div>
						<div class="col-md-2">
							<label><?= translate('class') ?></label>
							<?= form_dropdown($period_scoped ? '' : 'class_id', $class_options, $filter_class_id, "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' data-plugin-selectTwo data-width='100%'" . ($period_scoped ? ' disabled' : '')) ?>
							<?php if ($period_scoped): ?><input type="hidden" name="class_id" value="<?= (int) $filter_class_id ?>"><?php endif; ?>
						</div>
						<div class="col-md-2">
							<label><?= translate('section') ?></label>
							<?= form_dropdown($period_scoped ? '' : 'section_id', $section_options, $filter_section_id, "class='form-control' id='section_id' data-plugin-selectTwo data-width='100%'" . ($period_scoped ? ' disabled' : '')) ?>
							<?php if ($period_scoped): ?><input type="hidden" name="section_id" value="<?= (int) $filter_section_id ?>"><?php endif; ?>
						</div>
						<div class="col-md-2" <?= $period_scoped ? 'style="display:none;"' : '' ?>>
							<label>Subject Group</label>
							<?= form_dropdown('subject_group', $subject_group_options, $filter_subject_group, "class='form-control' id='subject_group' data-plugin-selectTwo data-width='100%'") ?>
						</div>
						<div class="col-md-2" <?= $period_scoped ? 'style="display:none;"' : '' ?>>
							<label><?= translate('subject') ?></label>
							<select name="subject_id" id="subject_id" class="form-control" data-plugin-selectTwo data-width="100%" <?= $period_scoped ? 'style="display:none;"' : '' ?>>
								<option value=""><?= translate('all') ?></option>
							</select>
						</div>
					</div>
					<?php if ($period_scoped): ?>
					<p class="text-muted">Period locked to <?= html_escape(get_type_name_by_id('class', $filter_class_id)) ?> — <?= html_escape(get_type_name_by_id('section', $filter_section_id)) ?>, <?= html_escape(get_type_name_by_id('subject', $filter_subject_id)) ?>.</p>
					<?php endif; ?>
					<?php echo form_close(); ?>
					<?php endif; ?>

					<?php if ($today_work_table_ok && !empty($filter_class_id) && !empty($filter_section_id) && empty($is_today_work_day_final)): ?>
						<?php echo form_open(base_url('digital_diary/today_work_save'), array('class' => 'frm-submit-data', 'id' => 'frm_today_work_add', 'enctype' => 'multipart/form-data')); ?>
						<input type="hidden" name="work_date" id="tw_work_date" value="<?= html_escape($today_date) ?>">
						<input type="hidden" name="class_id" id="tw_class_id" value="<?= (int) $filter_class_id ?>">
						<input type="hidden" name="section_id" id="tw_section_id" value="<?= (int) $filter_section_id ?>">
						<?php if ($period_scoped): ?>
						<input type="hidden" name="timetable_id" value="<?= (int) $timetable_id ?>">
						<?php endif; ?>
						<input type="hidden" name="filter_subject_id" value="<?= (int) $filter_subject_id ?>">
						<input type="hidden" name="filter_subject_group" value="<?= html_escape($filter_subject_group) ?>">
						<?php if (is_superadmin_loggedin()): ?>
						<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
						<?php else: ?>
						<input type="hidden" name="branch_id" value="<?= (int) get_loggedin_branch_id() ?>">
						<?php endif; ?>
						<input type="hidden" name="subject_id" id="add_work_subject_id" value="<?= (int) $filter_subject_id ?>">
						<?php if (!$this->db->field_exists('cw_notebook_type_id', 'digital_diary_today_work')): ?>
						<div class="alert alert-warning mb-sm"><strong>DB update:</strong> Run <code>application/sql/digital_diary_today_work_multilesson_notebook.sql</code> for notebook types and multi-lesson storage.</div>
						<?php elseif (!$this->db->field_exists('cw_notebook_type_ids', 'digital_diary_today_work')): ?>
						<div class="alert alert-warning mb-sm"><strong>DB update:</strong> Run <code>application/sql/digital_diary_today_work_notebook_type_ids.sql</code> for multiple notebook types per row.</div>
						<?php elseif (!$this->db->table_exists('digital_diary_lesson_completion')): ?>
						<div class="alert alert-warning mb-sm"><strong>DB update:</strong> Run <code>application/sql/digital_diary_lesson_completion.sql</code> to enable lesson completion prompts and syllabus % from completed lessons.</div>
						<?php elseif ($this->db->table_exists('digital_diary_teacher_activity_log') && !$this->db->field_exists('lesson_id', 'digital_diary_teacher_activity_log')): ?>
						<div class="alert alert-warning mb-sm"><strong>DB update:</strong> Run <code>application/sql/digital_diary_teacher_activity_log_lesson_id.sql</code> to store teaching activity per completed lesson.</div>
						<?php endif; ?>
						<div id="tw_edit_hint" style="display:none;"></div>
						<div id="tw_edit_panel" style="display:none;">
						<div class="row mb-sm">
							<div class="col-md-4">
								<label><?= translate('subject') ?></label>
								<p class="form-control-static mb-none" id="tw_form_subject_label" style="padding-top:7px;font-weight:600;">—</p>
								<p class="help-block mb-none">Using subject from the filter above.</p>
							</div>
							<div class="col-md-8">
								<label>Lesson(s) <span class="required">*</span> <span class="text-muted">(for class work / home work &amp; syllabus %)</span></label>
								<select name="lesson_id[]" id="tw_lesson_id" class="form-control" multiple data-plugin-selectTwo data-width="100%" data-placeholder="Select lesson(s)">
								</select>
								<p class="help-block mb-none">Select lesson(s) for this subject. After save you will confirm which are completed for class work / home work (Syllabus %).</p>
							</div>
						</div>
						<div class="row mb-sm">
							<div class="col-md-3">
								<label>Class work — activity type</label>
								<select name="cw" id="tw_cw" class="form-control">
									<option value=""><?= translate('select') ?></option>
									<?php foreach ($activity_type_options as $k => $lab): ?>
									<?php if ($k === '' || $k === null) continue; ?>
									<option value="<?= html_escape($k) ?>"><?= html_escape($lab) ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-3">
								<label>Class work — detail <span class="text-muted">(Ex / Page / custom)</span></label>
								<input type="text" name="cw_detail" id="tw_cw_detail" class="form-control" placeholder="e.g. Exercise 3 / Page 42" <?= $is_today_work_day_final ? 'disabled' : '' ?>>
							</div>
							<div class="col-md-3">
								<label>Class work — notebook type</label>
								<?= form_dropdown('cw_notebook_type_ids[]', $notebook_types_multiselect, array(), "class='form-control' id='cw_notebook_type_ids' data-plugin-selectTwo data-width='100%' multiple") ?>
							</div>
							<div class="col-md-3">
								<label>Class work — Photo / Scan</label>
								<input type="file" name="cw_attachment" id="tw_cw_attachment" class="form-control" accept="image/*,application/pdf" <?= $is_today_work_day_final ? 'disabled' : '' ?>>
								<div id="tw_cw_attachment_preview" class="mt-xs"></div>
							</div>
						</div>
						<div class="row mb-sm">
							<div class="col-md-3">
								<label>Home work — activity type</label>
								<select name="hw" id="tw_hw" class="form-control">
									<option value=""><?= translate('select') ?></option>
									<?php foreach ($activity_type_options as $k => $lab): ?>
									<?php if ($k === '' || $k === null) continue; ?>
									<option value="<?= html_escape($k) ?>"><?= html_escape($lab) ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-3">
								<label>Home work — detail <span class="text-muted">(Ex / Page / custom)</span></label>
								<input type="text" name="hw_detail" id="tw_hw_detail" class="form-control" placeholder="e.g. Exercise 4 / Page 50" <?= $is_today_work_day_final ? 'disabled' : '' ?>>
							</div>
							<div class="col-md-3">
								<label>Home work — notebook type</label>
								<?= form_dropdown('hw_notebook_type_ids[]', $notebook_types_multiselect, array(), "class='form-control' id='hw_notebook_type_ids' data-plugin-selectTwo data-width='100%' multiple") ?>
							</div>
							<div class="col-md-3">
								<label>Home work — Photo / Scan</label>
								<input type="file" name="hw_attachment" id="tw_hw_attachment" class="form-control" accept="image/*,application/pdf" <?= $is_today_work_day_final ? 'disabled' : '' ?>>
								<div id="tw_hw_attachment_preview" class="mt-xs"></div>
							</div>
						</div>
						<div class="row mb-sm">
							<div class="col-md-12">
								<label><i class="fas fa-graduation-cap"></i> Learning work (Assignments) — from Question Bank</label>
								<p class="help-block mb-none">Uses <strong>all lessons</strong> for this subject (not only the class/home work lessons above). Pick question type + count → Auto-assign for a preview, then untick unwanted ones — or use Browse / Manual select.</p>
							</div>
						</div>
						<div class="row mb-sm">
							<div class="col-md-4">
								<label>Question type</label>
								<?php
								$learning_question_type_options = isset($learning_question_type_options) ? $learning_question_type_options : array('' => translate('select'));
								echo form_dropdown('learning_qtype', $learning_question_type_options, '', "class='form-control' id='tw_learning_qtype' data-plugin-selectTwo data-width='100%'");
								?>
							</div>
							<div class="col-md-3">
								<label>Count</label>
								<select name="learning_qcount" id="tw_learning_qcount" class="form-control">
									<option value="5">5</option>
									<option value="10">10</option>
									<option value="15">15</option>
								</select>
							</div>
							<div class="col-md-5" style="padding-top:24px;">
								<button type="button" class="btn btn-default" id="btn_tw_auto_pick" <?= $is_today_work_day_final ? 'disabled' : '' ?>><i class="fas fa-magic"></i> Auto-assign / Regenerate</button>
								<button type="button" class="btn btn-default" id="btn_tw_manual_pick" <?= $is_today_work_day_final ? 'disabled' : '' ?>><i class="fas fa-list"></i> Browse / Manual select</button>
								<label class="ml-md" style="font-weight:normal;">
									<input type="checkbox" name="learning_is_test" id="tw_learning_is_test" value="1">
									Includes test (marks entry next day)
								</label>
							</div>
						</div>
						<div class="row mb-sm">
							<div class="col-md-12">
								<label>Assigned questions <span class="text-muted">(preview — tick to keep, untick to remove)</span></label>
								<div class="mb-xs">
									<button type="button" class="btn btn-xs btn-default" id="btn_tw_q_select_all" <?= $is_today_work_day_final ? 'disabled' : '' ?>>Select all</button>
									<button type="button" class="btn btn-xs btn-default" id="btn_tw_q_unselect_all" <?= $is_today_work_day_final ? 'disabled' : '' ?>>Unselect all</button>
									<span class="text-muted ml-sm" id="tw_learning_selected_count">0 selected</span>
								</div>
								<div id="tw_learning_preview" style="max-height:280px;overflow:auto;border:1px solid #ddd;padding:8px 10px;background:#fafafa;border-radius:3px;">
									<p class="text-muted mb-none" id="tw_learning_preview_empty">Click <strong>Auto-assign</strong> for a picked preview, or <strong>Browse / Manual select</strong> to choose yourself.</p>
									<div id="tw_learning_preview_list"></div>
								</div>
								<select id="tw_learning_questions" class="hidden" multiple style="display:none !important;" aria-hidden="true"></select>
								<p class="help-block mb-none" id="tw_learning_pick_msg"></p>
								<p class="help-block mb-none">Students will practice the ticked questions on their portal.</p>
							</div>
						</div>
						<div class="row mb-sm">
							<div class="col-md-12 text-right">
								<button type="button" class="btn btn-default" id="btn_tw_cancel_edit"><?= translate('cancel') ?></button>
								<button type="submit" class="btn btn-primary" id="btn_tw_add" data-label-add="<?= html_escape(translate('add')) ?>" data-label-update="<?= html_escape(translate('update')) ?>" <?= $is_today_work_day_final ? 'disabled' : '' ?>><?= translate('add') ?></button>
							</div>
						</div>
						<div class="row mb-md">
							<div class="col-md-12">
								<label>Home work note</label>
								<input type="text" name="home_work_note" id="home_work_note" class="form-control" placeholder="e.g. One page writing" <?= $is_today_work_day_final ? 'disabled' : '' ?>>
							</div>
						</div>
						</div>
						<?php echo form_close(); ?>
					<?php endif; ?>

					<?php if ($is_today_work_day_final): ?>
					<div class="alert alert-warning">
						<i class="fas fa-lock"></i> <strong>Final submitted.</strong>
					</div>
					<?php endif; ?>

					<h3 class="text-center mt-lg mb-md" style="color:#c00;text-decoration:underline;">Home work and Syllabus Report<?= !empty($class_name) ? ' for class - ' . html_escape($class_name) : '' ?></h3>

					<div class="table-responsive">
						<table class="table table-bordered table-condensed table-hover text-center">
							<thead>
								<tr>
									<th><?= translate('subject') ?> &amp; Subject Group</th>
									<th>Total Lesson</th>
									<th>Now Going On</th>
									<th>Class work <small class="text-muted">(activity type)</small></th>
									<th>Home work <small class="text-muted">(activity type)</small></th>
									<th>Learning work</th>
									<th>Syllabus Percentage</th>
									<th>Work Percentage</th>
									<th style="width:110px;"><?= translate('action') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($filter_class_id) || empty($filter_section_id)): ?>
									<tr>
										<td colspan="9" class="text-center text-muted"><?= translate('select') ?> <?= translate('class') ?> &amp; <?= translate('section') ?> <?= translate('to') ?> <?= translate('continue') ?>.</td>
									</tr>
								<?php elseif (empty($report_rows)): ?>
									<tr>
										<td colspan="9" class="text-center text-muted">No data found for selected filters.</td>
									</tr>
								<?php else: ?>
									<?php foreach ($report_rows as $row): ?>
										<?php $has_work = !empty($row['has_work']); ?>
										<tr>
											<td class="text-left">
												<div><?= !empty($row['subject_name']) ? html_escape($row['subject_name']) : '—' ?></div>
												<small class="text-muted"><?= !empty($row['subject_group']) ? html_escape($row['subject_group']) : '—' ?></small>
											</td>
											<td><?= isset($row['total_lesson']) ? (int) $row['total_lesson'] : 0 ?></td>
											<td class="text-left"><?= !empty($row['now_going_on']) ? html_escape($row['now_going_on']) : '—' ?></td>
											<td class="text-left"><?= !empty($row['class_work']) ? html_escape($row['class_work']) : '—' ?></td>
											<td class="text-left"><?= !empty($row['home_work']) ? html_escape($row['home_work']) : '—' ?></td>
											<td class="text-left"><?= !empty($row['learning_work']) ? html_escape($row['learning_work']) : '—' ?></td>
											<td><?= !empty($row['syllabus_percentage']) ? html_escape($row['syllabus_percentage']) : '—' ?></td>
											<td><?= !empty($row['student_work_percentage']) ? html_escape($row['student_work_percentage']) : '—' ?></td>
											<td class="text-center">
												<?php if (empty($is_today_work_day_final)): ?>
													<?php if ($has_work): ?>
												<button type="button" class="btn btn-default btn-xs tw-edit-row" data-subject="<?= (int) $row['subject_id'] ?>" title="<?= translate('edit') ?>">
													<i class="fas fa-pencil-alt"></i> <?= translate('edit') ?>
												</button>
													<?php if ($is_class_teacher_for_section && !empty($row['today_work_id'])): ?>
												<?php echo form_open(base_url('digital_diary/today_work_delete'), array('class' => 'frm-submit-data tw-delete-form', 'onsubmit' => "return confirm('Delete this work, its assigned questions, and linked student submissions?')")); ?>
												<input type="hidden" name="work_id" value="<?= (int) $row['today_work_id'] ?>">
												<input type="hidden" name="work_date" value="<?= html_escape($today_date) ?>">
												<input type="hidden" name="class_id" value="<?= (int) $filter_class_id ?>">
												<input type="hidden" name="section_id" value="<?= (int) $filter_section_id ?>">
												<input type="hidden" name="subject_id" value="<?= (int) $row['subject_id'] ?>">
												<?php if (is_superadmin_loggedin()): ?>
												<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
												<?php endif; ?>
												<button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Delete</button>
												<?php echo form_close(); ?>
													<?php endif; ?>
													<?php else: ?>
												<button type="button" class="btn btn-success btn-xs tw-add-row" data-subject="<?= (int) $row['subject_id'] ?>" title="<?= translate('add') ?>">
													<i class="fas fa-plus"></i> <?= translate('add') ?>
												</button>
													<?php endif; ?>
												<?php else: ?>
												<span class="text-muted">—</span>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<?php
					$swr_q = array(
						'date' => $today_date,
						'class_id' => (int) $filter_class_id,
						'section_id' => (int) $filter_section_id,
					);
					if (!empty($branch_id)) {
						$swr_q['branch_id'] = (int) $branch_id;
					}
					if ($period_scoped) {
						$swr_q['subject_id'] = (int) $filter_subject_id;
						$swr_q['timetable_id'] = (int) $timetable_id;
					}
					$swr_url = base_url('digital_diary/student_work_report_entry') . '?' . http_build_query($swr_q);
					$timetable_q = array('date' => $today_date);
					if (!empty($branch_id)) {
						$timetable_q['branch_id'] = (int) $branch_id;
					}
					$timetable_url = base_url('digital_diary/student_work_report_entry') . '?' . http_build_query($timetable_q);
					?>
					<div class="text-right mt-md">
						<?php if (is_teacher_loggedin()): ?>
						<a href="<?= html_escape($timetable_url) ?>" class="btn btn-primary"><i class="fas fa-calendar-alt"></i> My timetable</a>
						<?php endif; ?>
						<?php if ($is_today_work_day_final): ?>
						<a href="<?= html_escape($swr_url) ?>" class="btn btn-default"><i class="fas fa-eye"></i> View Step 1: Student Work Report</a>
						<?php else: ?>
						<a href="<?= html_escape($swr_url) ?>" class="btn btn-default"><i class="fas fa-edit"></i> Edit Step 1: Student Work Report</a>
						<?php endif; ?>
					</div>

					<?php
						$all_subjects_filled = !empty($all_subjects_filled);
						$subjects_filled_count = isset($subjects_filled_count) ? (int) $subjects_filled_count : 0;
						$subjects_total_count = isset($subjects_total_count) ? (int) $subjects_total_count : 0;
						$my_subjects_filled_count = isset($my_subjects_filled_count) ? (int) $my_subjects_filled_count : 0;
						$my_subjects_total_count = isset($my_subjects_total_count) ? (int) $my_subjects_total_count : 0;
						$can_final_submit = !empty($can_final_submit);
						$is_class_teacher_for_section = !empty($is_class_teacher_for_section);
						$can_final = $can_final_submit && $all_subjects_filled && !$is_today_work_day_final;
					?>
					<?php if ($today_work_table_ok && $this->db->table_exists('digital_diary_today_work_day') && !empty($filter_class_id) && !empty($filter_section_id)): ?>
						<div class="mt-sm mb-sm">
							<?php if (is_teacher_loggedin() && !$is_class_teacher_for_section): ?>
							<div class="alert alert-info mb-sm">
								<strong>Your role:</strong> Subject teacher — submit Student Work Report + Today's Work for <em>your subject(s)</em> only
								(<?= (int) $my_subjects_filled_count ?>/<?= (int) $my_subjects_total_count ?> of your subjects done).
								<br>Class teacher will Final Submit to Principal after all subjects are filled (<?= (int) $subjects_filled_count ?>/<?= (int) $subjects_total_count ?> class-wide).
							</div>
							<?php elseif (!$is_today_work_day_final): ?>
							<div class="alert alert-warning mb-sm">
								<strong>Class teacher:</strong> Each subject teacher submits their own work first.
								Final Submit to Principal opens when all subjects are filled (<?= (int) $subjects_filled_count ?>/<?= (int) $subjects_total_count ?>).
							</div>
							<?php endif; ?>
						</div>
						<?php if ($can_final_submit): ?>
						<?php echo form_open(base_url('digital_diary/today_work_final_submit'), array('class' => 'frm-submit-data')); ?>
						<input type="hidden" name="work_date" value="<?= html_escape($today_date) ?>">
						<input type="hidden" name="class_id" value="<?= (int) $filter_class_id ?>">
						<input type="hidden" name="section_id" value="<?= (int) $filter_section_id ?>">
						<?php if (is_superadmin_loggedin()): ?>
						<input type="hidden" name="branch_id" value="<?= (int) $branch_id ?>">
						<?php else: ?>
						<input type="hidden" name="branch_id" value="<?= (int) get_loggedin_branch_id() ?>">
						<?php endif; ?>
						<div class="text-right mt-sm">
							<?php if (!$all_subjects_filled && !$is_today_work_day_final): ?>
							<p class="text-muted small mb-sm">Waiting for all subject teachers (<?= $subjects_filled_count ?>/<?= $subjects_total_count ?>). Then Final Submit → Principal.</p>
							<?php endif; ?>
							<button type="submit" class="btn btn-primary" id="btn_tw_final" <?= $can_final ? '' : 'disabled' ?> title="<?= $all_subjects_filled ? 'Submit to Principal' : 'All subject teachers must submit first' ?>">Final Submit<?= $all_subjects_filled ? ' → Principal' : '' ?></button>
						</div>
						<?php echo form_close(); ?>
						<?php endif; ?>
					<?php endif; ?>

				<?php endif; ?>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
	var twSavedEntries = <?= $today_work_table_ok ? $today_work_saved_json : '{}' ?>;
	var twKeepSubjectId = '<?= isset($keep_subject_id) ? (int) $keep_subject_id : 0 ?>';

	function twBuildLessonCompletionRows(data) {
		var rows = data.lesson_completion_options;
		if (rows && rows.length) {
			return rows;
		}
		rows = [];
		(data.lesson_ids || []).forEach(function (id) {
			if (!id) {
				return;
			}
			var sid = String(id);
			var $opt = $('#tw_lesson_id option[value="' + sid + '"]');
			var label = $opt.length ? $.trim($opt.text().replace(/^\s*✓\s+/, '')) : ('Lesson #' + sid);
			rows.push({ id: parseInt(id, 10), label: label });
		});
		return rows;
	}

	/** After Today's Work save: confirm lesson completion + teaching activity for class / home work. */
	window.twAfterTodayWorkSave = function (data, doneCallback) {
		if (!data.prompt_lesson_completion || !data.lesson_ids || !data.lesson_ids.length) {
			doneCallback();
			return;
		}
		if (typeof Swal === 'undefined') {
			doneCallback();
			return;
		}
		var rows = twBuildLessonCompletionRows(data);
		if (!rows.length) {
			doneCallback();
			return;
		}
		var askActivity = !!data.ask_teaching_activity && data.teaching_activity_options && data.teaching_activity_options.length;
		var defaultActivityId = data.cw_activity_id ? String(data.cw_activity_id) : '';
		if (!defaultActivityId && $('#tw_cw').length) {
			defaultActivityId = String($('#tw_cw').val() || '');
		}
		var cwLabel = data.class_work_label ? String(data.class_work_label) : '';
		var hwLabel = data.home_work_label ? String(data.home_work_label) : '';
		var esc = function (s) { return $('<div/>').text(s == null ? '' : String(s)).html(); };

		var html = '<style>'
			+ '.tw-swal{text-align:left;font-size:13px;color:#334155;}'
			+ '.tw-swal-meta{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 14px;}'
			+ '.tw-swal-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:12px;}'
			+ '.tw-swal-chip.hw{background:#ecfdf5;color:#065f46;}'
			+ '.tw-swal-list{max-height:340px;overflow:auto;padding-right:2px;}'
			+ '.tw-swal-card{border:1px solid #e2e8f0;border-radius:10px;background:#fff;padding:12px 14px;margin:0 0 10px;box-shadow:0 1px 2px rgba(15,23,42,.04);}'
			+ '.tw-swal-card-title{font-weight:700;font-size:14px;color:#0f172a;margin:0 0 10px;}'
			+ '.tw-swal-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:10px;margin-bottom:10px;}'
			+ '@media (max-width:560px){.tw-swal-grid{grid-template-columns:1fr;}}'
			+ '.tw-swal-label{display:block;font-size:11px;font-weight:600;letter-spacing:.02em;text-transform:uppercase;color:#64748b;margin:0 0 5px;}'
			+ '.tw-swal-card .form-control{height:34px;border-radius:8px;border-color:#cbd5e1;}'
			+ '.tw-swal-tog{display:inline-flex;width:100%;border:1px solid #cbd5e1;border-radius:8px;overflow:hidden;background:#f8fafc;}'
			+ '.tw-swal-tog button{flex:1;border:0;background:transparent;padding:7px 8px;font-size:12px;font-weight:600;color:#64748b;cursor:pointer;}'
			+ '.tw-swal-tog button.active[data-val="1"]{background:#16a34a;color:#fff;}'
			+ '.tw-swal-tog button.active[data-val="0"]{background:#94a3b8;color:#fff;}'
			+ '.tw-swal-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;padding-top:8px;border-top:1px dashed #e2e8f0;}'
			+ '.tw-swal-check{display:inline-flex;align-items:center;gap:8px;margin:0;font-weight:600;color:#0f172a;cursor:pointer;}'
			+ '.tw-swal-check input{margin:0;transform:scale(1.1);}'
			+ '</style>';

		html += '<div class="tw-swal">';
		if (cwLabel || hwLabel) {
			html += '<div class="tw-swal-meta">';
			if (cwLabel) {
				html += '<span class="tw-swal-chip"><i class="fas fa-chalkboard-teacher"></i> CW: ' + esc(cwLabel) + '</span>';
			}
			if (hwLabel) {
				html += '<span class="tw-swal-chip hw"><i class="fas fa-book"></i> HW: ' + esc(hwLabel) + '</span>';
			}
			html += '</div>';
		}
		html += '<div class="tw-swal-list">';
		rows.forEach(function (row, idx) {
			var eid = 'tw_lc_' + row.id;
			var aid = 'tw_la_' + row.id;
			var actDoneDefault = defaultActivityId ? '1' : '0';
			html += '<div class="tw-swal-card" data-lesson="' + row.id + '">';
			html += '<div class="tw-swal-card-title">' + (idx + 1) + '. ' + esc(row.label) + '</div>';
			if (askActivity) {
				html += '<div class="tw-swal-grid">';
				html += '<div><label class="tw-swal-label" for="' + aid + '">Teaching activity</label>';
				html += '<select id="' + aid + '" class="form-control tw-la-sel" data-lesson="' + row.id + '">';
				html += '<option value="">' + esc('<?= translate('select') ?>') + '</option>';
				(data.teaching_activity_options || []).forEach(function (opt) {
					var oid = String(opt.id);
					var selected = (defaultActivityId && oid === defaultActivityId) ? ' selected' : '';
					html += '<option value="' + oid + '"' + selected + '>' + esc(opt.name || ('#' + oid)) + '</option>';
				});
				html += '</select></div>';
				html += '<div><span class="tw-swal-label">Activity completed?</span>';
				html += '<div class="tw-swal-tog tw-ac-tog" data-lesson="' + row.id + '" data-val="' + actDoneDefault + '">';
				html += '<button type="button" data-val="1"' + (actDoneDefault === '1' ? ' class="active"' : '') + '>Yes</button>';
				html += '<button type="button" data-val="0"' + (actDoneDefault === '0' ? ' class="active"' : '') + '>No</button>';
				html += '</div></div>';
				html += '</div>';
			}
			html += '<div class="tw-swal-foot">';
			html += '<label class="tw-swal-check" for="' + eid + '">';
			html += '<input type="checkbox" id="' + eid + '" class="tw-lc-cb" value="' + row.id + '">';
			html += '<span>Lesson completed</span></label>';
			html += '</div></div>';
		});
		html += '</div></div>';

		Swal.fire({
			title: askActivity ? 'Confirm today’s teaching progress' : 'Confirm lesson completion',
			html: html,
			showCancelButton: true,
			confirmButtonText: '<i class="fas fa-check"></i> Save',
			cancelButtonText: 'Skip for now',
			focusConfirm: false,
			width: askActivity ? '46em' : '36em',
			customClass: {
				popup: 'tw-swal-popup',
				confirmButton: 'btn btn-primary',
				cancelButton: 'btn btn-default'
			},
			buttonsStyling: false,
			didOpen: function () {
				$('.swal2-container .tw-ac-tog button').on('click', function (e) {
					e.preventDefault();
					var $tog = $(this).closest('.tw-ac-tog');
					var val = String($(this).data('val'));
					$tog.attr('data-val', val);
					$tog.find('button').removeClass('active');
					$(this).addClass('active');
				});
				$('.swal2-container .tw-la-sel').on('change', function () {
					var lid = $(this).data('lesson');
					var $tog = $('.swal2-container .tw-ac-tog[data-lesson="' + lid + '"]');
					if (!$tog.length) {
						return;
					}
					if (!$(this).val()) {
						$tog.attr('data-val', '0');
						$tog.find('button').removeClass('active');
						$tog.find('button[data-val="0"]').addClass('active');
					}
				});
			},
			preConfirm: function () {
				var ids = [];
				var activities = {};
				var missingAct = false;
				$('.swal2-container .tw-lc-cb:checked').each(function () {
					ids.push(String($(this).val()));
				});
				$('.swal2-container .tw-la-sel').each(function () {
					var lid = String($(this).data('lesson') || '');
					var act = String($(this).val() || '');
					var done = String($('.swal2-container .tw-ac-tog[data-lesson="' + lid + '"]').attr('data-val') || '0') === '1';
					if (!lid) {
						return;
					}
					if (done && !act) {
						missingAct = true;
						return;
					}
					if (done && act) {
						activities[lid] = act;
					}
				});
				if (missingAct) {
					Swal.showValidationMessage('Select a teaching activity for each lesson marked Activity completed = Yes.');
					return false;
				}
				return { ids: ids, activities: activities };
			}
		}).then(function (result) {
			if (!result.isConfirmed) {
				doneCallback();
				return;
			}
			var value = result.value || {};
			var ids = value.ids || [];
			var activities = value.activities || {};
			var payload = {
				work_date: $('#tw_work_date').val(),
				class_id: $('#tw_class_id').val(),
				section_id: $('#tw_section_id').val(),
				subject_id: $('#add_work_subject_id').val(),
				lesson_ids: ids.join(','),
				cw_detail: $('#tw_cw_detail').val() || ''
			};
			Object.keys(activities).forEach(function (lid) {
				payload['lesson_activities[' + lid + ']'] = activities[lid];
			});
			var $br = $('#frm_today_work_add input[name="branch_id"]');
			if ($br.length) {
				payload.branch_id = $br.val();
			}
			if (typeof csrfData !== 'undefined') {
				$.extend(payload, csrfData);
			}
			$.ajax({
				url: base_url + 'digital_diary/lesson_completion_save',
				type: 'POST',
				data: payload,
				dataType: 'json'
			}).always(function () {
				doneCallback();
			});
		});
	};

	/** Rebuild Select2 after options HTML changes (theme stores instance on __select2). */
	function twReinitSelect2($sel) {
		if (!$sel || !$sel.length) {
			return;
		}
		try {
			if ($sel.hasClass('select2-hidden-accessible')) {
				$sel.select2('destroy');
			}
		} catch (e) {}
		$sel.removeData('__select2');
		var opts = {};
		var ph = $sel.attr('data-placeholder');
		if (ph) {
			opts.placeholder = ph;
			opts.allowClear = true;
		}
		if ($.fn.themePluginSelect2) {
			$sel.themePluginSelect2(opts);
		} else if ($.fn.select2) {
			opts.width = '100%';
			$sel.select2(opts);
		}
	}

	$(document).ajaxComplete(function (event, xhr, settings) {
		if (!settings.url) {
			return;
		}
		if (settings.url.indexOf('ajax/getSectionByClass') !== -1) {
			twReinitSelect2($('#section_id'));
		}
	});

	function twSubjectLabelById(sid) {
		sid = String(sid || '');
		if (!sid) {
			return '—';
		}
		var $opt = $('#subject_id option[value="' + sid + '"]');
		if ($opt.length) {
			return $.trim($opt.text()) || ('Subject #' + sid);
		}
		var $row = $('.tw-edit-row[data-subject="' + sid + '"], .tw-add-row[data-subject="' + sid + '"]').first().closest('tr');
		if ($row.length) {
			var name = $.trim($row.find('td').first().find('div').first().text() || '');
			if (name) {
				return name;
			}
		}
		return 'Subject #' + sid;
	}

	function syncAddFormSubjectId(sid) {
		sid = sid != null ? String(sid) : '';
		if (!sid && $('#add_work_subject_id').length) {
			sid = String($('#add_work_subject_id').val() || '');
		}
		if (!sid && $('#subject_id').length) {
			sid = String($('#subject_id').val() || '');
		}
		if ($('#add_work_subject_id').length) {
			$('#add_work_subject_id').val(sid);
		}
		if ($('#tw_form_subject_label').length) {
			$('#tw_form_subject_label').text(twSubjectLabelById(sid));
		}
	}

	function syncTwFormSubjectOptions(selectedID, mode) {
		// Subject comes from filter / row action — no duplicate dropdown.
		selectedID = selectedID != null ? String(selectedID) : '';
		if (!selectedID && $('#subject_id').length) {
			selectedID = String($('#subject_id').val() || '');
		}
		if (mode === 'add' && selectedID && twSavedEntries[selectedID]) {
			selectedID = '';
		}
		syncAddFormSubjectId(selectedID);
	}

	function loadSubjectsForTodayWork(classID, sectionID, selectedID, done) {
		var $subject = $('#subject_id');
		if (!$subject.length) {
			return;
		}
		selectedID = selectedID || '';
		if (!classID || !sectionID) {
			$subject.html('<option value=""><?= translate('all') ?></option>').trigger('change');
			syncAddFormSubjectId();
			if (typeof done === 'function') {
				done();
			}
			return;
		}
		$.ajax({
			url: base_url + 'subject/getByClassSection',
			type: 'POST',
			data: { classID: classID, sectionID: sectionID },
			success: function (html) {
				$subject.html('<option value=""><?= translate('all') ?></option>' + html);
				twReinitSelect2($subject);
				if (selectedID) {
					$subject.val(String(selectedID)).trigger('change');
				} else {
					$subject.trigger('change');
				}
				syncTwFormSubjectOptions(selectedID || '');
				syncAddFormSubjectId();
				if (typeof done === 'function') {
					done();
				}
			}
		});
	}

	function twWorkSubjectId() {
		if ($('#tw_edit_panel').is(':visible') && $('#add_work_subject_id').length) {
			var active = String($('#add_work_subject_id').val() || '');
			if (active) {
				return active;
			}
		}
		return String($('#subject_id').val() || $('#add_work_subject_id').val() || '');
	}

	function loadLessonsForTodayWork(skipQuestionReload) {
		var classID = $('#class_id').val();
		var sectionID = $('#section_id').val();
		var subjectID = twWorkSubjectId();
		var $les = $('#tw_lesson_id');
		if (!$les.length) return;
		var $pendHost = $('#subject_id');
		var pendingLessons = $pendHost.data('pending-lessons');
		var pendingLesson = $pendHost.data('pending-lesson');
		$pendHost.removeData('pending-lesson');
		$pendHost.removeData('pending-lessons');
		var afterLoad = $pendHost.data('tw-fill-after');
		$pendHost.removeData('tw-fill-after');
		$les.empty();
		twReinitSelect2($les);
		if (!classID || !sectionID || !subjectID) {
			if (typeof afterLoad === 'function') {
				afterLoad();
			} else {
				window._twPrefillActive = false;
			}
			return;
		}
		var data = { class_id: classID, section_id: sectionID, subject_id: subjectID };
		<?php if (is_superadmin_loggedin()): ?>
		data.branch_id = $('#filter_branch_id').length ? $('#filter_branch_id').val() : ($('#branch_pick').length ? $('#branch_pick').val() : '');
		<?php endif; ?>
		window._twIgnoreLessonChange = true;
		$.ajax({
			url: base_url + 'digital_diary/ajax_today_work_lessons',
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function (res) {
				if (!res || res.status !== 'success' || !res.lessons) {
					window._twIgnoreLessonChange = false;
					twReinitSelect2($les);
					if (typeof afterLoad === 'function') {
						afterLoad();
					} else {
						window._twPrefillActive = false;
					}
					if (!skipQuestionReload) {
						loadQuestionsForTodayWork(null);
					}
					return;
				}
				var opts = '';
				$.each(res.lessons, function (i, L) {
					var base = L.label || ((L.lesson_no ? (L.lesson_no + '. ') : '') + (L.lesson_name || ''));
					var label = L.completed ? ('✓ ' + base) : base;
					opts += '<option value="' + L.id + '">' + $('<div>').text(label).html() + '</option>';
				});
				$les.html(opts);
				twReinitSelect2($les);
				var sel = [];
				if (pendingLessons) {
					sel = String(pendingLessons).split(',').map(function (x) {
						return $.trim(x);
					}).filter(Boolean);
				} else if (pendingLesson) {
					sel = [String(pendingLesson)];
				}
				if (sel.length) {
					sel.forEach(function (id) {
						if (!$les.find('option[value="' + id + '"]').length) {
							$les.append($('<option>').val(id).text('Lesson #' + id));
						}
					});
					twReinitSelect2($les);
					$les.val(sel).trigger('change');
				} else {
					$les.val(null).trigger('change');
				}
				window._twIgnoreLessonChange = false;
				// Prefill CW/HW/notebooks/pending questions BEFORE loading question list
				if (typeof afterLoad === 'function') {
					afterLoad();
				} else {
					window._twPrefillActive = false;
				}
				if (!skipQuestionReload) {
					loadQuestionsForTodayWork(null);
				}
			},
			error: function () {
				window._twIgnoreLessonChange = false;
				twReinitSelect2($les);
				if (typeof afterLoad === 'function') {
					afterLoad();
				} else {
					window._twPrefillActive = false;
				}
				if (!skipQuestionReload) {
					loadQuestionsForTodayWork(null);
				}
			}
		});
	}

	function twEscapeHtml(str) {
		return $('<div>').text(str == null ? '' : String(str)).html();
	}

	function twLearningSelectedIds() {
		var ids = [];
		$('#tw_learning_preview_list input.tw-lq-check:checked').each(function () {
			ids.push(String($(this).val()));
		});
		return ids;
	}

	function twUpdateLearningSelectedCount() {
		var n = $('#tw_learning_preview_list input.tw-lq-check:checked').length;
		var total = $('#tw_learning_preview_list input.tw-lq-check').length;
		$('#tw_learning_selected_count').text(n + ' selected' + (total ? (' of ' + total) : ''));
		// Keep hidden select in sync for any code reading its value
		var $q = $('#tw_learning_questions');
		if ($q.length) {
			$q.empty();
			$('#tw_learning_preview_list input.tw-lq-check:checked').each(function () {
				$q.append($('<option>').val($(this).val()).prop('selected', true).text($(this).data('label') || $(this).val()));
			});
		}
	}

	function twClearLearningPreview() {
		$('#tw_learning_preview_list').empty();
		$('#tw_learning_preview_empty').show();
		$('#tw_learning_questions').empty();
		twUpdateLearningSelectedCount();
	}

	/**
	 * Render checkbox preview list.
	 * @param {Array} questions
	 * @param {Array|null} selectedIds  if null, check all; else check matching ids
	 * @param {boolean} mergeKeepSelected  when true, merge into existing list and keep current ticks
	 */
	function twRenderLearningPreview(questions, selectedIds, mergeKeepSelected) {
		var $list = $('#tw_learning_preview_list');
		var $empty = $('#tw_learning_preview_empty');
		var keep = {};
		if (mergeKeepSelected) {
			twLearningSelectedIds().forEach(function (id) { keep[id] = true; });
		}
		var selectedMap = null;
		if (selectedIds === null) {
			selectedMap = null; // check all
		} else {
			selectedMap = {};
			(selectedIds || []).forEach(function (id) { selectedMap[String(id)] = true; });
			if (mergeKeepSelected) {
				Object.keys(keep).forEach(function (id) { selectedMap[id] = true; });
			}
		}
		if (!mergeKeepSelected) {
			$list.empty();
		}
		var existing = {};
		$list.find('input.tw-lq-check').each(function () {
			existing[String($(this).val())] = true;
		});
		var added = 0;
		$.each(questions || [], function (i, Q) {
			var id = String(Q.id);
			if (existing[id]) {
				// update check state if needed
				if (selectedMap !== null && selectedMap[id]) {
					$list.find('input.tw-lq-check[value="' + id + '"]').prop('checked', true);
				}
				return;
			}
			existing[id] = true;
			var checked = (selectedMap === null) ? true : !!selectedMap[id];
			if (mergeKeepSelected && keep[id]) {
				checked = true;
			}
			var label = Q.label || ('Question #' + id);
			var disabled = <?= $is_today_work_day_final ? 'true' : 'false' ?>;
			var row = '<label class="tw-lq-row" style="display:block;margin:0 0 6px;padding:4px 6px;border-bottom:1px solid #eee;font-weight:normal;cursor:pointer;">'
				+ '<input type="checkbox" class="tw-lq-check" name="learning_question_ids[]" value="' + twEscapeHtml(id) + '" data-label="' + twEscapeHtml(label) + '"' + (checked ? ' checked' : '') + (disabled ? ' disabled' : '') + '> '
				+ '<span class="tw-lq-label">' + twEscapeHtml(label) + '</span>'
				+ '</label>';
			$list.append(row);
			added++;
		});
		if ($list.find('input.tw-lq-check').length) {
			$empty.hide();
		} else {
			$empty.show();
		}
		twUpdateLearningSelectedCount();
		return added;
	}

	function loadQuestionsForTodayWork(afterLoad) {
		var classID = $('#class_id').val();
		var sectionID = $('#section_id').val();
		var subjectID = twWorkSubjectId();
		var typeId = $('#tw_learning_qtype').val() || '';
		var pendingQ = $('#subject_id').data('pending-questions');
		var pendingTest = $('#subject_id').data('pending-test');
		$('#subject_id').removeData('pending-questions');
		$('#subject_id').removeData('pending-test');
		if (!classID || !sectionID || !subjectID) {
			twClearLearningPreview();
			if (typeof afterLoad === 'function') afterLoad();
			return;
		}
		// Learning work: all lessons for the subject (do not limit to CW/HW lesson selection).
		var data = {
			class_id: classID,
			section_id: sectionID,
			subject_id: subjectID,
			lesson_ids: []
		};
		if (typeId) {
			data.question_type = typeId;
		}
		<?php if (is_superadmin_loggedin()): ?>
		data.branch_id = $('#filter_branch_id').length ? $('#filter_branch_id').val() : ($('#branch_pick').length ? $('#branch_pick').val() : '');
		<?php endif; ?>
		$.ajax({
			url: base_url + 'digital_diary/ajax_today_work_questions',
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function (res) {
				var sel = [];
				if (pendingQ) {
					sel = String(pendingQ).split(',').map(function (x) { return $.trim(x); }).filter(Boolean);
				}
				if (!res || res.status !== 'success' || !res.questions) {
					twClearLearningPreview();
					if (sel.length) {
						// Fallback stubs for already-saved ids
						twRenderLearningPreview(sel.map(function (id) {
							return { id: id, label: 'Question #' + id };
						}), sel, false);
					}
					if (typeof afterLoad === 'function') afterLoad();
					return;
				}
				var questions = res.questions || [];
				if (sel.length) {
					// Ensure pending ids appear even if filtered out
					var seen = {};
					questions.forEach(function (Q) { seen[String(Q.id)] = true; });
					sel.forEach(function (id) {
						if (!seen[id]) {
							questions.push({ id: id, label: 'Question #' + id });
						}
					});
					twRenderLearningPreview(questions, sel, false);
				} else {
					// Lesson change / browse preload: show list unchecked for manual tick
					twRenderLearningPreview(questions, [], false);
				}
				$('#tw_learning_is_test').prop('checked', pendingTest == 1 || pendingTest === '1' || pendingTest === true);
				$('#tw_learning_pick_msg').text(questions.length ? (questions.length + ' question(s) available — tick to assign.') : 'No questions found for this filter.');
				if (typeof afterLoad === 'function') afterLoad();
			},
			error: function () {
				twClearLearningPreview();
				if (typeof afterLoad === 'function') afterLoad();
			}
		});
	}

	function autoPickLearningQuestions() {
		var classID = $('#class_id').val();
		var sectionID = $('#section_id').val();
		var subjectID = twWorkSubjectId();
		var typeId = $('#tw_learning_qtype').val();
		var count = $('#tw_learning_qcount').val() || 5;
		$('#tw_learning_pick_msg').text('');
		if (!classID || !sectionID || !subjectID) {
			$('#tw_learning_pick_msg').text('Select class, section and subject first.');
			return;
		}
		if (!typeId) {
			$('#tw_learning_pick_msg').text('Select a question type.');
			return;
		}
		var data = {
			class_id: classID,
			section_id: sectionID,
			subject_id: subjectID,
			lesson_ids: [],
			question_type: typeId,
			count: count
		};
		<?php if (is_superadmin_loggedin()): ?>
		data.branch_id = $('#filter_branch_id').length ? $('#filter_branch_id').val() : ($('#branch_pick').length ? $('#branch_pick').val() : '');
		<?php endif; ?>
		$('#btn_tw_auto_pick').prop('disabled', true);
		$.ajax({
			url: base_url + 'digital_diary/ajax_today_work_auto_questions',
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function (res) {
				$('#btn_tw_auto_pick').prop('disabled', false);
				if (!res || res.status !== 'success') {
					twClearLearningPreview();
					$('#tw_learning_pick_msg').text((res && res.warning) ? res.warning : 'Could not auto-assign questions.');
					return;
				}
				var questions = res.questions || [];
				var ids = questions.map(function (Q) { return String(Q.id); });
				twRenderLearningPreview(questions, ids, false);
				if (res.warning) {
					$('#tw_learning_pick_msg').text(res.warning + ' Untick any you do not want.');
				} else {
					$('#tw_learning_pick_msg').text('Auto-picked ' + (res.found || ids.length) + ' question(s). Untick to remove, or use Browse to add more.');
				}
			},
			error: function () {
				$('#btn_tw_auto_pick').prop('disabled', false);
				$('#tw_learning_pick_msg').text('Request failed. Try again.');
			}
		});
	}

	function manualBrowseLearningQuestions() {
		var classID = $('#class_id').val();
		var sectionID = $('#section_id').val();
		var subjectID = twWorkSubjectId();
		var typeId = $('#tw_learning_qtype').val() || '';
		$('#tw_learning_pick_msg').text('');
		if (!classID || !sectionID || !subjectID) {
			$('#tw_learning_pick_msg').text('Select class, section and subject first.');
			return;
		}
		var keepSelected = twLearningSelectedIds();
		var data = {
			class_id: classID,
			section_id: sectionID,
			subject_id: subjectID,
			lesson_ids: []
		};
		if (typeId) {
			data.question_type = typeId;
		}
		<?php if (is_superadmin_loggedin()): ?>
		data.branch_id = $('#filter_branch_id').length ? $('#filter_branch_id').val() : ($('#branch_pick').length ? $('#branch_pick').val() : '');
		<?php endif; ?>
		$('#btn_tw_manual_pick').prop('disabled', true);
		$.ajax({
			url: base_url + 'digital_diary/ajax_today_work_questions',
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function (res) {
				$('#btn_tw_manual_pick').prop('disabled', false);
				if (!res || res.status !== 'success') {
					$('#tw_learning_pick_msg').text('Could not load questions for manual select.');
					return;
				}
				var questions = res.questions || [];
				twRenderLearningPreview(questions, keepSelected, false);
				$('#tw_learning_pick_msg').text(
					questions.length
						? ('Showing ' + questions.length + ' question(s) from all lessons. Tick to select, untick to remove.')
						: 'No questions found for this subject' + (typeId ? ' / type' : '') + '.'
				);
			},
			error: function () {
				$('#btn_tw_manual_pick').prop('disabled', false);
				$('#tw_learning_pick_msg').text('Request failed. Try again.');
			}
		});
	}

	function twSetModeButton(mode) {
		var $btn = $('#btn_tw_add');
		if (!$btn.length) return;
		if (mode === 'update') {
			$btn.text($btn.data('label-update') || 'Update');
			$btn.data('tw-mode', 'update');
		} else {
			$btn.text($btn.data('label-add') || 'Add');
			$btn.data('tw-mode', 'add');
		}
	}

	function clearTwAddFormFields() {
		$('#subject_id').removeData('pending-questions');
		$('#subject_id').removeData('pending-test');
		$('#subject_id').removeData('pending-lessons');
		$('#subject_id').removeData('pending-lesson');
		$('#subject_id').removeData('tw-fill-after');
		$('#tw_learning_is_test').prop('checked', false);
		if ($('#tw_cw').length) { $('#tw_cw').val(''); }
		if ($('#tw_hw').length) { $('#tw_hw').val(''); }
		$('#tw_cw_detail').val('');
		$('#tw_hw_detail').val('');
		$('#home_work_note').val('');
		$('#tw_cw_attachment').val('');
		$('#tw_hw_attachment').val('');
		$('#tw_cw_attachment_preview').empty();
		$('#tw_hw_attachment_preview').empty();
		if ($('#tw_learning_qtype').length) {
			$('#tw_learning_qtype').val('').trigger('change');
		}
		if ($('#tw_learning_qcount').length) {
			$('#tw_learning_qcount').val('5');
		}
		$('#tw_learning_pick_msg').text('');
		twClearLearningPreview();
		if ($('#cw_notebook_type_ids').length) {
			$('#cw_notebook_type_ids').val(null).trigger('change');
		}
		if ($('#hw_notebook_type_ids').length) {
			$('#hw_notebook_type_ids').val(null).trigger('change');
		}
		twSetModeButton('add');
	}

	function twAttachmentPreview(slot, filename) {
		var $box = $('#tw_' + slot + '_attachment_preview');
		$box.empty();
		if (!filename) return;
		var url = base_url + 'uploads/attachments/digital_diary_today_work/' + encodeURIComponent(filename);
		$box.html('<a href="' + url + '" target="_blank" class="btn btn-xs btn-link"><i class="fas fa-image"></i> View current</a>');
	}

	function fillTwMultiFromSaved(p) {
		if (!p) return;
		var cwFirst = '';
		var hwFirst = '';
		if (p.class_work_items) {
			var cws = String(p.class_work_items).split(',').map(function (k) {
				return $.trim(k);
			}).filter(Boolean);
			cwFirst = cws.length ? cws[0] : '';
		}
		if (p.home_work_items) {
			var hws = String(p.home_work_items).split(',').map(function (k) {
				return $.trim(k);
			}).filter(Boolean);
			hwFirst = hws.length ? hws[0] : '';
		}
		if ($('#tw_cw').length) {
			$('#tw_cw').val(cwFirst);
		}
		if ($('#tw_hw').length) {
			$('#tw_hw').val(hwFirst);
		}
		$('#tw_cw_detail').val(p.cw_detail || '');
		$('#tw_hw_detail').val(p.hw_detail || '');
		$('#home_work_note').val(p.home_work_note || '');
		twAttachmentPreview('cw', p.cw_attachment || '');
		twAttachmentPreview('hw', p.hw_attachment || '');
		if ($('#tw_learning_qtype').length && p.learning_qtype) {
			$('#tw_learning_qtype').val(String(p.learning_qtype)).trigger('change');
		}
		if ($('#tw_learning_qcount').length && p.learning_qcount) {
			var qc = String(p.learning_qcount);
			if ($('#tw_learning_qcount option[value="' + qc + '"]').length) {
				$('#tw_learning_qcount').val(qc);
			} else {
				$('#tw_learning_qcount').append($('<option>', { value: qc, text: qc }));
				$('#tw_learning_qcount').val(qc);
			}
		}
		if ($('#cw_notebook_type_ids').length) {
			var cwNb = [];
			if (p.cw_notebook_type_ids) {
				cwNb = String(p.cw_notebook_type_ids).split(',').map(function (x) {
					return $.trim(x);
				}).filter(Boolean);
			} else if (p.cw_notebook_type_id) {
				cwNb = [String(p.cw_notebook_type_id)];
			}
			$('#cw_notebook_type_ids').val(cwNb.length ? cwNb : null).trigger('change');
		}
		if ($('#hw_notebook_type_ids').length) {
			var hwNb = [];
			if (p.hw_notebook_type_ids) {
				hwNb = String(p.hw_notebook_type_ids).split(',').map(function (x) {
					return $.trim(x);
				}).filter(Boolean);
			} else if (p.hw_notebook_type_id) {
				hwNb = [String(p.hw_notebook_type_id)];
			}
			$('#hw_notebook_type_ids').val(hwNb.length ? hwNb : null).trigger('change');
		}
		$('#subject_id').data('pending-questions', p.learning_question_ids || '');
		$('#subject_id').data('pending-test', p.learning_is_test || 0);
	}

	function twSetPendingFromSaved(p) {
		$('#subject_id').removeData('pending-lesson');
		$('#subject_id').removeData('pending-lessons');
		if (p.lesson_ids) {
			$('#subject_id').data('pending-lessons', String(p.lesson_ids));
		} else if (p.lesson_id) {
			$('#subject_id').data('pending-lesson', String(p.lesson_id));
		}
		$('#subject_id').data('pending-questions', p.learning_question_ids || '');
		$('#subject_id').data('pending-test', p.learning_is_test || 0);
		$('#subject_id').data('tw-fill-after', function () {
			fillTwMultiFromSaved(p);
			window._twPrefillActive = false;
		});
	}

	function twShowEditPanel() {
		$('#tw_edit_hint').hide();
		$('#tw_edit_panel').show();
	}

	function twHideEditPanel() {
		$('#tw_edit_panel').hide();
		$('#tw_edit_hint').show();
		clearTwAddFormFields();
		$('#tw_lesson_id').empty();
		twReinitSelect2($('#tw_lesson_id'));
		twClearLearningPreview();
		syncAddFormSubjectId($('#subject_id').val() || '');
		twSetModeButton('add');
	}

	/** Empty Add form — uses subject from filter or row (+) action. */
	function openTwAddForm(preferredSubjectId) {
		preferredSubjectId = preferredSubjectId ? String(preferredSubjectId) : String($('#subject_id').val() || '');
		// Do not preselect a subject that already has work
		if (preferredSubjectId && twSavedEntries[preferredSubjectId]) {
			preferredSubjectId = '';
		}
		window._twPrefillActive = false;
		window._twIgnoreSubjectChange = true;
		$('#subject_id').removeData('pending-lesson');
		$('#subject_id').removeData('pending-lessons');
		$('#subject_id').removeData('pending-questions');
		$('#subject_id').removeData('pending-test');
		$('#subject_id').removeData('tw-fill-after');
		twShowEditPanel();
		twSetModeButton('add');
		clearTwAddFormFields();
		syncTwFormSubjectOptions(preferredSubjectId, 'add');
		window._twIgnoreSubjectChange = false;
		if (!preferredSubjectId) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({ icon: 'info', title: 'Select a subject in the filter (or use + on a subject row).' });
			}
			twHideEditPanel();
			return;
		}
		loadLessonsForTodayWork(false);
	}

	/** Open form on Edit: prefill if saved, else empty Add for that subject. */
	function openTwEditForm(subjectId) {
		subjectId = String(subjectId || '');
		if (!subjectId) {
			return;
		}
		if (twSavedEntries[subjectId]) {
			applyTwPrefill(subjectId);
		} else {
			openTwAddForm(subjectId);
		}
	}

	/** Edit only: load saved Today's Work into the Add form. */
	function applyTwPrefill(subjectId) {
		subjectId = String(subjectId || '');
		if (!subjectId || !twSavedEntries[subjectId]) {
			twSetModeButton('add');
			return;
		}
		twShowEditPanel();
		var p = twSavedEntries[subjectId];
		twSetModeButton('update');
		window._twPrefillActive = true;
		window._twIgnoreSubjectChange = true;
		twSetPendingFromSaved(p);
		// Edit mode: include the current (already saved) subject in the list
		syncTwFormSubjectOptions(subjectId, 'all');
		syncAddFormSubjectId();

		var runLessons = function () {
			syncAddFormSubjectId();
			loadLessonsForTodayWork(false);
			window._twIgnoreSubjectChange = false;
		};

		var $subj = $('#subject_id');
		if ($subj.find('option[value="' + subjectId + '"]').length) {
			runLessons();
			return;
		}
		loadSubjectsForTodayWork($('#class_id').val(), $('#section_id').val(), '', function () {
			window._twIgnoreSubjectChange = true;
			twSetPendingFromSaved(p);
			syncTwFormSubjectOptions(subjectId, 'all');
			runLessons();
		});
	}

	$(document).ready(function () {
		if (!$('#frm_today_work_filter').length) {
			return;
		}
		var classID = '<?= (int) $filter_class_id ?>';
		var sectionID = '<?= (int) $filter_section_id ?>';
		var subjectID = '<?= (int) $filter_subject_id ?>';
		var pageFilter = {
			date: String($('#date').val() || ''),
			class_id: String(classID || ''),
			section_id: String(sectionID || ''),
			subject_group: String($('#subject_group').val() || ''),
			subject_id: String(subjectID > 0 ? subjectID : '')
		};
		var twSubmitting = false;
		var twReadyAt = Date.now() + 700;
		var twPendingClassReload = false;

		function twNorm(v) {
			return (v == null || v === '0') ? '' : String(v);
		}
		function twSubmitFilter(override) {
			if (Date.now() < twReadyAt || twSubmitting || window._twIgnoreSubjectChange || window._twPrefillActive) {
				return;
			}
			var cur = {
				date: twNorm(override && override.date != null ? override.date : $('#date').val()),
				class_id: twNorm(override && override.class_id != null ? override.class_id : $('#class_id').val()),
				section_id: twNorm(override && override.section_id != null ? override.section_id : $('#section_id').val()),
				subject_group: twNorm(override && override.subject_group != null ? override.subject_group : $('#subject_group').val()),
				subject_id: twNorm(override && override.subject_id != null ? override.subject_id : $('#subject_id').val())
			};
			if (
				cur.date === twNorm(pageFilter.date) &&
				cur.class_id === twNorm(pageFilter.class_id) &&
				cur.section_id === twNorm(pageFilter.section_id) &&
				cur.subject_group === twNorm(pageFilter.subject_group) &&
				cur.subject_id === twNorm(pageFilter.subject_id)
			) {
				return;
			}
			if (!cur.date || !cur.class_id || !cur.section_id) {
				return;
			}
			twSubmitting = true;
			$('#date').val(cur.date);
			if ($('#tw_work_date').length) {
				$('#tw_work_date').val(cur.date);
			}
			var form = document.getElementById('frm_today_work_filter');
			if (form) {
				form.submit();
			}
		}
		// Filter only: load subject list for filter. Do NOT open / fill the work form.
		if (classID && sectionID) {
			window._twIgnoreSubjectChange = true;
			loadSubjectsForTodayWork(classID, sectionID, subjectID || '', function () {
				window._twIgnoreSubjectChange = false;
				twHideEditPanel();
			});
		} else {
			twHideEditPanel();
		}
		$(document).ajaxComplete(function (event, xhr, settings) {
			if (!settings.url || settings.url.indexOf('ajax/getSectionByClass') === -1) {
				return;
			}
			if (!twPendingClassReload) {
				return;
			}
			twPendingClassReload = false;
			var $sec = $('#section_id');
			var opts = $sec.find('option').filter(function () {
				return String($(this).val() || '') !== '';
			});
			if (opts.length === 1) {
				$sec.val(String(opts.first().val())).trigger('change');
			}
		});
		$('#class_id').on('change', function () {
			twPendingClassReload = true;
			window._twIgnoreSubjectChange = true;
			loadSubjectsForTodayWork($(this).val(), '', '', function () {
				window._twIgnoreSubjectChange = false;
			});
			twHideEditPanel();
		});
		$('#section_id').on('change', function () {
			var sid = $(this).val();
			if ($('#class_id').val() && sid) {
				// Reset subject filter when class/section changes
				window._twIgnoreSubjectChange = true;
				$('#subject_id').val('').trigger('change');
				window._twIgnoreSubjectChange = false;
				twSubmitFilter({
					class_id: $('#class_id').val(),
					section_id: sid,
					subject_id: ''
				});
				return;
			}
			window._twIgnoreSubjectChange = true;
			loadSubjectsForTodayWork($('#class_id').val(), sid || '', '', function () {
				window._twIgnoreSubjectChange = false;
			});
			twHideEditPanel();
		});
		$('#date').on('changeDate', function (e) {
			var v = '';
			if (e && typeof e.format === 'function') {
				try { v = e.format('yyyy-mm-dd'); } catch (err) { v = e.format(); }
			}
			if (!v) {
				try { v = $(this).datepicker('getFormattedDate'); } catch (err2) {}
			}
			if (!v) {
				v = $(this).val();
			}
			if (v) {
				$(this).val(v);
			}
			twSubmitFilter({ date: v });
		});
		$('#subject_group').on('change', function () {
			twSubmitFilter({ subject_group: $(this).val() });
		});
		$('#subject_id').on('change', function () {
			syncAddFormSubjectId();
			if (window._twIgnoreSubjectChange || window._twPrefillActive) {
				return;
			}
			twHideEditPanel();
			twSubmitFilter({ subject_id: $(this).val() });
		});
		// Lesson(s) above are for class/home work + syllabus only.
		// Learning work Question Bank uses all lessons — do not reload on CW/HW lesson change.

		$(document).on('click', '.tw-edit-row', function (e) {
			e.preventDefault();
			var sid = $(this).attr('data-subject') || $(this).data('subject');
			if (sid) {
				openTwEditForm(String(sid));
			}
			if ($('#tw_edit_panel').length && $('#tw_edit_panel').is(':visible')) {
				$('html, body').animate({ scrollTop: $('#tw_edit_panel').offset().top - 80 }, 300);
			}
		});

		$(document).on('click', '#btn_tw_auto_pick', function (e) {
			e.preventDefault();
			autoPickLearningQuestions();
		});

		$(document).on('click', '#btn_tw_manual_pick', function (e) {
			e.preventDefault();
			manualBrowseLearningQuestions();
		});

		$(document).on('click', '#btn_tw_q_select_all', function (e) {
			e.preventDefault();
			$('#tw_learning_preview_list input.tw-lq-check').prop('checked', true);
			twUpdateLearningSelectedCount();
		});

		$(document).on('click', '#btn_tw_q_unselect_all', function (e) {
			e.preventDefault();
			$('#tw_learning_preview_list input.tw-lq-check').prop('checked', false);
			twUpdateLearningSelectedCount();
		});

		$(document).on('change', '#tw_learning_preview_list input.tw-lq-check', function () {
			twUpdateLearningSelectedCount();
		});

		$(document).on('click', '.tw-add-row', function (e) {
			e.preventDefault();
			var sid = $(this).attr('data-subject') || $(this).data('subject');
			openTwAddForm(sid ? String(sid) : '');
			if ($('#tw_edit_panel').length && $('#tw_edit_panel').is(':visible')) {
				$('html, body').animate({ scrollTop: $('#tw_edit_panel').offset().top - 80 }, 300);
			}
		});

		$(document).on('click', '#btn_tw_cancel_edit', function (e) {
			e.preventDefault();
			twHideEditPanel();
		});

		// Do not auto-fill after Add — user clicks Edit when they want to change a saved row
		twKeepSubjectId = '0';

		var twAddForm = document.getElementById('frm_today_work_add');
		if (twAddForm) {
			twAddForm.addEventListener(
				'submit',
				function (e) {
					syncAddFormSubjectId();
					var hid = document.getElementById('add_work_subject_id');
					if (hid && !String(hid.value || '').trim()) {
						e.preventDefault();
						e.stopImmediatePropagation();
						if (typeof Swal !== 'undefined') {
							Swal.fire({ icon: 'warning', title: '<?= translate('select') ?> <?= translate('subject') ?>' });
						} else {
							alert('<?= translate('select') ?> <?= translate('subject') ?>');
						}
					}
				},
				true
			);
		}
	});
</script>

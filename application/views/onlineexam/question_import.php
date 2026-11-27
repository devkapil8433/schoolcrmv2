<style type="text/css">
	.qi-import {
		--qi-ink: #1e293b;
		--qi-muted: #64748b;
		--qi-line: #e2e8f0;
		--qi-soft: #f8fafc;
		--qi-accent: #0088cc;
		--qi-accent-soft: #e8f6fc;
		--qi-ok: #0f766e;
		--qi-ok-soft: #ecfdf5;
	}
	.qi-import .panel { border: 1px solid var(--qi-line); box-shadow: none; }
	.qi-import .panel-heading {
		background: #fff;
		border-bottom: 1px solid var(--qi-line);
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		flex-wrap: wrap;
	}
	.qi-import .panel-heading h4 {
		margin: 0;
		color: var(--qi-ink);
		font-weight: 600;
	}
	.qi-import .qi-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

	.qi-hero {
		background:
			radial-gradient(1200px 280px at 10% -40%, rgba(0, 136, 204, 0.18), transparent 60%),
			linear-gradient(180deg, #f0f9fd 0%, #fff 70%);
		border: 1px solid var(--qi-line);
		border-radius: 10px;
		padding: 22px 24px;
		margin-bottom: 18px;
		animation: qiFadeUp .45s ease both;
	}
	.qi-hero h3 {
		margin: 0 0 6px;
		font-size: 20px;
		font-weight: 700;
		color: var(--qi-ink);
		letter-spacing: -0.02em;
	}
	.qi-hero p {
		margin: 0;
		color: var(--qi-muted);
		max-width: 52ch;
		line-height: 1.5;
	}
	.qi-steps {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin-top: 16px;
	}
	.qi-step {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		background: #fff;
		border: 1px solid var(--qi-line);
		border-radius: 999px;
		padding: 6px 12px 6px 6px;
		font-size: 12px;
		font-weight: 600;
		color: var(--qi-ink);
	}
	.qi-step span {
		width: 22px;
		height: 22px;
		border-radius: 50%;
		background: var(--qi-accent);
		color: #fff;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 11px;
	}

	.qi-layout {
		display: grid;
		grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
		gap: 18px;
		align-items: start;
	}
	@media (max-width: 991px) {
		.qi-layout { grid-template-columns: 1fr; }
	}

	.qi-card {
		background: #fff;
		border: 1px solid var(--qi-line);
		border-radius: 10px;
		overflow: hidden;
		animation: qiFadeUp .5s ease both;
	}
	.qi-card:nth-child(2) { animation-delay: .06s; }
	.qi-card-head {
		padding: 14px 18px;
		border-bottom: 1px solid var(--qi-line);
		background: var(--qi-soft);
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
	}
	.qi-card-head h5 {
		margin: 0;
		font-size: 14px;
		font-weight: 700;
		color: var(--qi-ink);
	}
	.qi-card-head small {
		color: var(--qi-muted);
		font-weight: 500;
	}
	.qi-card-body { padding: 18px; }

	.qi-sample {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		flex-wrap: wrap;
		padding: 14px 16px;
		border-radius: 8px;
		background: var(--qi-accent-soft);
		border: 1px dashed rgba(0, 136, 204, 0.35);
		margin-bottom: 18px;
	}
	.qi-sample strong {
		display: block;
		color: var(--qi-ink);
		font-size: 13px;
		margin-bottom: 2px;
	}
	.qi-sample span {
		color: var(--qi-muted);
		font-size: 12px;
	}

	.qi-fields {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 14px 16px;
	}
	.qi-fields .qi-field-full { grid-column: 1 / -1; }
	@media (max-width: 640px) {
		.qi-fields { grid-template-columns: 1fr; }
	}
	.qi-fields .form-group { margin-bottom: 0; }
	.qi-lesson-hint {
		display: block;
		margin-top: 6px;
		color: var(--qi-muted);
		font-size: 11px;
		line-height: 1.5;
	}
	.qi-lesson-hint code {
		font-size: 10px;
		background: var(--qi-soft);
		border: 1px solid var(--qi-line);
		border-radius: 4px;
		padding: 0 4px;
	}
	.qi-fields .portal-label {
		display: block;
		margin-bottom: 6px;
		font-size: 12px;
		font-weight: 600;
		color: var(--qi-ink);
	}
	.qi-fields .portal-input,
	.qi-fields .select2-container { width: 100% !important; }

	.qi-footer {
		margin-top: 18px;
		padding-top: 16px;
		border-top: 1px solid var(--qi-line);
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		flex-wrap: wrap;
	}
	.qi-footer .qi-hint {
		color: var(--qi-muted);
		font-size: 12px;
		margin: 0;
	}
	.qi-footer .btn-qi-import {
		min-width: 160px;
		font-weight: 600;
	}

	.qi-type-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
		margin-bottom: 14px;
	}
	@media (max-width: 480px) {
		.qi-type-grid { grid-template-columns: 1fr; }
	}
	.qi-type {
		border: 1px solid var(--qi-line);
		border-radius: 8px;
		padding: 10px 12px;
		background: #fff;
		transition: border-color .2s ease, transform .2s ease, background .2s ease;
	}
	.qi-type:hover {
		border-color: rgba(0, 136, 204, 0.45);
		background: var(--qi-accent-soft);
		transform: translateY(-1px);
	}
	.qi-type code {
		display: block;
		font-size: 11px;
		font-weight: 700;
		color: var(--qi-accent);
		margin-bottom: 2px;
		background: transparent;
		padding: 0;
	}
	.qi-type em {
		font-style: normal;
		font-size: 11px;
		color: var(--qi-muted);
	}

	.qi-rules {
		list-style: none;
		margin: 0;
		padding: 0;
	}
	.qi-rules li {
		display: flex;
		gap: 10px;
		padding: 10px 0;
		border-top: 1px solid var(--qi-line);
		font-size: 12px;
		line-height: 1.45;
		color: var(--qi-ink);
	}
	.qi-rules li:first-child { border-top: 0; padding-top: 0; }
	.qi-rules i {
		color: var(--qi-ok);
		margin-top: 2px;
		flex: 0 0 auto;
	}
	.qi-rules code {
		font-size: 11px;
		background: var(--qi-soft);
		border: 1px solid var(--qi-line);
		border-radius: 4px;
		padding: 1px 5px;
		color: #0f172a;
	}

	.qi-note {
		margin-top: 14px;
		padding: 12px 14px;
		border-radius: 8px;
		background: var(--qi-ok-soft);
		border: 1px solid #a7f3d0;
		color: var(--qi-ok);
		font-size: 12px;
		line-height: 1.45;
	}

	@keyframes qiFadeUp {
		from { opacity: 0; transform: translateY(8px); }
		to { opacity: 1; transform: translateY(0); }
	}
</style>

<div class="row qi-import">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2">
					<i class="fas fa-file-csv"></i> <?=translate('question')?> <?=translate('import')?>
				</h4>
				<div class="qi-actions">
					<a href="javascript:void(0);" onclick="window.history.back();" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-arrow-left"></i> <?=translate('back')?>
					</a>
					<a href="<?=base_url('onlineexam/question')?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-list"></i> <?=translate('question') . " " . translate('list')?>
					</a>
				</div>
			</header>
			<div class="panel-body">
				<?php if ($this->session->flashdata('csvimport')): ?>
					<div class="alert alert-danger"><?php echo $this->session->flashdata('csvimport'); ?></div>
				<?php endif; ?>

				<div class="qi-hero">
					<h3>Bulk import questions from CSV</h3>
					<p>Download the sample, fill in one question per row, keep the header row, and upload the CSV. Choose the target class, section, and subject before importing.</p>
					<div class="qi-steps">
						<div class="qi-step"><span>1</span> Download sample</div>
						<div class="qi-step"><span>2</span> Choose class, subject &amp; lesson</div>
						<div class="qi-step"><span>3</span> Upload CSV &amp; import</div>
					</div>
				</div>

				<?php echo form_open_multipart('onlineexam/questionCsvImport', array('class' => 'frm-submit-data', 'id' => 'qiImportForm')); ?>
				<div class="qi-layout">
					<div class="qi-card">
						<div class="qi-card-head">
							<div>
								<h5><i class="fas fa-cloud-arrow-up text-muted"></i> Import setup</h5>
								<small>Target class &amp; CSV file</small>
							</div>
						</div>
						<div class="qi-card-body">
							<div class="qi-sample">
								<div>
									<strong><i class="fas fa-file-arrow-down"></i> Sample import file</strong>
									<span>Includes all 11 question types, lesson number, lesson, exercise, and explanation columns.</span>
								</div>
								<a class="btn-portal-primary btn-portal-sm" href="<?=base_url('onlineexam/csv_Sampledownloader')?>">
									<i class="fas fa-download"></i> Download CSV
								</a>
							</div>

							<div class="qi-fields">
								<?php if (is_superadmin_loggedin()): ?>
								<div class="qi-field-full form-group">
									<label class="portal-label"><?php echo translate('branch');?> <span class="required">*</span></label>
									<?php
										$arrayBranch = $this->app_lib->getSelectList('branch');
										echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='portal-input' onchange='getClassByBranch(this.value)'
										data-plugin-selectTwo data-width='100%'");
									?>
									<span class="error"><?=form_error('branch_id')?></span>
								</div>
								<?php endif; ?>

								<div class="form-group">
									<label class="portal-label"><?=translate('class')?> <span class="required">*</span></label>
									<?php
										$arrayClass = $this->app_lib->getClass($branch_id);
										echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='portal-input' id='class_id' onchange='getSectionByClass(this.value,0)'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>

								<div class="form-group">
									<label class="portal-label"><?=translate('section')?> <span class="required">*</span></label>
									<?php
										$arraySection = $this->app_lib->getSections(set_value('class_id'));
										echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='portal-input' id='section_id'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
									?>
									<span class="error"></span>
								</div>

								<div class="qi-field-full form-group">
									<label class="portal-label"><?=translate('subject')?> <span class="required">*</span></label>
									<?php
										$arraySubject = array("" => translate('select_class_first'));
										echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='portal-input' id='subject_id'
										data-plugin-selectTwo data-width='100%' ");
									?>
									<span class="error"></span>
								</div>

								<div class="qi-field-full form-group">
									<label class="portal-label"><?=translate('lesson')?></label>
									<?php
										$arrayLesson = array("" => translate('select') . ' (' . translate('lesson') . ')');
										echo form_dropdown("lesson_id", $arrayLesson, set_value('lesson_id'), "class='portal-input' id='lesson_id'
										data-plugin-selectTwo data-width='100%' ");
									?>
									<span class="error"></span>
									<small class="qi-lesson-hint">Choose one lesson to attach every CSV row to it, or leave this blank and enter each row's Digital Diary lesson number in the <code>lesson_no</code> column. The sample leaves lesson fields blank so you can use the dropdown.</small>
								</div>

								<div class="qi-field-full form-group">
									<label class="portal-label">CSV File <span class="required">*</span></label>
									<input type="file" name="userfile" class="dropify" data-height="120" data-allowed-file-extensions="csv" data-default-file="" />
									<span class="error"></span>
								</div>
							</div>

							<div class="qi-footer">
								<p class="qi-hint"><i class="fas fa-circle-info"></i> Only <code>.csv</code> files are accepted.</p>
								<button type="submit" class="btn-portal-primary btn-qi-import" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Importing…">
									<i class="fas fa-file-import"></i> <?=translate('import')?>
								</button>
							</div>
						</div>
					</div>

					<div class="qi-card">
						<div class="qi-card-head">
							<div>
								<h5><i class="fas fa-book-open text-muted"></i> Format guide</h5>
								<small>Type codes &amp; answer rules</small>
							</div>
						</div>
						<div class="qi-card-body">
							<div class="qi-type-grid">
								<div class="qi-type"><code>single_choice</code><em>1 correct option</em></div>
								<div class="qi-type"><code>multi_choice</code><em>Multiple correct</em></div>
								<div class="qi-type"><code>true_false</code><em>TRUE / FALSE</em></div>
								<div class="qi-type"><code>descriptive</code><em>Legacy text</em></div>
								<div class="qi-type"><code>match</code><em>Left|Right pairs</em></div>
								<div class="qi-type"><code>reading</code><em>Comprehension</em></div>
								<div class="qi-type"><code>writing</code><em>Long writing</em></div>
								<div class="qi-type"><code>fill_blanks</code><em>Fill blanks</em></div>
								<div class="qi-type"><code>very_short</code><em>Very short</em></div>
								<div class="qi-type"><code>short</code><em>Short answer</em></div>
								<div class="qi-type"><code>long</code><em>Long answer</em></div>
							</div>

							<ul class="qi-rules">
								<li><i class="fas fa-check"></i><span><strong>Level:</strong> <code>easy</code>, <code>medium</code>, <code>hard</code></span></li>
								<li><i class="fas fa-check"></i><span><strong>Choose one lesson method:</strong> select a Lesson above to attach every sample row to it, or leave the dropdown blank and fill <code>lesson_no</code> for each row. If you also enter <code>lesson</code>, it must match that lesson.</span></li>
								<li><i class="fas fa-check"></i><span><strong>Lesson number:</strong> when no lesson is selected above, enter the Lesson No. shown in Digital Diary for the selected class, section, and subject to place each row under its own lesson.</span></li>
								<li><i class="fas fa-check"></i><span><strong>Lesson:</strong> optional extra check; enter the exact lesson name or lesson ID. If both Lesson No. and Lesson are filled, both must match the same lesson.</span></li>
								<li><i class="fas fa-check"></i><span><strong>Exercise:</strong> optional; enter the exact exercise name or exercise ID for the selected class and subject. Leave blank if not applicable.</span></li>
								<li><i class="fas fa-check"></i><span><strong>Single:</strong> answer <code>option_1</code> … <code>option_4</code></span></li>
								<li><i class="fas fa-check"></i><span><strong>Multi:</strong> <code>["option_1","option_3"]</code></span></li>
								<li><i class="fas fa-check"></i><span><strong>Match:</strong> options as <code>Left|Right</code>; answer <code>1-1,2-2,3-3,4-4</code></span></li>
								<li><i class="fas fa-check"></i><span><strong>Text types:</strong> leave options empty; put the model answer in the answer column.</span></li>
								<li><i class="fas fa-check"></i><span><strong>Explanation:</strong> optional; add the answer explanation in the last column.</span></li>
							</ul>

							<div class="qi-note">
								<i class="fas fa-lightbulb"></i>
								The selected Branch, Class, Section, and Subject apply to every CSV row. Fill Lesson No. to place each question under a lesson, then choose its question type and exercise. Keep the sample header unchanged and save as CSV UTF-8. Blank rows are ignored; unmatched lesson/exercise values are skipped.
							</div>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>
			</div>
		</section>
	</div>
</div>

<script type="text/javascript">
	var qiAllLessons = <?= json_encode(isset($all_lessons) ? $all_lessons : array()) ?>;
	(function ($) {
		function qiRenderLessons() {
			var classID = $('#class_id').val();
			var sectionID = $('#section_id').val();
			var subjectID = $('#subject_id').val();
			var $lesson = $('#lesson_id');
			var key = (classID || '') + '|' + (sectionID || '') + '|' + (subjectID || '');
			var list = qiAllLessons[key] || [];
			var html = '<option value=""><?= translate('select') ?> (<?= translate('lesson') ?>)</option>';
			if (!classID || !sectionID || !subjectID) {
				html = '<option value="">Select subject first</option>';
			} else if (list.length === 0) {
				html = '<option value="">No lessons found for this subject</option>';
			} else {
				$.each(list, function (i, item) {
					html += '<option value="' + item.id + '">' + $('<div>').text(item.label).html() + '</option>';
				});
			}
			$lesson.html(html);
			if ($lesson.hasClass('select2-hidden-accessible')) {
				$lesson.trigger('change.select2');
			}
		}

		function qiLoadSubjects() {
			var classID = $('#class_id').val();
			var sectionID = $('#section_id').val();
			if (!classID || !sectionID) {
				$('#subject_id').html('<option value="">Select class &amp; section first</option>').trigger('change');
				qiRenderLessons();
				return;
			}
			$.ajax({
				url: base_url + 'subject/getByClassSection',
				type: 'POST',
				data: { classID: classID, sectionID: sectionID },
				success: function (data) {
					$('#subject_id').html(data);
					if ($('#subject_id').hasClass('select2-hidden-accessible')) {
						$('#subject_id').trigger('change.select2');
					}
					qiRenderLessons();
				}
			});
		}

		$(document).on('change', '#section_id', qiLoadSubjects);
		$(document).on('change', '#class_id', function () {
			window.setTimeout(qiLoadSubjects, 400);
		});
		$(document).on('change', '#subject_id', qiRenderLessons);
		if ($('#section_id').val()) {
			qiLoadSubjects();
		} else {
			qiRenderLessons();
		}
	})(jQuery);
</script>

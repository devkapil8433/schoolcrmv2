<?php
	$work = isset($work) && is_array($work) ? $work : array();
	$assignments = isset($assignments) && is_array($assignments) ? $assignments : array();
	$hwSub = isset($hw_submission) ? $hw_submission : null;
	$learnSub = isset($learning_submission) ? $learning_submission : null;
?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-book-open"></i> <?= html_escape(isset($work['subject_name']) ? $work['subject_name'] : "Today's Work") ?></h4>
		<div class="panel-btn">
			<a href="<?= base_url('userrole/today_work?date=' . urlencode(isset($work['work_date']) ? $work['work_date'] : date('Y-m-d'))) ?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> <?= translate('back') ?></a>
		</div>
	</header>
	<div class="panel-body">
		<p><strong><?= translate('date') ?>:</strong> <?= html_escape(isset($work['work_date']) ? $work['work_date'] : '') ?></p>
		<?php if (!empty($lesson_label)): ?>
		<p><strong>Lesson:</strong> <?= html_escape($lesson_label) ?></p>
		<?php endif; ?>
		<p><strong>Class work:</strong> <?= html_escape(isset($class_work_display) ? $class_work_display : '—') ?>
			<?php if (!empty($work['cw_attachment'])): ?>
				<a href="<?= base_url('uploads/attachments/digital_diary_today_work/' . rawurlencode($work['cw_attachment'])) ?>" target="_blank" class="btn btn-xs btn-link"><i class="fas fa-image"></i> Teacher photo</a>
			<?php endif; ?>
		</p>
		<p><strong>Home work:</strong> <?= html_escape(isset($home_work_display) ? $home_work_display : '—') ?>
			<?php if (!empty($work['home_work_note'])): ?> — <?= html_escape($work['home_work_note']) ?><?php endif; ?>
			<?php if (!empty($work['hw_attachment'])): ?>
				<a href="<?= base_url('uploads/attachments/digital_diary_today_work/' . rawurlencode($work['hw_attachment'])) ?>" target="_blank" class="btn btn-xs btn-link"><i class="fas fa-image"></i> Teacher photo</a>
			<?php endif; ?>
		</p>

		<hr>
		<h4><i class="fas fa-microphone"></i> Learning work — Practice aloud</h4>
		<p class="text-muted">Read / speak the questions below to remember them, then mark Learning as Done.</p>
		<?php if (empty($assignments)): ?>
			<p class="text-muted">No learning assignments for this subject today.</p>
		<?php else: ?>
			<ol>
				<?php foreach ($assignments as $a): ?>
				<li class="mb-sm">
					<span class="label label-default"><?= html_escape(isset($a['type_label']) ? $a['type_label'] : '') ?></span>
					<?= html_escape(isset($a['question_preview']) ? $a['question_preview'] : '') ?>
					<?php if (!empty($a['is_test'])): ?><span class="label label-warning">Test</span><?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ol>
			<?php if ($learnSub): ?>
				<div class="alert alert-success"><i class="fas fa-check"></i> Learning submitted (<?= html_escape($learnSub['status']) ?>).</div>
			<?php else: ?>
				<?php echo form_open(base_url('userrole/today_work_view/' . (int) $work['id'])); ?>
					<input type="hidden" name="submit_type" value="learning">
					<div class="form-group">
						<label>Note (optional)</label>
						<input type="text" name="message" class="form-control" placeholder="I practiced these aloud">
					</div>
					<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Mark Learning Done</button>
				<?php echo form_close(); ?>
			<?php endif; ?>
		<?php endif; ?>

		<hr>
		<h4><i class="fas fa-camera"></i> Home work — Upload photo</h4>
		<?php if ($hwSub): ?>
			<div class="alert alert-success">
				<i class="fas fa-check"></i> Homework submitted (<?= html_escape($hwSub['status']) ?>).
				<?php if (!empty($hwSub['photo'])): ?>
					<a href="<?= base_url('uploads/attachments/digital_diary_submit/' . rawurlencode($hwSub['photo'])) ?>" target="_blank">View photo</a>
				<?php endif; ?>
			</div>
		<?php else: ?>
			<?php echo form_open_multipart(base_url('userrole/today_work_view/' . (int) $work['id'])); ?>
				<input type="hidden" name="submit_type" value="homework">
				<div class="form-group">
					<label>Message</label>
					<input type="text" name="message" class="form-control" placeholder="Homework completed">
				</div>
				<div class="form-group">
					<label>Photo <span class="required">*</span></label>
					<input type="file" name="photo" class="form-control" accept="image/*,application/pdf" required>
				</div>
				<button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Submit Homework</button>
			<?php echo form_close(); ?>
		<?php endif; ?>
	</div>
</section>

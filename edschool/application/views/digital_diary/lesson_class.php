<?php
	$lesson_url = base_url('digital_diary/lesson') . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '');
	$branch_query = (!empty($branch_id)) ? '?branch_id=' . urlencode($branch_id) : '';
?>
<div class="row">
	<div class="col-md-12">
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body">
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
				<a href="<?=$lesson_url?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
			</div>
		</section>
		<?php endif; ?>

		<section class="panel" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list"></i> Lesson — <?=html_escape($class_name)?> - <?=html_escape($section_name)?></h4>
				<div class="panel-btn">
					<a href="<?=$lesson_url?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> Back</a>
				</div>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th><?=translate('sl')?></th>
								<th><?=translate('class')?></th>
								<th><?=translate('section')?></th>
								<th>Subject Group</th>
								<th>Subject</th>
								<th>Total Lesson</th>
								<th><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if (!empty($subject_summary)):
								$sn = 1;
								foreach ($subject_summary as $row):
									$view_url = base_url('digital_diary/lesson_subject/' . $row['class_id'] . '/' . $row['section_id'] . '/' . $row['subject_id']) . $branch_query;
							?>
							<tr>
								<td><?=$sn++?></td>
								<td><?=html_escape($row['class_name'])?></td>
								<td><?=html_escape($row['section_name'])?></td>
								<td><?=html_escape($row['subject_group'])?></td>
								<td><?=html_escape($row['subject_name'])?></td>
								<td><?=(int)$row['total_lesson']?></td>
								<td>
									<a href="<?=$view_url?>" class="btn btn-circle btn-default btn-sm" title="View Lessons"><i class="fas fa-arrow-right"></i> View Lessons</a>
								</td>
							</tr>
							<?php
								endforeach;
							else:
							?>
							<tr>
								<td colspan="7" class="text-center text-muted">No subjects assigned to this class/section.</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>

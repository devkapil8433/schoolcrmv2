<?php
	$lesson_url = base_url('digital_diary/lesson') . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '');
	$branch_query = (!empty($branch_id)) ? '?branch_id=' . urlencode($branch_id) : '';
?>
?>
<style type="text/css">
	.subject-card {
		background: #fff;
		border-radius: 12px;
		padding: 20px;
		margin-bottom: 20px;
		transition: all 0.3s ease;
		border: 1px solid #eee;
		position: relative;
		overflow: hidden;
		display: block;
		text-decoration: none !important;
		color: inherit !important;
		box-shadow: 0 4px 6px rgba(0,0,0,0.02);
	}
	.subject-card:hover {
		transform: translateY(-5px);
		box-shadow: 0 12px 20px rgba(0,0,0,0.08);
		border-color: #2c7ba2;
	}
	.subject-card .icon-box {
		width: 50px;
		height: 50px;
		border-radius: 10px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 20px;
		margin-bottom: 15px;
	}
	.bg-orange { background: #fff3e0; color: #ff9800; }
	.bg-blue { background: #e3f2fd; color: #2196f3; }
	.bg-green { background: #e8f5e9; color: #4caf50; }
	.bg-purple { background: #f3e5f5; color: #9c27b0; }

	.subject-name {
		font-size: 16px;
		font-weight: 700;
		margin-bottom: 5px;
		display: block;
	}
	.subject-group {
		font-size: 12px;
		color: #888;
		display: block;
		margin-bottom: 15px;
	}
	.lesson-count-badge {
		background: #f8f9fa;
		padding: 4px 10px;
		border-radius: 20px;
		font-size: 12px;
		font-weight: 600;
		color: #555;
	}
	.view-btn-circle {
		position: absolute;
		right: 20px;
		bottom: 20px;
		width: 35px;
		height: 35px;
		border-radius: 50%;
		background: #f0f0f0;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #888;
		transition: all 0.2s;
	}
	.subject-card:hover .view-btn-circle {
		background: #0088cc;
		color: #fff;
	}
</style>
<div class="row">
	<div class="col-md-12">
		<section class="panel mb-md">
			<div class="panel-body">
				<div class="pull-left">
					<h4 class="mt-none mb-none" style="font-weight: 700;">
						<i class="fas fa-university text-primary mr-xs"></i> 
						<?=html_escape($class_name)?> - <?=html_escape($section_name)?>
					</h4>
					<p class="text-muted mb-none">Select a subject to manage its lessons</p>
				</div>
				<div class="pull-right">
					<a href="<?=$lesson_url?>" class="btn btn-default btn-circle"><i class="fas fa-arrow-left"></i> <?=translate('back')?></a>
				</div>
			</div>
		</section>

		<div class="row">
			<?php
			if (!empty($subject_summary)):
				$colors = ['bg-orange', 'bg-blue', 'bg-green', 'bg-purple'];
				foreach ($subject_summary as $index => $row):
					$color_class = $colors[$index % 4];
					$view_url = base_url('digital_diary/lesson_subject/' . $row['class_id'] . '/' . $row['section_id'] . '/' . $row['subject_id']) . $branch_query;
			?>
			<div class="col-md-4 col-sm-6">
				<a href="<?=$view_url?>" class="subject-card">
					<div class="icon-box <?=$color_class?>">
						<i class="fas fa-book-open"></i>
					</div>
					<span class="subject-name"><?=html_escape($row['subject_name'])?></span>
					<span class="subject-group"><?=html_escape($row['subject_group'] ? $row['subject_group'] : 'Core Subject')?></span>
					
					<div class="d-flex align-items-center">
						<span class="lesson-count-badge">
							<i class="far fa-file-alt mr-xs"></i> <?=(int)$row['total_lesson']?> Lessons
						</span>
					</div>

					<div class="view-btn-circle">
						<i class="fas fa-chevron-right"></i>
					</div>
				</a>
			</div>
			<?php
				endforeach;
			else:
			?>
			<div class="col-md-12">
				<div class="alert alert-info text-center">No subjects assigned to this class/section.</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

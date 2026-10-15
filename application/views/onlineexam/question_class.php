<?php
	$question_url = base_url('onlineexam/question') . (!empty($branch_id) ? '?branch_id=' . urlencode($branch_id) : '');
	$branch_query = (!empty($branch_id)) ? '?branch_id=' . urlencode($branch_id) : '';
?>
<div class="row">
	<div class="col-md-12">
		<?php if (!empty($branch_id) && !empty($branch_name)): ?>
		<section class="panel">
			<div class="panel-body clearfix">
				<a href="<?=$question_url?>" class="btn btn-default btn-sm pull-right"><i class="fas fa-edit"></i> Change</a>
				<strong><?=translate('branch')?>:</strong> <?=html_escape($branch_name)?>
			</div>
		</section>
		<?php endif; ?>

		<section class="panel" data-appear-animation-type="<?php echo $global_config['animations'];?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title">
					<div class="pull-right">
						<a href="<?=$question_url?>" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> <?=translate('back')?></a>
					</div>
					<i class="fas fa-list"></i> <?=translate('question')?> — <?=html_escape($class_name)?> - <?=html_escape($section_name)?>
				</h4>
			</header>
			<div class="panel-body">
				<div class="portal-table-wrap">
					<table class="table table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th><?=translate('sl')?></th>
								<th>Subject Group</th>
								<th><?=translate('subject')?></th>
								<th><?=translate('total') . " " . translate('question')?></th>
								<th><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$question_type_map = $this->onlineexam_model->question_type();
							if (!empty($subject_summary)):
								$sn = 1;
								foreach ($subject_summary as $row):
									$view_url = base_url('onlineexam/question_subject/' . (int) $row['class_id'] . '/' . (int) $row['section_id'] . '/' . (int) $row['subject_id']) . $branch_query;
									$typeBreakdown = array();
									for ($ti = 1; $ti <= 11; $ti++) {
										$ck = 'count_type_' . $ti;
										$cn = isset($row[$ck]) ? (int) $row[$ck] : 0;
										if ($cn > 0) {
											$lbl = isset($question_type_map[(string) $ti]) ? $question_type_map[(string) $ti] : ('Type ' . $ti);
											$typeBreakdown[] = html_escape($lbl) . ': <strong>' . $cn . '</strong>';
										}
									}
							?>
							<tr>
								<td><?=$sn++?></td>
								<td><?=html_escape($row['subject_group'])?></td>
								<td><?=html_escape($row['subject_name'])?></td>
								<td>
									<span class="text-weight-bold"><?=(int) $row['total_question']?></span>
									<?php if (!empty($typeBreakdown)) { ?>
									<div class="text-muted mt-xs" style="font-size:12px;line-height:1.5;"><?=implode(' &middot; ', $typeBreakdown)?></div>
									<?php } ?>
								</td>
								<td>
									<a href="<?=html_escape($view_url)?>" class="btn btn-circle btn-default btn-sm" title="<?=translate('view')?>"><i class="fas fa-arrow-right"></i> <?=translate('view')?></a>
								</td>
							</tr>
							<?php
								endforeach;
							else:
							?>
							<tr>
								<td colspan="5" class="text-center text-muted"><?=translate('no_information_available')?>.</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>

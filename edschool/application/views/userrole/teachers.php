<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-chalkboard-teacher"></i> <?=translate('teachers_list')?></h4>
	</header>
	<div class="panel-body">
		<div class="export_title"><?= translate('teachers_list') ?></div>
		<table class="table table-bordered table-hover table-condensed table-export" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th><?php echo translate('sl'); ?></th>
					<th><?php echo translate('photo'); ?></th>
					<th><?php echo translate('name'); ?></th>
					<th><?php echo translate('staff_id'); ?></th>
					<th><?php echo translate('designation'); ?></th>
					<th><?php echo translate('department'); ?></th>
				<?php if ($getSchoolConfig->teacher_email_visible == 1) { ?>
					<th><?php echo translate('email'); ?></th>
				<?php } if ($getSchoolConfig->teacher_mobile_visible == 1) { ?>
					<th><?php echo translate('mobile_no'); ?></th>
				<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 1;
				$branch_id = get_loggedin_branch_id();
				$employees = $this->userrole_model->getTeachersList($branch_id);
				if (count($employees)) {
				foreach ($employees as $row):
				?>
				<tr>
					<td><?php echo $i++; ?></td>
					<td class="center">
						<img  src="<?php echo get_image_url('staff', $row->photo); ?>" width="40" height="40" alt="" />
					</td>
					<td><?php echo html_escape($row->name); ?></td>
					<td><?php echo html_escape($row->staff_id); ?></td>
					<td><?php echo html_escape($row->designation_name); ?></td>
					<td><?php echo html_escape($row->department_name); ?></td>
				<?php if ($getSchoolConfig->teacher_email_visible == 1) { ?>
					<td><a href="mailto:<?php echo html_escape($row->email); ?>"><?php echo html_escape($row->email); ?></a></td>
				<?php } if ($getSchoolConfig->teacher_mobile_visible == 1) { ?>
					<td><?php echo html_escape($row->mobileno); ?></td>
				<?php } ?>
				</tr>
				<?php endforeach; } else { ?>
				<tr>
					<td colspan="8">
						<p class="mb-none"><?php echo translate('no_information_available'); ?></p>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</section>

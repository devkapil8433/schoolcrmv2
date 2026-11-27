<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#assigned" data-toggle="tab"><i class="fas fa-store-alt"></i> <?=translate('assigned')?></a>
			</li>
			<li>
				<a href="#list" data-toggle="tab"><i class="fas fa-door-open"></i> <?=translate('room_list')?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div id="assigned" class="tab-pane active">
                <?php if (!empty($student['hostel_id']) && !empty($student['room_id'])):
                $route_info = $this->userrole_model->getHostelDetails($student['hostel_id'], $student['room_id']);
                ?>
                <table class="table table-bordered table-condensed mb-none">
                	<tbody>
                		<tr><th><?=translate('hostel_name')?></th><td><?=html_escape($route_info->hostel_name)?></td></tr>
                		<tr><th><?=translate('hostel_category')?></th><td><?=html_escape($route_info->hcategory_name)?></td></tr>
                		<tr><th><?=translate('watchman_name')?></th><td><?=html_escape($route_info->watchman)?></td></tr>
                		<tr><th><?=translate('hostel_address')?></th><td><?=html_escape($route_info->address)?></td></tr>
                		<tr><th><?=translate('room_name')?></th><td><?=html_escape($route_info->room_name)?></td></tr>
                		<tr><th><?=translate('room_category')?></th><td><?=html_escape($route_info->rcategory_name)?></td></tr>
                		<tr><th><?=translate('no_of_beds')?></th><td><?=html_escape($route_info->no_beds)?></td></tr>
                		<tr><th><?=translate('cost_per_bed')?></th><td><?=currencyFormat($route_info->bed_fee)?></td></tr>
                	</tbody>
                </table>
	            <?php else: ?>
					<p class="text-center text-muted mb-none"><?=translate('there_is_no_room_allocation')?></p>
				<?php endif; ?>
			</div>
			<div class="tab-pane" id="list">
				<table class="table table-bordered table-hover table-condensed table-export">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('room_name')?></th>
							<th><?=translate('hostel_name')?></th>
							<th><?=translate('room_category')?></th>
							<th><?=translate('no_of_beds')?></th>
							<th><?=translate('cost_per_bed')?></th>
							<th><?=translate('remarks')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						$branchID = $student['branch_id'];
						$this->db->order_by('id', 'desc');
						$this->db->where('branch_id', $branchID);
						$rooms = $this->db->get('hostel_room')->result();
						if (count($rooms)) {
							foreach($rooms as $room):
						?>
						<tr>
							<td><?php echo $count++;?></td>
							<td><?php echo html_escape($room->name);?></td>
							<td><?php echo html_escape(get_type_name_by_id('hostel', $room->hostel_id));?></td>
							<td><?php echo html_escape(get_type_name_by_id('hostel_category', $room->category_id));?></td>
							<td><?php echo html_escape($room->no_beds);?></td>
							<td><?php echo currencyFormat($room->bed_fee);?></td>
							<td><?php echo html_escape($room->remarks);?></td>
						</tr>
						<?php endforeach; } else { ?>
						<tr>
							<td colspan="7">
								<p class="text-center text-muted mb-none"><?=translate('no_information_available')?></p>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>

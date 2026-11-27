<section class="panel">
	<header class="panel-heading">
		<div class="panel-actions">
			<a class="btn btn-default btn-sm" data-toggle="tooltip" data-original-title="<?=translate('refresh_mail')?>" 
			href="<?=base_url('communication/mailbox/trash')?>">
				<i class="fas fa-sync"></i>
			</a>
			<button class="btn btn-default btn-sm" id="msgAction" data-type="restore" data-toggle="tooltip" data-original-title="<?=translate('restore')?>"><i class="fas fa-reply"></i></button>
			<button class="btn btn-danger btn-sm" id="msgAction" data-type="forever" data-toggle="tooltip" data-original-title="<?=translate('delete_forever')?>"><i class="far fa-trash-alt"></i></button>
		</div>
		<h4 class="panel-title"><i class="far fa-trash-alt"></i> <?=translate('trash')?></h4>
	</header>
	<div class="panel-body">
		<table class="table table-bordered table-hover table-condensed text-dark table-export">
			<thead>
				<tr>
					<th>
						<div class="checkbox-replace">
							<label class="i-checks"><input type="checkbox" id="selectAllchkbox"><i></i></label>
						</div>
					</th>
					<th><?=translate('receiver')?></th>
					<th><?=translate('subjects')?></th>
					<th><?=translate('message')?></th>
					<th><?=translate('time')?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$sql = "SELECT * FROM message WHERE (sender = " . $this->db->escape($active_user) . " AND trash_sent = 1) OR (reciever = " .
					$this->db->escape($active_user) . " AND trash_inbox = 1) ORDER BY id DESC";
					$messages = $this->db->query($sql)->result();
					foreach ($messages as $message):
						// defining the user to show
						if ($message->sender == $active_user)
							$getUser = explode('-', $message->reciever);
						if ($message->reciever == $active_user)
							$getUser = explode('-', $message->sender);
						$userRoleID = $getUser[0];
						$userID 	= $getUser[1];
				?>
				<tr>
					<td class="checked-area" width="30px">
						<div class="checkbox-replace">
							<label class="i-checks">
								<input type="checkbox" class="msg_checkbox" id="<?=$message->id?>"><i></i>
							</label>
						</div>
					</td>
					<td width="20%"><?=$this->application_model->getUserNameByRoleID($userRoleID, $userID)['name']; ?></td>
					<td><?php echo $message->subject; ?></td>
					<td>
					<?php
						$body = strip_tags($message->body);
						echo mb_strimwidth($body, 0, 60, "...");
					?>
					</td>
					<td><?php echo get_nicetime($message->created_at); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>

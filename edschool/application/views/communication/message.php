<?php
$active = html_escape($this->input->get('type'));
$isCompose = ($inside_subview == 'message_compose');
?>
<section class="panel">
	<div class="mailbox">
		<div class="row">
			<div class="col-md-3">
				<a href="<?php echo base_url('communication/mailbox/compose'); ?>" class="btn btn-default btn-block mb-md">
					<i class="fas fa-envelope"></i> <?php echo translate('compose'); ?>
				</a>
				<ul class="nav nav-pills nav-stacked">
					<li class="<?php echo ($inside_subview == 'message_inbox' || $active == 'inbox') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('communication/mailbox/inbox'); ?>">
							<i class="far fa-envelope"></i> <?php echo translate('inbox'); ?>
							<span class="label pull-right"><?php echo $this->application_model->count_unread_message(); ?></span>
						</a>
					</li>
					<li class="<?php echo ($inside_subview == 'message_sent' || $active == 'sent') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('communication/mailbox/sent'); ?>">
							<i class="fas fa-share-square"></i> <?php echo translate('sent'); ?>
							<span class="label pull-right"><?php echo $this->application_model->reply_count_unread_message(); ?></span>
						</a>
					</li>
					<li class="<?php echo ($inside_subview == 'message_important') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('communication/mailbox/important'); ?>">
							<i class="far fa-bell"></i> <?php echo translate('important'); ?>
						</a>
					</li>
					<li class="<?php echo ($inside_subview == 'message_trash') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('communication/mailbox/trash'); ?>">
							<i class="far fa-trash-alt"></i> <?php echo translate('trash'); ?>
						</a>
					</li>
				</ul>
			</div>
			<div class="col-md-9">
				<?php $this->load->view('communication/' . $inside_subview . '.php'); ?>
			</div>
		</div>
	</div>
</section>

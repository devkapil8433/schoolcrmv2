<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list"></i> <?=isset($this->lang->language['diary_entries']) ? translate('diary_entries') : 'Diary Entries'?></h4>
				<div class="panel-btn">
					<a href="<?=base_url('digital_diary/add_entry')?>" class="btn btn-default btn-circle">
						<i class="fas fa-plus-circle"></i> <?=translate('add')?>
					</a>
				</div>
			</header>
			<div class="panel-body">
				<p class="text-muted"><?=isset($this->lang->language['diary_entries']) ? translate('diary_entries') : 'Diary Entries'?> - <?=isset($this->lang->language['no_data_available']) ? translate('no_data_available') : 'No data available'?></p>
			</div>
		</section>
	</div>
</div>

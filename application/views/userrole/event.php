<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-calendar-check"></i> <?=translate('event_list')?></h4>
	</header>
	<div class="panel-body">
		<div class="export_title"><?php echo translate('event_list'); ?></div>
		<table class="table table-bordered table-hover table-condensed" id="eventTable" width="100%">
			<thead>
				<tr>
					<th><?=translate('title')?></th>
					<th><?=translate('type')?></th>
					<th><?=translate('date_of_start')?></th>
					<th><?=translate('date_of_end')?></th>
					<th><?=translate('audience')?></th>
					<th><?=translate('created_by')?></th>
					<th class="no-sort"><?=translate('action')?></th>
				</tr>
			</thead>
		</table>
	</div>
</section>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<div class="panel-actions">
				<button type="button" onclick="fn_printElem('printResult')" class="btn btn-default btn-sm"><i class="fas fa-print"></i></button>
			</div>
			<h4 class="panel-title"><i class="fas fa-info-circle"></i> <?=translate('event_details')?></h4>
		</header>
		<div class="panel-body" id="printResult">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed" id="ev_table"></table>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="button" class="btn btn-default modal-dismiss"><?=translate('close')?></button>
				</div>
			</div>
		</footer>
	</section>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		initDatatable('#eventTable', 'userrole/getEventListDT');
	});
</script>

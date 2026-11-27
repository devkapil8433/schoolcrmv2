<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<?php if (is_superadmin_loggedin()): ?>
	<section class="panel fee-collection-filter">
		<header class="panel-heading">
			<h4 class="panel-title">Select Branch</h4>
			<p class="fee-panel-subtitle">Choose the branch to search students and collect fees.</p>
		</header>
		<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
		<div class="panel-body">
			<div class="row mb-sm">
				<div class="col-md-offset-3 col-md-6">
					<div class="form-group">
						<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
						<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $branch_id), "class='form-control'
						data-plugin-selectTwo data-width='100%'");
						?>
					</div>
				</div>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-offset-10 col-md-2">
					<button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i class="fas fa-filter"></i> <?= translate('filter') ?></button>
				</div>
			</div>
		</footer>
		<?php echo form_close(); ?>
	</section>
<?php endif; ?>

<div class="row">
	<div class="col-md-12">
		<?php if (!empty($branch_id)) : ?>
			<section class="panel fee-collection-search">
				<header class="panel-heading">
					<h4 class="panel-title"><?= translate('search_student') ?></h4>
				</header>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-12">
							<label class="fee-student-search-label" for="studentSearch">Find a student</label>
							<div class="fee-student-search-control">
								<input type="search" id="studentSearch" class="form-control" placeholder="Try a name, admission no., mobile, guardian, roll no., class or section" autocomplete="off" aria-label="Search by student name, admission number, mobile, guardian, roll number, class, or section" aria-describedby="studentSearchHelp">
								<button type="button" class="fee-student-search-clear" aria-label="Clear student search" title="Clear search" hidden><i class="fas fa-times"></i></button>
							</div>
						</div>
					</div>
					<div class="fee-student-search-tips" aria-label="Searchable student details">
						<span>Search by</span><span>Name</span><span>Admission no.</span><span>Mobile</span><span>Guardian</span><span>Roll / class</span>
					</div>
					<p id="studentSearchHelp" class="fee-student-search-hint">Enter at least 2 characters. You can combine words, for example a student name and class. Results are limited to the selected branch and current session.</p>
					<div id="searchResults" class="fee-student-search-results mt-3" role="region" aria-live="polite" aria-relevant="additions text"></div>
				</div>
			</section>

			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><?= translate('select_ground') ?></h4>
				</header>
				<?php echo form_open($this->uri->uri_string(), array('class' => 'search_form')); ?>
				<input type="hidden" id="branch_id" name="branch_id" value="<?= $branch_id ?>">
				<div class="panel-body">
					<div class="row mb-sm">
						<div class="col-md-<?php echo $widget; ?> mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('class') ?></label>
								<?php
								$arrayClass = $this->app_lib->getClass($branch_id);
								echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value)'
								data-plugin-selectTwo data-width='100%' ");
								?>
								<span class="error"></span>
							</div>
						</div>
						<div class="col-md-<?php echo $widget; ?> mb-sm">
							<div class="form-group">
								<label class="control-label"><?= translate('section') ?></label>
								<?php
								$arraySection = $this->app_lib->getSections(set_value('class_id'));
								echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id'
								data-plugin-selectTwo data-width='100%'");
								?>
								<span class="error"></span>
							</div>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button type="submit" name="search" id="searchBtn" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing" value="1" class="btn btn-default btn-block"> <i class="fas fa-filter"></i> <?= translate('filter') ?></button>
						</div>
					</div>
				</footer>
				<?php echo form_close(); ?>
			</section>

			<section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations']; ?>" data-appear-animation-delay="100">
				<?php echo form_open('fees/invoicePDFdownload', array('class' => 'printIn')); ?>
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-list-ol"></i> <?= translate('invoice_list') ?>
						<div class="panel-btn">
							<button type="submit" class="btn btn-default btn-circle" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fa-solid fa-file-pdf"></i> <?= translate('download') ?> PDF
							</button>
							<button type="button" class="btn btn-default btn-circle" id="printBtn" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
								<i class="fas fa-print"></i> <?= translate('print') ?>
							</button>
						</div>
					</h4>
				</header>
				<div class="panel-body">
					<div class="mb-md mt-md">
						<div class="export_title"><?= translate('invoice') . " " . translate('list') ?></div>
						<table class="table table-bordered table-condensed table-hover mb-none tbr-top" id="invoiceTable" width="100%">
							<thead>
								<tr>
									<th width="5" class="no-sort no-export">
										<div class="checkbox-replace">
											<label class="i-checks" data-toggle="tooltip" data-original-title="Select All">
												<input type="checkbox" name="select-all" id="selectAllchkbox"> <i></i>
											</label>
										</div>
									</th>
									<th><?= translate('student') ?></th>
									<th><?= translate('class') ?></th>
									<th><?= translate('section') ?></th>
									<th><?= translate('register_no') ?></th>
									<th><?= translate('roll') ?></th>
									<th><?= translate('mobile_no') ?></th>
									<th><?= translate('fee_group') ?></th>
									<th class="no-sort"><?= translate('status') ?></th>
									<th><?= translate('action') ?></th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
				<?php echo form_close(); ?>
			</section>
		<?php endif; ?>
	</div>
</div>

<script>
	$(document).ready(function() {
		var liveSearchTimer;
		var liveSearchRequest;
		$("#studentSearch").on('input', function() {
			var query = $.trim($(this).val());
			var branchID = $('#branch_id').val();
			$('.fee-student-search-clear').prop('hidden', query.length === 0);
			window.clearTimeout(liveSearchTimer);
			if (liveSearchRequest) {
				liveSearchRequest.abort();
			}

			if (query.length < 2) {
				$("#searchResults").html(query.length ? '<div class="fee-student-search-status">Type at least 2 characters to search.</div>' : '');
				return;
			}
			$("#searchResults").html('<div class="fee-student-search-status"><i class="fas fa-spinner fa-spin"></i> Searching students...</div>');
			liveSearchTimer = window.setTimeout(function() {
				liveSearchRequest = $.ajax({
					url: "<?= base_url('student/live_search') ?>",
					method: "POST",
					data: {
						query: query,
						branch_id: branchID,
						"<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
					},
					dataType: 'html',
					success: function(data) {
						$("#searchResults").html(data);
					},
					error: function(xhr, status) {
						if (status !== 'abort') {
							$("#searchResults").html('<p class="text-center text-danger">Student search failed. Please try again.</p>');
						}
					}
				});
			}, 250);
		});
		$('.fee-student-search-clear').on('click', function() {
			$('#studentSearch').val('').trigger('input').trigger('focus');
		});
	});
</script>

<script type="text/javascript">
	var searchBtn = "";
	var cusDataTable = "";
	var branch_ID = "<?php echo $branch_id; ?>";
	$(document).ready(function() {
		if (!$('#invoiceTable').length) {
			return;
		}

		let filter = function(d) {
			d.branch_id = $('#branch_id').val();
			d.class_id = $('#class_id').val();
			d.section_id = $('#section_id').val();
			d.submit_btn = searchBtn;
		};
		cusDataTable = initDatatable("#invoiceTable", "fees/getInvoiceListDT", filter);

		let showInvoicePanel = function() {
			var $panel = $('#invoiceTable').closest('section.appear-animation');
			$panel.addClass('appear-animation-visible');
			var animationClass = $panel.attr('data-appear-animation-type') || $panel.attr('data-appear-animation');
			if (animationClass) {
				$panel.addClass(animationClass);
			}
		};

		if ($('#branch_id').val()) {
			searchBtn = 1;
			cusDataTable.draw();
			showInvoicePanel();
		}

		$("form.search_form").on('submit', function(e) {
			var $this = $(this);
			e.preventDefault();
			var btn = $this.find('[type="submit"]');
			$.ajax({
				url: $(this).attr('action'),
				type: "POST",
				data: $(this).serialize(),
				dataType: 'json',
				beforeSend: function() {
					btn.button('loading');
				},
				success: function(data) {
					$('.error').html("");
					if (data.status == "fail") {
						$.each(data.error, function(index, value) {
							$this.find("[name='" + index + "']").parents('.form-group').find('.error').html(value);
						});
					} else {
						$(".export_title").html(data.export_title);
						searchBtn = 1;
						branch_ID = $('#branch_id').val();
						cusDataTable.draw();
						showInvoicePanel();
					}
				},
				complete: function() {
					btn.button('reset');
				},
				error: function() {
					btn.button('reset');
				}
			});
		});

		$('form.printIn').on('submit', function(e) {
			e.preventDefault();
			var btn = $(this).find('[type="submit"]');
			var countRow = $(this).find('input[name="student_id[]"]:checked').length;
			if (countRow > 0) {
				var class_name = $('#class_id').find('option:selected').text();
				var section_name = $('#section_id').find('option:selected').text();
				section_name = (section_name == 'Select Class First' ? "<?php echo translate('all_section') ?>" : section_name);
				class_name = (class_name == 'Select' ? "<?php echo translate('all_class') ?>" : class_name);
				var fileName = class_name + ' (' + section_name + ")-Invoice.pdf";
				$.ajax({
					url: $(this).attr('action'),
					type: "POST",
					data: $(this).serialize(),
					cache: false,
					xhr: function() {
						var xhr = new XMLHttpRequest();
						xhr.responseType = 'blob'
						return xhr;
					},
					beforeSend: function() {
						btn.button('loading');
					},
					success: function(data, jqXHR, response) {
						const blob = new Blob([data], {
							type: 'application/pdf'
						});
						const url = window.URL.createObjectURL(blob);
						const a = document.createElement('a');
						a.href = url;
						a.download = fileName;
						document.body.appendChild(a);
						a.click();
						a.remove();
						window.URL.revokeObjectURL(url);
						btn.button('reset');
					},
					error: function() {
						btn.button('reset');
						alert("An error occured, please try again");
					},
					complete: function() {
						btn.button('reset');
					}
				});
			} else {
				popupMsg("<?php echo translate('no_row_are_selected') ?>", "error");
			}
		});

		$(document).on('click', '#printBtn', function() {
			btn = $(this);
			var arrayData = [];
			$('form.printIn input[name="student_id[]"]').each(function() {
				if ($(this).is(':checked')) {
					studentID = $(this).val();
					arrayData.push(studentID);
				}
			});
			if (arrayData.length === 0) {
				popupMsg("<?php echo translate('no_row_are_selected') ?>", "error");
				btn.button('reset');
			} else {
				$.ajax({
					url: "<?php echo base_url('fees/invoicePrint') ?>",
					type: "POST",
					data: {
						'student_id[]': arrayData,
					},
					dataType: 'html',
					beforeSend: function() {
						btn.button('loading');
					},
					success: function(data) {
						fn_printElem(data, true);
					},
					error: function() {
						btn.button('reset');
						alert("An error occured, please try again");
					},
					complete: function() {
						btn.button('reset');
					}
				});
			}
		});
	});

	function pdf_sendByemail(enrollID = '', ele) {
		var btn = $(ele);
		if (enrollID !== '') {
			$.ajax({
				url: "<?php echo base_url('fees/pdf_sendByemail') ?>",
				type: "POST",
				data: {
					'enrollID': enrollID,
					'branch_id': branch_ID,
				},
				dataType: 'JSON',
				beforeSend: function() {
					btn.button('loading');
				},
				success: function(data) {
					popupMsg(data.message, data.status);
				},
				error: function() {
					btn.button('reset');
					alert("An error occured, please try again");
				},
				complete: function() {
					btn.button('reset');
				}
			});
		}
	}
</script>

<style>
	
	.table-up strong {
		display: inline-block;
		min-width: 130px;
		font-weight: 600;
		color: #222;
	}

	.toggle-receipts {
		display: inline-block;
		margin-top: 4px;
		color: #0d6efd;
		font-weight: 600;
		font-size: 12px;
		cursor: pointer;
		text-decoration: none;
	}

	.toggle-receipts:hover {
		text-decoration: underline;
	}

	.receipt-more {
		margin-top: 2px;
		line-height: 1.5;
	}

	.fees-card-table tr.month-blurred {
		filter: blur(3px);
		opacity: 0.4;
		color: #999 !important;
		user-select: none;
		pointer-events: none;
		background-color: #fafafa;
	}
</style>
<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<?php if (is_superadmin_loggedin()): ?>
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><?= translate('select_ground') ?></h4>
		</header>
		<?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
		<div class="panel-body">
			<div class="row mb-sm">
				<div class="col-md-offset-3 col-md-6">
					<div class="form-group">
						<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
						<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control'
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
<?php
endif;
?>
<div class="row">
	<div class="col-md-12">
		<?php
		if (!empty($branch_id)) :
			if (is_superadmin_loggedin()) :
		?>
				<section class="panel">
					<header class="panel-heading">
						<h4 class="panel-title"><?= translate('search_student') ?></h4>
					</header>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<input type="text" id="studentSearch" class="form-control" placeholder="Search by Admission No, Name, or Mobile No">
								<input type="hidden" id="branch_id" value="<?= $branch_id ?>">

							</div>
						</div>
						<div id="searchResults" class="mt-3"></div>
					</div>
				</section>
		<?php
			endif;
		endif;
		?>

		<!-- ================= FEES CARD TABLE ================= -->
        <section class="panel" id="feesCardPanel" style="display:none;">
            <header class="panel-heading">
                <h4 class="panel-title">Student Fees Card</h4>
            </header>

            <div class="panel-body" id="feesCardBody">
                <!-- AJAX CONTENT -->
            </div>
        </section>

	</div>
</div>
<script>
$(document).ready(function () {

    /* ===== SEARCH STUDENT (ONLY NAMES) ===== */
    $("#studentSearch").keyup(function () {

        let query = $(this).val();
        let branch_id = $("#branch_id").val();

        if (query.length < 1) {
            $("#searchResults").html('');
            return;
        }

        $.ajax({
            url: "<?= base_url('student/live_search_for_fees_card') ?>",
            type: "POST",
            data: { query, branch_id },
            success: function (res) {
				 $("#feesCardPanel").show(); 
                $("#searchResults").html(res);
            }
        });
    });

    /* ===== CLICK NAME → LOAD FEES CARD ===== */
    $(document).on("click", ".student-select", function () {

        let enroll_id = $(this).data("enroll");

        $("#searchResults").html('');
        $("#studentSearch").val($(this).text());

        $.ajax({
    url: "<?= base_url('fees_structure/getStudentFeesCardTable') ?>",
    type: "POST",
    data: { enroll_id },
    success: function (res) {
        $("#feesCardBody").html(res);
    }
});

    });

    /* ===== RECEIPT NO: SHOW MORE / LESS ===== */
    $(document).on('click', '.toggle-receipts', function (e) {
        e.preventDefault();
        var $link = $(this);
        var $more = $link.siblings('.receipt-more');
        var moreCount = $link.data('more');
        if ($more.is(':visible')) {
            $more.hide();
            $link.text('Show more (' + moreCount + ')');
        } else {
            $more.show();
            $link.text('Show less');
        }
    });

});
</script>
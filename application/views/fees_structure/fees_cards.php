<style>
	#feesCardBody {
		padding: 20px;
		background: #f7f8fa;
	}

	.fees-card-view {
		color: #344054;
	}

	.fees-card-student {
		display: flex;
		align-items: center;
		gap: 20px;
		padding: 20px 22px;
		margin-bottom: 18px;
		border: 1px solid #e4e7ec;
		border-left: 4px solid #f0ad4e;
		border-radius: 10px;
		background: #fff;
		box-shadow: 0 3px 12px rgba(16, 24, 40, .06);
	}

	.fees-card-student__photo {
		flex: 0 0 88px;
		width: 88px;
		height: 88px;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
		border: 1px solid #e4e7ec;
		border-radius: 12px;
		background: #fff8eb;
		color: #d88916;
		font-size: 38px;
	}

	.fees-card-student__photo img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.fees-card-student__details {
		flex: 1;
		min-width: 0;
	}

	.fees-card-student__details h3 {
		margin: 0 0 4px;
		color: #253247;
		font-size: 20px;
		font-weight: 700;
	}

	.fees-card-student__class {
		margin-bottom: 14px;
		color: #667085;
		font-size: 13px;
	}

	.fees-card-student__meta {
		display: grid;
		grid-template-columns: repeat(3, minmax(120px, 1fr));
		gap: 12px 18px;
	}

	.fees-card-student__meta span {
		min-width: 0;
		color: #344054;
		font-size: 13px;
		overflow-wrap: anywhere;
	}

	.fees-card-student__meta b {
		display: block;
		margin-bottom: 3px;
		color: #98a2b3;
		font-size: 10px;
		font-weight: 700;
		letter-spacing: .05em;
		text-transform: uppercase;
	}

	.fees-card-table {
		min-width: 1080px;
		margin-bottom: 0;
		border: 1px solid #e4e7ec;
		border-radius: 8px;
		background: #fff;
		font-size: 13px;
	}

	.fees-card-table thead th {
		padding: 12px 10px !important;
		border-color: #e4e7ec !important;
		background: #f2f4f7;
		color: #475467;
		font-weight: 700;
		white-space: nowrap;
	}

	.fees-card-table tbody td,
	.fees-card-table tfoot td {
		padding: 11px 10px !important;
		border-color: #eaecf0 !important;
		vertical-align: middle !important;
	}

	.fees-card-table tbody tr:not(.month-blurred):hover {
		background: #fffaf1;
	}

	.fees-card-table .fees-card-carry,
	.fees-card-table .fees-card-total,
	.fees-card-table .fees-card-paid,
	.fees-card-table .fees-card-balance {
		display: inline-block;
		padding: 4px 8px;
		border-radius: 6px;
		white-space: nowrap;
	}

	.fees-card-table .fees-card-carry {
		background: #fff4dc;
		color: #9a6700;
	}

	.fees-card-table .fees-card-total {
		background: #eef4ff;
		color: #2458a6;
	}

	.fees-card-table .fees-card-paid {
		background: #eaf7ef;
		color: #18794e;
	}

	.fees-card-table .fees-card-balance.is-due {
		background: #fff0ee;
		color: #c43224;
	}

	.fees-card-table .fees-card-balance.is-clear {
		background: #eaf7ef;
		color: #18794e;
	}

	.fees-card-table .fees-card-grand-total td {
		border-top: 2px solid #f0ad4e !important;
		background: #fff7e8;
		color: #253247;
		font-weight: 700;
	}

	.toggle-receipts {
		display: inline-block;
		margin-top: 4px;
		color: #b87508;
		font-size: 12px;
		font-weight: 600;
		cursor: pointer;
		text-decoration: none;
	}

	.toggle-receipts:hover {
		color: #8a5705;
		text-decoration: underline;
	}

	.receipt-more {
		margin-top: 4px;
		line-height: 1.6;
	}

	#searchResults .table {
		margin-top: 12px;
		border: 1px solid #e4e7ec;
		background: #fff;
	}

	#searchResults .table thead th {
		background: #f2f4f7;
		color: #475467;
	}

	.fees-card-table tr.month-blurred {
		filter: blur(2px);
		opacity: .42;
		color: #667085 !important;
		user-select: none;
		pointer-events: none;
		background: #fafafa;
	}

	@media (max-width: 767px) {
		#feesCardBody {
			padding: 12px;
		}

		.fees-card-student {
			align-items: flex-start;
			padding: 15px;
		}

		.fees-card-student__meta {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 480px) {
		.fees-card-student {
			flex-direction: column;
		}

		.fees-card-student__meta {
			grid-template-columns: 1fr;
		}
	}
</style>
<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<?php if (is_superadmin_loggedin()): ?>
	<section class="panel fee-collection-filter">
		<header class="panel-heading">
			<h4 class="panel-title">Select Branch</h4>
			<p class="fee-panel-subtitle">Choose the school branch before opening student fee cards.</p>
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
<?php
endif;
?>
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
								<input type="hidden" id="branch_id" value="<?= $branch_id ?>">
							</div>
						</div>
						<div class="fee-student-search-tips" aria-label="Searchable student details">
							<span>Search by</span><span>Name</span><span>Admission no.</span><span>Mobile</span><span>Guardian</span><span>Roll / class</span>
						</div>
						<p id="studentSearchHelp" class="fee-student-search-hint">Enter at least 2 characters. You can combine words, for example a student name and class. Select a result to open the fee card.</p>
						<div id="searchResults" class="fee-student-search-results mt-3" role="region" aria-live="polite" aria-relevant="additions text"></div>
					</div>
				</section>
		<?php endif; ?>

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
    var searchTimer;
    var searchRequest;
    $("#studentSearch").on("input", function () {
        var query = $.trim($(this).val());
        var branchID = $("#branch_id").val();
        $(".fee-student-search-clear").prop("hidden", query.length === 0);
        window.clearTimeout(searchTimer);
        if (searchRequest) {
            searchRequest.abort();
        }
        if (query.length < 2) {
            $("#searchResults").html(query.length ? '<div class="fee-student-search-status">Type at least 2 characters to search.</div>' : '');
            return;
        }
        $("#searchResults").html('<div class="fee-student-search-status"><i class="fas fa-spinner fa-spin"></i> Searching students...</div>');
        searchTimer = window.setTimeout(function () {
            searchRequest = $.ajax({
                url: "<?= base_url('student/live_search_for_fees_card') ?>",
                type: "POST",
                data: {
                    query: query,
                    branch_id: branchID,
                    "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
                },
                success: function (res) {
                    $("#searchResults").html(res);
                },
                error: function (xhr, status) {
                    if (status !== "abort") {
                        $("#searchResults").html('<div class="fee-student-search-status text-danger">Student search failed. Please try again.</div>');
                    }
                }
            });
        }, 250);
    });
    $(".fee-student-search-clear").on("click", function () {
        $("#studentSearch").val("").trigger("input").trigger("focus");
    });

    $(document).on("click", ".student-select", function (event) {
        event.preventDefault();
        var enrollID = $(this).data("enroll");
        var branchID = $("#branch_id").val();
        var selectedName = $(this).find(".fee-student-result__name").text().trim();
        $("#searchResults").empty();
        $("#studentSearch").val(selectedName);
        $("#feesCardPanel").show();
        $("#feesCardBody").html('<div class="fee-student-search-status"><i class="fas fa-spinner fa-spin"></i> Loading fee card...</div>');
        $.ajax({
            url: "<?= base_url('fees_structure/getStudentFeesCardTable') ?>",
            type: "POST",
            dataType: "html",
            data: {
                enroll_id: enrollID,
                branch_id: branchID,
                "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
            },
            success: function (res) {
                $("#feesCardBody").html(res);
            },
            error: function () {
                $("#feesCardBody").html('<div class="alert alert-danger">Unable to load this student fee card. Please try again.</div>');
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
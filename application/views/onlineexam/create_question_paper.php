<?php
$branch_id = isset($branch_id) ? $branch_id : '';
$branch_id_int = (int) $branch_id;
$paper_templates = isset($paper_templates) && is_array($paper_templates) ? $paper_templates : array();
$cqp_type_meta = isset($cqp_type_meta) && is_array($cqp_type_meta) ? $cqp_type_meta : array();
$cqp_preselect_template = isset($cqp_preselect_template) ? (int) $cqp_preselect_template : 0;
$cqp_templates_js = array();
foreach ($paper_templates as $tplr) {
	$tid = (string) $tplr['id'];
	$decoded = json_decode(isset($tplr['config']) ? $tplr['config'] : '', true);
	$cqp_templates_js[$tid] = array(
		'title' => isset($tplr['title']) ? $tplr['title'] : '',
		'config' => is_array($decoded) ? $decoded : array(),
	);
}
$cqp_qtype_meta_js = array();
foreach ($cqp_type_meta as $qt) {
	$cqp_qtype_meta_js[(string) $qt['id']] = array(
		'name' => $qt['name'],
		'marks' => isset($qt['marks']) ? $qt['marks'] : '',
		'question_type_id' => isset($qt['question_type_id']) ? (int) $qt['question_type_id'] : 0,
	);
}
$bd = isset($branch_details) && is_array($branch_details) ? $branch_details : array();
$schoolName = !empty($bd['school_name']) ? $bd['school_name'] : (!empty($bd['name']) ? $bd['name'] : '');
$cityLine = '';
if (!empty($bd['city'])) {
	$cityLine = $bd['city'];
}
if (!empty($bd['state']) && $cityLine !== '') {
	$cityLine .= ' ' . $bd['state'];
} elseif (!empty($bd['state'])) {
	$cityLine = $bd['state'];
}
$logoUrl = isset($paper_logo_url) ? $paper_logo_url : '';
$cqp_preview_i18n = array(
	'stemSample' => translate('question') . ' …',
	'option' => translate('option'),
	'answer' => translate('answer'),
	'true' => 'TRUE',
	'false' => 'FALSE',
	'selectMatch' => '— Select match —',
	'passage' => 'Passage / context',
	'yourAnswer' => 'Your answer',
	'colA' => 'Column A',
	'colB' => 'Column B',
	'andMoreSame' => '… and %d more question(s) with the same layout.',
);
$cqp_tpl_select = '';
if (!empty($paper_templates) && $cqp_preselect_template > 0) {
	foreach ($paper_templates as $tplr) {
		if ((int) $tplr['id'] === (int) $cqp_preselect_template) {
			$cqp_tpl_select = (string) $tplr['id'];
			break;
		}
	}
}
$cqp_paper_design_initial = $cqp_tpl_select !== '' ? (int) $cqp_tpl_select : 0;
$arrayClass = $this->app_lib->getClass($branch_id);
$arraySection = $this->app_lib->getSections('', false);
$arraySubject = array('' => translate('select_class_first'));
$timeOpts = array(
	'' => translate('select'),
	'30' => '30M',
	'45' => '45M',
	'60' => '60M',
	'90' => '90M',
	'120' => '120M',
	'150' => '150M',
	'180' => '180M',
);
$mmOpts = array(
	'' => translate('select'),
	'20' => '20',
	'30' => '30',
	'40' => '40',
	'50' => '50',
	'70' => '70',
	'100' => '100',
);
$examModeOpts = array(
	'' => translate('select'),
	'online' => 'Online',
	'offline' => 'Offline',
	'hybrid' => 'Hybrid',
);
$examTypeOpts = array(
	'' => translate('select'),
	'monthly' => 'Monthly Test',
	'half_yearly' => 'Half Yearly',
	'annual' => 'Annual',
	'unit' => 'Unit Test',
	'term' => 'Term Exam',
);
$assessOpts = array(
	'' => translate('select'),
	'formative' => 'Formative',
	'summative' => 'Summative',
);
$examNameOpts = array(
	'' => translate('select'),
	'Monthly Test' => 'Monthly Test',
	'Half Yearly' => 'Half Yearly',
	'Annual' => 'Annual',
	'Unit Test' => 'Unit Test',
	'Pre Board' => 'Pre Board',
	'Term Exam' => 'Term Exam',
);
$pubDate = date('Y-m-d');
$pubTime = date('H:i');
?>
<style type="text/css">
	/* Create question paper — clearer guided layout */
	.cqp-workspace { margin-bottom: 8px; }
	.cqp-workspace .cqp-steps {
		display: flex;
		gap: 8px;
		flex-wrap: wrap;
		margin-bottom: 14px;
		padding: 0;
		list-style: none;
	}
	.cqp-workspace .cqp-steps li {
		flex: 1;
		min-width: 120px;
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 10px 12px;
		border: 1px solid #e1e4e8;
		border-radius: 6px;
		background: #fff;
		color: #586069;
		font-size: 12px;
		font-weight: 600;
		cursor: pointer;
		transition: border-color .12s ease, background .12s ease;
	}
	.cqp-workspace .cqp-steps li:hover { border-color: #c8e1ff; background: #f6fbff; }
	.cqp-workspace .cqp-steps li .cqp-step-num {
		width: 24px; height: 24px; border-radius: 50%;
		display: inline-flex; align-items: center; justify-content: center;
		background: #e1e4e8; color: #24292e; font-size: 12px; font-weight: 700; flex-shrink: 0;
	}
	.cqp-workspace .cqp-steps li.is-active {
		border-color: #0366d6; background: #f1f8ff; color: #0366d6;
	}
	.cqp-workspace .cqp-steps li.is-active .cqp-step-num { background: #0366d6; color: #fff; }
	.cqp-workspace .cqp-steps li.is-done { border-color: #85e89d; background: #f0fff4; color: #22863a; }
	.cqp-workspace .cqp-steps li.is-done .cqp-step-num { background: #28a745; color: #fff; }
	.cqp-workspace .cqp-step-hint {
		font-size: 12px; color: #6a737d; margin: -6px 0 12px; line-height: 1.4;
	}
	.cqp-workspace .cqp-form-section {
		background: #fff;
		border: 1px solid #e1e4e8;
		border-radius: 6px;
		padding: 14px 16px;
		margin-bottom: 12px;
	}
	.cqp-workspace .cqp-form-section.is-dimmed { opacity: 0.55; }
	.cqp-workspace .cqp-form-section > h5 {
		margin: 0 0 12px;
		padding-bottom: 8px;
		border-bottom: 1px solid #eef1f4;
		font-size: 13px;
		font-weight: 700;
		color: #24292e;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.cqp-workspace .cqp-form-section > h5 .cqp-sec-badge {
		display: inline-flex; align-items: center; justify-content: center;
		width: 22px; height: 22px; border-radius: 50%;
		background: #0366d6; color: #fff; font-size: 11px; font-weight: 700;
	}
	.cqp-workspace .cqp-form-section > h5 .cqp-sec-sub {
		margin-left: auto; font-size: 11px; font-weight: 500; color: #6a737d;
	}
	.cqp-workspace .cqp-more-toggle {
		display: inline-flex; align-items: center; gap: 6px;
		padding: 6px 0; margin: 4px 0 8px;
		font-size: 12px; font-weight: 600; color: #0366d6;
		background: none; border: 0; cursor: pointer;
	}
	.cqp-workspace .cqp-more-toggle:hover { text-decoration: underline; }
	.cqp-workspace .cqp-more-body { display: none; padding-top: 4px; border-top: 1px dashed #e1e4e8; margin-top: 4px; }
	.cqp-workspace .cqp-more-body.is-open { display: block; }
	.cqp-workspace .cqp-sticky-actions {
		position: sticky; bottom: 0; z-index: 5;
		margin: 12px -16px -14px;
		padding: 12px 16px;
		background: rgba(255,255,255,.96);
		border-top: 1px solid #e1e4e8;
		display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
		box-shadow: 0 -4px 12px rgba(27,31,35,.04);
	}
	.cqp-workspace .cqp-sticky-actions .cqp-sel-chip {
		font-size: 12px; color: #586069; background: #f6f8fa;
		border: 1px solid #e1e4e8; border-radius: 12px; padding: 4px 10px;
	}
	.cqp-workspace #cqp_wrap_channel .portal-label { font-weight: 600; color: #24292e; margin-bottom: 8px; }
	.cqp-workspace .cqp-toggle-wrap { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 4px; }
	.cqp-workspace .cqp-toggle {
		display: inline-flex; align-items: center; gap: 8px; margin: 0;
		padding: 8px 14px; font-size: 13px; font-weight: 500;
		border: 1px solid #d1d5da; border-radius: 4px; background: #fff; color: #24292e; cursor: pointer;
	}
	.cqp-workspace .cqp-toggle:hover { border-color: #959da5; background: #f6f8fa; }
	.cqp-workspace .cqp-toggle.active {
		border-color: #0366d6; background: #f1f8ff; color: #0366d6; font-weight: 600;
	}
	.cqp-workspace .cqp-toggle input[type="radio"] { margin: 0; width: 15px; height: 15px; }
	.cqp-workspace .cqp-chapters-toolbar {
		display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;
	}
	.cqp-workspace .cqp-chapters-toolbar .cqp-chapters-title { font-weight: 600; font-size: 13px; color: #24292e; }
	.cqp-workspace .cqp-qbank-bar {
		display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;
		padding: 10px 12px; background: #f6f8fa; border: 1px solid #e1e4e8; border-radius: 4px 4px 0 0; font-size: 13px;
	}
	.cqp-workspace .cqp-qbank-body {
		border: 1px solid #e1e4e8; border-top: none; border-radius: 0 0 4px 4px; padding: 14px 16px; background: #fff;
	}
	.cqp-workspace .cqp-qbank-body.collapsed { display: none; }
	.cqp-workspace .cqp-chapter-grid {
		display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 14px; max-height: 280px; overflow-y: auto;
	}
	@media (max-width: 767px) { .cqp-workspace .cqp-chapter-grid { grid-template-columns: 1fr; } }
	.cqp-workspace .cqp-chapter-item { font-size: 13px; display: flex; align-items: flex-start; gap: 8px; margin-bottom: 4px; }
	.cqp-workspace .cqp-chapter-item .cqp-qnum { font-weight: 600; color: #0366d6; }
	.cqp-workspace .cqp-badge-num {
		display: inline-flex; align-items: center; justify-content: center;
		min-width: 22px; height: 22px; padding: 0 6px; font-size: 11px; font-weight: 600;
		border-radius: 10px; background: #0366d6; color: #fff;
	}
	.cqp-workspace .cqp-template-summary {
		margin-top: 10px; padding: 10px 12px; border-radius: 4px;
		background: #f1f8ff; border: 1px solid #c8e1ff; font-size: 13px;
	}
	.cqp-workspace .cqp-template-summary h6 {
		margin: 0 0 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #0366d6;
	}
	.cqp-workspace .cqp-question-box { margin-top: 12px; padding-top: 12px; border-top: 1px dashed #e1e4e8; }
	.cqp-workspace .cqp-question-list {
		max-height: 320px; overflow-y: auto; border: 1px solid #e1e4e8; border-radius: 4px; background: #fff; padding: 8px;
	}
	.cqp-workspace .cqp-sec-panel { border: 1px solid #e1e4e8; border-radius: 4px; margin-bottom: 10px; overflow: hidden; background: #fff; }
	.cqp-workspace .cqp-sec-head {
		display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;
		padding: 8px 12px; background: #f6f8fa; border-bottom: 1px solid #e1e4e8; font-size: 13px;
	}
	.cqp-workspace .cqp-sec-prog { font-size: 12px; font-weight: 600; font-variant-numeric: tabular-nums; color: #22863a; }
	.cqp-workspace .cqp-sec-prog.cqp-sec-prog-warn { color: #cb2431; }
	.cqp-workspace .cqp-sec-qlist { max-height: 220px; overflow-y: auto; padding: 8px 10px; }
	.cqp-workspace label.cqp-question-item.cqp-sec-capped { opacity: 0.45; cursor: not-allowed; }
	.cqp-workspace .cqp-question-item {
		display: flex; align-items: flex-start; gap: 10px; padding: 10px;
		margin-bottom: 6px; font-size: 13px; border: 1px solid #eaecef; border-radius: 4px;
		background: #fff; cursor: pointer;
	}
	.cqp-workspace .cqp-question-item:last-child { margin-bottom: 0; }
	.cqp-workspace .cqp-question-item:hover:not(.cqp-sec-capped) { border-color: #d1d5da; }
	.cqp-workspace .cqp-question-item.cqp-question-item--on {
		border-color: #0366d6; background: #f6fcff; box-shadow: inset 3px 0 0 #0366d6;
	}
	.cqp-workspace .cqp-qitem-side input { margin-top: 2px; }
	.cqp-workspace .cqp-q-chapter-tag {
		display: inline-block; font-size: 10px; font-weight: 600; text-transform: uppercase;
		letter-spacing: 0.03em; padding: 2px 7px; border-radius: 3px; background: #eef2f7; color: #444d56; margin-bottom: 4px;
	}
	.cqp-workspace .cqp-question-text { color: #24292e; line-height: 1.45; }
	.cqp-workspace .cqp-preview-wrap { position: sticky; top: 56px; }
	@media (max-width: 991px) { .cqp-workspace .cqp-preview-wrap { position: static; } }
	.cqp-workspace .cqp-preview-page {
		position: relative; background: #fff; border: 1px solid #e1e4e8; border-radius: 4px;
		padding: 16px 18px 28px; min-height: 420px; max-height: calc(100vh - 130px); overflow-y: auto;
	}
	.cqp-workspace .cqp-preview-watermark {
		position: absolute; left: 50%; top: 45%; transform: translate(-50%, -50%) rotate(-14deg);
		font-size: 48px; font-weight: 700; color: rgba(3, 102, 214, 0.06); pointer-events: none; white-space: nowrap; z-index: 0;
	}
	.cqp-workspace .cqp-preview-inner { position: relative; z-index: 1; }
	.cqp-workspace .cqp-preview-head-top { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 8px; color: #57606a; }
	.cqp-workspace .cqp-preview-head-top .cqp-center { flex: 1; text-align: center; font-weight: 600; color: #24292e; padding: 0 8px; }
	.cqp-workspace .cqp-header-stage {
		position: relative; width: 100%; min-height: 100px; height: 140px; margin-bottom: 8px;
		box-sizing: border-box;
	}
	.cqp-workspace .cqp-drag-item { position: absolute; z-index: 2; }
	.cqp-workspace .cqp-school-logo { width: 52px; height: 52px; object-fit: contain; border-radius: 4px; border: 1px solid #eaecef; display: block; }
	.cqp-workspace .cqp-school-logo-placeholder { width: 52px; height: 52px; border-radius: 4px; background: #f6f8fa; border: 1px dashed #d1d5da; }
	.cqp-workspace .cqp-school-logo-wrap.hidden { display: none !important; }
	.cqp-workspace .cqp-school-text { min-width: 120px; max-width: 70%; }
	.cqp-workspace .cqp-school-text.cqp-align-left { text-align: left; }
	.cqp-workspace .cqp-school-text.cqp-align-center { text-align: center; }
	.cqp-workspace .cqp-school-text.cqp-align-right { text-align: right; }
	.cqp-workspace .cqp-school-title { font-size: 16px; font-weight: 700; margin: 0 0 2px; color: #24292e; }
	.cqp-workspace .cqp-school-loc { font-size: 12px; font-weight: 600; color: #586069; margin: 0; }
	.cqp-workspace .cqp-preview-meta {
		display: flex; justify-content: space-between; font-size: 12px; padding-top: 8px; margin-top: 8px;
		border-top: 1px solid #24292e; color: #24292e;
	}
	.cqp-workspace .cqp-preview-meta ul { list-style: none; padding: 0; margin: 0; }
	.cqp-workspace .cqp-preview-note { font-size: 12px; margin: 10px 0 12px; color: #444d56; }
	.cqp-workspace .cqp-q-title { font-weight: 700; font-size: 13px; margin: 10px 0 2px; color: #24292e; }
	.cqp-workspace .cqp-attempt-line {
		font-size: 12px; font-weight: 600; color: #0366d6; margin: 0 0 8px; font-style: italic;
	}
	.cqp-workspace .cqp-sample-block { margin-bottom: 14px; }
	.cqp-workspace .cqp-bar-placeholder {
		width: 80px; height: 80px; aspect-ratio: 1 / 1;
		border: 1px dashed #d1d5da; border-radius: 4px; font-size: 10px; color: #959da5;
		display: flex; align-items: center; justify-content: center; text-align: center;
		background: #fff; box-sizing: border-box; padding: 6px; line-height: 1.2; overflow: hidden;
	}
	.cqp-workspace .cqp-bar-placeholder.hidden { display: none !important; }
	.cqp-workspace .cqp-drag-item.hidden { display: none !important; }
	.cqp-workspace .cqp-school-text { min-width: 80px; max-width: 80%; height: auto !important; }
	.cqp-workspace .cqp-footer-mark {
		position: absolute; right: 10px; bottom: 20px; font-size: 22px; font-weight: 700;
		color: rgba(203, 36, 49, 0.35); transform: rotate(-28deg); pointer-events: none;
	}
	.cqp-workspace .cqp-preview-page .dqp-mock-panel { margin-top: 8px; border: 1px solid #e1e4e8; box-shadow: none; }
	.cqp-workspace .cqp-preview-page .dqp-mock-panel .panel-body { padding: 10px 12px; }
	.cqp-workspace .cqp-preview-page .dqp-mock-stem { font-size: 12px; color: #444d56; }
	.cqp-workspace .cqp-preview-page .cqp-pv-stem { font-size: 12px; line-height: 1.45; color: #24292e; white-space: pre-wrap; word-break: break-word; }
	.cqp-workspace .cqp-preview-page .cqp-pv-opts-grid { margin-left: -6px; margin-right: -6px; }
	.cqp-workspace .cqp-preview-page .cqp-pv-opts-grid > [class*="col-"] { padding-left: 6px; padding-right: 6px; }
	.cqp-workspace .cqp-preview-page .cqp-pv-opts-grid .radio-custom,
	.cqp-workspace .cqp-preview-page .cqp-pv-opts-grid .checkbox-replace { margin-top: 6px; margin-bottom: 2px; }
	.cqp-workspace .cqp-preview-page .cqp-pv-opts-grid .radio-custom label,
	.cqp-workspace .cqp-preview-page .cqp-pv-opts-grid .checkbox-replace label { font-size: 12px; word-break: break-word; }
	.cqp-workspace .cqp-layout-panel {
		background: #f6f8fa; border: 1px solid #e1e4e8; border-radius: 4px; padding: 10px 12px 4px; margin-bottom: 12px;
	}
	.cqp-workspace .cqp-layout-panel .control-label { font-size: 11px; font-weight: 600; color: #586069; margin-bottom: 2px; }
	.cqp-workspace .cqp-layout-tabs { display: flex; gap: 6px; margin-bottom: 10px; flex-wrap: wrap; }
	.cqp-workspace .cqp-layout-tabs button {
		border: 1px solid #d1d5da; background: #fff; border-radius: 14px; padding: 4px 12px;
		font-size: 12px; font-weight: 600; color: #586069; cursor: pointer;
	}
	.cqp-workspace .cqp-layout-tabs button.active { border-color: #0366d6; background: #f1f8ff; color: #0366d6; }
	.cqp-workspace .cqp-layout-pane { display: none; }
	.cqp-workspace .cqp-layout-pane.is-active { display: block; }
	.cqp-workspace .cqp-preview-wrap > .panel-heading {
		display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
	}
	.cqp-workspace .cqp-editor-col .panel-heading {
		display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
	}
	.cqp-workspace .cqp-nav-links { display: flex; flex-wrap: wrap; gap: 6px; }
	.cqp-workspace .cqp-empty-step {
		padding: 16px; text-align: center; color: #6a737d; font-size: 13px; background: #fafbfc; border-radius: 4px; border: 1px dashed #d1d5da;
	}
	@media print {
		body * { visibility: hidden !important; }
		.cqp-workspace .cqp-preview-page,
		.cqp-workspace .cqp-preview-page * { visibility: visible !important; }
		.cqp-workspace .cqp-preview-page {
			position: absolute !important; left: 0; top: 0; width: 100%;
			max-height: none !important; overflow: visible !important;
			border: none !important; box-shadow: none !important; padding: 12mm !important;
		}
		.cqp-workspace .cqp-preview-watermark,
		.cqp-workspace .cqp-layout-panel,
		.cqp-workspace .cqp-footer-mark { display: none !important; }
		.sidebar-left, .header, .inner-menu, .page-header, .panel-heading, .cqp-editor-col,
		.btn, button, .notice-top-bar, .content-with-menu-sm, footer { display: none !important; }
	}
</style>

<div class="row">
	<div class="col-md-12">
		<?php if (is_superadmin_loggedin() && $branch_id_int < 1): ?>
		<section class="panel panel-default">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-file-circle-plus"></i> Create Question Paper</h4>
				<div class="flex items-center gap-2">
					<a href="<?= base_url('onlineexam/question') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-book-open"></i> <?= translate('question') ?>
					</a>
					<a href="<?= base_url('onlineexam/design_question_paper') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-file-lines"></i> Question Paper Template
					</a>
					<a href="<?= base_url('onlineexam/my_question_paper') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-folder-open"></i> My Question Paper
					</a>
				</div>
			</header>
			<div class="panel-body">
				<p class="text-muted mb-md"><?= translate('select') ?> <?= translate('branch') ?> to load classes and lessons.</p>
				<?php echo form_open('onlineexam/create_question_paper', array('method' => 'get', 'class' => 'cqp-branch-pick-form')); ?>
				<div class="cqp-branch-pick" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
					<div class="form-group mb-none" style="flex:1 1 260px; min-width:220px; max-width:360px;">
						<label class="portal-label"><?= translate('branch') ?> <span class="required">*</span></label>
						<?php
							$arrayBranchPick = $this->app_lib->getSelectList('branch');
							echo form_dropdown('branch_id', $arrayBranchPick, '', "class='portal-input' id='cqp_branch_pick' required data-plugin-selectTwo data-width='100%'");
						?>
					</div>
					<div class="form-group mb-none" style="flex:0 0 auto;">
						<button type="submit" class="btn-portal-secondary">
							<i class="fas fa-check"></i> <?= translate('select') ?>
						</button>
					</div>
				</div>
				<?php echo form_close(); ?>
			</div>
		</section>
		<?php elseif ($branch_id_int > 0 && empty($paper_templates)): ?>
		<section class="panel panel-default">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-file-circle-plus"></i> Create Question Paper</h4>
				<div class="flex items-center gap-2">
					<a href="<?= base_url('onlineexam/question') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-book-open"></i> <?= translate('question') ?>
					</a>
					<a href="<?= base_url('onlineexam/design_question_paper') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-file-lines"></i> Question Paper Template
					</a>
					<a href="<?= base_url('onlineexam/my_question_paper') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-folder-open"></i> My Question Paper
					</a>
				</div>
			</header>
			<div class="panel-body">
				<div class="alert alert-warning mb-none">
					<p class="mb-sm"><i class="fas fa-file-lines"></i> No question paper templates for this branch yet. Create a template first, then return here to choose questions.</p>
					<a href="<?= base_url('onlineexam/design_question_paper?branch_id=' . (int) $branch_id_int) ?>" class="btn-portal-primary btn-sm"><i class="fas fa-plus"></i> Question Paper Template</a>
				</div>
			</div>
		</section>
		<?php elseif ($branch_id_int > 0): ?>
		<div class="cqp-workspace">
			<?php echo form_open('onlineexam/create_question_paper_proceed', array('id' => 'cqp_form', 'class' => 'cqp-form')); ?>
			<input type="hidden" name="branch_id" value="<?= $branch_id_int ?>">
			<input type="hidden" name="paper_design_id" id="cqp_paper_design_id" value="<?= (int) $cqp_paper_design_initial ?>">
			<div class="row">
				<div class="col-md-6 col-lg-5 cqp-editor-col">
					<section class="panel panel-default">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-file-circle-plus"></i> Create Question Paper</h4>
							<div class="cqp-nav-links">
								<a href="<?= base_url('onlineexam/design_question_paper?branch_id=' . (int) $branch_id_int) ?>" class="btn btn-default btn-sm"><i class="fas fa-file-lines"></i> Templates</a>
								<a href="<?= base_url('onlineexam/my_question_paper') ?>" class="btn btn-default btn-sm"><i class="fas fa-folder-open"></i> My papers</a>
							</div>
						</header>
						<div class="panel-body">
							<ul class="cqp-steps" id="cqp_steps">
								<li class="is-active" data-step="1"><span class="cqp-step-num">1</span><span>Template</span></li>
								<li data-step="2"><span class="cqp-step-num">2</span><span>Class &amp; exam</span></li>
								<li data-step="3"><span class="cqp-step-num">3</span><span>Questions</span></li>
							</ul>
							<p class="cqp-step-hint" id="cqp_step_hint">Start by choosing a paper template. Class, time, and marks will load from it.</p>

							<div class="cqp-form-section" id="cqp_sec_template" data-step-panel="1">
								<h5><span class="cqp-sec-badge">1</span> Choose template <span class="required">*</span></h5>
								<div class="form-group mb-sm">
									<?php
									$tplDd = array('' => translate('select') . ' template');
									foreach ($paper_templates as $tplr) {
										$tplDd[(string) $tplr['id']] = $tplr['title'];
									}
									echo form_dropdown('cqp_template_pick', $tplDd, $cqp_tpl_select, "class='portal-input' id='cqp_template_pick' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
									?>
								</div>
								<div id="cqp_template_summary" class="cqp-template-summary" style="display:none;"></div>
								<p class="help-block text-muted text-sm mb-none" id="cqp_tpl_values_hint" style="display:none;">
									<i class="fas fa-lock"></i> Some fields come from the template.
									<a href="<?= base_url('onlineexam/design_question_paper?branch_id=' . (int) $branch_id_int) ?>">Edit template</a> to change them permanently.
								</p>
							</div>

							<div class="cqp-form-section" id="cqp_sec_exam" data-step-panel="2">
								<h5><span class="cqp-sec-badge">2</span> Class &amp; exam <span class="cqp-sec-sub">only missing fields</span></h5>
								<div class="form-group mb-md" id="cqp_wrap_channel">
									<label class="portal-label">Exam channel</label>
									<div class="cqp-toggle-wrap">
										<label class="cqp-toggle active" id="cqp_lbl_on">
											<input type="radio" name="cqp_channel" value="online" checked> Online
										</label>
										<label class="cqp-toggle" id="cqp_lbl_off">
											<input type="radio" name="cqp_channel" value="offline"> Offline
										</label>
									</div>
								</div>

								<div class="row" id="cqp_row_class_section">
									<div class="col-md-6 mb-sm" id="cqp_col_class">
										<div class="form-group">
											<label class="portal-label"><?= translate('class') ?> <span class="required">*</span></label>
											<?php
												echo form_dropdown('class_id', $arrayClass, '', "class='portal-input' id='class_id' required onchange='getSectionByClass(this.value,0); getSubjectByClass(this.value);'
												data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
											?>
										</div>
									</div>
									<div class="col-md-6 mb-sm" id="cqp_col_section">
										<div class="form-group">
											<label class="portal-label"><?= translate('section') ?> <span class="required">*</span></label>
											<?php
												echo form_dropdown('section_id', $arraySection, '', "class='portal-input' id='section_id' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
											?>
										</div>
									</div>
								</div>
								<div class="row" id="cqp_row_subject_exam">
									<div class="col-md-12 mb-sm" id="cqp_col_subject">
										<div class="form-group">
											<label class="portal-label"><?= translate('subject') ?> <span class="required">*</span></label>
											<?php
												echo form_dropdown('subject_id', $arraySubject, '', "class='portal-input' id='subject_id' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
											?>
										</div>
									</div>
								</div>

								<button type="button" class="cqp-more-toggle" id="cqp_more_toggle"><i class="fas fa-chevron-down"></i> More options (mode, time, marks, publish)</button>
								<div class="cqp-more-body" id="cqp_more_body">
									<div class="row">
										<div class="col-md-6 mb-sm" id="cqp_col_exam_mode_dd">
											<div class="form-group">
												<label class="portal-label">Exam mode</label>
												<?php echo form_dropdown('exam_mode', $examModeOpts, 'online', "class='portal-input' id='cqp_exam_mode' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
											</div>
										</div>
										<div class="col-md-6 mb-sm" id="cqp_col_exam_type">
											<div class="form-group">
												<label class="portal-label">Exam Type</label>
												<?php echo form_dropdown('exam_type', $examTypeOpts, '', "class='portal-input' id='cqp_exam_type' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
											</div>
										</div>
									</div>
									<div class="row" id="cqp_row_assess_time_mm">
										<div class="col-md-4 mb-sm" id="cqp_col_assess">
											<div class="form-group">
												<label class="portal-label">Assessment</label>
												<?php echo form_dropdown('exam_assessment', $assessOpts, '', "class='portal-input' id='cqp_assess' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
											</div>
										</div>
										<div class="col-md-4 mb-sm" id="cqp_col_paper_time">
											<div class="form-group">
												<label class="portal-label">Time</label>
												<?php echo form_dropdown('paper_time', $timeOpts, '150', "class='portal-input' id='cqp_time' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
											</div>
										</div>
										<div class="col-md-4 mb-sm" id="cqp_col_max_marks">
											<div class="form-group">
												<label class="portal-label">Max marks</label>
												<?php echo form_dropdown('max_marks', $mmOpts, '70', "class='portal-input' id='cqp_mm' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
											</div>
										</div>
									</div>
									<div class="row" id="cqp_row_exam_name_publish">
										<div class="col-md-4 mb-sm" id="cqp_col_exam_name">
											<div class="form-group">
												<label class="portal-label">Exam name</label>
												<?php echo form_dropdown('exam_name', $examNameOpts, 'Monthly Test', "class='portal-input' id='cqp_exam_name' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
											</div>
										</div>
										<div class="col-md-4 mb-sm" id="cqp_col_publish_date">
											<div class="form-group">
												<label class="portal-label">Publish date</label>
												<input type="date" name="publish_date" class="portal-input" value="<?= html_escape($pubDate) ?>">
											</div>
										</div>
										<div class="col-md-4 mb-sm" id="cqp_col_publish_time">
											<div class="form-group">
												<label class="portal-label">Publish time</label>
												<input type="time" name="publish_time" class="portal-input" value="<?= html_escape($pubTime) ?>">
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="cqp-form-section" id="cqp_sec_questions" data-step-panel="3">
								<h5><span class="cqp-sec-badge">3</span> Pick questions <span class="cqp-sec-sub" id="cqp_q_pick_sub">select class &amp; subject first</span></h5>
								<div class="cqp-chapters-toolbar">
									<span class="cqp-chapters-title">Selection</span>
									<span class="text-muted text-sm" id="cqp_chapter_summary">0 lessons · 0 questions</span>
								</div>

								<div class="cqp-qbank-bar">
									<strong><i class="fas fa-list-check"></i> Question bank</strong>
									<div>
										<button type="button" class="btn btn-xs btn-default" id="cqp_toggle_qbank"><i class="fas fa-chevron-up"></i> Hide</button>
										<span class="cqp-badge-num" id="cqp_group_badge">0</span>
										<button type="button" class="btn btn-xs btn-default ml-sm" id="cqp_select_all_lessons" style="display:none;">All lessons</button>
									</div>
								</div>
								<div class="cqp-qbank-body" id="cqp_qbank_body">
									<p class="text-muted mb-sm" id="cqp_chapter_hint"><?= translate('select') ?> <?= translate('class') ?>, <?= translate('section') ?>, <?= translate('subject') ?> to load lessons. Questions are by Lesson + Type.</p>
									<div id="cqp_lesson_block" class="cqp-lesson-select-wrap mb-md" style="display:none;">
										<label class="portal-label"><i class="fas fa-book-open"></i> <?= translate('lesson') ?> <span class="text-muted">(optional, multi-select)</span></label>
										<select id="cqp_lesson_select" name="lesson_ids[]" multiple="multiple" class="portal-input populate" data-width="100%"></select>
									</div>
									<div class="cqp-question-box" id="cqp_question_box" style="display:none;">
										<p class="text-muted text-sm mb-sm" id="cqp_template_sec_hint" style="display:none;">
											<i class="fas fa-info-circle"></i> Pick all questions that appear on the paper (can be more than max attempt). Students only attempt up to the max attempt for that section.
										</p>
										<div class="cqp-question-list" id="cqp_question_list"></div>
									</div>
								</div>

								<div class="cqp-sticky-actions" id="cqp_row_submit">
									<span class="cqp-sel-chip" id="cqp_sticky_summary">0 questions selected</span>
									<div id="cqp_col_submit">
										<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save and Proceed</button>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
				<div class="col-md-6 col-lg-7 cqp-preview-col">
					<section class="panel panel-default cqp-preview-wrap">
						<header class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-eye"></i> Live preview</h4>
							<div class="cqp-nav-links">
								<button type="button" class="btn btn-default btn-sm" id="cqp_toggle_layout" title="Customize header">
									<i class="fas fa-palette"></i> Customize look
								</button>
								<button type="button" class="btn btn-primary btn-sm" id="cqp_print_paper" title="Print paper">
									<i class="fas fa-print"></i> Print paper
								</button>
							</div>
						</header>
						<div class="panel-body">
							<div class="cqp-layout-panel" id="cqp_layout_panel" style="display:none;">
								<div class="cqp-layout-tabs">
									<button type="button" class="active" data-pane="school">School</button>
									<button type="button" data-pane="header">Header text</button>
									<button type="button" data-pane="sizes">Sizes</button>
								</div>
								<div class="cqp-layout-pane is-active" data-pane="school">
									<div class="row">
										<div class="col-md-6 mb-sm">
											<label class="control-label">School name</label>
											<input type="text" class="form-control input-sm" id="cqp_ui_school_name" value="<?= html_escape($schoolName !== '' ? $schoolName : '') ?>" placeholder="School name">
										</div>
										<div class="col-md-6 mb-sm">
											<label class="control-label">City / location</label>
											<input type="text" class="form-control input-sm" id="cqp_ui_school_loc" value="<?= html_escape($cityLine !== '' ? $cityLine : '') ?>" placeholder="City">
										</div>
										<div class="col-md-6 mb-sm">
											<label class="control-label">Show logo</label>
											<select class="form-control input-sm" id="cqp_ui_logo_show">
												<option value="show">Show</option>
												<option value="hide">Hide</option>
											</select>
										</div>
									</div>
								</div>
								<div class="cqp-layout-pane" data-pane="header">
									<div class="row">
										<div class="col-md-6 mb-sm">
											<label class="control-label">Reg. No.</label>
											<input type="text" class="form-control input-sm" id="cqp_ui_reg_no" placeholder="Reg. No.">
										</div>
										<div class="col-md-6 mb-sm">
											<label class="control-label">Udise No.</label>
											<input type="text" class="form-control input-sm" id="cqp_ui_udise" placeholder="Udise No.">
										</div>
										<div class="col-md-12 mb-sm">
											<label class="control-label">Recognition (center)</label>
											<input type="text" class="form-control input-sm" id="cqp_ui_recognition" value="Recognized by U.P goverment upto 8 class">
										</div>
										<div class="col-md-12 mb-sm">
											<label class="control-label">Note</label>
											<input type="text" class="form-control input-sm" id="cqp_ui_note" value="All Question are compulsory . Writing should be clean and neat.">
										</div>
									</div>
								</div>
								<div class="cqp-layout-pane" data-pane="sizes">
									<div class="row">
										<div class="col-md-6 mb-sm">
											<label class="control-label">Top header size</label>
											<select class="form-control input-sm" id="cqp_ui_header_size">
												<option value="10">Small</option>
												<option value="11" selected>Normal</option>
												<option value="12">Large</option>
												<option value="13">X-Large</option>
											</select>
										</div>
										<div class="col-md-6 mb-sm">
											<label class="control-label">Meta text size</label>
											<select class="form-control input-sm" id="cqp_ui_meta_size">
												<option value="11">Small</option>
												<option value="12" selected>Normal</option>
												<option value="13">Large</option>
												<option value="14">X-Large</option>
											</select>
										</div>
										<div class="col-md-12 mb-sm">
											<p class="help-block text-muted text-xs mb-none">Logo / school / barcode size &amp; placement template se aate hain (design page pe drag/resize).</p>
										</div>
									</div>
								</div>
							</div>
							<div class="cqp-preview-page" id="cqp_preview_paper">
								<div class="cqp-preview-watermark">Preview</div>
								<div class="cqp-preview-inner">
									<div class="cqp-preview-head-top">
										<span id="cqp_pv_reg">Reg. No- <span id="cqp_pv_reg_val">—</span></span>
										<span class="cqp-center" id="cqp_pv_recognition">Recognized by U.P goverment upto 8 class</span>
										<span id="cqp_pv_udise_wrap">Udise No - <span id="cqp_pv_udise_val">—</span></span>
									</div>
									<div class="cqp-header-stage" id="cqp_pv_header_stage">
										<div class="cqp-drag-item cqp-school-logo-wrap" id="cqp_pv_logo_wrap">
											<?php if ($logoUrl !== ''): ?>
												<img src="<?= html_escape($logoUrl) ?>" class="cqp-school-logo" id="cqp_pv_logo_img" alt="">
											<?php else: ?>
												<div class="cqp-school-logo-placeholder" id="cqp_pv_logo_ph"></div>
											<?php endif; ?>
										</div>
										<div class="cqp-drag-item cqp-school-text" id="cqp_pv_school_text">
											<p class="cqp-school-title" id="cqp_pv_school"><?= html_escape($schoolName !== '' ? $schoolName : 'School Name'); ?></p>
											<p class="cqp-school-loc" id="cqp_pv_city"><?= html_escape($cityLine !== '' ? $cityLine : 'City'); ?></p>
										</div>
										<div class="cqp-drag-item cqp-bar-placeholder" id="cqp_pv_bar" style="display:none;">Bar code</div>
									</div>
									<div class="cqp-preview-meta">
										<ul>
											<li id="cqp_pv_subject">Subject - —</li>
											<li>Class - <span id="cqp_pv_class">—</span></li>
											<li id="cqp_pv_term">Term - —</li>
										</ul>
										<ul class="text-right">
											<li>Time - <span id="cqp_pv_time">—</span></li>
											<li>M.M - <span id="cqp_pv_mm">—</span></li>
										</ul>
									</div>
									<p class="cqp-preview-note" id="cqp_pv_note_wrap"><strong>Note :-</strong> <span id="cqp_pv_note">All Question are compulsory . Writing should be clean and neat.</span></p>
									<div id="cqp_pv_sections"></div>
									<div class="cqp-footer-mark" id="cqp_pv_subject_mark">Subject</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
			<?php echo form_close(); ?>
		</div>
		<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
window.CQP_TEMPLATES = <?= json_encode($cqp_templates_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.CQP_QTYPE_META = <?= json_encode($cqp_qtype_meta_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.CQP_PRESELECT = <?= (int) $cqp_preselect_template ?>;
window.CQP_HAS_FORM = <?= ($branch_id_int > 0 && !empty($paper_templates)) ? 'true' : 'false' ?>;
window.CQP_PREVIEW_I18N = <?= json_encode($cqp_preview_i18n, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.CQP_PREVIEW_DEFAULTS = <?= json_encode(array(
	'recognition' => 'Recognized by U.P goverment upto 8 class',
	'note' => 'All Question are compulsory . Writing should be clean and neat.',
), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.CQP_SEL_TXT = <?= json_encode(translate('select')) ?>;
window.CQP_SEL_CLASS_FIRST = <?= json_encode(translate('select_class_first')) ?>;
window.CQP_TXT_MARKS = <?= json_encode(translate('marks')) ?>;
(function ($) {
	'use strict';
	window._cqpSkipLoadChapters = false;
	var PV_I18N = window.CQP_PREVIEW_I18N || {};
	var PV_DEF = window.CQP_PREVIEW_DEFAULTS || {};
	var TXT_MARKS = window.CQP_TXT_MARKS || 'marks';
	function parseMarks(val) {
		var n = parseFloat(String(val), 10);
		return isNaN(n) ? 0 : n;
	}
	function escapeHtml(s) {
		return String(s === undefined || s === null ? '' : s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}
	function pvT(key, def) {
		return PV_I18N[key] !== undefined && PV_I18N[key] !== '' ? PV_I18N[key] : def;
	}
	function cqpGetActiveTemplateConfig() {
		var id = $('#cqp_template_pick').val() || '';
		if (!id) {
			return null;
		}
		var pack = (window.CQP_TEMPLATES || {})[id];
		return pack && pack.config ? pack.config : null;
	}
	function cqpResolveAttempt(qnum, attemptVal) {
		var att = parseInt(attemptVal, 10) || 0;
		if (qnum < 1) {
			return 0;
		}
		if (att < 1) {
			att = qnum;
		}
		if (att > qnum) {
			att = qnum;
		}
		return att;
	}
	function cqpAttemptLabel(qnum, attempt) {
		if (qnum < 1) {
			return '';
		}
		if (attempt < qnum) {
			return 'Attempt any ' + attempt + ' of ' + qnum;
		}
		return 'Attempt all ' + qnum;
	}
	function cqpGetValidTemplateSections() {
		var cfg = cqpGetActiveTemplateConfig();
		var meta = window.CQP_QTYPE_META || {};
		var out = [];
		if (!cfg || !cfg.sections || !cfg.sections.length) {
			return out;
		}
		var i;
		for (i = 0; i < cfg.sections.length; i++) {
			var sec = cfg.sections[i];
			var tid = sec.qtype_id ? String(sec.qtype_id) : '';
			var num = parseInt(sec.qnum, 10) || 0;
			if (!tid || num < 1) {
				continue;
			}
			var m = meta[tid] || {};
			var baseType = m.question_type_id !== undefined ? parseInt(m.question_type_id, 10) : 0;
			var att = cqpResolveAttempt(num, sec.attempt);
			out.push({
				branchQtypeId: tid,
				qnum: num,
				attempt: att,
				typeName: m.name || ('Type #' + tid),
				baseType: baseType
			});
		}
		return out;
	}
	function cqpQuestionMatchesSectionType(row, baseType) {
		var qt = parseInt(row.type, 10) || 0;
		return baseType > 0 && qt === baseType;
	}
	function cqpBuildMockQuestionBlock(baseTypeId, secNum, qIndex, typeName) {
		var uid = 'cqp_u_' + secNum + '_' + qIndex;
		var qLabel = 'Q' + secNum + '.' + qIndex;
		var optWord = pvT('option', 'Option');
		var html = '';
		html += '<section class="panel pg-fw dqp-mock-panel">';
		html += '<div class="panel-body">';
		html += '<h5 class="chart-title mb-xs"><i class="fas fa-clipboard-question"></i> ' + escapeHtml(qLabel);
		if (typeName) {
			html += ' <span class="text-muted">(' + escapeHtml(typeName) + ')</span>';
		}
		html += '</h5>';
		html += '<p class="dqp-mock-stem">' + escapeHtml(pvT('stemSample', 'Question …')) + '</p>';
		html += '<div class="mt-sm">';
		var t = parseInt(baseTypeId, 10) || 0;
		var o, r, rb, opts;
		if (t === 1) {
			html += '<div class="row cqp-pv-opts-grid">';
			for (o = 1; o <= 4; o++) {
				rb = uid + '_o' + o;
				html += '<div class="col-xs-6">';
				html += '<div class="radio-custom radio-success">';
				html += '<input type="radio" disabled name="cqp_pv_grp_' + uid + '" id="' + rb + '">';
				html += '<label for="' + rb + '">' + escapeHtml(optWord + ' ' + o) + '</label>';
				html += '</div></div>';
			}
			html += '</div>';
		} else if (t === 2) {
			html += '<div class="row cqp-pv-opts-grid">';
			for (o = 1; o <= 4; o++) {
				rb = uid + '_c' + o;
				html += '<div class="col-xs-6">';
				html += '<div class="checkbox-replace">';
				html += '<label class="i-checks"><input type="checkbox" disabled id="' + rb + '"><i></i> ' + escapeHtml(optWord + ' ' + o) + '</label>';
				html += '</div></div>';
			}
			html += '</div>';
		} else if (t === 3) {
			rb = uid + '_tf1';
			html += '<div class="radio-custom radio-success mt-md">';
			html += '<input type="radio" disabled name="cqp_pv_' + uid + '_tf" id="' + rb + '">';
			html += '<label for="' + rb + '">' + escapeHtml(pvT('true', 'TRUE')) + '</label>';
			html += '</div>';
			rb = uid + '_tf2';
			html += '<div class="radio-custom radio-success mt-md">';
			html += '<input type="radio" disabled name="cqp_pv_' + uid + '_tf" id="' + rb + '">';
			html += '<label for="' + rb + '">' + escapeHtml(pvT('false', 'FALSE')) + '</label>';
			html += '</div>';
		} else if (t === 4 || t === 8 || t === 9 || t === 10 || t === 11) {
			html += '<div class="form-group">';
			html += '<label class="portal-label">' + escapeHtml(pvT('yourAnswer', 'Your answer')) + '</label>';
			html += '<textarea class="portal-input" rows="5" disabled placeholder="' + escapeHtml(pvT('writeAnswerHere', 'Write your answer here…')) + '"></textarea>';
			html += '</div>';
		} else if (t === 5) {
			html += '<div class="dqp-mock-match">';
			for (r = 0; r < 4; r++) {
				html += '<div class="row form-group mt-md mb-none">';
				html += '<div class="col-sm-4"><label class="portal-label">' + escapeHtml(pvT('colA', 'Column A') + ' ' + (r + 1)) + '</label></div>';
				html += '<div class="col-sm-8">';
				opts = '<option value="">' + escapeHtml(pvT('selectMatch', '— Select match —')) + '</option>';
				for (o = 1; o <= 4; o++) {
					opts += '<option value="' + o + '">' + escapeHtml(pvT('colB', 'Column B') + ' ' + o) + '</option>';
				}
				html += '<select class="portal-input match-select" disabled>' + opts + '</select>';
				html += '</div></div>';
			}
			html += '</div>';
		} else if (t === 6 || t === 7) {
			html += '<div class="form-group">';
			html += '<label class="portal-label">' + escapeHtml(pvT('passage', 'Passage / context')) + '</label>';
			html += '<div class="well well-sm" style="max-height:88px;overflow:hidden;">…</div>';
			html += '</div>';
			html += '<div class="form-group">';
			html += '<label class="portal-label">' + escapeHtml(pvT('yourAnswer', 'Your answer')) + '</label>';
			html += '<textarea class="portal-input" rows="5" disabled placeholder="…"></textarea>';
			html += '</div>';
		} else {
			html += '<div class="mock-q mb-none">' + escapeHtml(qLabel + (typeName ? ' — ' + typeName : '')) + '</div>';
		}
		html += '</div></div></section>';
		return html;
	}
	function cqpPreviewPlainFromHtml(s) {
		return String(s || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
	}
	function cqpParseMatchQuestionPayload(qRow) {
		var raw = qRow && qRow.question;
		if (raw == null) {
			return null;
		}
		var s = typeof raw === 'string' ? raw.trim() : '';
		if (s.charAt(0) !== '{') {
			return null;
		}
		try {
			var j = JSON.parse(s);
			if (!j || typeof j !== 'object') {
				return null;
			}
			return {
				instruction: j.instruction != null ? String(j.instruction) : '',
				left: Array.isArray(j.left) ? j.left : [],
				right: Array.isArray(j.right) ? j.right : []
			};
		} catch (e) {
			return null;
		}
	}
	function cqpPreviewQuestionLookup() {
		var map = {};
		var i;
		var list = typeof cqpAllQuestions !== 'undefined' && cqpAllQuestions ? cqpAllQuestions : [];
		for (i = 0; i < list.length; i++) {
			var q = list[i];
			if (q && q.id != null) {
				map[String(q.id)] = q;
			}
		}
		return map;
	}
	function cqpPreviewRowFromCheckbox($cb, qMap) {
		var id = String($cb.val());
		if (qMap[id]) {
			return qMap[id];
		}
		var stem = $cb.closest('label').find('.cqp-question-text').first().text() || '';
		stem = stem.replace(/^\[[^\]]+\]\s*/, '').trim();
		var qt = parseInt($cb.attr('data-q-type'), 10) || 0;
		return { id: id, question: stem, type: qt, opt_1: '', opt_2: '', opt_3: '', opt_4: '', answer: '' };
	}
	function cqpBuildRealQuestionPreviewHtml(qRow, qLabel, baseTypeId, typeName) {
		var t = parseInt(baseTypeId, 10) || parseInt(qRow.type, 10) || 0;
		var matchPayload = (t === 5) ? cqpParseMatchQuestionPayload(qRow) : null;
		var stemRaw = cqpPreviewPlainFromHtml(qRow.question);
		if (!stemRaw && qRow.question) {
			stemRaw = String(qRow.question).trim();
		}
		var stem = escapeHtml(stemRaw.length > 2200 ? stemRaw.substring(0, 2200) + '…' : stemRaw);
		var uid = 'pvr_' + String(qRow.id || qLabel).replace(/\W/g, '_') + '_' + String(qLabel).replace(/\W/g, '_');
		var html = '';
		html += '<section class="panel pg-fw dqp-mock-panel cqp-pv-real">';
		html += '<div class="panel-body">';
		html += '<h5 class="chart-title mb-xs"><i class="fas fa-clipboard-question"></i> ' + escapeHtml(qLabel);
		if (typeName) {
			html += ' <span class="text-muted">(' + escapeHtml(typeName) + ')</span>';
		}
		html += '</h5>';
		if (t !== 6 && t !== 7) {
			if (t === 5 && matchPayload) {
				var instrPlain = cqpPreviewPlainFromHtml(matchPayload.instruction);
				if (instrPlain) {
					var instrEsc = escapeHtml(instrPlain.length > 2200 ? instrPlain.substring(0, 2200) + '…' : instrPlain);
					html += '<div class="cqp-pv-stem">' + instrEsc + '</div>';
				}
			} else {
				html += '<div class="cqp-pv-stem">' + stem + '</div>';
			}
		}
		html += '<div class="mt-sm">';
		var o, rb, optText;
		if (t === 1) {
			html += '<div class="row cqp-pv-opts-grid">';
			for (o = 1; o <= 4; o++) {
				optText = cqpPreviewPlainFromHtml(qRow['opt_' + o] || '');
				if (!optText) {
					continue;
				}
				rb = uid + '_o' + o;
				html += '<div class="col-xs-6">';
				html += '<div class="radio-custom radio-success">';
				html += '<input type="radio" disabled name="cqp_pvsel_' + uid + '" id="' + rb + '">';
				html += '<label for="' + rb + '">' + escapeHtml(optText) + '</label></div></div>';
			}
			html += '</div>';
		} else if (t === 2) {
			html += '<div class="row cqp-pv-opts-grid">';
			for (o = 1; o <= 4; o++) {
				optText = cqpPreviewPlainFromHtml(qRow['opt_' + o] || '');
				if (!optText) {
					continue;
				}
				rb = uid + '_c' + o;
				html += '<div class="col-xs-6">';
				html += '<div class="checkbox-replace">';
				html += '<label class="i-checks"><input type="checkbox" disabled id="' + rb + '"><i></i> ' + escapeHtml(optText) + '</label></div></div>';
			}
			html += '</div>';
		} else if (t === 3) {
			rb = uid + '_tf1';
			html += '<div class="radio-custom radio-success mt-md">';
			html += '<input type="radio" disabled name="cqp_pv_' + uid + '_tf" id="' + rb + '">';
			html += '<label for="' + rb + '">' + escapeHtml(pvT('true', 'TRUE')) + '</label></div>';
			rb = uid + '_tf2';
			html += '<div class="radio-custom radio-success mt-md">';
			html += '<input type="radio" disabled name="cqp_pv_' + uid + '_tf" id="' + rb + '">';
			html += '<label for="' + rb + '">' + escapeHtml(pvT('false', 'FALSE')) + '</label></div>';
		} else if (t === 4 || t === 8 || t === 9 || t === 10 || t === 11) {
			html += '<div class="form-group">';
			html += '<label class="portal-label">' + escapeHtml(pvT('yourAnswer', 'Your answer')) + '</label>';
			html += '<textarea class="portal-input" rows="6" disabled placeholder="' + escapeHtml(pvT('writeAnswerHere', 'Write your answer here…')) + '"></textarea>';
			html += '</div>';
		} else if (t === 5) {
			html += '<div class="dqp-mock-match">';
			var leftItems = [];
			var rightMap = {};
			var r, idx, maxRows, opts, rk, leftLabel;
			if (matchPayload) {
				leftItems = matchPayload.left.slice();
				for (o = 0; o < matchPayload.right.length; o++) {
					optText = cqpPreviewPlainFromHtml(matchPayload.right[o] || '');
					if (optText) {
						rightMap[String(o + 1)] = optText;
					}
				}
			}
			if (Object.keys(rightMap).length === 0) {
				for (o = 1; o <= 4; o++) {
					optText = cqpPreviewPlainFromHtml(qRow['opt_' + o] || '');
					if (optText) {
						rightMap[String(o)] = optText;
					}
				}
			}
			if (Object.keys(rightMap).length === 0) {
				for (o = 1; o <= 4; o++) {
					rightMap[String(o)] = pvT('colB', 'Column B') + ' ' + o;
				}
			}
			maxRows = Math.min(Math.max(leftItems.length, Object.keys(rightMap).length, 4), 10);
			for (idx = 0; idx < maxRows; idx++) {
				if (leftItems[idx] != null && String(leftItems[idx]).trim() !== '') {
					leftLabel = cqpPreviewPlainFromHtml(String(leftItems[idx]));
				} else {
					leftLabel = pvT('item', 'Item') + ' ' + (idx + 1);
				}
				html += '<div class="row form-group mt-md mb-none">';
				html += '<div class="col-sm-5"><label class="portal-label">' + escapeHtml(leftLabel) + '</label></div>';
				html += '<div class="col-sm-7">';
				opts = '<option value="">' + escapeHtml(pvT('selectMatch', '— Select match —')) + '</option>';
				rk = Object.keys(rightMap).sort(function (a, b) {
					return parseInt(a, 10) - parseInt(b, 10);
				});
				for (r = 0; r < rk.length; r++) {
					opts += '<option value="' + escapeHtml(rk[r]) + '">' + escapeHtml(rightMap[rk[r]]) + '</option>';
				}
				html += '<select class="portal-input match-select" disabled>' + opts + '</select>';
				html += '</div></div>';
			}
			html += '</div>';
		} else if (t === 6 || t === 7) {
			html += '<div class="form-group">';
			html += '<label class="portal-label">' + escapeHtml(pvT('passage', 'Passage / context')) + '</label>';
			html += '<div class="well well-sm" style="max-height:88px;overflow:hidden;">' + stem + '</div>';
			html += '</div>';
			html += '<div class="form-group">';
			html += '<label class="portal-label">' + escapeHtml(pvT('yourAnswer', 'Your answer')) + '</label>';
			html += '<textarea class="portal-input" rows="4" disabled placeholder="…"></textarea>';
			html += '</div>';
		}
		html += '</div></div></section>';
		return html;
	}
	function cqpRenderPreviewSections() {
		var cfg = cqpGetActiveTemplateConfig();
		var meta = window.CQP_QTYPE_META || {};
		var $wrap = $('#cqp_pv_sections');
		var html = '';
		var marksWord = TXT_MARKS;
		var plan = window._cqpSectionPlan;
		var qMap = cqpPreviewQuestionLookup();
		if (plan && plan.length) {
			var sidx;
			for (sidx = 0; sidx < plan.length; sidx++) {
				var sec = plan[sidx];
				var name = sec.typeName || '—';
				var baseTypeId = sec.baseType;
				var num = sec.qnum;
				var att = cqpResolveAttempt(num, sec.attempt);
				var eachMk = meta[sec.branchQtypeId] && meta[sec.branchQtypeId].marks !== undefined && meta[sec.branchQtypeId].marks !== ''
					? meta[sec.branchQtypeId].marks : null;
				var secTot = eachMk !== null ? (att * parseMarks(eachMk)) : null;
				var title = 'Section ' + (sidx + 1) + ' — ' + name;
				if (eachMk !== null) {
					title += ' (' + eachMk + ' ' + marksWord + ' each';
					if (secTot !== null) {
						title += ', section total ' + secTot;
					}
					title += ')';
				}
				html += '<div class="cqp-sample-block">';
				html += '<div class="cqp-q-title">' + escapeHtml(title) + '</div>';
				html += '<div class="cqp-attempt-line">' + escapeHtml(cqpAttemptLabel(num, att)) + '</div>';
				var $picked = $('#cqp_question_list .cqp-qitem[data-section-idx="' + sidx + '"]:checked');
				var qn = 0;
				if (!$picked.length) {
					html += '<p class="text-muted text-sm mb-xs"><em>No questions selected for this section yet.</em></p>';
				}
				$picked.each(function () {
					var $cb = $(this);
					qn++;
					var row = cqpPreviewRowFromCheckbox($cb, qMap);
					html += cqpBuildRealQuestionPreviewHtml(row, 'Q' + (sidx + 1) + '.' + qn, baseTypeId, name);
				});
				if ($picked.length < num) {
					html += '<p class="text-muted text-xs mb-none"><em>' + escapeHtml(String(num - $picked.length)) + ' more required to match the template.</em></p>';
				}
				html += '</div>';
			}
			$wrap.html(html);
			return;
		}
		var flatChecked = $('.cqp-qitem:checked').filter(function () {
			var sdx = $(this).attr('data-section-idx');
			return sdx === undefined || sdx === null || String(sdx) === '';
		});
		if (flatChecked.length) {
			html += '<div class="cqp-sample-block">';
			html += '<div class="cqp-q-title">Selected questions</div>';
			var qi = 0;
			flatChecked.each(function () {
				var $cb = $(this);
				qi++;
				var row = cqpPreviewRowFromCheckbox($cb, qMap);
				var bt = parseInt(row.type, 10) || 0;
				html += cqpBuildRealQuestionPreviewHtml(row, 'Q' + qi, bt, '');
			});
			html += '</div>';
			$wrap.html(html);
			return;
		}
		if (!cfg || !cfg.sections || !cfg.sections.length) {
			$wrap.html('<p class="text-muted text-sm mb-none">Select a template to see the paper layout by section.</p>');
			return;
		}
		var si;
		var secDisplay = 0;
		for (si = 0; si < cfg.sections.length; si++) {
			var sec2 = cfg.sections[si];
			var tid = sec2.qtype_id ? String(sec2.qtype_id) : '';
			var num2 = parseInt(sec2.qnum, 10) || 0;
			if (!tid || num2 < 1) {
				continue;
			}
			secDisplay++;
			var att2 = cqpResolveAttempt(num2, sec2.attempt);
			var m = meta[tid] || {};
			var name2 = m.name || '—';
			var baseTypeId2 = m.question_type_id !== undefined ? parseInt(m.question_type_id, 10) : 0;
			var eachMk2 = m.marks !== undefined && m.marks !== null && m.marks !== '' ? m.marks : null;
			var secTot2 = eachMk2 !== null ? (att2 * parseMarks(eachMk2)) : null;
			var title2 = 'Section ' + secDisplay + ' — ' + name2;
			if (eachMk2 !== null) {
				title2 += ' (' + eachMk2 + ' ' + marksWord + ' each';
				if (secTot2 !== null) {
					title2 += ', section total ' + secTot2;
				}
				title2 += ')';
			}
			html += '<div class="cqp-sample-block">';
			html += '<div class="cqp-q-title">' + escapeHtml(title2) + '</div>';
			html += '<div class="cqp-attempt-line">' + escapeHtml(cqpAttemptLabel(num2, att2)) + '</div>';
			html += cqpBuildMockQuestionBlock(baseTypeId2, secDisplay, 1, name2);
			if (num2 > 1) {
				var moreStr2 = pvT('andMoreSame', '… and %d more question(s) with the same layout.').replace('%d', String(num2 - 1));
				html += '<p class="text-muted text-xs mb-none mt-sm"><em>' + escapeHtml(moreStr2) + '</em></p>';
			}
			html += '</div>';
		}
		if (!html) {
			html = '<p class="text-muted text-sm mb-none">This template has no valid sections for preview.</p>';
		}
		$wrap.html(html);
	}
	function cqpRefreshPaperPreview() {
		if (!window.CQP_HAS_FORM || !$('#cqp_preview_paper').length) {
			return;
		}
		var pm = window._cqpPreviewMeta;
		if (!pm) {
			pm = {
				reg_no: '',
				udise: '',
				recognition: PV_DEF.recognition || '',
				note: PV_DEF.note || '',
				logo_show: 'show',
				barcode_show: 'hide',
				school_show: 'show',
				reg_show: 'show',
				recognition_show: 'show',
				udise_show: 'show',
				note_show: 'show',
				school_name: '',
				school_location: '',
				school_name_size: '20',
				school_loc_size: '12',
				logo_size: '52',
				barcode_width: '80',
				header_stage_height: '140',
				logo_align: 'left',
				school_align: 'left',
				barcode_align: 'center',
				logo_pos_x: '',
				logo_pos_y: '',
				school_pos_x: '',
				school_pos_y: '',
				barcode_pos_x: '',
				barcode_pos_y: '',
				header_font_size: '11',
				meta_font_size: '12'
			};
		}
		var CQP_ALIGN_PRESETS = {
			logo: { left: { x: 2, y: 12 }, center: { x: 44, y: 8 }, right: { x: 86, y: 12 } },
			school: { left: { x: 14, y: 18 }, center: { x: 28, y: 28 }, right: { x: 55, y: 18 } },
			barcode: { left: { x: 2, y: 10 }, center: { x: 42, y: 10 }, right: { x: 78, y: 10 } }
		};
		function cqpPos(key, align, pmObj) {
			var alignKey = align || 'left';
			var preset = (CQP_ALIGN_PRESETS[key] && CQP_ALIGN_PRESETS[key][alignKey]) ? CQP_ALIGN_PRESETS[key][alignKey] : { x: 2, y: 10 };
			var x = parseFloat(pmObj[key + '_pos_x']);
			var y = parseFloat(pmObj[key + '_pos_y']);
			return {
				x: isFinite(x) ? x : preset.x,
				y: isFinite(y) ? y : preset.y
			};
		}
		var uiSchool = $.trim($('#cqp_ui_school_name').val() || '');
		var uiLoc = $.trim($('#cqp_ui_school_loc').val() || '');
		var uiReg = $.trim($('#cqp_ui_reg_no').val() || '');
		var uiUdise = $.trim($('#cqp_ui_udise').val() || '');
		var uiRec = $.trim($('#cqp_ui_recognition').val() || '');
		var uiNote = $.trim($('#cqp_ui_note').val() || '');
		var uiLogoShow = $('#cqp_ui_logo_show').val() || pm.logo_show || 'show';
		var logoAlign = pm.logo_align || 'left';
		var schoolAlign = pm.school_align || 'left';
		var barAlign = pm.barcode_align || 'center';
		var snSize = parseInt(pm.school_name_size, 10) || 16;
		var slSize = parseInt(pm.school_loc_size, 10) || 12;
		var logoSize = parseInt(pm.logo_size, 10) || 52;
		var barW = parseInt(pm.barcode_width, 10) || 80;
		barW = Math.max(40, Math.min(160, barW));
		var stageH = parseInt(pm.header_stage_height, 10) || 140;
		stageH = Math.max(90, Math.min(360, stageH));
		var headSize = parseInt($('#cqp_ui_header_size').val(), 10) || parseInt(pm.header_font_size, 10) || 11;
		var metaSize = parseInt($('#cqp_ui_meta_size').val(), 10) || parseInt(pm.meta_font_size, 10) || 12;

		$('#cqp_pv_reg_val').text(uiReg || pm.reg_no || '—');
		$('#cqp_pv_udise_val').text(uiUdise || pm.udise || '—');
		$('#cqp_pv_recognition').text(uiRec || pm.recognition || PV_DEF.recognition || '—');
		$('#cqp_pv_note').text(uiNote || pm.note || PV_DEF.note || '—');
		$('#cqp_pv_school').text(uiSchool || pm.school_name || '<?= addslashes($schoolName !== '' ? $schoolName : 'School Name') ?>');
		$('#cqp_pv_city').text(uiLoc || pm.school_location || '<?= addslashes($cityLine !== '' ? $cityLine : 'City') ?>');
		$('#cqp_pv_school').css({ 'font-size': snSize + 'px', 'line-height': '1.25', 'font-weight': '700' });
		$('#cqp_pv_city').css({ 'font-size': slSize + 'px', 'font-weight': '600' });
		$('#cqp_pv_logo_img, #cqp_pv_logo_ph').css({ width: logoSize + 'px', height: logoSize + 'px' });
		$('#cqp_pv_bar').css({
			width: barW + 'px',
			height: barW + 'px',
			'min-width': barW + 'px',
			'min-height': barW + 'px',
			'max-width': barW + 'px',
			'max-height': barW + 'px',
			'font-size': Math.max(9, Math.round(barW * 0.12)) + 'px'
		});
		$('#cqp_pv_school_text').css({ height: 'auto', 'min-height': 0 });
		$('#cqp_pv_header_stage').css({ height: stageH + 'px', 'min-height': stageH + 'px' });
		$('.cqp-preview-head-top').css({ 'font-size': headSize + 'px' });
		$('.cqp-preview-meta').css({ 'font-size': metaSize + 'px' });
		$('.cqp-preview-note').css({ 'font-size': Math.max(metaSize, 12) + 'px' });
		if (uiLogoShow === 'hide') {
			$('#cqp_pv_logo_wrap').addClass('hidden');
		} else {
			$('#cqp_pv_logo_wrap').removeClass('hidden');
		}
		if (pm.school_show === 'hide') {
			$('#cqp_pv_school_text').addClass('hidden');
		} else {
			$('#cqp_pv_school_text').removeClass('hidden');
		}
		if (pm.barcode_show === 'show') {
			$('#cqp_pv_bar').show().removeClass('hidden');
		} else {
			$('#cqp_pv_bar').hide().addClass('hidden');
		}
		if (pm.reg_show === 'hide') {
			$('#cqp_pv_reg').hide();
		} else {
			$('#cqp_pv_reg').show();
		}
		if (pm.recognition_show === 'hide') {
			$('#cqp_pv_recognition').hide();
		} else {
			$('#cqp_pv_recognition').show();
		}
		if (pm.udise_show === 'hide') {
			$('#cqp_pv_udise_wrap').hide();
		} else {
			$('#cqp_pv_udise_wrap').show();
		}
		if (pm.note_show === 'hide') {
			$('#cqp_pv_note_wrap').hide();
		} else {
			$('#cqp_pv_note_wrap').show();
		}
		var posMeta = {
			logo_pos_x: pm.logo_pos_x,
			logo_pos_y: pm.logo_pos_y,
			school_pos_x: pm.school_pos_x,
			school_pos_y: pm.school_pos_y,
			barcode_pos_x: pm.barcode_pos_x,
			barcode_pos_y: pm.barcode_pos_y
		};
		var logoPos = cqpPos('logo', logoAlign, posMeta);
		var schoolPos = cqpPos('school', schoolAlign, posMeta);
		var barPos = cqpPos('barcode', barAlign, posMeta);
		if (barPos.y > 45) {
			barPos = CQP_ALIGN_PRESETS.barcode.right;
		}
		$('#cqp_pv_logo_wrap').css({ left: logoPos.x + '%', top: logoPos.y + '%' });
		$('#cqp_pv_school_text').css({ left: schoolPos.x + '%', top: schoolPos.y + '%' })
			.removeClass('cqp-align-left cqp-align-center cqp-align-right')
			.addClass('cqp-align-' + schoolAlign);
		$('#cqp_pv_bar').css({ left: barPos.x + '%', top: barPos.y + '%' });
		var cls = $('#class_id option:selected').text() || '—';
		$('#cqp_pv_class').text(cls);
		var subj = $('#subject_id option:selected').text() || '';
		var selTxt = window.CQP_SEL_TXT || '';
		var selCf = window.CQP_SEL_CLASS_FIRST || '';
		if (subj && subj !== selTxt && subj !== selCf) {
			$('#cqp_pv_subject').text('Subject - ' + subj);
			$('#cqp_pv_subject_mark').text(subj);
		} else {
			$('#cqp_pv_subject').text('Subject - —');
			$('#cqp_pv_subject_mark').text('Subject');
		}
		var term = $('#cqp_exam_name option:selected').text() || '';
		if ($('#cqp_exam_name').val()) {
			$('#cqp_pv_term').text('Term - ' + term);
		} else {
			$('#cqp_pv_term').text('Term - —');
		}
		var t = $('#cqp_time option:selected').text();
		$('#cqp_pv_time').text(t && t !== selTxt ? t : '—');
		var mm = $('#cqp_mm option:selected').text();
		$('#cqp_pv_mm').text(mm && mm !== selTxt ? mm : '—');
		cqpRenderPreviewSections();
	}
	function cqpFillLayoutUiFromMeta(pm) {
		if (!pm) {
			return;
		}
		if (pm.school_name) {
			$('#cqp_ui_school_name').val(pm.school_name);
		}
		if (pm.school_location) {
			$('#cqp_ui_school_loc').val(pm.school_location);
		}
		if (pm.header_font_size) {
			$('#cqp_ui_header_size').val(String(pm.header_font_size));
		}
		if (pm.meta_font_size) {
			$('#cqp_ui_meta_size').val(String(pm.meta_font_size));
		}
		$('#cqp_ui_reg_no').val(pm.reg_no || '');
		$('#cqp_ui_udise').val(pm.udise || '');
		$('#cqp_ui_recognition').val(pm.recognition || PV_DEF.recognition || '');
		$('#cqp_ui_note').val(pm.note || PV_DEF.note || '');
		$('#cqp_ui_logo_show').val(pm.logo_show === 'hide' ? 'hide' : 'show');
	}
	function cqpTplFieldIsSet(v) {
		if (v === undefined || v === null) {
			return false;
		}
		if (typeof v === 'number') {
			return v > 0;
		}
		var s = String(v).trim();
		return s !== '' && s !== '0';
	}
	function cqpTplRowReflow() {
		function rowAnyVisible($row, selectors) {
			var any = false;
			var j;
			for (j = 0; j < selectors.length; j++) {
				var $c = $row.find(selectors[j]);
				if (!$c.length) {
					$c = $(selectors[j]);
				}
				if ($c.length && $c.is(':visible')) {
					any = true;
					break;
				}
			}
			if ($row.length) {
				$row.toggle(any);
			}
		}
		rowAnyVisible($('#cqp_row_class_section'), ['#cqp_col_class', '#cqp_col_section']);
		rowAnyVisible($('#cqp_row_subject_exam'), ['#cqp_col_subject']);
		rowAnyVisible($('#cqp_row_assess_time_mm'), ['#cqp_col_assess', '#cqp_col_paper_time', '#cqp_col_max_marks']);
		rowAnyVisible($('#cqp_row_exam_name_publish'), ['#cqp_col_exam_name', '#cqp_col_publish_date', '#cqp_col_publish_time']);
		// Always keep sticky save bar
		$('#cqp_row_submit').show();
		cqpMaybeOpenMoreOptions();
		cqpUpdateStepUi();
	}
	function cqpMaybeOpenMoreOptions() {
		var needsMore = $('#cqp_col_exam_mode_dd:visible, #cqp_col_exam_type:visible, #cqp_col_assess:visible, #cqp_col_paper_time:visible, #cqp_col_max_marks:visible, #cqp_col_exam_name:visible, #cqp_col_publish_date:visible, #cqp_col_publish_time:visible').length > 0;
		var $btn = $('#cqp_more_toggle');
		var $body = $('#cqp_more_body');
		if (!needsMore) {
			$btn.hide();
			$body.removeClass('is-open');
			return;
		}
		$btn.show();
	}
	function cqpUpdateStepUi() {
		var hasTpl = !!($('#cqp_template_pick').val() || '');
		var hasClass = !!($('#class_id').val() || '');
		var hasSec = !!($('#section_id').val() || '');
		var hasSubj = !!($('#subject_id').val() || '');
		var hasBasics = hasClass && hasSec && hasSubj;
		var qCount = $('.cqp-qitem:checked').length;
		var $steps = $('#cqp_steps li');
		$steps.removeClass('is-active is-done');
		if (hasTpl) {
			$steps.filter('[data-step="1"]').addClass('is-done');
		}
		if (hasBasics) {
			$steps.filter('[data-step="2"]').addClass('is-done');
		}
		if (qCount > 0) {
			$steps.filter('[data-step="3"]').addClass('is-done');
		}
		var active = 1;
		if (hasTpl && !hasBasics) {
			active = 2;
		} else if (hasTpl && hasBasics) {
			active = 3;
		}
		$steps.filter('[data-step="' + active + '"]').removeClass('is-done').addClass('is-active');
		var hints = {
			1: 'Start by choosing a paper template. Class, time, and marks will load from it.',
			2: 'Confirm class, section, and subject. Open More options only if you need time, marks, or publish settings.',
			3: 'Pick lessons, then select questions for each template section.'
		};
		$('#cqp_step_hint').text(hints[active] || hints[1]);
		$('#cqp_sec_exam').toggleClass('is-dimmed', !hasTpl);
		$('#cqp_sec_questions').toggleClass('is-dimmed', !hasBasics);
		$('#cqp_q_pick_sub').text(hasBasics ? 'fill each section' : 'select class & subject first');
	}
	function cqpUpdateTplFieldVisibility() {
		var pick = $('#cqp_template_pick').val() || '';
		var cfg = cqpGetActiveTemplateConfig();
		var $hint = $('#cqp_tpl_values_hint');
		if (!pick || !cfg) {
			$('#cqp_wrap_channel, #cqp_col_class, #cqp_col_section, #cqp_col_subject, #cqp_col_exam_mode_dd, #cqp_col_exam_type, #cqp_col_assess, #cqp_col_paper_time, #cqp_col_max_marks, #cqp_col_exam_name').show();
			$hint.hide();
			cqpTplRowReflow();
			return;
		}
		function hideIf($el, cond) {
			if (cond) {
				$el.hide();
			} else {
				$el.show();
			}
		}
		/* Templates are reusable for any class / section / subject / term — never lock these. */
		$('#cqp_col_class, #cqp_col_section, #cqp_col_subject, #cqp_col_exam_name').show();
		hideIf($('#cqp_wrap_channel'), cqpTplFieldIsSet(cfg.exam_mode));
		hideIf($('#cqp_col_exam_mode_dd'), cqpTplFieldIsSet(cfg.exam_mode));
		hideIf($('#cqp_col_exam_type'), cqpTplFieldIsSet(cfg.exam_type));
		hideIf($('#cqp_col_assess'), cqpTplFieldIsSet(cfg.exam_assessment));
		hideIf($('#cqp_col_paper_time'), cqpTplFieldIsSet(cfg.paper_time));
		hideIf($('#cqp_col_max_marks'), cqpTplFieldIsSet(cfg.max_marks));
		var anyHidden = !$('#cqp_wrap_channel').is(':visible') || !$('#cqp_col_exam_mode_dd').is(':visible') || !$('#cqp_col_exam_type').is(':visible')
			|| !$('#cqp_col_assess').is(':visible') || !$('#cqp_col_paper_time').is(':visible') || !$('#cqp_col_max_marks').is(':visible');
		$hint.toggle(anyHidden);
		cqpTplRowReflow();
	}
	function cqpApplyExamFields(cfg) {
		if (!cfg) {
			return;
		}
		if (cfg.exam_mode) {
			$('#cqp_exam_mode').val(cfg.exam_mode).trigger('change');
			if (String(cfg.exam_mode).toLowerCase() === 'offline') {
				$('input[name="cqp_channel"][value="offline"]').prop('checked', true);
			} else {
				$('input[name="cqp_channel"][value="online"]').prop('checked', true);
			}
			syncChannelUi();
		}
		if (cfg.exam_type) {
			$('#cqp_exam_type').val(cfg.exam_type).trigger('change');
		}
		if (cfg.exam_assessment) {
			$('#cqp_assess').val(cfg.exam_assessment).trigger('change');
		}
		if (cfg.paper_time) {
			$('#cqp_time').val(cfg.paper_time).trigger('change');
		}
		if (cfg.max_marks) {
			$('#cqp_mm').val(cfg.max_marks).trigger('change');
		}
		if (cfg.exam_name) {
			$('#cqp_exam_name').val(cfg.exam_name).trigger('change');
		}
		cqpUpdateTplFieldVisibility();
	}
	function cqpRenderTemplateSummary(cfg) {
		var $box = $('#cqp_template_summary');
		var meta = window.CQP_QTYPE_META || {};
		var sections = cfg && cfg.sections ? cfg.sections : [];
		var lines = [];
		var secNum = 0;
		var i;
		for (i = 0; i < sections.length; i++) {
			var sec = sections[i];
			var tid = sec.qtype_id ? String(sec.qtype_id) : '';
			var num = parseInt(sec.qnum, 10) || 0;
			if (!tid || num < 1) {
				continue;
			}
			secNum++;
			var att = cqpResolveAttempt(num, sec.attempt);
			var m = meta[tid] || {};
			var nm = m.name || ('Type #' + tid);
			var mk = m.marks !== undefined && m.marks !== '' ? m.marks : '';
			var line = '<strong>Section ' + secNum + '</strong> — ' + $('<span/>').text(nm).html()
				+ ' · ' + num + ' on paper'
				+ ' · attempt ' + att;
			if (mk !== '') {
				line += ' <span class="text-muted">(' + $('<span/>').text(String(mk)).html() + ' marks each · section ' + (att * parseFloat(mk || 0)) + ')</span>';
			}
			lines.push('<li>' + line + '</li>');
		}
		if (!lines.length) {
			$box.hide().empty();
			return;
		}
		$box.html('<h6>Template section plan</h6><ul>' + lines.join('') + '</ul>').show();
	}
	function cqpApplyTemplateFromPicker() {
		var id = $('#cqp_template_pick').val() || '';
		var templates = window.CQP_TEMPLATES || {};
		if (!id) {
			$('#cqp_paper_design_id').val('0');
			$('#cqp_template_summary').hide().empty();
			window._cqpPreviewMeta = null;
			cqpRefreshPaperPreview();
			cqpUpdateTplFieldVisibility();
			return;
		}
		var pack = templates[id];
		if (!pack || !pack.config) {
			$('#cqp_paper_design_id').val('0');
			window._cqpPreviewMeta = null;
			cqpRefreshPaperPreview();
			cqpUpdateTplFieldVisibility();
			return;
		}
		var cfg = pack.config;
		window._cqpPreviewMeta = {
			reg_no: cfg.reg_no || '',
			udise: cfg.udise || '',
			recognition: cfg.recognition || (PV_DEF.recognition || ''),
			note: cfg.note || (PV_DEF.note || ''),
			logo_show: cfg.logo_show !== undefined && cfg.logo_show !== '' ? cfg.logo_show : 'show',
			barcode_show: cfg.barcode_show !== undefined && cfg.barcode_show !== '' ? cfg.barcode_show : 'hide',
			school_show: cfg.school_show === 'hide' ? 'hide' : 'show',
			reg_show: cfg.reg_show === 'hide' ? 'hide' : 'show',
			recognition_show: cfg.recognition_show === 'hide' ? 'hide' : 'show',
			udise_show: cfg.udise_show === 'hide' ? 'hide' : 'show',
			note_show: cfg.note_show === 'hide' ? 'hide' : 'show',
			school_name: cfg.school_name || '',
			school_location: cfg.school_location || '',
			school_name_size: cfg.school_name_size || '20',
			school_loc_size: cfg.school_loc_size || '12',
			logo_size: cfg.logo_size || '52',
			barcode_width: cfg.barcode_width || '80',
			header_stage_height: cfg.header_stage_height || '140',
			logo_align: cfg.logo_align || 'left',
			school_align: cfg.school_align || 'left',
			barcode_align: cfg.barcode_align || 'center',
			logo_pos_x: cfg.logo_pos_x || '',
			logo_pos_y: cfg.logo_pos_y || '',
			school_pos_x: cfg.school_pos_x || '',
			school_pos_y: cfg.school_pos_y || '',
			barcode_pos_x: cfg.barcode_pos_x || '',
			barcode_pos_y: cfg.barcode_pos_y || '',
			header_font_size: cfg.header_font_size || '11',
			meta_font_size: cfg.meta_font_size || '12'
		};
		cqpFillLayoutUiFromMeta(window._cqpPreviewMeta);
		$('#cqp_paper_design_id').val(id);
		cqpRenderTemplateSummary(cfg);
		cqpApplyExamFields(cfg);
		cqpRefreshPaperPreview();
		/* Templates are reusable — do not lock/auto-fill class, section, or subject. */
		cqpUpdateTplFieldVisibility();
	}
	$('#cqp_template_pick').on('change', cqpApplyTemplateFromPicker);
	$(document).on('select2:select', '#cqp_template_pick', cqpApplyTemplateFromPicker);
	$(document).on('change', '#class_id, #section_id, #subject_id, #cqp_exam_mode, #cqp_exam_type, #cqp_assess, #cqp_time, #cqp_mm, #cqp_exam_name', cqpRefreshPaperPreview);
	$(document).on('select2:select', '#class_id, #section_id, #subject_id, #cqp_exam_mode, #cqp_exam_type, #cqp_assess, #cqp_time, #cqp_mm, #cqp_exam_name', cqpRefreshPaperPreview);
	$(document).on('change input', '#cqp_ui_school_name, #cqp_ui_school_loc, #cqp_ui_reg_no, #cqp_ui_udise, #cqp_ui_recognition, #cqp_ui_note, #cqp_ui_header_size, #cqp_ui_meta_size, #cqp_ui_logo_show', cqpRefreshPaperPreview);
	$('#cqp_toggle_layout').on('click', function () {
		var $p = $('#cqp_layout_panel');
		$p.slideToggle(160);
		$(this).toggleClass('active');
	});
	$('#cqp_print_paper').on('click', function () {
		cqpOpenStudentPrintWindow();
	});
	function cqpEsc(s) {
		return $('<div/>').text(s == null ? '' : String(s)).html();
	}
	function cqpOpenStudentPrintWindow() {
		var school = $.trim($('#cqp_ui_school_name').val() || $('#cqp_pv_school').text() || 'School Name');
		var city = $.trim($('#cqp_ui_school_loc').val() || $('#cqp_pv_city').text() || '');
		var recognition = $.trim($('#cqp_ui_recognition').val() || 'Recognized by U.P Government');
		var reg = $.trim($('#cqp_ui_reg_no').val() || '');
		var udise = $.trim($('#cqp_ui_udise').val() || '');
		var note = $.trim($('#cqp_ui_note').val() || 'All questions are compulsory. Write neatly and clearly.');
		var cls = ($('#class_id option:selected').text() || '—');
		var sec = ($('#section_id option:selected').text() || '');
		var subj = ($('#subject_id option:selected').text() || '—');
		var term = ($('#cqp_exam_name option:selected').text() || 'Question Paper');
		var timeT = ($('#cqp_time option:selected').text() || '—');
		var mm = ($('#cqp_mm option:selected').text() || '—');
		var logoSrc = $('#cqp_pv_logo_img').attr('src') || '';
		var showLogo = $('#cqp_ui_logo_show').val() !== 'hide' && logoSrc;
		var letters = { 1: 'A', 2: 'B', 3: 'C', 4: 'D' };
		var linesByType = { 8: 2, 9: 3, 10: 5, 11: 8, 4: 6, 6: 5, 7: 6 };
		var qMap = cqpPreviewQuestionLookup();
		var plan = window._cqpSectionPlan;
		var bodyHtml = '';
		var globalSn = 1;
		var roman = ['', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

		function writeAnswerLines(t) {
			var n = linesByType[t] || 4;
			var h = '<div class="lines">';
			var i;
			for (i = 0; i < n; i++) {
				h += '<div class="ln"></div>';
			}
			return h + '</div>';
		}
		function writeOneQuestion(row, qNo, baseType) {
			var t = parseInt(baseType, 10) || parseInt(row.type, 10) || 0;
			var stemRaw = cqpPreviewPlainFromHtml(row.question);
			var match = (t === 5) ? cqpParseMatchQuestionPayload(row) : null;
			if (t === 5 && match && match.instruction) {
				stemRaw = cqpPreviewPlainFromHtml(match.instruction);
			}
			var mark = row.mark != null && row.mark !== '' ? row.mark : '';
			var h = '<div class="q">';
			h += '<div class="q-head"><span class="q-no">' + qNo + '.</span><div class="q-body">';
			if (mark !== '') {
				h += '<span class="marks">[' + cqpEsc(String(mark)) + ']</span>';
			}
			h += '<div class="stem">' + cqpEsc(stemRaw) + '</div>';
			var o, optText;
			if (t === 1 || t === 2) {
				h += '<div class="opts">';
				for (o = 1; o <= 4; o++) {
					optText = cqpPreviewPlainFromHtml(row['opt_' + o] || '');
					if (!optText) { continue; }
					h += '<div class="opt"><span class="ol">(' + letters[o] + ')</span><span>' + cqpEsc(optText) + '</span></div>';
				}
				h += '</div>';
			} else if (t === 3) {
				h += '<div class="opts"><div class="opt"><span class="ol">(A)</span><span>True</span></div><div class="opt"><span class="ol">(B)</span><span>False</span></div></div>';
			} else if (t === 5) {
				var leftItems = match && match.left ? match.left.slice() : [];
				var rightItems = [];
				if (match && match.right && match.right.length) {
					rightItems = match.right.slice();
				} else {
					for (o = 1; o <= 4; o++) {
						optText = cqpPreviewPlainFromHtml(row['opt_' + o] || '');
						if (optText) { rightItems.push(optText); }
					}
				}
				var rows = Math.max(leftItems.length, rightItems.length, 1);
				var idx;
				h += '<table class="match"><thead><tr><th>Column A</th><th>Ans</th><th>Column B</th></tr></thead><tbody>';
				for (idx = 0; idx < rows; idx++) {
					h += '<tr><td>' + cqpEsc(cqpPreviewPlainFromHtml(leftItems[idx] != null ? String(leftItems[idx]) : ('Item ' + (idx + 1)))) + '</td>';
					h += '<td class="ans">____</td>';
					h += '<td>' + (rightItems[idx] != null ? ('(' + letters[idx + 1] + ') ' + cqpEsc(cqpPreviewPlainFromHtml(String(rightItems[idx])))) : '') + '</td></tr>';
				}
				h += '</tbody></table>';
			} else {
				h += writeAnswerLines(t);
			}
			h += '</div></div></div>';
			return h;
		}

		if (plan && plan.length) {
			var sidx;
			for (sidx = 0; sidx < plan.length; sidx++) {
				var sec = plan[sidx];
				var $picked = $('#cqp_question_list .cqp-qitem[data-section-idx="' + sidx + '"]:checked');
				if (!$picked.length) {
					continue;
				}
				bodyHtml += '<div class="sec">Section ' + (roman[sidx + 1] || (sidx + 1)) + ' — ' + cqpEsc(sec.typeName || '')
					+ '<div class="sec-att">' + cqpEsc(cqpAttemptLabel(sec.qnum, sec.attempt)) + '</div></div>';
				var qn = 0;
				$picked.each(function () {
					qn++;
					var row = cqpPreviewRowFromCheckbox($(this), qMap);
					bodyHtml += writeOneQuestion(row, globalSn++, sec.baseType);
				});
			}
		}
		if (!bodyHtml) {
			var flat = $('.cqp-qitem:checked');
			if (flat.length) {
				bodyHtml += '<div class="sec">Questions</div>';
				flat.each(function () {
					var row = cqpPreviewRowFromCheckbox($(this), qMap);
					bodyHtml += writeOneQuestion(row, globalSn++, row.type);
				});
			}
		}
		if (!bodyHtml) {
			if (typeof swal === 'function') {
				swal({ type: 'warning', title: 'Select questions first to print a student paper.' });
			} else {
				alert('Select questions first to print a student paper.');
			}
			return;
		}

		var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Question Paper</title>';
		html += '<style>';
		html += '@page{size:A4;margin:12mm}';
		html += 'body{font-family:"Times New Roman",Times,Georgia,serif;font-size:12.5pt;line-height:1.45;color:#111;margin:0;padding:0}';
		html += '.sheet{padding:0 2mm}';
		html += '.top{display:flex;justify-content:space-between;font-size:9.5pt;color:#444;margin-bottom:8px}';
		html += '.top .c{flex:1;text-align:center;font-weight:700;color:#111;padding:0 8px;font-size:10pt}';
		html += '.brand{display:flex;align-items:center;gap:12px;border-bottom:2px solid #111;padding-bottom:10px;margin-bottom:8px}';
		html += '.brand img{width:58px;height:58px;object-fit:contain}';
		html += '.brand h1{margin:0;font-size:18pt}';
		html += '.brand p{margin:2px 0 0;font-size:11pt;font-weight:600;color:#444}';
		html += '.title{text-align:center;font-size:13pt;font-weight:700;text-transform:uppercase;margin:8px 0 10px;letter-spacing:.5px}';
		html += '.stu{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:8px 16px;font-size:11pt;margin-bottom:10px;border-bottom:1px solid #333;padding-bottom:8px}';
		html += '.blank{display:inline-block;min-width:55%;border-bottom:1px solid #222;height:1.1em;vertical-align:bottom}';
		html += '.meta{display:grid;grid-template-columns:1fr 1fr;gap:2px 24px;font-size:11pt;margin-bottom:8px}';
		html += '.note{font-size:10.5pt;border:1px solid #333;padding:6px 8px;margin:0 0 12px}';
		html += '.sec{margin:14px 0 8px;padding:4px 0;border-top:1.5px solid #111;border-bottom:1px solid #111;text-align:center;font-weight:700;text-transform:uppercase;font-size:12pt}';
		html += '.sec .sec-att{display:block;margin-top:2px;font-size:10pt;font-weight:600;text-transform:none;font-style:italic;letter-spacing:0}';
		html += '.q{margin:0 0 14px;page-break-inside:avoid}.q-head{display:flex;gap:8px}.q-no{font-weight:700;min-width:1.6em}.q-body{flex:1}';
		html += '.marks{float:right;font-size:10pt;font-weight:700;margin-left:8px}.stem{margin:0 0 6px}';
		html += '.opts{display:grid;grid-template-columns:1fr 1fr;gap:4px 18px;margin-left:4px;font-size:11.5pt}';
		html += '.opt{display:flex;gap:6px}.ol{font-weight:700;min-width:1.5em}';
		html += '.lines .ln{border-bottom:1px solid #888;height:22px}';
		html += '.match{width:100%;border-collapse:collapse;margin-top:6px;font-size:11pt}.match th,.match td{border:1px solid #444;padding:6px 8px}';
		html += '.match th{background:#f3f3f3;text-align:center}.match .ans{text-align:center}';
		html += '.end{margin-top:20px;text-align:center;font-weight:700;border-top:1px dashed #666;padding-top:10px;font-size:11pt}';
		html += '@media print{body{padding:0}}';
		html += '</style></head><body><div class="sheet">';
		html += '<div class="top"><span>Reg. No. ' + (reg ? cqpEsc(reg) : '________') + '</span><span class="c">' + cqpEsc(recognition) + '</span><span>UDISE ' + (udise ? cqpEsc(udise) : '________') + '</span></div>';
		html += '<div class="brand">';
		if (showLogo) {
			html += '<img src="' + cqpEsc(logoSrc) + '" alt="">';
		}
		html += '<div><h1>' + cqpEsc(school) + '</h1><p>' + cqpEsc(city) + '</p></div></div>';
		html += '<div class="title">' + cqpEsc(term) + '</div>';
		html += '<div class="stu"><div>Name : <span class="blank"></span></div><div>Roll No. : <span class="blank"></span></div><div>Date : <span class="blank"></span></div></div>';
		html += '<div class="meta"><div><strong>Class / Section :</strong> ' + cqpEsc(cls + (sec ? ' / ' + sec : '')) + '</div>';
		html += '<div><strong>Subject :</strong> ' + cqpEsc(subj) + '</div>';
		html += '<div><strong>Time Allowed :</strong> ' + cqpEsc(timeT) + '</div>';
		html += '<div><strong>Maximum Marks :</strong> ' + cqpEsc(mm) + '</div></div>';
		html += '<div class="note"><strong>General Instructions :</strong> ' + cqpEsc(note) + '</div>';
		html += bodyHtml;
		html += '<div class="end">*** End of Question Paper ***</div>';
		html += '</div><script>window.onload=function(){window.focus();window.print();}<\/script></body></html>';

		var w = window.open('', '_blank', 'noopener,noreferrer,width=900,height=1000');
		if (!w) {
			alert('Please allow pop-ups to print the student paper.');
			return;
		}
		w.document.open();
		w.document.write(html);
		w.document.close();
	}
	$('#cqp_more_toggle').on('click', function () {
		var $b = $('#cqp_more_body');
		$b.toggleClass('is-open');
		var open = $b.hasClass('is-open');
		$(this).html(open
			? '<i class="fas fa-chevron-up"></i> Hide options'
			: '<i class="fas fa-chevron-down"></i> More options (mode, time, marks, publish)');
	});
	$('.cqp-layout-tabs').on('click', 'button', function () {
		var pane = $(this).data('pane');
		$('.cqp-layout-tabs button').removeClass('active');
		$(this).addClass('active');
		$('.cqp-layout-pane').removeClass('is-active');
		$('.cqp-layout-pane[data-pane="' + pane + '"]').addClass('is-active');
	});
	$('#cqp_steps').on('click', 'li', function () {
		var step = $(this).data('step');
		var map = { 1: '#cqp_sec_template', 2: '#cqp_sec_exam', 3: '#cqp_sec_questions' };
		var $t = $(map[step]);
		if ($t.length) {
			$('html, body').animate({ scrollTop: $t.offset().top - 70 }, 220);
		}
	});
	$(document).on('change', '#cqp_template_pick, #class_id, #section_id, #subject_id', cqpUpdateStepUi);

	if ($('#cqp_form').length) {
		$('#cqp_form').on('submit', function (e) {
			$('.cqp-qitem').prop('disabled', false).closest('label.cqp-question-item').removeClass('cqp-sec-capped');
			var pid = parseInt($('#cqp_paper_design_id').val(), 10) || 0;
			if (pid < 1) {
				e.preventDefault();
				if (typeof swal === 'function') {
					swal({ type: 'warning', title: 'Please select a question paper template.' });
				} else {
					alert('Please select a question paper template.');
				}
				cqpUpdateSectionPickProgress();
				return false;
			}
			var plan = window._cqpSectionPlan;
			if (plan && plan.length) {
				var si;
				for (si = 0; si < plan.length; si++) {
					var need = parseInt(plan[si].qnum, 10) || 0;
					var have = $('.cqp-qitem[data-section-idx="' + si + '"]:checked').length;
					if (have !== need) {
						e.preventDefault();
						var msg = 'Section ' + (si + 1) + ' (' + plan[si].typeName + '): select exactly ' + need + ' question(s). Currently ' + have + '.';
						if (typeof swal === 'function') {
							swal({ type: 'warning', title: msg });
						} else {
							alert(msg);
						}
						cqpUpdateSectionPickProgress();
						return false;
					}
				}
			}
		});
	}

	var cqpLoadXhr = null;
	var cqpAllChapters = [];
	var cqpAllQuestions = [];
	function syncChannelUi() {
		var v = $('input[name="cqp_channel"]:checked').val();
		$('#cqp_lbl_on').toggleClass('active', v === 'online');
		$('#cqp_lbl_off').toggleClass('active', v === 'offline');
		if (v === 'offline') {
			$('#cqp_exam_mode').val('offline').trigger('change');
		} else {
			$('#cqp_exam_mode').val('online').trigger('change');
		}
	}
	$('input[name="cqp_channel"]').on('change', syncChannelUi);
	syncChannelUi();

	function lessonSelectedCount() {
		var $s = $('#cqp_lesson_select');
		if (!$s.length) return 0;
		var v = $s.val();
		return v ? (Array.isArray(v) ? v.length : 1) : 0;
	}
	function getSelectedLessonIds() {
		var v = $('#cqp_lesson_select').val() || [];
		if (!Array.isArray(v)) {
			v = [v];
		}
		return v.map(function (id) {
			return parseInt(id, 10) || 0;
		}).filter(function (id) {
			return id > 0;
		});
	}
	function refreshChapterStats() {
		var nLes = lessonSelectedCount();
		var selectedQ = $('.cqp-qitem:checked').length;
		$('#cqp_group_badge').text(nLes);
		$('#cqp_chapter_summary').text(
			nLes + ' lesson' + (nLes === 1 ? '' : 's') + ' · ' + selectedQ + ' question' + (selectedQ === 1 ? '' : 's')
		);
		$('#cqp_sticky_summary').text(selectedQ + ' question' + (selectedQ === 1 ? '' : 's') + ' selected');
		cqpUpdateSectionPickProgress();
		cqpUpdateStepUi();
	}
	function renderBySelectedLessons() {
		var selectedLessons = getSelectedLessonIds();
		var visibleQuestions = cqpAllQuestions.slice();
		var hasLessonPool = $('#cqp_lesson_select option').length > 0;
		if (hasLessonPool) {
			if (selectedLessons.length > 0) {
				visibleQuestions = visibleQuestions.filter(function (q) {
					var lid = parseInt(q.lesson_id, 10) || 0;
					return lid > 0 && selectedLessons.indexOf(lid) !== -1;
				});
			} else {
				visibleQuestions = [];
			}
		}
		renderQuestionPicker(visibleQuestions);
		if (hasLessonPool && selectedLessons.length === 0) {
			$('#cqp_chapter_hint').show().text('Select at least one lesson to view matching questions.');
		} else if (visibleQuestions.length === 0) {
			$('#cqp_chapter_hint').show().text('No questions available for selected lesson(s).');
		} else {
			$('#cqp_chapter_hint').hide();
		}
		if (window._cqpSectionPlan && window._cqpSectionPlan.length) {
			cqpTrimSectionSelectionsIfOverCap();
		}
		cqpUpdateSectionPickProgress();
		cqpRefreshPaperPreview();
		cqpSyncQuestionPickVisuals();
		refreshChapterStats();
	}
	function cqpSyncQuestionPickVisuals() {
		$('#cqp_question_list label.cqp-question-item').each(function () {
			$(this).toggleClass('cqp-question-item--on', $(this).find('.cqp-qitem').first().is(':checked'));
		});
	}
	function renderQuestionPicker(questions) {
		var secs = cqpGetValidTemplateSections();
		if (secs.length > 0) {
			renderSectionedQuestionPicker(questions, secs);
		} else {
			renderFlatQuestionPicker(questions);
		}
	}
	function renderFlatQuestionPicker(questions) {
		window._cqpSectionPlan = null;
		$('#cqp_template_sec_hint').hide();
		var $list = $('#cqp_question_list');
		$list.empty();
		if (!questions || !questions.length) {
			$('#cqp_question_box').hide();
			return;
		}
		$.each(questions, function (i, row) {
			var qid = parseInt(row.id, 10) || 0;
			if (!qid) return;
			var text = String(row.question || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
			if (text.length > 140) text = text.substring(0, 140) + '...';
			var label = $('<label class="cqp-question-item"></label>');
			var $side = $('<span class="cqp-qitem-side"></span>');
			$side.append(
				$('<input type="checkbox" class="cqp-qitem" name="question_ids[]">')
					.val(qid)
					.prop('checked', true)
			);
			var $body = $('<span class="cqp-question-body"></span>');
			$body.append($('<div class="cqp-question-text"></div>').text(text));
			label.append($side, $body);
			$list.append(label);
		});
		$('#cqp_question_box').show();
		cqpSyncQuestionPickVisuals();
	}
	function renderSectionedQuestionPicker(questions, secs) {
		window._cqpSectionPlan = secs;
		$('#cqp_template_sec_hint').show();
		var $list = $('#cqp_question_list');
		$list.empty();
		if (!questions || !questions.length) {
			$('#cqp_question_box').hide();
			return;
		}
		var si;
		for (si = 0; si < secs.length; si++) {
			var sec = secs[si];
			var baseType = sec.baseType;
			var $panel = $('<div class="cqp-sec-panel"></div>');
			var $head = $('<div class="cqp-sec-head"></div>');
			$head.append(
				$('<span></span>').html(
					'<strong>Section ' + (si + 1) + '</strong> — ' + $('<span/>').text(sec.typeName).html()
					+ ' · pick ' + sec.qnum + ' for paper'
					+ (sec.attempt < sec.qnum ? (' · students attempt ' + sec.attempt) : '')
				)
			);
			$head.append($('<span class="cqp-sec-prog cqp-sec-prog-warn" id="cqp_sec_prog_' + si + '">0 / ' + sec.qnum + '</span>'));
			$panel.append($head);
			var $inner = $('<div class="cqp-sec-qlist"></div>');
			var pool = [];
			$.each(questions, function (_, row) {
				if (cqpQuestionMatchesSectionType(row, baseType)) {
					pool.push(row);
				}
			});
			if (!pool.length) {
				$inner.append(
					$('<p class="text-muted text-sm mb-none"></p>').text(
						'No questions of this type in the bank for the current class, section, and subject (and lesson filter).'
					)
				);
			} else {
				$.each(pool, function (_, row) {
					var qid = parseInt(row.id, 10) || 0;
					if (!qid) return;
					var text = String(row.question || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
					if (text.length > 140) text = text.substring(0, 140) + '...';
					var label = $('<label class="cqp-question-item"></label>');
					var $side = $('<span class="cqp-qitem-side"></span>');
					$side.append(
						$('<input type="checkbox" class="cqp-qitem" name="question_ids[]">')
							.val(qid)
							.attr('data-section-idx', String(si))
							.attr('data-q-type', row.type != null ? String(row.type) : '')
							.prop('checked', false)
					);
					var $body = $('<span class="cqp-question-body"></span>');
					$body.append($('<div class="cqp-question-text"></div>').text(text));
					label.append($side, $body);
					$inner.append(label);
				});
			}
			$panel.append($inner);
			$list.append($panel);
		}
		$('#cqp_question_box').show();
		cqpUpdateSectionPickProgress();
		cqpSyncQuestionPickVisuals();
	}
	function cqpTrimSectionSelectionsIfOverCap() {
		var plan = window._cqpSectionPlan;
		if (!plan || !plan.length) {
			return;
		}
		var si;
		for (si = 0; si < plan.length; si++) {
			var need = parseInt(plan[si].qnum, 10) || 0;
			if (need < 1) {
				continue;
			}
			var $checked = $('.cqp-qitem[data-section-idx="' + si + '"]:checked');
			if ($checked.length <= need) {
				continue;
			}
			var excess = $checked.length - need;
			var arr = $checked.toArray();
			var remove = arr.slice(-excess);
			$.each(remove, function (_, el) {
				$(el).prop('checked', false);
			});
		}
		cqpSyncQuestionPickVisuals();
	}
	function cqpUpdateSectionPickProgress() {
		var plan = window._cqpSectionPlan;
		if (!plan || !plan.length) {
			$('.cqp-qitem[data-section-idx]').prop('disabled', false).closest('label.cqp-question-item').removeClass('cqp-sec-capped');
			cqpSyncQuestionPickVisuals();
			return;
		}
		var si;
		for (si = 0; si < plan.length; si++) {
			var need = parseInt(plan[si].qnum, 10) || 0;
			var $secInputs = $('.cqp-qitem[data-section-idx="' + si + '"]');
			var n = $secInputs.filter(':checked').length;
			var $p = $('#cqp_sec_prog_' + si);
			if ($p.length) {
				$p.text(n + ' / ' + need);
				$p.toggleClass('cqp-sec-prog-warn', n !== need);
			}
			var atCap = need > 0 && n >= need;
			$secInputs.each(function () {
				var $cb = $(this);
				var $lbl = $cb.closest('label.cqp-question-item');
				if (atCap && !$cb.is(':checked')) {
					$cb.prop('disabled', true);
					$lbl.addClass('cqp-sec-capped');
				} else {
					$cb.prop('disabled', false);
					$lbl.removeClass('cqp-sec-capped');
				}
			});
		}
		cqpSyncQuestionPickVisuals();
	}
	function initLessonMultiselect() {
		var $sel = $('#cqp_lesson_select');
		if (!$sel.length || !$.fn.select2) return;
		try {
			if ($sel.hasClass('select2-hidden-accessible')) {
				$sel.select2('destroy');
			}
			$sel.removeData('__select2');
			$sel.select2({
				theme: 'bootstrap',
				width: '100%',
				placeholder: '<?= addslashes(translate('select')) ?> <?= addslashes(translate('lesson')) ?> (multiple)',
				closeOnSelect: false,
				allowClear: true
			});
		} catch (err) {}
	}
	function loadChapters() {
		if (window._cqpSkipLoadChapters) {
			return;
		}
		var bid = $('input[name="branch_id"]').val();
		var cid = $('#class_id').val();
		var sid = $('#section_id').val();
		var sub = $('#subject_id').val();
		var requestKey = [String(cid || ''), String(sid || ''), String(sub || '')].join('|');
		if (cqpLoadXhr && cqpLoadXhr.readyState !== 4) {
			cqpLoadXhr.abort();
		}
		var $lessonSel = $('#cqp_lesson_select');
		if ($lessonSel.length && $lessonSel.hasClass('select2-hidden-accessible')) {
			$lessonSel.select2('destroy');
		}
		$lessonSel.empty();
		$('#cqp_lesson_block').hide();
		$('#cqp_select_all_lessons').hide();
		$('#cqp_question_list').empty();
		$('#cqp_question_box').hide();
		if (!cid || !sid || !sub) {
			$('#cqp_chapter_summary').text('0 lessons · 0 questions');
			$('#cqp_sticky_summary').text('0 questions selected');
			$('#cqp_group_badge').text('0');
			$('#cqp_chapter_hint').show().text('<?= addslashes(translate('select')) ?> <?= addslashes(translate('class')) ?>, <?= addslashes(translate('section')) ?>, <?= addslashes(translate('subject')) ?> to load lessons.');
			return;
		}
		$('#cqp_chapter_hint').hide();
		cqpLoadXhr = $.post(base_url + 'onlineexam/ajax_paper_chapters', {
			branch_id: bid,
			class_id: cid,
			section_id: sid,
			subject_id: sub
		}, function (res) {
			var nowKey = [
				String($('#class_id').val() || ''),
				String($('#section_id').val() || ''),
				String($('#subject_id').val() || '')
			].join('|');
			if (nowKey !== requestKey) {
				return;
			}
			if (!res || !res.ok) {
				return;
			}
			var lessons = res.lessons || [];
			var questions = res.questions || [];
			if (lessons.length === 0 && questions.length === 0) {
				$('#cqp_chapter_hint').show().text('No lessons or questions for this class, section, and subject.');
				$('#cqp_group_badge').text('0');
				$('#cqp_chapter_summary').text('0 lessons · 0 questions');
				$('#cqp_sticky_summary').text('0 questions selected');
				return;
			}
			$('#cqp_chapter_hint').hide();
			if (lessons.length > 0) {
				$('#cqp_lesson_block').show();
				$('#cqp_select_all_lessons').show();
				$.each(lessons, function (i, row) {
					var cnt = parseInt(row.qcount, 10) || 0;
					var label = (row.name || '') + ' (' + cnt + ')';
					$lessonSel.append(
						$('<option></option>').val(row.id).text(label).attr('data-qcount', cnt).prop('selected', true)
					);
				});
				initLessonMultiselect();
				$lessonSel.off('change.cqp').on('change.cqp', refreshChapterStats);
			}
			cqpAllChapters = [];
			cqpAllQuestions = questions;
			renderBySelectedLessons();
		}, 'json');
	}
	$('#class_id, #section_id, #subject_id').on('change', loadChapters);
	$(document).on('change', '.cqp-qitem', function () {
		var $t = $(this);
		var sdx = $t.attr('data-section-idx');
		if (sdx !== undefined && sdx !== null && String(sdx) !== '') {
			var secIdx = parseInt(sdx, 10);
			if ($t.is(':checked')) {
				var qid = String($t.val());
				var dup = $('.cqp-qitem:checked').filter(function () {
					return String($(this).val()) === qid;
				}).length;
				if (dup > 1) {
					$t.prop('checked', false);
					if (typeof swal === 'function') {
						swal({ type: 'info', title: 'This question is already selected in another section.' });
					} else {
						alert('This question is already selected in another section.');
					}
					cqpUpdateSectionPickProgress();
					refreshChapterStats();
					cqpRefreshPaperPreview();
					cqpSyncQuestionPickVisuals();
					return;
				}
				var plan = window._cqpSectionPlan && window._cqpSectionPlan[secIdx];
				if (plan) {
					var needLim = parseInt(plan.qnum, 10) || 0;
					var n = $('.cqp-qitem[data-section-idx="' + secIdx + '"]:checked').length;
					if (needLim > 0 && n > needLim) {
						$t.prop('checked', false);
						if (typeof swal === 'function') {
							swal({ type: 'warning', title: 'This section allows only ' + needLim + ' question(s).' });
						} else {
							alert('This section allows only ' + needLim + ' question(s).');
						}
						cqpUpdateSectionPickProgress();
						refreshChapterStats();
						cqpRefreshPaperPreview();
						cqpSyncQuestionPickVisuals();
						return;
					}
				}
			}
		}
		refreshChapterStats();
		cqpRefreshPaperPreview();
		cqpSyncQuestionPickVisuals();
	});
	$('#cqp_select_all_lessons').on('click', function (e) {
		e.preventDefault();
		var $sel = $('#cqp_lesson_select');
		if (!$sel.length) return;
		$sel.find('option').prop('selected', true);
		$sel.trigger('change');
	});
	$(document).on('change', '#cqp_lesson_select', function () {
		renderBySelectedLessons();
	});
	$('#cqp_toggle_qbank').on('click', function () {
		var $b = $('#cqp_qbank_body');
		$b.toggleClass('collapsed');
		$(this).html($b.hasClass('collapsed') ? '<i class="fas fa-chevron-down"></i> Show' : '<i class="fas fa-chevron-up"></i> Hide');
	});
	loadChapters();
	if (!window.CQP_HAS_FORM) {
		return;
	}
	var _pre = parseInt(String(window.CQP_PRESELECT || '0'), 10) || 0;
	if (_pre > 0 && $('#cqp_template_pick option[value="' + _pre + '"]').length) {
		$('#cqp_template_pick').val(String(_pre)).trigger('change');
	} else if ($('#cqp_paper_design_id').val() !== '0' && $('#cqp_template_pick').val()) {
		cqpApplyTemplateFromPicker();
	}
	cqpRefreshPaperPreview();
	cqpUpdateTplFieldVisibility();
	cqpUpdateStepUi();
})(jQuery);
</script>

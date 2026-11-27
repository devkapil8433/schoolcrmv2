<?php
$branch_id = isset($branch_id) ? $branch_id : '';
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
$sc = (isset($saved_config) && is_array($saved_config)) ? $saved_config : null;
$dqp = function ($key, $def = '') use ($sc) {
	if ($sc === null || !array_key_exists($key, $sc)) {
		return $def;
	}
	return $sc[$key];
};
// Class / section / group / subject are chosen when creating a paper from this template.
$branchQtypes = isset($branch_question_types) && is_array($branch_question_types) ? $branch_question_types : array();
$qtMeta = array();
$qtDropdown = array('' => translate('select'));
foreach ($branchQtypes as $bt) {
	$tid = (string) $bt['id'];
	$qtDropdown[$tid] = $bt['name'];
	$qtMeta[$tid] = array(
		'name' => $bt['name'],
		'marks' => isset($bt['marks']) ? $bt['marks'] : '',
		'question_type_id' => isset($bt['question_type_id']) ? (int) $bt['question_type_id'] : 0,
	);
}
$preview_i18n = array(
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
$numOpts = array('' => translate('select'));
for ($n = 1; $n <= 30; $n++) {
	$numOpts[(string) $n] = (string) $n;
}
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
$logoShowOpts = array(
	'show' => 'Show logo',
	'hide' => 'Hide logo',
);
$barcodeOpts = array(
	'show' => 'Show bar code',
	'hide' => 'Hide bar code',
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
$editing_id = isset($editing_design_id) ? (int) $editing_design_id : 0;
$tpl_title = isset($editing_title) ? $editing_title : '';
$paper_designs = isset($paper_designs) && is_array($paper_designs) ? $paper_designs : array();
$def_exam_mode = $dqp('exam_mode', '');
$def_exam_type = $dqp('exam_type', '');
$def_assess = $dqp('exam_assessment', '');
$def_time = $dqp('paper_time', '150');
$def_max_marks = (string) $dqp('max_marks', '');
$def_exam_name = $dqp('exam_name', 'Monthly Test');
$def_logo = $dqp('logo_show', 'show');
$def_bar = $dqp('barcode_show', 'hide');
$def_school_show = $dqp('school_show', 'show');
$def_reg_show = $dqp('reg_show', 'show');
$def_recognition_show = $dqp('recognition_show', 'show');
$def_udise_show = $dqp('udise_show', 'show');
$def_note_show = $dqp('note_show', 'show');
$def_reg = $dqp('reg_no', '');
$def_udise = $dqp('udise', '');
$def_rec = $dqp('recognition', 'Recognized by U.P goverment upto 8 class');
$def_note = $dqp('note', 'All Question are compulsory . Writing should be clean and neat.');
$def_school_name = $dqp('school_name', $schoolName);
$def_school_loc = $dqp('school_location', $cityLine);
$def_school_name_size = (string) $dqp('school_name_size', '16');
$def_school_loc_size = (string) $dqp('school_loc_size', '12');
$def_logo_size = (string) $dqp('logo_size', '52');
$def_header_size = (string) $dqp('header_font_size', '11');
$def_meta_size = (string) $dqp('meta_font_size', '12');
$def_logo_align = (string) $dqp('logo_align', 'left');
$def_school_align = (string) $dqp('school_align', 'left');
$def_barcode_align = (string) $dqp('barcode_align', 'center');
$def_logo_pos_x = $dqp('logo_pos_x', '');
$def_logo_pos_y = $dqp('logo_pos_y', '');
$def_school_pos_x = $dqp('school_pos_x', '');
$def_school_pos_y = $dqp('school_pos_y', '');
$def_barcode_pos_x = $dqp('barcode_pos_x', '');
$def_barcode_pos_y = $dqp('barcode_pos_y', '');
$nameSizeOpts = array(
	'16' => 'Normal (16px)',
	'18' => 'Medium (18px)',
	'20' => 'Large (20px)',
	'22' => 'X-Large (22px)',
	'24' => 'Huge (24px)',
	'28' => 'Extra Huge (28px)',
);
$locSizeOpts = array(
	'11' => 'Small (11px)',
	'12' => 'Normal (12px)',
	'14' => 'Large (14px)',
	'16' => 'X-Large (16px)',
);
$logoSizeOpts = array(
	'40' => 'Small (40px)',
	'52' => 'Normal (52px)',
	'64' => 'Large (64px)',
	'80' => 'Huge (80px)',
);
$alignOpts = array(
	'left' => 'Left',
	'center' => 'Center',
	'right' => 'Right',
);
if (!isset($alignOpts[$def_logo_align])) {
	$def_logo_align = 'left';
}
if (!isset($alignOpts[$def_school_align])) {
	$def_school_align = 'left';
}
if (!isset($alignOpts[$def_barcode_align])) {
	$def_barcode_align = 'center';
}
$headerSizeOpts = array(
	'10' => 'Small (10px)',
	'11' => 'Normal (11px)',
	'12' => 'Large (12px)',
	'13' => 'X-Large (13px)',
);
$metaSizeOpts = array(
	'11' => 'Small (11px)',
	'12' => 'Normal (12px)',
	'13' => 'Large (13px)',
	'14' => 'X-Large (14px)',
);
if ($def_max_marks !== '' && !isset($mmOpts[$def_max_marks])) {
	$mmOpts[$def_max_marks] = $def_max_marks;
}
$secRows = $dqp('sections', array());
if (!is_array($secRows)) {
	$secRows = array();
}
$mapLegacyQtypeToBranchRowId = function ($legacyKey) use ($branchQtypes) {
	if ($legacyKey === '' || $legacyKey === null) {
		return '';
	}
	foreach ($branchQtypes as $bt) {
		if ((string) $bt['question_type_id'] === (string) $legacyKey) {
			return (string) $bt['id'];
		}
	}
	return '';
};
$sectionRowsForView = array();
if (!empty($secRows) && is_array($secRows)) {
	foreach ($secRows as $one) {
		if (!is_array($one)) {
			continue;
		}
		$hasData = !empty($one['qtype_id']) || !empty($one['qtype'])
			|| (isset($one['qnum']) && (string) $one['qnum'] !== '');
		if ($hasData) {
			$sectionRowsForView[] = $one;
		}
	}
}
if (count($sectionRowsForView) < 1) {
	$sectionRowsForView[] = array();
}
$can_save_new = get_permission('question_bank', 'is_add');
$can_save_edit = get_permission('question_bank', 'is_edit');
$can_delete_tpl = get_permission('question_bank', 'is_delete');
$show_save = ($editing_id > 0 && $can_save_edit) || ($editing_id === 0 && $can_save_new);
$branch_id_int = (int) $branch_id;
$show_designer = !empty($show_designer);
$branch_query = $branch_id_int > 0 ? ('?branch_id=' . (int) $branch_id_int) : '';
$new_template_url = base_url('onlineexam/design_question_paper' . ($branch_id_int > 0 ? ('?branch_id=' . (int) $branch_id_int . '&new=1') : '?new=1'));
$list_template_url = base_url('onlineexam/design_question_paper' . $branch_query);
?>
<style>
.dqp-list-shell .panel {
	border: 1px solid #e2e8f0; border-radius: 12px;
	box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px rgba(15, 23, 42, .04);
	overflow: hidden;
}
.dqp-list-shell .panel-heading {
	background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
	border-bottom: 1px solid #e2e8f0;
}
.dqp-list-shell .table > thead > tr > th {
	background: #f8fafc; border-bottom-width: 1px; font-size: 12px; text-transform: uppercase;
	letter-spacing: .03em; color: #64748b; font-weight: 700;
}
.dqp-list-shell .table > tbody > tr.info > td { background: #eff6ff !important; }
.dqp-list-shell .table > tbody > tr:hover > td { background: #f8fafc; }
.dqp-empty {
	text-align: center; padding: 28px 16px; border: 1px dashed #cbd5e1; border-radius: 10px; background: #f8fafc;
}
.dqp-pick-shell .panel {
	border: 1px solid #e2e8f0; border-radius: 12px;
	box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px rgba(15, 23, 42, .04);
}
</style>
<?php if ($branch_id_int < 1): ?>
<div class="row dqp-pick-shell">
	<div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
		<section class="panel panel-default">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-file-lines"></i> Question Paper Template</h4>
				<div class="flex items-center gap-2">
					<a href="<?= base_url('onlineexam/question') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-arrow-left"></i> <?= translate('back') ?>
					</a>
				</div>
			</header>
			<div class="panel-body">
				<p class="mb-md"><i class="fas fa-building"></i> <?= translate('select') ?> <?= translate('branch') ?> first to view and manage saved templates.</p>
				<?php echo form_open('onlineexam/design_question_paper', array('method' => 'get')); ?>
				<div class="form-group">
					<label class="portal-label"><?= translate('branch') ?> <span class="required">*</span></label>
					<?php
						$arrayBranchPick = $this->app_lib->getSelectList('branch');
						echo form_dropdown('branch_id', $arrayBranchPick, '', "class='portal-input' required data-plugin-selectTwo data-width='100%'");
					?>
				</div>
				<button type="submit" class="btn-portal-primary"><i class="fas fa-arrow-right"></i> <?= translate('continue') ?></button>
				<?php echo form_close(); ?>
			</div>
		</section>
	</div>
</div>
<?php else: ?>
<div class="row dqp-list-shell">
	<div class="col-md-12">
		<section class="panel panel-default">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-folder-open"></i> Saved Templates</h4>
				<div class="flex items-center gap-2">
					<?php if (!$show_designer): ?>
					<?php if (is_superadmin_loggedin()): ?>
					<a href="<?= base_url('onlineexam/design_question_paper') ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-arrow-left"></i> <?= translate('back') ?>
					</a>
					<?php else: ?>
					<a href="<?= base_url('onlineexam/question') . $branch_query ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-arrow-left"></i> <?= translate('back') ?>
					</a>
					<?php endif; ?>
					<?php endif; ?>
					<a href="<?= base_url('onlineexam/create_question_paper') . $branch_query ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-file-circle-plus"></i> Create Question Paper
					</a>
					<?php if ($can_save_new): ?>
					<a href="<?= html_escape($new_template_url) ?>" class="btn-portal-primary btn-portal-sm">
						<i class="fas fa-plus"></i> Add New Template
					</a>
					<?php endif; ?>
				</div>
			</header>
			<div class="panel-body">
				<?php if ($branch_id_int > 0 && empty($branchQtypes)): ?>
				<div class="alert alert-warning mb-md">
					<i class="fas fa-tags"></i> No branch question types found. Configure names and marks under
					<a href="<?= base_url('onlineexam/question_type?branch_id=' . (int) $branch_id_int) ?>">Question Type</a>, then reload this page.
				</div>
				<?php endif; ?>
				<?php if (count($paper_designs) > 0): ?>
				<div class="portal-table-wrap">
					<table class="table table-bordered table-condensed table-hover mb-none">
						<thead>
							<tr>
								<th width="60">#</th>
								<th><?= translate('title') ?></th>
								<th><?= translate('updated_at') ?></th>
								<th class="text-center no-sort" width="160"><?= translate('action') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php $sn = 1; foreach ($paper_designs as $pd):
								$pid = (int) $pd['id'];
								$isActive = ($editing_id > 0 && $editing_id === $pid);
								?>
							<tr<?= $isActive ? ' class="info"' : '' ?>>
								<td><?= (int) $sn++ ?></td>
								<td><?= html_escape($pd['title']) ?></td>
								<td><?= !empty($pd['updated_at']) ? html_escape($pd['updated_at']) : '—' ?></td>
								<td class="text-center">
									<a href="<?= base_url('onlineexam/design_question_paper?id=' . $pid . '&branch_id=' . (int) $branch_id_int) ?>" class="btn btn-xs btn-default"><i class="fas fa-pen-nib"></i> <?= translate('edit') ?></a>
									<?php if ($can_delete_tpl): ?>
									<button type="button" class="btn btn-xs btn-danger dqp-delete-btn" data-id="<?= $pid ?>"><i class="fas fa-trash"></i></button>
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php else: ?>
				<div class="dqp-empty">
					<i class="fas fa-file-circle-plus" style="font-size:28px;color:#94a3b8;margin-bottom:8px;"></i>
					<p class="mb-sm text-muted">No saved templates yet.</p>
					<?php if ($can_save_new): ?>
					<a href="<?= html_escape($new_template_url) ?>" class="btn-portal-primary btn-sm"><i class="fas fa-plus"></i> Add New Template</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
<?php if ($show_designer): ?>
<style>
.dqp-workspace {
	--dqp-ink: #0f172a;
	--dqp-muted: #64748b;
	--dqp-line: #e2e8f0;
	--dqp-soft: #f8fafc;
	--dqp-accent: #0366d6;
	--dqp-accent-soft: #eff6ff;
	--dqp-ok: #15803d;
	--dqp-radius: 12px;
}
.dqp-workspace .panel {
	border: 1px solid var(--dqp-line);
	border-radius: var(--dqp-radius);
	box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px rgba(15, 23, 42, .04);
	overflow: hidden;
}
.dqp-workspace .panel-heading {
	background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
	border-bottom: 1px solid var(--dqp-line);
	padding: 14px 16px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 10px;
	flex-wrap: wrap;
}
.dqp-workspace .panel-heading h4 {
	margin: 0;
	font-size: 15px;
	font-weight: 700;
	color: var(--dqp-ink);
	display: flex;
	align-items: center;
	gap: 8px;
}
.dqp-workspace .panel-heading h4 i {
	width: 28px; height: 28px; border-radius: 8px;
	display: inline-flex; align-items: center; justify-content: center;
	background: var(--dqp-accent-soft); color: var(--dqp-accent); font-size: 13px;
}
.dqp-workspace .panel-body { padding: 16px; background: #fff; }
.dqp-workspace .dqp-form-section {
	border: 1px solid var(--dqp-line);
	border-radius: 10px;
	padding: 14px 14px 6px;
	margin-bottom: 14px;
	background: linear-gradient(180deg, #fff 0%, #fcfdff 100%);
}
.dqp-workspace .dqp-form-section > h5 {
	margin: 0 0 12px;
	padding-bottom: 10px;
	border-bottom: 1px dashed var(--dqp-line);
	font-size: 13px;
	font-weight: 700;
	color: var(--dqp-ink);
	display: flex;
	align-items: center;
	gap: 8px;
}
.dqp-workspace .dqp-form-section > h5 .dqp-sec-badge {
	display: inline-flex; align-items: center; justify-content: center;
	min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px;
	background: var(--dqp-accent); color: #fff; font-size: 11px; font-weight: 700;
}
.dqp-workspace .dqp-form-section > h5 .dqp-sec-sub {
	margin-left: auto; font-size: 11px; font-weight: 600; color: var(--dqp-muted);
}
.dqp-workspace .dqp-hint {
	font-size: 12px; color: var(--dqp-muted); line-height: 1.45; margin: 0 0 12px;
	padding: 10px 12px; border-radius: 8px; background: #f1f5f9; border: 1px solid #e2e8f0;
}
.dqp-workspace .dqp-hint a { font-weight: 600; }
.dqp-workspace .dqp-section-row {
	border: 1px solid var(--dqp-line);
	border-radius: 10px;
	padding: 12px 12px 4px;
	margin-bottom: 10px;
	background: #fff;
	box-shadow: inset 3px 0 0 var(--dqp-accent);
	transition: border-color .15s, box-shadow .15s;
}
.dqp-workspace .dqp-section-row:hover {
	border-color: #cbd5e1;
	box-shadow: inset 3px 0 0 var(--dqp-accent), 0 4px 14px rgba(15,23,42,.05);
}
.dqp-workspace .dqp-section-total {
	display: inline-flex; align-items: center; justify-content: center;
	min-width: 48px; padding: 6px 10px; border-radius: 8px;
	background: #ecfdf5; color: var(--dqp-ok); font-size: 14px; border: 1px solid #bbf7d0;
}
.dqp-workspace .dqp-actions-row {
	display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 4px;
}
.dqp-workspace .dqp-mm-warn {
	display: none; margin-top: 10px; padding: 10px 12px; border-radius: 8px;
	border: 1px solid #fbbf24; background: #fffbeb; color: #92400e;
	align-items: flex-start; justify-content: space-between; gap: 10px; flex-wrap: wrap;
}
.dqp-workspace .dqp-mm-warn.is-visible { display: flex; }
.dqp-workspace .dqp-mm-warn.is-over {
	border-color: #f87171; background: #fef2f2; color: #991b1b;
}
.dqp-workspace .dqp-mm-warn-text {
	flex: 1; min-width: 180px; font-size: 12.5px; line-height: 1.45; font-weight: 600;
}
.dqp-workspace .dqp-mm-warn-text .dqp-mm-nums { font-variant-numeric: tabular-nums; }
.dqp-workspace #dqp_mm_sync_btn { white-space: nowrap; flex-shrink: 0; }
.dqp-workspace .dqp-preview-col .panel { position: sticky; top: 56px; }
@media (max-width: 991px) {
	.dqp-workspace .dqp-preview-col .panel { position: static; }
}
.dqp-preview-desk {
	background:
		radial-gradient(circle at 12% 8%, rgba(3,102,214,.06), transparent 40%),
		linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
	border: 1px solid var(--dqp-line);
	border-radius: 12px;
	padding: 14px;
}
.dqp-preview {
	position: relative; background: #fff; border: 1px solid #dbe3ee; border-radius: 6px;
	padding: 18px 20px 28px; min-height: 420px; max-height: calc(100vh - 180px); overflow-y: auto;
	box-shadow: 0 1px 0 rgba(255,255,255,.8) inset, 0 10px 30px rgba(15,23,42,.08);
}
.dqp-preview-toggles {
	display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
	padding: 10px 12px; margin-bottom: 12px;
	background: #fff; border: 1px solid var(--dqp-line); border-radius: 10px;
}
.dqp-preview-toggles .dqp-tog {
	border: 1px solid #d1d5da; background: #fff; color: #475569;
	border-radius: 999px; padding: 5px 11px; font-size: 12px; font-weight: 600;
	cursor: pointer; line-height: 1.2; transition: all .12s ease;
}
.dqp-preview-toggles .dqp-tog:hover { border-color: #94a3b8; background: #f8fafc; }
.dqp-preview-toggles .dqp-tog.active {
	border-color: var(--dqp-accent); background: var(--dqp-accent-soft); color: var(--dqp-accent);
	box-shadow: 0 0 0 2px rgba(3,102,214,.12);
}
.dqp-preview-toggles .dqp-tog-label {
	font-size: 11px; font-weight: 700; color: var(--dqp-muted); margin-right: 2px; text-transform: uppercase; letter-spacing: .04em;
}
.dqp-preview-toggles .btn-portal-secondary.btn-sm {
	border-radius: 999px; margin-left: 2px;
}
.dqp-text-modal-backdrop {
	position: fixed; inset: 0; z-index: 13000; background: rgba(15, 23, 42, .48);
	display: none; align-items: center; justify-content: center; padding: 16px;
	backdrop-filter: blur(2px);
}
.dqp-text-modal-backdrop.is-open { display: flex; }
.dqp-text-modal {
	width: min(520px, 96vw); background: #fff; border-radius: 14px;
	box-shadow: 0 24px 60px rgba(15,23,42,.28); overflow: hidden; border: 1px solid #e2e8f0;
}
.dqp-text-modal-head {
	display: flex; align-items: center; justify-content: space-between; gap: 10px;
	padding: 14px 16px; border-bottom: 1px solid var(--dqp-line); background: linear-gradient(180deg,#fff,#f8fafc);
}
.dqp-text-modal-head h5 { margin: 0; font-size: 14px; font-weight: 700; color: var(--dqp-ink); }
.dqp-text-modal-body { padding: 14px 16px 4px; max-height: 70vh; overflow: auto; }
.dqp-text-modal-body .control-label {
	font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px; display: block; text-transform: uppercase; letter-spacing: .03em;
}
.dqp-text-modal-foot {
	padding: 12px 16px 16px; display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--dqp-line); background: #f8fafc;
}
.dqp-preview-watermark {
	position: absolute; left: 50%; top: 45%; transform: translate(-50%, -50%) rotate(-14deg);
	font-size: 48px; font-weight: 700; color: rgba(3, 102, 214, 0.06); pointer-events: none; white-space: nowrap; z-index: 0;
}
.dqp-preview-inner { position: relative; z-index: 1; }
.dqp-preview-head-top { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 8px; color: #57606a; }
.dqp-preview-head-top .dqp-center { flex: 1; text-align: center; font-weight: 600; color: #24292e; padding: 0 8px; }
.dqp-preview-head-top > span,
.dqp-preview-note,
#pv_school_text {
	cursor: pointer;
}
.dqp-preview-head-top > span:hover,
.dqp-preview-note:hover,
#pv_school_text:hover {
	outline: 1px dashed #94a3b8;
	outline-offset: 2px;
}
.dqp-header-stage {
	position: relative; width: 100%; min-height: 100px; height: 140px; margin-bottom: 14px;
	border: 1px dashed #c8d0d8; border-radius: 6px; background: rgba(241, 248, 255, 0.45);
	overflow: hidden; box-sizing: border-box;
}
.dqp-header-stage::before {
	content: 'Drag items · corner resize · bottom edge = box height';
	position: absolute; left: 8px; top: 4px; font-size: 10px; color: #94a3b8; pointer-events: none; z-index: 0;
}
.dqp-stage-height-handle {
	position: absolute; left: 0; right: 0; bottom: -7px; height: 14px; z-index: 8;
	cursor: ns-resize; touch-action: none;
	display: flex; align-items: center; justify-content: center;
}
.dqp-stage-height-handle::before {
	content: ''; width: 42px; height: 4px; border-radius: 3px; background: #0366d6; opacity: .55;
}
.dqp-stage-height-handle:hover::before,
.dqp-header-stage.dqp-stage-resizing .dqp-stage-height-handle::before { opacity: 1; }
.dqp-stage-height-badge {
	position: absolute; right: 8px; bottom: 6px; z-index: 3;
	font-size: 10px; font-weight: 700; color: #0366d6; background: rgba(255,255,255,.92);
	border: 1px solid #c8e1ff; border-radius: 8px; padding: 1px 7px; pointer-events: none;
}
.dqp-guide-v, .dqp-guide-h {
	position: absolute; pointer-events: none; z-index: 1; opacity: 0; transition: opacity .12s;
}
.dqp-guide-v {
	top: 0; bottom: 0; left: 50%; width: 0; border-left: 1px dashed #0366d6;
}
.dqp-guide-h {
	left: 0; right: 0; top: 50%; height: 0; border-top: 1px dashed #0366d6;
}
.dqp-header-stage.dqp-show-guides .dqp-guide-v,
.dqp-header-stage.dqp-show-guides .dqp-guide-h { opacity: .85; }
.dqp-header-stage.dqp-snap-x .dqp-guide-v { border-left-style: solid; opacity: 1; }
.dqp-header-stage.dqp-snap-y .dqp-guide-h { border-top-style: solid; opacity: 1; }
.dqp-drag-item {
	position: absolute; z-index: 2; cursor: move; touch-action: none; user-select: none;
	padding: 4px; border-radius: 4px; border: 1px solid transparent; box-sizing: border-box;
}
.dqp-drag-item:hover,
.dqp-drag-item.dqp-dragging,
.dqp-drag-item.dqp-resizing { border-color: #0366d6; background: rgba(3, 102, 214, 0.04); }
.dqp-drag-item.dqp-dragging,
.dqp-drag-item.dqp-resizing { box-shadow: 0 4px 14px rgba(3,102,214,.2); opacity: .95; z-index: 5; }
.dqp-drag-item.dqp-is-centered {
	border-color: #1a7f37; background: rgba(26, 127, 55, 0.06);
}
.dqp-place-badge {
	position: absolute; left: 50%; top: -18px; transform: translateX(-50%);
	font-size: 10px; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
	background: #24292e; color: #fff; border-radius: 10px; padding: 1px 7px; pointer-events: none;
	opacity: 0; transition: opacity .12s;
}
.dqp-drag-item:hover .dqp-place-badge,
.dqp-drag-item.dqp-dragging .dqp-place-badge,
.dqp-drag-item.dqp-resizing .dqp-place-badge,
.dqp-drag-item.dqp-is-centered .dqp-place-badge { opacity: 1; }
.dqp-place-badge.dqp-badge-center { background: #1a7f37; }
.dqp-resize-handle {
	position: absolute; right: -5px; bottom: -5px; width: 12px; height: 12px;
	border-radius: 2px; background: #0366d6; border: 1px solid #fff; cursor: nwse-resize;
	touch-action: none; z-index: 6;
}
.dqp-school-logo { width: 52px; height: 52px; object-fit: contain; border-radius: 4px; border: 1px solid #eaecef; display: block; }
.dqp-school-logo-placeholder { width: 52px; height: 52px; border-radius: 4px; background: #f6f8fa; border: 1px dashed #d1d5da; }
.dqp-school-logo-wrap.hidden { display: none !important; }
.dqp-drag-item.hidden,
#pv_reg.hidden,
#pv_recognition.hidden,
#pv_udise_wrap.hidden,
#pv_note_wrap.hidden { display: none !important; }
.dqp-school-text { min-width: 80px; max-width: 80%; height: auto !important; }
.dqp-school-title { font-size: 16px; font-weight: 700; margin: 0 0 2px; color: #24292e; }
.dqp-school-loc { font-size: 12px; font-weight: 600; color: #586069; margin: 0; }
.dqp-preview-meta {
	display: flex; justify-content: space-between; font-size: 12px; padding-top: 8px; margin-top: 8px;
	border-top: 1px solid #24292e; color: #24292e;
}
.dqp-preview-meta ul { list-style: none; padding: 0; margin: 0; }
.dqp-preview-note { font-size: 12px; margin: 10px 0 12px; color: #444d56; }
.dqp-q-title { font-weight: 700; font-size: 13px; margin: 10px 0 2px; color: #24292e; }
.dqp-attempt-line {
	font-size: 12px; font-weight: 600; color: #0366d6; margin: 0 0 8px;
	font-style: italic;
}
.dqp-sample-block {
	margin-bottom: 14px; padding: 10px 12px; border: 1px solid #e8eef5; border-radius: 8px; background: #fbfdff;
}
.dqp-bar-placeholder {
	width: 80px; height: 80px; aspect-ratio: 1 / 1;
	border: 1px dashed #d1d5da; border-radius: 4px; font-size: 10px; color: #959da5;
	display: flex; align-items: center; justify-content: center; text-align: center;
	background: #fff; box-sizing: border-box; padding: 6px; line-height: 1.2; overflow: hidden;
}
.dqp-bar-placeholder .dqp-bar-label { display: block; word-break: break-word; max-width: 100%; }
.dqp-bar-placeholder.hidden, #pv_bar.hidden { display: none !important; }
.dqp-footer-mark {
	position: absolute; right: 10px; bottom: 20px; font-size: 22px; font-weight: 700;
	color: rgba(203, 36, 49, 0.35); transform: rotate(-28deg); pointer-events: none;
}
@media print {
	body * { visibility: hidden !important; }
	#dqp_preview, #dqp_preview * { visibility: visible !important; }
	#dqp_preview {
		position: absolute !important; left: 0; top: 0; width: 100%;
		max-height: none !important; overflow: visible !important;
		border: none !important; box-shadow: none !important; padding: 12mm !important;
	}
	.dqp-preview-watermark,
	.dqp-header-stage::before,
	.dqp-guide-v, .dqp-guide-h,
	.dqp-place-badge,
	.dqp-resize-handle,
	.dqp-stage-height-handle,
	.dqp-stage-height-badge,
	.dqp-preview-desk { display: none !important; background: none !important; padding: 0 !important; border: none !important; }
	.dqp-header-stage { border: none !important; background: transparent !important; overflow: visible !important; }
	.dqp-drag-item { border-color: transparent !important; background: transparent !important; box-shadow: none !important; }
	.dqp-sample-block { border: none !important; background: transparent !important; padding: 0 !important; }
}
</style>
<div class="row dqp-workspace">
	<div class="col-md-6 col-lg-5">
		<section class="panel panel-default">
			<header class="panel-heading">
				<h4><i class="fas fa-file-lines"></i> <?= $editing_id > 0 ? 'Edit Template' : 'Add New Template' ?></h4>
				<div class="flex items-center gap-2">
					<a href="<?= html_escape($list_template_url) ?>" class="btn-portal-secondary btn-portal-sm">
						<i class="fas fa-arrow-left"></i> <?= translate('back') ?>
					</a>
				</div>
			</header>
			<div class="panel-body dqp-form">
				<input type="hidden" id="dqp_editing_id" value="<?= (int) $editing_id ?>">
				<input type="hidden" id="dqp_branch_id" name="branch_id" value="<?= $branch_id_int ?>">
				<div class="dqp-form-section">
				<h5><span class="dqp-sec-badge">1</span> Template name &amp; actions</h5>
				<div class="row">
					<div class="col-md-8 mb-sm">
						<div class="form-group">
							<label class="portal-label">Template name</label>
							<input type="text" class="portal-input" id="dqp_template_title" value="<?= html_escape($tpl_title) ?>" placeholder="<?= translate('title') ?>">
						</div>
					</div>
					<div class="col-md-4 mb-sm">
						<label class="portal-label" style="display:block">&nbsp;</label>
						<?php if ($show_save): ?>
						<button type="button" class="btn-portal-primary w-full" id="dqp_save_btn"><i class="fas fa-save"></i> <?= translate('save') ?> template</button>
						<?php else: ?>
						<p class="text-muted text-sm mt-xs"><?= translate('access_denied') ?></p>
						<?php endif; ?>
					</div>
				</div>
				</div>
				<div class="dqp-form-section">
				<h5><span class="dqp-sec-badge">2</span> Exam settings <span class="dqp-sec-sub">time · marks · mode</span></h5>
				<div class="row">
					<div class="col-md-4 mb-sm">
						<div class="form-group">
							<label class="portal-label">Time <span class="required">*</span></label>
							<?php echo form_dropdown("paper_time", $timeOpts, $def_time, "class='portal-input' id='dqp_time' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
						</div>
					</div>
					<div class="col-md-4 mb-sm">
						<div class="form-group">
							<label class="portal-label">Max Marks <span class="required">*</span></label>
							<?php echo form_dropdown("max_marks", $mmOpts, $def_max_marks, "class='portal-input' id='dqp_mm' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
						</div>
					</div>
					<div class="col-md-4 mb-sm">
						<div class="form-group">
							<label class="portal-label">Exam mode</label>
							<?php echo form_dropdown("exam_mode", $examModeOpts, $def_exam_mode, "class='portal-input' id='dqp_exam_mode' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 mb-sm">
						<div class="form-group">
							<label class="portal-label">Exam Type</label>
							<?php echo form_dropdown("exam_type", $examTypeOpts, $def_exam_type, "class='portal-input' id='dqp_exam_type' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
						</div>
					</div>
					<div class="col-md-4 mb-sm">
						<div class="form-group">
							<label class="portal-label">Exam Assessment</label>
							<?php echo form_dropdown("exam_assessment", $assessOpts, $def_assess, "class='portal-input' id='dqp_assess' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
						</div>
					</div>
					<div class="col-md-4 mb-sm">
						<div class="form-group">
							<label class="portal-label">Exam Name</label>
							<?php echo form_dropdown("exam_name", $examNameOpts, $def_exam_name, "class='portal-input' id='dqp_exam_name' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
						</div>
					</div>
				</div>
				<p class="dqp-hint mb-sm"><i class="fas fa-calculator"></i> Section totals use <strong>Max attempt × marks</strong> (from <a href="<?= $branch_id_int > 0 ? base_url('onlineexam/question_type?branch_id=' . (int) $branch_id_int) : base_url('onlineexam/question_type') ?>">question type marks</a>). Class / section / subject are chosen later when creating a paper.</p>
				</div>
				<input type="hidden" id="dqp_logo_show" value="<?= html_escape($def_logo === 'hide' ? 'hide' : 'show') ?>">
				<input type="hidden" id="dqp_barcode" value="<?= html_escape($def_bar === 'show' ? 'show' : 'hide') ?>">
				<input type="hidden" id="dqp_school_show" value="<?= html_escape($def_school_show === 'hide' ? 'hide' : 'show') ?>">
				<input type="hidden" id="dqp_reg_show" value="<?= html_escape($def_reg_show === 'hide' ? 'hide' : 'show') ?>">
				<input type="hidden" id="dqp_recognition_show" value="<?= html_escape($def_recognition_show === 'hide' ? 'hide' : 'show') ?>">
				<input type="hidden" id="dqp_udise_show" value="<?= html_escape($def_udise_show === 'hide' ? 'hide' : 'show') ?>">
				<input type="hidden" id="dqp_note_show" value="<?= html_escape($def_note_show === 'hide' ? 'hide' : 'show') ?>">
				<input type="hidden" id="dqp_logo_pos_x" value="<?= html_escape((string) $def_logo_pos_x) ?>">
				<input type="hidden" id="dqp_logo_pos_y" value="<?= html_escape((string) $def_logo_pos_y) ?>">
				<input type="hidden" id="dqp_school_pos_x" value="<?= html_escape((string) $def_school_pos_x) ?>">
				<input type="hidden" id="dqp_school_pos_y" value="<?= html_escape((string) $def_school_pos_y) ?>">
				<input type="hidden" id="dqp_barcode_pos_x" value="<?= html_escape((string) $def_barcode_pos_x) ?>">
				<input type="hidden" id="dqp_barcode_pos_y" value="<?= html_escape((string) $def_barcode_pos_y) ?>">
				<input type="hidden" id="dqp_logo_size" value="<?= html_escape((string) $def_logo_size) ?>">
				<input type="hidden" id="dqp_school_name_size" value="<?= html_escape((string) $def_school_name_size) ?>">
				<input type="hidden" id="dqp_school_loc_size" value="<?= html_escape((string) $def_school_loc_size) ?>">
				<input type="hidden" id="dqp_barcode_width" value="<?= html_escape((string) $dqp('barcode_width', '80')) ?>">
				<input type="hidden" id="dqp_header_stage_height" value="<?= html_escape((string) $dqp('header_stage_height', '140')) ?>">
				<input type="hidden" id="dqp_logo_align" value="<?= html_escape($def_logo_align) ?>">
				<input type="hidden" id="dqp_school_align" value="<?= html_escape($def_school_align) ?>">
				<input type="hidden" id="dqp_barcode_align" value="<?= html_escape($def_barcode_align) ?>">
				<input type="hidden" id="dqp_header_size" value="<?= html_escape((string) $def_header_size) ?>">
				<input type="hidden" id="dqp_meta_size" value="<?= html_escape((string) $def_meta_size) ?>">
				<div class="dqp-form-section">
					<h5><span class="dqp-sec-badge">3</span> Sections <span class="dqp-sec-sub">questions · max attempt</span></h5>
					<p class="dqp-hint"><strong>Questions (on paper)</strong> can include extras for choice. <strong>Max attempt</strong> = how many answers count. Section marks = max attempt × marks per question.</p>
				<div id="dqp_sections_list">
					<?php foreach ($sectionRowsForView as $one):
						$one = is_array($one) ? $one : array();
						$sel_qt = '';
						if (!empty($one['qtype_id'])) {
							$sel_qt = (string) $one['qtype_id'];
						} elseif (!empty($one['qtype'])) {
							$sel_qt = $mapLegacyQtypeToBranchRowId($one['qtype']);
						}
						$sel_qn = isset($one['qnum']) ? $one['qnum'] : '';
						$sel_att = isset($one['attempt']) ? $one['attempt'] : '';
						if ($sel_att === '' && $sel_qn !== '') {
							$sel_att = $sel_qn;
						}
						?>
					<div class="dqp-section-row">
						<div class="row">
							<div class="col-md-4 col-sm-12 mb-sm">
								<div class="form-group mb-none">
									<label class="portal-label"><span class="dqp-section-num">Section 1</span> — <?= translate('question_type') ?></label>
									<?php echo form_dropdown('section_qtype[]', $qtDropdown, $sel_qt, "class='portal-input dqp-qtype' data-plugin-selectTwo data-width='100%'"); ?>
								</div>
							</div>
							<div class="col-md-2 col-sm-4 mb-sm">
								<div class="form-group mb-none">
									<label class="portal-label">Questions (on paper)</label>
									<?php echo form_dropdown('section_qnum[]', $numOpts, $sel_qn, "class='portal-input dqp-qnum' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
								</div>
							</div>
							<div class="col-md-2 col-sm-4 mb-sm">
								<div class="form-group mb-none">
									<label class="portal-label">Max attempt</label>
									<?php echo form_dropdown('section_attempt[]', $numOpts, $sel_att, "class='portal-input dqp-attempt' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"); ?>
								</div>
							</div>
							<div class="col-md-2 col-sm-4 mb-sm">
								<div class="form-group mb-none">
									<label class="portal-label">Section total</label>
									<p class="portal-input-static mb-none"><strong class="dqp-section-total">—</strong></p>
								</div>
							</div>
							<div class="col-md-2 col-sm-12 mb-sm">
								<div class="form-group mb-none">
									<label class="portal-label">&nbsp;</label>
									<button type="button" class="btn-portal-secondary btn-sm w-full dqp-remove-section" title="<?= translate('delete') ?>"><i class="fas fa-times"></i></button>
								</div>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="dqp-actions-row mb-sm">
					<button type="button" class="btn-portal-secondary btn-sm" id="dqp_add_section"><i class="fas fa-plus"></i> Add section</button>
					<button type="button" class="btn-portal-secondary btn-sm" id="dqp_print"><i class="fas fa-print"></i> Print preview</button>
				</div>
				<div class="dqp-mm-warn" id="dqp_mm_warn" style="display:none;" role="alert">
					<div class="dqp-mm-warn-text"></div>
					<button type="button" class="btn btn-warning btn-xs" id="dqp_mm_sync_btn"><i class="fas fa-sync"></i> Update Max Marks</button>
				</div>
				</div>
			</div>
		</section>
	</div>
	<div class="col-md-6 col-lg-7 dqp-preview-col">
		<section class="panel panel-default">
			<header class="panel-heading">
				<h4><i class="fas fa-eye"></i> Template preview</h4>
				<span class="text-muted text-xs">Drag · resize · bottom edge = box height</span>
			</header>
			<div class="panel-body">
				<div class="dqp-preview-toggles" id="dqp_preview_toggles">
					<span class="dqp-tog-label">Show</span>
					<button type="button" class="dqp-tog<?= $def_logo !== 'hide' ? ' active' : '' ?>" data-tog="logo">Logo</button>
					<button type="button" class="dqp-tog<?= $def_school_show !== 'hide' ? ' active' : '' ?>" data-tog="school">School name</button>
					<button type="button" class="dqp-tog<?= $def_bar === 'show' ? ' active' : '' ?>" data-tog="barcode">Barcode</button>
					<button type="button" class="dqp-tog<?= $def_reg_show !== 'hide' ? ' active' : '' ?>" data-tog="reg">Reg. No</button>
					<button type="button" class="dqp-tog<?= $def_recognition_show !== 'hide' ? ' active' : '' ?>" data-tog="recognition">Recognition</button>
					<button type="button" class="dqp-tog<?= $def_udise_show !== 'hide' ? ' active' : '' ?>" data-tog="udise">Udise</button>
					<button type="button" class="dqp-tog<?= $def_note_show !== 'hide' ? ' active' : '' ?>" data-tog="note">Note</button>
					<button type="button" class="btn-portal-secondary btn-sm" id="dqp_edit_texts_btn" title="Edit header texts"><i class="fas fa-pen"></i> Edit texts</button>
					<button type="button" class="btn-portal-secondary btn-sm" id="dqp_reset_drag" title="Reset header layout"><i class="fas fa-crosshairs"></i> Reset</button>
				</div>
				<input type="hidden" id="dqp_school_name" value="<?= html_escape($def_school_name) ?>">
				<input type="hidden" id="dqp_school_loc" value="<?= html_escape($def_school_loc) ?>">
				<input type="hidden" id="dqp_reg_no" value="<?= html_escape($def_reg) ?>">
				<input type="hidden" id="dqp_udise" value="<?= html_escape($def_udise) ?>">
				<input type="hidden" id="dqp_recognition" value="<?= html_escape($def_rec) ?>">
				<input type="hidden" id="dqp_note" value="<?= html_escape($def_note) ?>">
				<div class="dqp-text-modal-backdrop" id="dqp_text_modal" aria-hidden="true">
					<div class="dqp-text-modal" role="dialog" aria-modal="true">
						<div class="dqp-text-modal-head">
							<h5><i class="fas fa-pen"></i> Edit header texts</h5>
							<button type="button" class="btn btn-default btn-xs" id="dqp_text_modal_x"><i class="fas fa-times"></i></button>
						</div>
						<div class="dqp-text-modal-body">
							<div class="row">
								<div class="col-md-6 mb-sm">
									<label class="control-label">School name</label>
									<input type="text" class="form-control input-sm" id="dqp_modal_school_name" placeholder="School name">
								</div>
								<div class="col-md-6 mb-sm">
									<label class="control-label">City / location</label>
									<input type="text" class="form-control input-sm" id="dqp_modal_school_loc" placeholder="City">
								</div>
								<div class="col-md-6 mb-sm">
									<label class="control-label">Reg. No.</label>
									<input type="text" class="form-control input-sm" id="dqp_modal_reg_no" placeholder="e.g. 3379">
								</div>
								<div class="col-md-6 mb-sm">
									<label class="control-label">Udise No.</label>
									<input type="text" class="form-control input-sm" id="dqp_modal_udise" placeholder="">
								</div>
								<div class="col-md-12 mb-sm">
									<label class="control-label">Recognition</label>
									<input type="text" class="form-control input-sm" id="dqp_modal_recognition" placeholder="">
								</div>
								<div class="col-md-12 mb-sm">
									<label class="control-label">Note</label>
									<input type="text" class="form-control input-sm" id="dqp_modal_note" placeholder="">
								</div>
							</div>
						</div>
						<div class="dqp-text-modal-foot">
							<button type="button" class="btn btn-default btn-sm" id="dqp_text_modal_cancel">Cancel</button>
							<button type="button" class="btn btn-primary btn-sm" id="dqp_text_modal_save"><i class="fas fa-check"></i> Apply</button>
						</div>
					</div>
				</div>
				<div class="dqp-preview-desk">
				<div class="dqp-preview" id="dqp_preview">
					<div class="dqp-preview-watermark">Preview</div>
					<div class="dqp-preview-inner">
						<div class="dqp-preview-head-top">
							<span id="pv_reg" title="Double-click to edit">Reg. No- <span id="pv_reg_val">—</span></span>
							<span class="dqp-center" id="pv_recognition" title="Double-click to edit">Recognized by U.P goverment upto 8 class</span>
							<span id="pv_udise_wrap" title="Double-click to edit">Udise No - <span id="pv_udise_val">—</span></span>
						</div>
						<div class="dqp-header-stage" id="pv_header_stage">
							<div class="dqp-guide-v" aria-hidden="true"></div>
							<div class="dqp-guide-h" aria-hidden="true"></div>
							<span class="dqp-stage-height-badge" id="pv_stage_h_badge">140px</span>
							<div class="dqp-drag-item dqp-school-logo-wrap" id="pv_logo_wrap" data-drag="logo" title="Drag to move, corner to resize">
								<span class="dqp-place-badge" id="pv_logo_badge">Logo</span>
								<?php if ($logoUrl !== ''): ?>
									<img src="<?= html_escape($logoUrl) ?>" class="dqp-school-logo" id="pv_logo_img" alt="">
								<?php else: ?>
									<div class="dqp-school-logo-placeholder" id="pv_logo_ph"></div>
								<?php endif; ?>
								<span class="dqp-resize-handle" data-resize="logo" title="Resize"></span>
							</div>
							<div class="dqp-drag-item dqp-school-text" id="pv_school_text" data-drag="school" title="Drag to move · Double-click to edit text">
								<span class="dqp-place-badge" id="pv_school_badge">School</span>
								<p class="dqp-school-title" id="pv_school"><?= html_escape($schoolName !== '' ? $schoolName : 'School Name'); ?></p>
								<p class="dqp-school-loc" id="pv_city"><?= html_escape($cityLine !== '' ? $cityLine : 'City'); ?></p>
								<span class="dqp-resize-handle" data-resize="school" title="Resize"></span>
							</div>
							<div class="dqp-drag-item dqp-bar-placeholder" id="pv_bar" data-drag="barcode" title="Drag to move, corner to resize" style="display:none;">
								<span class="dqp-place-badge" id="pv_barcode_badge">Barcode</span>
								<span class="dqp-bar-label">Bar code</span>
								<span class="dqp-resize-handle" data-resize="barcode" title="Resize"></span>
							</div>
							<div class="dqp-stage-height-handle" id="pv_stage_height_handle" title="Drag to change box height"></div>
						</div>
						<div class="dqp-preview-meta">
							<ul>
								<li id="pv_term">Term - —</li>
							</ul>
							<ul class="text-right">
								<li>Time - <span id="pv_time">—</span></li>
								<li>M.M - <span id="pv_mm">—</span></li>
							</ul>
						</div>
						<p class="dqp-preview-note" id="pv_note_wrap" title="Double-click to edit"><strong>Note :-</strong> <span id="pv_note">All Question are compulsory . Writing should be clean and neat.</span></p>
						<div id="pv_sections"></div>
						<div class="dqp-footer-mark" id="pv_subject_mark">Question Paper</div>
					</div>
				</div>
				</div>
			</div>
		</section>
	</div>
</div>
<?php endif; /* show_designer */ ?>
<?php if (!$show_designer): ?>
<input type="hidden" id="dqp_branch_id" value="<?= $branch_id_int ?>">
<?php endif; ?>
<script type="text/javascript">
(function ($) {
	var listUrl = <?= json_encode($list_template_url) ?>;
	$(document).on('click', '.dqp-delete-btn', function () {
		var delId = $(this).data('id');
		if (!delId || !window.confirm('<?= addslashes(translate('are_you_sure')) ?>')) {
			return;
		}
		$.post(base_url + 'onlineexam/paper_design_delete', { id: delId, branch_id: $('#dqp_branch_id').val() || '' }, function (res) {
			if (res.status === 'success') {
				window.location.href = listUrl;
			} else if (typeof res.error === 'string') {
				alert(res.error);
			}
		}, 'json');
	});
})(jQuery);
</script>
<?php if ($show_designer): ?>
<script type="text/javascript">
window.DQP_TYPE_META = <?= json_encode($qtMeta, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
window.DQP_PREVIEW_I18N = <?= json_encode($preview_i18n, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
(function ($) {
	'use strict';
	var meta = window.DQP_TYPE_META || {};
	var TXT_MARKS = <?= json_encode(translate('marks')) ?>;
	var DQP_MAX_SECTIONS = 30;
	function parseMarks(val) {
		var n = parseFloat(String(val), 10);
		return isNaN(n) ? 0 : n;
	}
	function destroySelect2OnRow($row) {
		$row.find('select').each(function () {
			var $s = $(this);
			if ($s.hasClass('select2-hidden-accessible') && $.fn.select2) {
				try {
					$s.select2('destroy');
				} catch (err) {
				}
			}
			$s.removeData('__select2');
			$s.removeAttr('data-select2-id');
		});
		$row.find('.select2-container').remove();
	}
	function initSelect2OnRow($row) {
		$row.find('select[data-plugin-selectTwo]').each(function () {
			var $s = $(this);
			if ($.fn.themePluginSelect2 && !$s.data('__select2')) {
				$s.themePluginSelect2({});
			}
		});
	}
	function renumberSections() {
		$('#dqp_sections_list .dqp-section-row').each(function (i) {
			$(this).find('.dqp-section-num').text('Section ' + (i + 1));
		});
	}
	function toggleRemoveAndAddButtons() {
		var n = $('#dqp_sections_list .dqp-section-row').length;
		$('.dqp-remove-section').toggleClass('hidden', n <= 1);
		$('#dqp_add_section').prop('disabled', n >= DQP_MAX_SECTIONS);
	}
	function dqpResolveAttempt(qnum, attemptVal) {
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
	function dqpAttemptLabel(qnum, attempt) {
		if (qnum < 1) {
			return '';
		}
		if (attempt < qnum) {
			return 'Attempt any ' + attempt + ' of ' + qnum;
		}
		return 'Attempt all ' + qnum;
	}
	function syncAttemptWithQnum($row) {
		var qnum = parseInt($row.find('.dqp-qnum').val(), 10) || 0;
		var $att = $row.find('.dqp-attempt');
		var att = parseInt($att.val(), 10) || 0;
		if (qnum > 0 && (att < 1 || att > qnum)) {
			$att.val(String(qnum)).trigger('change.select2');
		}
	}
	function updateSectionTotals() {
		$('#dqp_sections_list .dqp-section-row').each(function () {
			var $row = $(this);
			var id = $row.find('.dqp-qtype').val();
			var num = parseInt($row.find('.dqp-qnum').val(), 10) || 0;
			var att = dqpResolveAttempt(num, $row.find('.dqp-attempt').val());
			var $tot = $row.find('.dqp-section-total');
			if (id && meta[id] && att > 0) {
				$tot.text(String(att * parseMarks(meta[id].marks)));
			} else {
				$tot.text('—');
			}
		});
	}
	function computeSectionMarksTotal() {
		var total = 0;
		$('#dqp_sections_list .dqp-section-row').each(function () {
			var id = $(this).find('.dqp-qtype').val();
			var num = parseInt($(this).find('.dqp-qnum').val(), 10) || 0;
			var att = dqpResolveAttempt(num, $(this).find('.dqp-attempt').val());
			if (id && meta[id] && att > 0) {
				total += att * parseMarks(meta[id].marks);
			}
		});
		return total;
	}
	var PV_I18N = window.DQP_PREVIEW_I18N || {};
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
	function buildMockQuestionBlock(baseTypeId, secNum, qIndex, typeName) {
		var uid = 'pv_' + secNum + '_' + qIndex;
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
			for (o = 1; o <= 4; o++) {
				rb = uid + '_o' + o;
				html += '<div class="radio-custom radio-success mt-md">';
				html += '<input type="radio" disabled name="dqp_pv_' + uid + '" id="' + rb + '">';
				html += '<label for="' + rb + '">' + escapeHtml(optWord + ' ' + o) + '</label>';
				html += '</div>';
			}
		} else if (t === 2) {
			for (o = 1; o <= 4; o++) {
				rb = uid + '_c' + o;
				html += '<div class="checkbox-replace mt-lg">';
				html += '<label class="i-checks"><input type="checkbox" disabled id="' + rb + '"><i></i> ' + escapeHtml(optWord + ' ' + o) + '</label>';
				html += '</div>';
			}
		} else if (t === 3) {
			rb = uid + '_tf1';
			html += '<div class="radio-custom radio-success mt-md">';
			html += '<input type="radio" disabled name="dqp_pv_' + uid + '_tf" id="' + rb + '">';
			html += '<label for="' + rb + '">' + escapeHtml(pvT('true', 'TRUE')) + '</label>';
			html += '</div>';
			rb = uid + '_tf2';
			html += '<div class="radio-custom radio-success mt-md">';
			html += '<input type="radio" disabled name="dqp_pv_' + uid + '_tf" id="' + rb + '">';
			html += '<label for="' + rb + '">' + escapeHtml(pvT('false', 'FALSE')) + '</label>';
			html += '</div>';
		} else if (t === 4 || t === 8 || t === 9 || t === 10 || t === 11) {
			var rows = (t === 8) ? 2 : (t === 9 ? 3 : (t === 10 ? 4 : 6));
			html += '<div class="form-group">';
			html += '<label class="portal-label">' + escapeHtml(pvT('yourAnswer', 'Your answer')) + '</label>';
			html += '<textarea class="portal-input" rows="' + rows + '" disabled placeholder="' + escapeHtml(pvT('writeAnswerHere', 'Write your answer here…')) + '"></textarea>';
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
	function renderPvSections() {
		var $wrap = $('#pv_sections');
		var html = '';
		var marksWord = TXT_MARKS;
		$('#dqp_sections_list .dqp-section-row').each(function (si) {
			var $row = $(this);
			var secNum = si + 1;
			var id = $row.find('.dqp-qtype').val();
			var num = parseInt($row.find('.dqp-qnum').val(), 10) || 0;
			if (!id || num < 1) {
				return;
			}
			var att = dqpResolveAttempt(num, $row.find('.dqp-attempt').val());
			var m = meta[id] || {};
			var name = m.name || '—';
			var baseTypeId = m.question_type_id !== undefined ? parseInt(m.question_type_id, 10) : 0;
			var eachMk = m.marks !== undefined && m.marks !== null && m.marks !== '' ? m.marks : null;
			var secTot = eachMk !== null ? (att * parseMarks(eachMk)) : null;
			var title = 'Section ' + secNum + ' — ' + name;
			if (eachMk !== null) {
				title += ' (' + eachMk + ' ' + marksWord + ' each';
				if (secTot !== null) {
					title += ', section total ' + secTot;
				}
				title += ')';
			}
			html += '<div class="dqp-sample-block">';
			html += '<div class="dqp-q-title">' + escapeHtml(title) + '</div>';
			html += '<div class="dqp-attempt-line">' + escapeHtml(dqpAttemptLabel(num, att)) + '</div>';
			html += buildMockQuestionBlock(baseTypeId, secNum, 1, name);
			if (num > 1) {
				var moreStr = pvT('andMoreSame', '… and %d more question(s) with the same layout.').replace('%d', String(num - 1));
				html += '<p class="text-muted text-xs mb-none mt-sm"><em>' + escapeHtml(moreStr) + '</em></p>';
			}
			html += '</div>';
		});
		if (!html) {
			html = '<p class="text-muted text-sm mb-none">Select section types and counts on the left to preview the paper structure.</p>';
		}
		$wrap.html(html);
	}
	function selText($el) {
		var v = $el.find('option:selected').text();
		return v || '—';
	}
	function ensureMmOption(val) {
		val = String(val || '');
		if (!val) {
			return;
		}
		var $mm = $('#dqp_mm');
		if (!$mm.length) {
			return;
		}
		if ($mm.find('option[value="' + val.replace(/"/g, '\\"') + '"]').length < 1) {
			$mm.append($('<option></option>').attr('value', val).text(val));
		}
	}
	function syncMaxMarksFromSections(force) {
		var total = computeSectionMarksTotal();
		if (total <= 0) {
			return;
		}
		var cur = $('#dqp_mm').val() || '';
		if (!force && cur !== '') {
			return;
		}
		ensureMmOption(String(total));
		$('#dqp_mm').val(String(total)).trigger('change.select2');
	}
	function updateMaxMarksWarning() {
		var $box = $('#dqp_mm_warn');
		if (!$box.length) {
			return;
		}
		var sectionTotal = computeSectionMarksTotal();
		var mm = parseFloat($('#dqp_mm').val(), 10);
		var hasMm = isFinite(mm) && mm > 0;
		if (sectionTotal <= 0) {
			$box.removeClass('is-visible is-over').hide();
			return;
		}
		if (!hasMm) {
			$box.find('.dqp-mm-warn-text').html(
				'Section totals are <span class="dqp-mm-nums">' + sectionTotal + '</span> marks. Max Marks is empty — set it to match sections.'
			);
			$box.removeClass('is-over').addClass('is-visible').css('display', 'flex');
			$('#dqp_mm_sync_btn').data('total', sectionTotal).html('<i class="fas fa-sync"></i> Set Max Marks to ' + sectionTotal);
			return;
		}
		if (Math.abs(mm - sectionTotal) < 0.001) {
			$box.removeClass('is-visible is-over').hide();
			return;
		}
		var over = sectionTotal > mm;
		var msg = over
			? ('Warning: sections add up to <span class="dqp-mm-nums">' + sectionTotal + '</span> marks, but Max Marks is only <span class="dqp-mm-nums">' + mm + '</span>. Max Marks should be updated.')
			: ('Section totals are <span class="dqp-mm-nums">' + sectionTotal + '</span> marks, but Max Marks is <span class="dqp-mm-nums">' + mm + '</span>. Update Max Marks to match?');
		$box.find('.dqp-mm-warn-text').html(msg);
		$box.toggleClass('is-over', over).addClass('is-visible').css('display', 'flex');
		$('#dqp_mm_sync_btn').data('total', sectionTotal).html('<i class="fas fa-sync"></i> Update Max Marks to ' + sectionTotal);
	}

	var DQP_ALIGN_PRESETS = {
		logo: { left: { x: 2, y: 12 }, center: { x: 44, y: 8 }, right: { x: 86, y: 12 } },
		school: { left: { x: 14, y: 18 }, center: { x: 28, y: 28 }, right: { x: 55, y: 18 } },
		/* top-right default: 80px square fits inside ~140px stage (y≈10, not 72 which crops) */
		barcode: { left: { x: 2, y: 10 }, center: { x: 42, y: 10 }, right: { x: 78, y: 10 } }
	};
	var DQP_DEFAULT_SIZES = { logo: 52, school_name: 16, school_loc: 12, barcode_width: 80, stage_height: 140 };
	var dqpDragState = null;
	var dqpResizeState = null;
	var dqpStageResizeState = null;
	var DQP_SNAP = 2.2;

	function dqpParsePos(v, fallback) {
		var n = parseFloat(v);
		return isFinite(n) ? n : fallback;
	}
	function dqpGetPos(key) {
		var preset = DQP_ALIGN_PRESETS[key] ? DQP_ALIGN_PRESETS[key].left : { x: 2, y: 10 };
		return {
			x: dqpParsePos($('#dqp_' + key + '_pos_x').val(), preset.x),
			y: dqpParsePos($('#dqp_' + key + '_pos_y').val(), preset.y)
		};
	}
	function dqpSetPos(key, x, y, maxX, maxY) {
		maxX = (typeof maxX === 'number' && isFinite(maxX)) ? maxX : 92;
		maxY = (typeof maxY === 'number' && isFinite(maxY)) ? maxY : 55;
		x = Math.max(0, Math.min(maxX, x));
		y = Math.max(0, Math.min(maxY, y));
		$('#dqp_' + key + '_pos_x').val(String(Math.round(x * 10) / 10));
		$('#dqp_' + key + '_pos_y').val(String(Math.round(y * 10) / 10));
	}
	function dqpItemSelector(key) {
		if (key === 'logo') { return '#pv_logo_wrap'; }
		if (key === 'school') { return '#pv_school_text'; }
		return '#pv_bar';
	}
	function dqpBadgeSelector(key) {
		if (key === 'logo') { return '#pv_logo_badge'; }
		if (key === 'school') { return '#pv_school_badge'; }
		return '#pv_barcode_badge';
	}
	function dqpDetectPlacement(key) {
		var $stage = $('#pv_header_stage');
		var $item = $(dqpItemSelector(key));
		if (!$stage.length || !$item.length || !$item.is(':visible')) {
			return { label: '—', centered: false, h: '', v: '' };
		}
		var stage = $stage[0].getBoundingClientRect();
		var item = $item[0].getBoundingClientRect();
		if (!stage.width || !stage.height) {
			return { label: '—', centered: false, h: '', v: '' };
		}
		var cx = ((item.left + item.width / 2) - stage.left) / stage.width * 100;
		var cy = ((item.top + item.height / 2) - stage.top) / stage.height * 100;
		var h = 'Left';
		var v = 'Top';
		if (Math.abs(cx - 50) <= DQP_SNAP) { h = 'Center'; }
		else if (cx > 55) { h = 'Right'; }
		else if (cx < 45) { h = 'Left'; }
		else { h = 'Near center'; }
		if (Math.abs(cy - 50) <= DQP_SNAP) { v = 'Middle'; }
		else if (cy > 55) { v = 'Bottom'; }
		else { v = 'Top'; }
		var centered = (h === 'Center');
		var sizeTxt = '';
		if (key === 'logo') {
			sizeTxt = ' · ' + (parseInt($('#dqp_logo_size').val(), 10) || 52) + 'px';
		} else if (key === 'school') {
			sizeTxt = ' · ' + (parseInt($('#dqp_school_name_size').val(), 10) || 16) + 'px';
		} else {
			sizeTxt = ' · ' + (parseInt($('#dqp_barcode_width').val(), 10) || 80) + 'px □';
		}
		return {
			label: (centered ? 'CENTER' : h) + (centered ? '' : ' · ' + v) + sizeTxt,
			centered: centered,
			h: h,
			v: v,
			cx: cx,
			cy: cy
		};
	}
	function dqpUpdatePlacementBadges(activeKey) {
		['logo', 'school', 'barcode'].forEach(function (key) {
			var info = dqpDetectPlacement(key);
			var $item = $(dqpItemSelector(key));
			var $badge = $(dqpBadgeSelector(key));
			$badge.text(info.label).toggleClass('dqp-badge-center', !!info.centered);
			$item.toggleClass('dqp-is-centered', !!info.centered);
		});
		var $stage = $('#pv_header_stage');
		if (activeKey) {
			var active = dqpDetectPlacement(activeKey);
			$stage.toggleClass('dqp-snap-x', active.h === 'Center');
			$stage.toggleClass('dqp-snap-y', active.v === 'Middle');
		} else {
			$stage.removeClass('dqp-snap-x dqp-snap-y');
		}
	}
	function dqpSnapPct(xPct, yPct, itemWpct, itemHpct) {
		var snappedX = false;
		var snappedY = false;
		var centerX = xPct + (itemWpct / 2);
		var centerY = yPct + (itemHpct / 2);
		if (Math.abs(centerX - 50) <= DQP_SNAP) {
			xPct = 50 - (itemWpct / 2);
			snappedX = true;
		}
		if (Math.abs(centerY - 50) <= DQP_SNAP) {
			yPct = 50 - (itemHpct / 2);
			snappedY = true;
		}
		return { x: xPct, y: yPct, snappedX: snappedX, snappedY: snappedY };
	}
	function dqpApplySizes() {
		var logoSize = parseInt($('#dqp_logo_size').val(), 10) || DQP_DEFAULT_SIZES.logo;
		var snSize = parseInt($('#dqp_school_name_size').val(), 10) || DQP_DEFAULT_SIZES.school_name;
		var slSize = parseInt($('#dqp_school_loc_size').val(), 10) || DQP_DEFAULT_SIZES.school_loc;
		var barW = parseInt($('#dqp_barcode_width').val(), 10) || DQP_DEFAULT_SIZES.barcode_width;
		logoSize = Math.max(28, Math.min(120, logoSize));
		snSize = Math.max(12, Math.min(36, snSize));
		slSize = Math.max(10, Math.min(24, slSize));
		barW = Math.max(40, Math.min(160, barW));
		$('#dqp_logo_size').val(String(logoSize));
		$('#dqp_school_name_size').val(String(snSize));
		$('#dqp_school_loc_size').val(String(slSize));
		$('#dqp_barcode_width').val(String(barW));
		$('#pv_logo_img, #pv_logo_ph').css({ width: logoSize + 'px', height: logoSize + 'px' });
		$('#pv_school').css({ 'font-size': snSize + 'px', 'line-height': '1.25' });
		$('#pv_city').css({ 'font-size': slSize + 'px' });
		// barcode stays square; school/text height follows content
		$('#pv_bar').css({
			width: barW + 'px',
			height: barW + 'px',
			'min-width': barW + 'px',
			'min-height': barW + 'px',
			'max-width': barW + 'px',
			'max-height': barW + 'px',
			'font-size': Math.max(9, Math.round(barW * 0.12)) + 'px'
		});
		$('#pv_school_text').css({ height: 'auto', 'min-height': 0 });
		dqpApplyStageHeight();
	}
	function dqpApplyStageHeight() {
		var h = parseInt($('#dqp_header_stage_height').val(), 10) || DQP_DEFAULT_SIZES.stage_height;
		h = Math.max(90, Math.min(360, h));
		$('#dqp_header_stage_height').val(String(h));
		$('#pv_header_stage').css({ height: h + 'px', 'min-height': h + 'px' });
		$('#pv_stage_h_badge').text(h + 'px');
	}
	function dqpApplyDragPositions() {
		var logo = dqpGetPos('logo');
		var school = dqpGetPos('school');
		var bar = dqpGetPos('barcode');
		$('#pv_logo_wrap').css({ left: logo.x + '%', top: logo.y + '%' });
		$('#pv_school_text').css({ left: school.x + '%', top: school.y + '%' });
		$('#pv_bar').css({ left: bar.x + '%', top: bar.y + '%' });
		dqpUpdatePlacementBadges(null);
	}
	function dqpSyncAlignHiddenFromPlacement() {
		['logo', 'school', 'barcode'].forEach(function (key) {
			var info = dqpDetectPlacement(key);
			var align = 'left';
			if (info.centered || info.h === 'Center') { align = 'center'; }
			else if (info.h === 'Right') { align = 'right'; }
			$('#dqp_' + key + '_align').val(align);
		});
	}
	function dqpApplyAlignPresets() {
		dqpSetPos('logo', DQP_ALIGN_PRESETS.logo.left.x, DQP_ALIGN_PRESETS.logo.left.y);
		dqpSetPos('school', DQP_ALIGN_PRESETS.school.left.x, DQP_ALIGN_PRESETS.school.left.y);
		dqpSetPos('barcode', DQP_ALIGN_PRESETS.barcode.right.x, DQP_ALIGN_PRESETS.barcode.right.y);
		$('#dqp_logo_size').val(String(DQP_DEFAULT_SIZES.logo));
		$('#dqp_school_name_size').val(String(DQP_DEFAULT_SIZES.school_name));
		$('#dqp_school_loc_size').val(String(DQP_DEFAULT_SIZES.school_loc));
		$('#dqp_barcode_width').val(String(DQP_DEFAULT_SIZES.barcode_width));
		$('#dqp_header_stage_height').val(String(DQP_DEFAULT_SIZES.stage_height));
		dqpApplySizes();
		dqpApplyDragPositions();
		dqpSyncAlignHiddenFromPlacement();
	}
	function dqpPointFromEvent(e) {
		if (e.type.indexOf('touch') === 0) {
			var t = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
			return t ? { x: t.clientX, y: t.clientY } : null;
		}
		return { x: e.clientX, y: e.clientY };
	}
	function dqpBindHeaderDrag() {
		$(document).on('mousedown.dqpDrag touchstart.dqpDrag', '#pv_stage_height_handle', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var point = dqpPointFromEvent(e);
			if (!point) { return; }
			dqpStageResizeState = {
				startY: point.y,
				startH: parseInt($('#dqp_header_stage_height').val(), 10) || DQP_DEFAULT_SIZES.stage_height
			};
			$('#pv_header_stage').addClass('dqp-stage-resizing');
		});
		$(document).on('mousedown.dqpDrag touchstart.dqpDrag', '#pv_header_stage .dqp-resize-handle', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var point = dqpPointFromEvent(e);
			if (!point) { return; }
			var key = $(this).data('resize');
			var $item = $(dqpItemSelector(key));
			var $stage = $('#pv_header_stage');
			var stageRect = $stage[0].getBoundingClientRect();
			dqpResizeState = {
				key: key,
				$item: $item,
				startX: point.x,
				startY: point.y,
				startLogo: parseInt($('#dqp_logo_size').val(), 10) || 52,
				startSn: parseInt($('#dqp_school_name_size').val(), 10) || 16,
				startSl: parseInt($('#dqp_school_loc_size').val(), 10) || 12,
				startBarW: parseInt($('#dqp_barcode_width').val(), 10) || 80,
				stageW: stageRect.width || 1
			};
			$stage.addClass('dqp-show-guides');
			$item.addClass('dqp-resizing');
		});
		$(document).on('mousedown.dqpDrag touchstart.dqpDrag', '#pv_header_stage .dqp-drag-item', function (e) {
			if ($(e.target).closest('.dqp-resize-handle').length) {
				return;
			}
			if ($(this).hasClass('hidden') || !$(this).is(':visible')) {
				return;
			}
			var point = dqpPointFromEvent(e);
			if (!point) { return; }
			e.preventDefault();
			var $item = $(this);
			var key = $item.data('drag');
			var $stage = $('#pv_header_stage');
			var stageRect = $stage[0].getBoundingClientRect();
			var itemRect = $item[0].getBoundingClientRect();
			dqpDragState = {
				key: key,
				$item: $item,
				offsetX: point.x - itemRect.left,
				offsetY: point.y - itemRect.top,
				stageLeft: stageRect.left,
				stageTop: stageRect.top,
				stageW: stageRect.width || 1,
				stageH: stageRect.height || 1,
				itemW: itemRect.width,
				itemH: itemRect.height
			};
			$stage.addClass('dqp-show-guides');
			$item.addClass('dqp-dragging');
		});
		$(document).on('mousemove.dqpDrag touchmove.dqpDrag', function (e) {
			var point = dqpPointFromEvent(e);
			if (!point) { return; }
			if (dqpStageResizeState) {
				e.preventDefault();
				var nextH = Math.round(dqpStageResizeState.startH + (point.y - dqpStageResizeState.startY));
				$('#dqp_header_stage_height').val(String(nextH));
				dqpApplyStageHeight();
				return;
			}
			if (dqpResizeState) {
				e.preventDefault();
				var dx = point.x - dqpResizeState.startX;
				if (dqpResizeState.key === 'logo') {
					$('#dqp_logo_size').val(String(Math.round(dqpResizeState.startLogo + dx)));
				} else if (dqpResizeState.key === 'school') {
					var nextSn = Math.round(dqpResizeState.startSn + dx * 0.12);
					var nextSl = Math.round(dqpResizeState.startSl + dx * 0.08);
					$('#dqp_school_name_size').val(String(nextSn));
					$('#dqp_school_loc_size').val(String(nextSl));
				} else {
					$('#dqp_barcode_width').val(String(Math.round(dqpResizeState.startBarW + dx)));
				}
				dqpApplySizes();
				dqpUpdatePlacementBadges(dqpResizeState.key);
				return;
			}
			if (!dqpDragState) { return; }
			e.preventDefault();
			var leftPx = point.x - dqpDragState.stageLeft - dqpDragState.offsetX;
			var topPx = point.y - dqpDragState.stageTop - dqpDragState.offsetY;
			var xPct = (leftPx / dqpDragState.stageW) * 100;
			var yPct = (topPx / dqpDragState.stageH) * 100;
			var wPct = (dqpDragState.itemW / dqpDragState.stageW) * 100;
			var hPct = (dqpDragState.itemH / dqpDragState.stageH) * 100;
			var snapped = dqpSnapPct(xPct, yPct, wPct, hPct);
			var maxX = Math.max(0, 100 - wPct - 1);
			var maxY = Math.max(0, 100 - hPct - 1);
			dqpSetPos(dqpDragState.key, snapped.x, snapped.y, maxX, maxY);
			dqpDragState.$item.css({
				left: $('#dqp_' + dqpDragState.key + '_pos_x').val() + '%',
				top: $('#dqp_' + dqpDragState.key + '_pos_y').val() + '%'
			});
			$('#pv_header_stage')
				.toggleClass('dqp-snap-x', snapped.snappedX)
				.toggleClass('dqp-snap-y', snapped.snappedY);
			dqpUpdatePlacementBadges(dqpDragState.key);
		});
		$(document).on('mouseup.dqpDrag touchend.dqpDrag touchcancel.dqpDrag', function () {
			if (dqpDragState) {
				dqpDragState.$item.removeClass('dqp-dragging');
				dqpDragState = null;
				dqpSyncAlignHiddenFromPlacement();
			}
			if (dqpResizeState) {
				dqpResizeState.$item.removeClass('dqp-resizing');
				dqpResizeState = null;
			}
			if (dqpStageResizeState) {
				dqpStageResizeState = null;
				$('#pv_header_stage').removeClass('dqp-stage-resizing');
			}
			$('#pv_header_stage').removeClass('dqp-show-guides dqp-snap-x dqp-snap-y');
			dqpUpdatePlacementBadges(null);
		});
	}

	function upd() {
		renumberSections();
		toggleRemoveAndAddButtons();
		updateSectionTotals();
		syncMaxMarksFromSections(false);
		updateMaxMarksWarning();
		var term = selText($('#dqp_exam_name'));
		if ($('#dqp_exam_name').val()) {
			$('#pv_term').text('Term - ' + term);
		} else {
			$('#pv_term').text('Term - —');
		}
		var t = $('#dqp_time option:selected').text();
		$('#pv_time').text(t && t !== '<?= addslashes(translate('select')) ?>' ? t : '—');
		var mmVal = $('#dqp_mm').val() || '';
		var mmTotal = computeSectionMarksTotal();
		if (!mmVal && mmTotal > 0) {
			mmVal = String(mmTotal);
		}
		$('#pv_mm').text(mmVal !== '' ? mmVal : '—');
		var note = $('#dqp_note').val();
		$('#pv_note').text(note || '—');
		$('#pv_reg_val').text($('#dqp_reg_no').val() || '—');
		$('#pv_udise_val').text($('#dqp_udise').val() || '—');
		$('#pv_recognition').text($('#dqp_recognition').val() || '');
		if ($('#dqp_logo_show').val() === 'hide') {
			$('#pv_logo_wrap').addClass('hidden');
		} else {
			$('#pv_logo_wrap').removeClass('hidden');
		}
		if ($('#dqp_school_show').val() === 'hide') {
			$('#pv_school_text').addClass('hidden');
		} else {
			$('#pv_school_text').removeClass('hidden');
		}
		if ($('#dqp_barcode').val() === 'show') {
			$('#pv_bar').show().removeClass('hidden');
		} else {
			$('#pv_bar').hide().addClass('hidden');
		}
		if ($('#dqp_reg_show').val() === 'hide') {
			$('#pv_reg').addClass('hidden').hide();
		} else {
			$('#pv_reg').removeClass('hidden').show();
		}
		if ($('#dqp_recognition_show').val() === 'hide') {
			$('#pv_recognition').addClass('hidden').hide();
		} else {
			$('#pv_recognition').removeClass('hidden').show();
		}
		if ($('#dqp_udise_show').val() === 'hide') {
			$('#pv_udise_wrap').addClass('hidden').hide();
		} else {
			$('#pv_udise_wrap').removeClass('hidden').show();
		}
		if ($('#dqp_note_show').val() === 'hide') {
			$('#pv_note_wrap').addClass('hidden').hide();
		} else {
			$('#pv_note_wrap').removeClass('hidden').show();
		}
		var headTopEmpty = ($('#dqp_reg_show').val() === 'hide' && $('#dqp_recognition_show').val() === 'hide' && $('#dqp_udise_show').val() === 'hide');
		$('.dqp-preview-head-top').toggle(!headTopEmpty);
		var schoolName = $.trim($('#dqp_school_name').val() || '');
		var schoolLoc = $.trim($('#dqp_school_loc').val() || '');
		$('#pv_school').text(schoolName || 'School Name');
		$('#pv_city').text(schoolLoc || 'City');
		var headSize = parseInt($('#dqp_header_size').val(), 10) || 11;
		var metaSize = parseInt($('#dqp_meta_size').val(), 10) || 12;
		$('.dqp-preview-head-top').css({ 'font-size': headSize + 'px' });
		$('.dqp-preview-meta').css({ 'font-size': metaSize + 'px' });
		$('.dqp-preview-note').css({ 'font-size': Math.max(metaSize, 12) + 'px' });
		dqpSyncPreviewToggles();
		dqpApplySizes();
		dqpApplyDragPositions();
		renderPvSections();
	}
	function dqpSyncPreviewToggles() {
		var map = {
			logo: $('#dqp_logo_show').val() !== 'hide',
			school: $('#dqp_school_show').val() !== 'hide',
			barcode: $('#dqp_barcode').val() === 'show',
			reg: $('#dqp_reg_show').val() !== 'hide',
			recognition: $('#dqp_recognition_show').val() !== 'hide',
			udise: $('#dqp_udise_show').val() !== 'hide',
			note: $('#dqp_note_show').val() !== 'hide'
		};
		$('#dqp_preview_toggles .dqp-tog').each(function () {
			var key = $(this).data('tog');
			$(this).toggleClass('active', !!map[key]);
		});
	}
	$('#dqp_exam_mode, #dqp_exam_type, #dqp_assess, #dqp_time, #dqp_mm, #dqp_exam_name').on('change', upd);
	$(document).on('click', '#dqp_preview_toggles .dqp-tog', function (e) {
		e.preventDefault();
		var key = $(this).data('tog');
		var on = !$(this).hasClass('active');
		if (key === 'logo') {
			$('#dqp_logo_show').val(on ? 'show' : 'hide');
		} else if (key === 'school') {
			$('#dqp_school_show').val(on ? 'show' : 'hide');
		} else if (key === 'barcode') {
			$('#dqp_barcode').val(on ? 'show' : 'hide');
		} else if (key === 'reg') {
			$('#dqp_reg_show').val(on ? 'show' : 'hide');
		} else if (key === 'recognition') {
			$('#dqp_recognition_show').val(on ? 'show' : 'hide');
		} else if (key === 'udise') {
			$('#dqp_udise_show').val(on ? 'show' : 'hide');
		} else if (key === 'note') {
			$('#dqp_note_show').val(on ? 'show' : 'hide');
		}
		upd();
	});
	$('#dqp_reset_drag').on('click', function (e) {
		e.preventDefault();
		dqpApplyAlignPresets();
	});
	function dqpOpenTextModal(focusId) {
		$('#dqp_modal_school_name').val($('#dqp_school_name').val() || '');
		$('#dqp_modal_school_loc').val($('#dqp_school_loc').val() || '');
		$('#dqp_modal_reg_no').val($('#dqp_reg_no').val() || '');
		$('#dqp_modal_udise').val($('#dqp_udise').val() || '');
		$('#dqp_modal_recognition').val($('#dqp_recognition').val() || '');
		$('#dqp_modal_note').val($('#dqp_note').val() || '');
		$('#dqp_text_modal').addClass('is-open').attr('aria-hidden', 'false');
		var $focus = focusId ? $(focusId) : $('#dqp_modal_school_name');
		setTimeout(function () { $focus.trigger('focus').select(); }, 40);
	}
	function dqpCloseTextModal() {
		$('#dqp_text_modal').removeClass('is-open').attr('aria-hidden', 'true');
	}
	function dqpApplyTextModal() {
		$('#dqp_school_name').val($.trim($('#dqp_modal_school_name').val() || ''));
		$('#dqp_school_loc').val($.trim($('#dqp_modal_school_loc').val() || ''));
		$('#dqp_reg_no').val($.trim($('#dqp_modal_reg_no').val() || ''));
		$('#dqp_udise').val($.trim($('#dqp_modal_udise').val() || ''));
		$('#dqp_recognition').val($.trim($('#dqp_modal_recognition').val() || ''));
		$('#dqp_note').val($.trim($('#dqp_modal_note').val() || ''));
		dqpCloseTextModal();
		upd();
	}
	$('#dqp_edit_texts_btn').on('click', function (e) {
		e.preventDefault();
		dqpOpenTextModal('#dqp_modal_school_name');
	});
	$('#dqp_text_modal_save').on('click', function (e) {
		e.preventDefault();
		dqpApplyTextModal();
	});
	$('#dqp_text_modal_cancel, #dqp_text_modal_x').on('click', function (e) {
		e.preventDefault();
		dqpCloseTextModal();
	});
	$('#dqp_text_modal').on('click', function (e) {
		if (e.target === this) {
			dqpCloseTextModal();
		}
	});
	$(document).on('keydown.dqpTextModal', function (e) {
		if (!$('#dqp_text_modal').hasClass('is-open')) {
			return;
		}
		if (e.key === 'Escape') {
			dqpCloseTextModal();
		} else if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			dqpApplyTextModal();
		}
	});
	// Double-click preview text → edit popup (focused field)
	$(document).on('dblclick', '#pv_school_text, #pv_school, #pv_city', function (e) {
		e.preventDefault();
		e.stopPropagation();
		dqpOpenTextModal('#dqp_modal_school_name');
	});
	$(document).on('dblclick', '#pv_reg, #pv_reg_val', function (e) {
		e.preventDefault();
		e.stopPropagation();
		dqpOpenTextModal('#dqp_modal_reg_no');
	});
	$(document).on('dblclick', '#pv_recognition', function (e) {
		e.preventDefault();
		e.stopPropagation();
		dqpOpenTextModal('#dqp_modal_recognition');
	});
	$(document).on('dblclick', '#pv_udise_wrap, #pv_udise_val', function (e) {
		e.preventDefault();
		e.stopPropagation();
		dqpOpenTextModal('#dqp_modal_udise');
	});
	$(document).on('dblclick', '#pv_note_wrap, #pv_note', function (e) {
		e.preventDefault();
		e.stopPropagation();
		dqpOpenTextModal('#dqp_modal_note');
	});
	$(document).on('select2:select', '.dqp-form select', upd);
	$('#dqp_note, #dqp_reg_no, #dqp_udise, #dqp_recognition, #dqp_school_name, #dqp_school_loc').on('input', upd);
	$(document).on('change', '#dqp_sections_list .dqp-qtype, #dqp_sections_list .dqp-qnum, #dqp_sections_list .dqp-attempt', function () {
		var $row = $(this).closest('.dqp-section-row');
		if ($(this).hasClass('dqp-qnum')) {
			syncAttemptWithQnum($row);
		}
		upd();
	});
	$('#dqp_add_section').on('click', function () {
		var $list = $('#dqp_sections_list');
		if ($list.find('.dqp-section-row').length >= DQP_MAX_SECTIONS) {
			return;
		}
		var $last = $list.find('.dqp-section-row').last();
		var $c = $last.clone(false, false);
		destroySelect2OnRow($c);
		$c.find('select.dqp-qtype').prop('selectedIndex', 0);
		$c.find('select.dqp-qnum').prop('selectedIndex', 0);
		$c.find('select.dqp-attempt').prop('selectedIndex', 0);
		$c.find('.dqp-section-total').text('—');
		$list.append($c);
		initSelect2OnRow($c);
		upd();
		var mm = parseFloat($('#dqp_mm').val(), 10);
		if (isFinite(mm) && mm > 0) {
			var tip = 'New section added. After you set its type, questions and max attempt, check Max Marks — section totals must match.';
			if (typeof swal === 'function') {
				swal({ type: 'info', title: 'Check Max Marks', text: tip, timer: 3200, showConfirmButton: true });
			}
		}
	});
	$('#dqp_mm_sync_btn').on('click', function (e) {
		e.preventDefault();
		var total = parseFloat($(this).data('total'), 10);
		if (!isFinite(total) || total <= 0) {
			total = computeSectionMarksTotal();
		}
		if (total <= 0) {
			return;
		}
		syncMaxMarksFromSections(true);
		upd();
	});
	$(document).on('click', '.dqp-remove-section:not(.hidden)', function () {
		var $list = $('#dqp_sections_list');
		if ($list.find('.dqp-section-row').length <= 1) {
			return;
		}
		var $row = $(this).closest('.dqp-section-row');
		destroySelect2OnRow($row);
		$row.remove();
		upd();
	});
	$('#dqp_print').on('click', function () {
		window.print();
	});
	function collectDqpConfig() {
		var sections = [];
		var totalMarks = computeSectionMarksTotal();
		var mm = $('#dqp_mm').val() || '';
		if (!mm && totalMarks > 0) {
			mm = String(totalMarks);
		}
		$('#dqp_sections_list .dqp-section-row').each(function () {
			var $row = $(this);
			sections.push({
				qtype_id: $row.find('.dqp-qtype').val() || '',
				qnum: $row.find('.dqp-qnum').val() || '',
				attempt: $row.find('.dqp-attempt').val() || ''
			});
		});
		return {
			exam_mode: $('#dqp_exam_mode').val() || '',
			exam_type: $('#dqp_exam_type').val() || '',
			exam_assessment: $('#dqp_assess').val() || '',
			paper_time: $('#dqp_time').val() || '',
			max_marks: mm,
			exam_name: $('#dqp_exam_name').val() || '',
			logo_show: $('#dqp_logo_show').val() || 'show',
			barcode_show: $('#dqp_barcode').val() || 'hide',
			school_show: $('#dqp_school_show').val() || 'show',
			reg_show: $('#dqp_reg_show').val() || 'show',
			recognition_show: $('#dqp_recognition_show').val() || 'show',
			udise_show: $('#dqp_udise_show').val() || 'show',
			note_show: $('#dqp_note_show').val() || 'show',
			reg_no: $('#dqp_reg_no').val() || '',
			udise: $('#dqp_udise').val() || '',
			recognition: $('#dqp_recognition').val() || '',
			note: $('#dqp_note').val() || '',
			school_name: $('#dqp_school_name').val() || '',
			school_location: $('#dqp_school_loc').val() || '',
			school_name_size: $('#dqp_school_name_size').val() || '16',
			school_loc_size: $('#dqp_school_loc_size').val() || '12',
			logo_size: $('#dqp_logo_size').val() || '52',
			barcode_width: $('#dqp_barcode_width').val() || '80',
			header_stage_height: $('#dqp_header_stage_height').val() || '140',
			logo_align: $('#dqp_logo_align').val() || 'left',
			school_align: $('#dqp_school_align').val() || 'left',
			barcode_align: $('#dqp_barcode_align').val() || 'center',
			logo_pos_x: $('#dqp_logo_pos_x').val() || '',
			logo_pos_y: $('#dqp_logo_pos_y').val() || '',
			school_pos_x: $('#dqp_school_pos_x').val() || '',
			school_pos_y: $('#dqp_school_pos_y').val() || '',
			barcode_pos_x: $('#dqp_barcode_pos_x').val() || '',
			barcode_pos_y: $('#dqp_barcode_pos_y').val() || '',
			header_font_size: $('#dqp_header_size').val() || '11',
			meta_font_size: $('#dqp_meta_size').val() || '12',
			sections: sections
		};
	}
	$('#dqp_save_btn').on('click', function () {
		dqpSyncAlignHiddenFromPlacement();
		var id = parseInt($('#dqp_editing_id').val(), 10) || 0;
		var title = $.trim($('#dqp_template_title').val());
		if (!title) {
			if (typeof swal === 'function') {
				swal({ type: 'warning', title: 'Enter a template name.' });
			} else {
				alert('Enter a template name.');
			}
			return;
		}
		if (!$('#dqp_time').val()) {
			if (typeof swal === 'function') {
				swal({ type: 'warning', title: 'Select Time.' });
			} else {
				alert('Select Time.');
			}
			return;
		}
		var mm = $('#dqp_mm').val() || '';
		if (!mm) {
			syncMaxMarksFromSections(true);
			mm = $('#dqp_mm').val() || '';
		}
		if (!mm) {
			if (typeof swal === 'function') {
				swal({ type: 'warning', title: 'Select Max Marks.' });
			} else {
				alert('Select Max Marks.');
			}
			return;
		}
		function doSave() {
			$.ajax({
				url: base_url + 'onlineexam/paper_design_save',
				type: 'POST',
				dataType: 'json',
				data: {
					id: id,
					title: title,
					config: JSON.stringify(collectDqpConfig()),
					branch_id: $('#dqp_branch_id').val() || ''
				},
				success: function (res) {
					if (res.status === 'success' && res.url) {
						window.location.href = res.url;
					} else if (res.status === 'fail' && res.error) {
						var msg = (res.error.form_error) ? res.error.form_error : (typeof res.error === 'string' ? res.error : 'Error');
						if (typeof swal === 'function') {
							swal({ type: 'error', title: msg });
						} else {
							alert(msg);
						}
					} else if (res.status === 'access_denied') {
						if (typeof swal === 'function') {
							swal({ type: 'error', title: 'Access denied' });
						} else {
							alert('Access denied');
						}
					}
				}
			});
		}
		var sectionTotal = computeSectionMarksTotal();
		var mmNum = parseFloat(mm, 10);
		if (sectionTotal > 0 && isFinite(mmNum) && Math.abs(mmNum - sectionTotal) >= 0.001) {
			var mismatchMsg = 'Section totals are ' + sectionTotal + ' but Max Marks is ' + mmNum + '. Save anyway?';
			if (typeof swal === 'function') {
				swal({
					title: 'Max Marks mismatch',
					text: mismatchMsg,
					type: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Save anyway',
					cancelButtonText: 'Fix first'
				}, function (isConfirm) {
					if (isConfirm) {
						doSave();
					} else {
						var el = document.getElementById('dqp_mm_warn');
						if (el && el.scrollIntoView) {
							el.scrollIntoView({ behavior: 'smooth', block: 'center' });
						}
					}
				});
				return;
			}
			if (!window.confirm(mismatchMsg)) {
				return;
			}
		}
		doSave();
	});
	dqpBindHeaderDrag();
	if (!$('#dqp_logo_pos_x').val() && !$('#dqp_school_pos_x').val() && !$('#dqp_barcode_pos_x').val()) {
		dqpApplyAlignPresets();
	} else {
		/* Fix legacy barcode defaults that sat too low and got cropped (y≈72). */
		var barY = parseFloat($('#dqp_barcode_pos_y').val());
		if (isFinite(barY) && barY > 45) {
			dqpSetPos('barcode', DQP_ALIGN_PRESETS.barcode.right.x, DQP_ALIGN_PRESETS.barcode.right.y);
		}
	}
	upd();
})(jQuery);
</script>
<?php endif; /* show_designer scripts */ ?>
<?php endif; /* branch selected */ ?>

<div id="<?php echo html_escape(isset($modal_id) ? $modal_id : 'portalModal'); ?>" class="zoom-anim-dialog mfp-hide bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col mx-auto <?php echo !empty($size) ? 'bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col mx-auto-' . html_escape($size) : ''; ?>">
    <?php if (!empty($title)) { ?>
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
            <?php if (!empty($icon)) { ?><i class="<?php echo html_escape($icon); ?> text-primary"></i><?php } ?>
            <?php echo html_escape($title); ?>
        </h3>
    </div>
    <?php } ?>
    <div class="p-5 <?php echo isset($body_class) ? html_escape($body_class) : ''; ?>" id="<?php echo html_escape(isset($body_id) ? $body_id : ''); ?>">
        <?php echo isset($body) ? $body : ''; ?>
    </div>
    <?php if (!empty($footer)) { ?>
    <div class="px-5 py-4 border-t border-slate-100 flex justify-end gap-2">
        <?php echo $footer; ?>
    </div>
    <?php } ?>
</div>

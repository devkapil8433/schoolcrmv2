<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden <?php echo isset($class) ? html_escape($class) : ''; ?>">
    <?php if (!empty($title)) { ?>
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
            <?php if (!empty($icon)) { ?><i class="<?php echo html_escape($icon); ?> text-primary"></i><?php } ?>
            <?php echo html_escape($title); ?>
        </h2>
        <?php if (!empty($actions)) { echo $actions; } ?>
    </div>
    <?php } ?>
    <div class="p-5">
        <?php echo isset($body) ? $body : ''; ?>
    </div>
</div>

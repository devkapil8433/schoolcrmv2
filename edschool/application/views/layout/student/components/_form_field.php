<div class="<?php echo !empty($wrapper_class) ? html_escape($wrapper_class) : 'mb-4'; ?>">
    <?php if (!empty($label)) { ?>
    <label class="block text-sm font-medium text-slate-700 mb-1.5" for="<?php echo html_escape(isset($id) ? $id : ''); ?>">
        <?php echo html_escape($label); ?>
        <?php if (!empty($required)) { ?><span class="text-red-500">*</span><?php } ?>
    </label>
    <?php } ?>
    <?php echo isset($field) ? $field : ''; ?>
    <?php if (!empty($hint)) { ?>
    <p class="mt-1 text-xs text-slate-500"><?php echo html_escape($hint); ?></p>
    <?php } ?>
</div>

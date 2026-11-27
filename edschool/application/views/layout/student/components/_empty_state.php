<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    <i class="<?php echo !empty($icon) ? html_escape($icon) : 'fas fa-inbox'; ?> w-12 h-12 text-slate-300 mb-3"></i>
    <p class="text-slate-500 text-sm"><?php echo html_escape(isset($message) ? $message : translate('no_information_available')); ?></p>
    <?php if (!empty($action)) { echo $action; } ?>
</div>

<div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
        <a href="<?php echo base_url('dashboard'); ?>" class="flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-primary hover:border-primary transition-colors">
            <i class="fas fa-home text-sm"></i>
        </a>
        <h1 class="text-2xl font-semibold text-slate-900 tracking-tight"><?php echo html_escape(isset($title) ? $title : ''); ?></h1>
    </div>
</div>

<div class="overflow-x-auto rounded-lg border border-slate-200">
    <table class="w-full text-sm text-left [&_thead]:bg-slate-50 [&_thead]:text-slate-600 [&_thead]:uppercase [&_thead]:text-xs [&_thead]:tracking-wider [&_th]:px-4 [&_th]:py-3 [&_th]:font-semibold [&_th]:border-b [&_th]:border-slate-200 [&_th]:whitespace-nowrap [&_td]:px-4 [&_td]:py-3 [&_td]:border-b [&_td]:border-slate-100 [&_tbody_tr:hover]:bg-slate-50 <?php echo isset($table_class) ? html_escape($table_class) : ''; ?>" id="<?php echo html_escape(isset($table_id) ? $table_id : ''); ?>" <?php echo !empty($width) ? 'width="100%"' : ''; ?>>
        <?php if (!empty($thead)) { echo $thead; } ?>
        <?php if (!empty($tbody)) { echo $tbody; } ?>
    </table>
</div>

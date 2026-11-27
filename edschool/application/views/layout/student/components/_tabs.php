<?php if (!empty($tabs) && is_array($tabs)) { ?>
<div data-portal-tab-group>
    <div class="flex flex-wrap gap-1 border-b border-slate-200 mb-4">
        <?php foreach ($tabs as $i => $tab) {
            $active = !empty($tab['active']) || ($i === 0 && !isset($active_set));
            if ($active) $active_set = true;
        ?>
        <a href="#<?php echo html_escape($tab['id']); ?>" data-portal-tab="<?php echo html_escape($tab['id']); ?>" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-500 border-b-2 border-transparent -mb-px transition-colors hover:text-slate-700 <?php echo $active ? 'text-primary border-primary' : ''; ?>">
            <?php if (!empty($tab['icon'])) { ?><i class="<?php echo html_escape($tab['icon']); ?>"></i><?php } ?>
            <?php echo html_escape($tab['label']); ?>
        </a>
        <?php } ?>
    </div>
    <div>
        <?php echo isset($content) ? $content : ''; ?>
    </div>
</div>
<?php } ?>

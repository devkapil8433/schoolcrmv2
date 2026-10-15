<?php
$variant = isset($variant) ? $variant : 'primary';
$size = isset($size) ? $size : '';
$classes = portal_tw_btn($variant, $size);
if (!empty($class)) {
    $classes .= ' ' . $class;
}
$tag = !empty($href) ? 'a' : 'button';
$attrs = !empty($href) ? ' href="' . html_escape($href) . '"' : ' type="' . html_escape(isset($type) ? $type : 'button') . '"';
if (!empty($id)) {
    $attrs .= ' id="' . html_escape($id) . '"';
}
if (!empty($extra_attrs)) {
    $attrs .= ' ' . $extra_attrs;
}
?>
<<?php echo $tag; ?> class="<?php echo $classes; ?>"<?php echo $attrs; ?>>
    <?php if (!empty($icon)) { ?><i class="<?php echo html_escape($icon); ?>"></i><?php } ?>
    <?php echo isset($label) ? html_escape($label) : ''; ?>
</<?php echo $tag; ?>>

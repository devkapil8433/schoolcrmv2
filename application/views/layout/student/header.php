<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo html_escape($global_config['institute_name']); ?>">
    <title><?php echo html_escape($title); ?></title>
    <link rel="shortcut icon" href="<?php echo asset_url('assets/images/favicon.png'); ?>">

    <?php
    $primaryColor = !empty($theme_config['primary_color']) ? $theme_config['primary_color'] : '#4f46e5';
    ?>
    <style>
        :root {
            --portal-primary: <?php echo html_escape($primaryColor); ?>;
            --portal-primary-dark: <?php echo html_escape($primaryColor); ?>;
            --portal-primary-50: color-mix(in srgb, <?php echo html_escape($primaryColor); ?> 10%, white);
            --portal-primary-100: color-mix(in srgb, <?php echo html_escape($primaryColor); ?> 20%, white);
        }
    </style>

    <?php $this->load->view('layout/student/stylesheet'); ?>

    <script src="<?php echo asset_url('assets/vendor/jquery/jquery.js'); ?>"></script>
    <script type="text/javascript">
        var base_url = '<?php echo base_url(); ?>';
        var isRTLenabled = '<?php echo $this->app_lib->isRTLenabled(); ?>';
        var csrfData = <?php echo json_encode(csrf_jquery_token()); ?>;
        $(function($) {
            $.ajaxSetup({
                cache: false,
                data: csrfData
            });
        });
    </script>

    <?php
    if (isset($headerelements)) {
        foreach ($headerelements as $type => $element) {
            if ($type == 'css' && count($element)) {
                foreach ($element as $css) {
                    echo '<link rel="stylesheet" href="' . headerelement_url($css) . '">' . "\n";
                }
            } elseif ($type == 'js' && count($element)) {
                foreach ($element as $js) {
                    echo '<script src="' . headerelement_url($js) . '"></script>' . "\n";
                }
            }
        }
    }
    ?>
</head>

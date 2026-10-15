<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo html_escape($global_config['institute_name']); ?>">
    <title><?php echo html_escape(isset($page_title) ? $page_title : translate('login')); ?></title>
    <link rel="shortcut icon" href="<?php echo asset_url('assets/images/favicon.png'); ?>">
    <?php $primaryColor = !empty($theme_config['primary_color']) ? $theme_config['primary_color'] : '#5061ab'; ?>
    <style>:root { --auth-primary: <?php echo html_escape($primaryColor); ?>; --auth-primary-dark: <?php echo html_escape($primaryColor); ?>; }</style>
    <link href="<?php echo is_secure('fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo asset_url('assets/vendor/bootstrap/css/bootstrap.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/vendor/font-awesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/login_page/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="<?php echo asset_url('assets/vendor/jquery/jquery.js'); ?>"></script>
    <script>var base_url = '<?php echo base_url(); ?>'; var login_slider_images = <?php echo json_encode(!empty($login_slider_images) ? $login_slider_images : array()); ?>;</script>
</head>
<body class="auth-body">
<div id="authBackdrop" class="auth-backdrop"></div>
<div class="auth-bg-shapes">
    <div class="auth-shape auth-shape-1"></div>
    <div class="auth-shape auth-shape-2"></div>
    <div class="auth-shape auth-shape-3"></div>
</div>
<div class="container-fluid auth-main">
    <div class="row auth-card slideIn">
        <?php $this->load->view('authentication/_brand_panel'); ?>
        <div class="col-lg-7 col-md-6 no-padding">
            <div class="sign-area">

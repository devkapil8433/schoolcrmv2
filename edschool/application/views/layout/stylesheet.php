<!-- Web Fonts  -->
<link href="<?php echo is_secure('fonts.googleapis.com/css?family=Signika:wght@300..700&display=swap'); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/bootstrap/css/bootstrap.css'); ?>">
<?php if ($this->app_lib->isRTLenabled()) { ?>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/bootstrap.rtl.min.css'); ?>">
<?php } ?>
<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/font-awesome/css/all.min.css'); ?>">

<!-- Jquery Datatables CSS -->
<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/datatables/media/css/dataTables.bootstrap.min.css'); ?>">

<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/select2/css/select2.css'); ?>">
<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/select2-bootstrap-theme/select2-bootstrap.min.css'); ?>">
<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css'); ?>">
<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/simple-line-icons/css/simple-line-icons.css'); ?>">
<link rel="stylesheet" href="<?php echo asset_url('assets/vendor/magnific-popup/magnific-popup.css'); ?>">
<link rel="stylesheet" href="<?php echo asset_url('assets/css/custom-style.css'); ?>">
<link rel="stylesheet" href="<?php echo asset_url('assets/css/skins/default.css'); ?>">
<!-- <link rel="stylesheet" href="<?php echo asset_url('assets/vendor/sweetalert/sweetalert-custom.css'); ?>"> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .swal-success-custom {
        background-color: #d4edda !important;
        color: #000000ff !important;
        font-size: 15px !important;
        margin-top: 52px !important;
        border: 1px solid #c3e6cb;
         width: 300px !important;
         padding: 8px 14px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
    }

    .swal-error-custom {
        background-color: #fcf3f3ff !important;
        color: #000000ff !important;
        font-size: 15px !important;
        margin-top: 52px !important;
        border: 1px solid #d00808ff;
         width: 300px !important;
         padding: 8px 14px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;

    }
</style>
<?php if ($this->app_lib->isRTLenabled()) { ?>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/style-rtl.css'); ?>">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Optional: custom CSS -->
    <link rel="stylesheet" href="<?= asset_url('assets/css/multistep.css') ?>">

<?php } ?>
<!-- jquery -->
<script src="<?php echo asset_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script src="<?php echo asset_url('assets/vendor/jquery-ui/jquery-ui.min.js'); ?>"></script>
<script src="<?php echo asset_url('assets/vendor/modernizr/modernizr.js'); ?>"></script>

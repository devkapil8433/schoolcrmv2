<!doctype html>
<html lang="en" class="h-full" <?php echo $this->app_lib->isRTLenabled() ? 'dir="rtl"' : ''; ?>>
<?php $this->load->view('layout/student/header'); ?>
<body class="h-full bg-slate-50">
<div id="portal-app" class="min-h-full flex">
    <?php $this->load->view('layout/student/sidebar'); ?>

    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
        <?php $this->load->view('layout/student/topbar'); ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <?php $this->load->view('layout/student/components/_page_header', array('title' => $title)); ?>
                <?php $this->load->view($sub_page); ?>
            </div>
        </main>
    </div>
</div>

<!-- Mobile sidebar overlay -->
<div id="portal-sidebar-overlay" class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden hidden" aria-hidden="true"></div>

<?php $this->load->view('layout/student/script'); ?>

<?php
$alertclass = '';
if ($this->session->flashdata('alert-message-success')) {
    $alertclass = 'success';
} elseif ($this->session->flashdata('alert-message-error')) {
    $alertclass = 'error';
} elseif ($this->session->flashdata('alert-message-info')) {
    $alertclass = 'info';
}
if ($alertclass != '') {
    $alert_message = $this->session->flashdata('alert-message-' . $alertclass);
    $swal_icon = $alertclass === 'error' ? 'error' : ($alertclass === 'info' ? 'info' : 'success');
?>
<script type="text/javascript">
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?php echo $swal_icon; ?>',
        title: <?php echo json_encode($alert_message); ?>,
        showConfirmButton: false,
        timer: 8000,
        timerProgressBar: true
    });
</script>
<?php } ?>

<script type="text/javascript">
function confirm_modal(delete_url) {
    Swal.fire({
        title: <?php echo json_encode(translate('are_you_sure')); ?>,
        text: <?php echo json_encode(translate('delete_this_information')); ?>,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: <?php echo json_encode(translate('yes_continue')); ?>,
        cancelButtonText: <?php echo json_encode(translate('cancel')); ?>,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        footer: <?php echo json_encode(translate('deleted_note')); ?>
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: delete_url,
                type: 'POST',
                success: function() {
                    Swal.fire({
                        title: <?php echo json_encode(translate('deleted')); ?>,
                        text: <?php echo json_encode(translate('information_deleted')); ?>,
                        icon: 'success'
                    }).then(function() {
                        if (typeof cusDataTable !== 'undefined') {
                            cusDataTable.ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                    });
                }
            });
        }
    });
}
</script>
</body>
</html>

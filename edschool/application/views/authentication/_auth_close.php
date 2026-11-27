            </div>
        </div>
    </div>
</div>
<script src="<?php echo asset_url('assets/login_page/js/jquery.backstretch.min.js'); ?>"></script>
<script src="<?php echo asset_url('assets/login_page/js/custom.js'); ?>"></script>
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
<script>
Swal.fire({ toast: true, position: 'top-end', icon: '<?php echo $swal_icon; ?>', title: <?php echo json_encode($alert_message); ?>, showConfirmButton: false, timer: 8000 });
</script>
<?php } ?>
</body>
</html>

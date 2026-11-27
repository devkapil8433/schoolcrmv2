<?php
$title = ($access['code'] === 'BRANCH_INACTIVE' || $access['code'] === 'SCHOOL_INACTIVE') ? 'Access Disabled' : 'Subscription ' . ($access['state'] === 'suspended' ? 'Suspended' : 'Expired');
$linked = get_linked_accounts();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($title); ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('assets/vendor/bootstrap/css/bootstrap.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/vendor/font-awesome/css/all.min.css'); ?>">
    <style>
        body { background:#f1f4f9; font-family: "Plus Jakarta Sans", Arial, sans-serif; }
        .blocked-card { max-width: 560px; margin: 8vh auto; background:#fff; border-radius:14px; padding:36px 32px; box-shadow:0 10px 40px rgba(31,45,90,.12); text-align:center; }
        .blocked-card .icon { font-size:54px; color:#d9534f; }
        .blocked-card h2 { margin:14px 0 8px; font-weight:700; color:#1f2d5a; }
        .blocked-card .meta { color:#6b7280; font-size:13px; margin-top:14px; }
    </style>
</head>
<body>
<div class="blocked-card">
    <div class="icon"><i class="fas fa-lock"></i></div>
    <h2><?php echo html_escape($title); ?></h2>
    <p><?php echo html_escape($access['message']); ?></p>
    <p class="text-muted"><?php echo html_escape($access['support_message']); ?></p>
    <?php if (!empty($access['expiry_date'])) { ?>
    <p class="meta">
        Expiry date: <strong><?php echo date('d M Y', strtotime($access['expiry_date'])); ?></strong>
        <?php if (!empty($access['grace_end'])) { ?> &middot; Grace period ended: <strong><?php echo date('d M Y', strtotime($access['grace_end'])); ?></strong><?php } ?>
    </p>
    <?php } ?>
    <p class="meta">Reference: <?php echo html_escape($access['code']); ?></p>
    <hr>
    <?php if ($linked) { ?>
        <p class="text-muted">Other signed-in accounts:</p>
        <?php foreach ($linked as $linkedID => $acc) { ?>
        <p><a class="btn btn-default btn-sm" href="<?php echo base_url('authentication/switch_account/' . (int) $linkedID); ?>"><i class="fas fa-user-friends"></i> <?php echo html_escape($acc['name']); ?> (<?php echo html_escape(ucfirst($acc['role'])); ?>)</a></p>
        <?php } ?>
    <?php } ?>
    <?php if ($access['code'] === 'BRANCH_INACTIVE' && is_admin_loggedin()) { ?>
        <a href="<?php echo base_url('branch_manager'); ?>" class="btn btn-primary"><i class="fas fa-code-branch"></i> Switch branch</a>
    <?php } ?>
    <a href="<?php echo base_url('authentication/logout'); ?>" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</body>
</html>

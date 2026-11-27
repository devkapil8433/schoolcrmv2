<?php $zoom_version = '3.10.0'; ?>
<!DOCTYPE html>
<html lang="en" >
<head>
    <title><?php echo translate('live_class_rooms'); ?></title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="<?php echo base_url('assets/student-portal/css/portal.css?v=' . version_combine()); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/font-awesome/css/all.min.css'); ?>">
    <link type="text/css" rel="stylesheet" href='<?php echo is_secure("source.zoom.us/$zoom_version/css/react-select.css"); ?>' />
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
</head>
<body oncontextmenu="return false;" class="antialiased">
<?php
    $getStudent = $this->application_model->getStudentDetails(get_loggedin_user_id());
    $config = $this->db->where('branch_id', $getStudent['branch_id'])->get('live_class_config')->row_array();
    $meetingID = $this->input->get('meeting_id', true);
    $liveID = $this->input->get('live_id', true);
    $this->db->where('id', $liveID);
    $this->db->where('meeting_id', $meetingID);
    $this->db->where('branch_id', $getStudent['branch_id']);
    $liveClass = $this->db->get('live_class')->row_array();
    if (!is_array($liveClass)) {
        access_denied();
    }
    $zoom_api_key = $config['zoom_api_key'];
    $zoom_api_secret = $config['zoom_api_secret'];
?>
<nav id="nav-tool" class="fixed top-0 inset-x-0">
    <div class="mx-auto">
        <h4 class="text-white">
            <i class="fab fa-chromecast"></i>
            <span ><?php echo html_escape($liveClass['title']); ?></span>
        </h4>
        <p >
            <i class="far fa-user-circle"></i>
            <?php echo translate('host'); ?>: <?php echo html_escape(get_type_name_by_id('staff', $liveClass['created_by'])); ?>
        </p>
    </div>
</nav>
<script src="https://source.zoom.us/<?php echo $zoom_version; ?>/lib/vendor/react.min.js"></script>
<script src="https://source.zoom.us/<?php echo $zoom_version; ?>/lib/vendor/react-dom.min.js"></script>
<script src="https://source.zoom.us/<?php echo $zoom_version; ?>/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/<?php echo $zoom_version; ?>/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/<?php echo $zoom_version; ?>/lib/vendor/lodash.min.js"></script>
<script src="https://source.zoom.us/zoom-meeting-<?php echo $zoom_version; ?>.min.js"></script>
<script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script type="text/javascript">
    document.onkeydown = function(e) {
        if (event.keyCode == 123) return false;
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) return false;
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) return false;
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) return false;
        if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false;
    };

    ZoomMtg.setZoomJSLib("https://source.zoom.us/<?php echo $zoom_version; ?>/lib", "/av");
    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareWebSDK();
    var meetConfig = {
        sdkKey: "<?php echo $zoom_api_key; ?>",
        sdkSecret: "<?php echo $zoom_api_secret; ?>",
        meetingNumber: "<?php echo $meetingID; ?>",
        userName: "<?php echo html_escape($getStudent['first_name'] . ' ' . $getStudent['last_name'] . ' (Roll - ' . $getStudent['roll'] . ')'); ?>",
        passWord: "<?php echo $liveClass['meeting_password']; ?>",
        leaveUrl: "<?php echo base_url('userrole/live_class'); ?>",
        role: parseInt(0, 10)
    };
    var signature = ZoomMtg.generateSDKSignature({
        meetingNumber: meetConfig.meetingNumber,
        sdkKey: meetConfig.sdkKey,
        sdkSecret: meetConfig.sdkSecret,
        role: meetConfig.role,
        success: function(res) { console.log(res.result); }
    });
    ZoomMtg.i18n.load("en-US");
    ZoomMtg.init({
        leaveUrl: meetConfig.leaveUrl,
        isSupportAV: true,
        webEndpoint: 'zoom.us',
        externalLinkPage: './externalLinkPage.html',
        success: function () {
            ZoomMtg.i18n.load("en-US");
            ZoomMtg.join({
                meetingNumber: meetConfig.meetingNumber,
                userName: meetConfig.userName,
                signature: signature,
                sdkKey: meetConfig.sdkKey,
                passWord: meetConfig.passWord,
                success: function(res) {
                    $('#nav-tool').hide();
                    console.log(res);
                },
                error: function(res) { console.log(res); }
            });
        },
        error: function(res) { console.log(res); }
    });
</script>
</body>
</html>

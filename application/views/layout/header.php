<head>
	<meta charset="UTF-8">
	<meta name="keywords" content="<?php echo $global_config['institute_name'] ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="<?php echo $global_config['institute_name'] ?>">
	<meta name="author" content="<?php echo $global_config['institute_name'] ?>">
	<title><?php echo $title;?></title>
    <link rel="shortcut icon" href="<?php echo asset_url('assets/images/favicon.png');?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<!-- include stylesheet -->
	<?php include 'stylesheet.php';?>

	<?php
	if(isset($headerelements)) {
		foreach ($headerelements as $type => $element) {
			if($type == 'css') {
				if(count($element)) {
					foreach ($element as $keycss => $css) {
						echo '<link rel="stylesheet" href="'. headerelement_url($css) . '">' . "\n";
					}
				}
			} elseif($type == 'js') {
				if(count($element)) {
					foreach ($element as $keyjs => $js) {
						echo '<script type="text/javascript" src="' . headerelement_url($js). '"></script>' . "\n";
					}
				}
			}
		}
	}
	?>
	<!-- theme css -->
	<link rel="stylesheet" href="<?php echo asset_url('assets/css/ramom.css');?>">
	<?php if ((isset($main_menu) && in_array($main_menu, array('fees', 'fees_structure', 'fees_repots'), true)) || in_array(strtolower($this->router->fetch_class()), array('fees', 'fees_structure'), true)): ?>
		<link rel="stylesheet" href="<?php echo asset_url('assets/css/fee-collection-ui.css?v=3');?>">
	<?php endif; ?>
	<?php if ($theme_config["border_mode"] == 'false'): ?>
		<link rel="stylesheet" href="<?php echo asset_url('assets/css/skins/square-borders.css');?>">
	<?php endif; ?>

	<!-- If user have enabled CSRF proctection this function will take care of the ajax requests and append custom header for CSRF -->
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
</head>
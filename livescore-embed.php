<?php
$absolute_path = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
$wp_load = $absolute_path[0] . 'wp-config.php';
require_once $wp_load;

$pl_refresh_interval = (int) (get_option('pl_refresh_interval') * 1000);
?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<title>Livescore</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="<?php echo PL_PLUGIN_URL; ?>/css/style.css">
<link rel="stylesheet" href="<?php echo PL_PLUGIN_URL; ?>/css/style-embed.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
jQuery(document).ready(function() {
    var rel = jQuery('.pl').attr('rel');
	jQuery('#online').load('<?php echo PL_PLUGIN_URL; ?>/includes/pl-refresh.php?rel=' + rel);
	var refreshId = setInterval(function() {
		jQuery('#online').load('<?php echo PL_PLUGIN_URL; ?>/includes/pl-refresh.php?randval=' + Math.random() + '&rel=' + rel);
	}, <?php echo $pl_refresh_interval; ?>);
});
</script>
</head>
<body>
<div id="online"><?php echo __('Livescore is loading...', 'pl'); ?></div>
</body>
</html>

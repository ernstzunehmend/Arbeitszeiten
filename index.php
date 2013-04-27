<?php 

include('includes/config.php');
include('classes/sql.class.php');
include('classes/arbeitszeiten.class.php');

?>

<!doctype html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<!-- Optimierungen für mobile Browser: j.mp/bplateviewport -->
	<meta name="viewport" content="width=device-width,initial-scale=1.0">

	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

	<!-- Place favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
	<link rel="shortcut icon" href="/favicon.ico">
	
	<link rel="stylesheet" href="css/reset.css" />
	<link rel="stylesheet" href="css/style.css" />
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
	
	<!-- Apple -->
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<link rel="apple-touch-startup-image" href="images/app/ios/startup.png">
	
	<link rel="apple-touch-icon" href="images/app/ios/apple-touch-icon-57x57-precomposed.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="images/app/ios/apple-touch-icon-72x72-precomposed.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="images/app/ios/apple-touch-icon-114x114-precomposed.png" />

	<title>Arbeitszeiten - Keep it simple!</title>
</head>
<body class="page start" lang="de">
<div class="wrapper">

	<?php
	
		$userid = intval((isset($_GET['u'])) ? $_GET['u'] : 1);
		$az = new arbeitszeiten($userid);

	
		$content = $_GET["cid"];
		if (is_file("parts/".$content.".php")) {
			include("parts/".$content.".php"); }
		else {
			include ('parts/arbeitszeiten.php'); }
			
		echo $msg;
	?>


	<!-- JavaScript am Ende, un die Seite schneller zu laden -->
	
	<!-- Grab Google CDN's jQuery. fall back to local if necessary -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="./scripts/plugins/jquery-1.9.1.js"><\/script>')</script>
	<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>	
	<script>
	$(function() {
    	$( "#datepicker" ).datepicker({ 
    		dateFormat: "yy-mm-dd"
    	});
 	});
	</script>
	
	
</div>
</body>
</html>
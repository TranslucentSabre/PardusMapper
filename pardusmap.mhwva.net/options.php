<?php
require_once('include/mysqldb.php');
$db = new mysqldb;

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { include('index.html'); exit; }

session_name($uni = $db->protect($_REQUEST['uni']));

session_start();

$testing = Settings::TESTING;
$debug = Settings::DEBUG;
$debug = 0;

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }

$css = $base_url . '/main.css';

$url = null;
if (isset($_REQUEST['url'])) { $url = $db->protect($_REQUEST['url']); }
else { $url = $_SERVER['HTTP_REFERER']; }


// Start the Session
session_start();

if ($debug) print_r($_SESSION);
if ($debug) echo '<br>';

$logged_in = 0;
if (isset($_SESSION['user'])) { $logged_in = 1; }

if (isset($_REQUEST['image'])) {
	$loc2 = $db->protect($_REQUEST['loc2']);
	$loc3 = $db->protect($_REQUEST['loc3']);
	if ($logged_in) {
		$db->query('UPDATE ' . $uni . '_Users set imagepack = \'' . $db->protect($loc2 . $loc3) . '\' where username = \'' . $_SESSION['user'] .  '\'');
	}
	setcookie("imagepack",$loc2 . $loc3,time()+60*60*24*365,"/");
}
if (!isset($_REQUEST['oldpwd'])) {
	if ($debug) echo 'Old Password was not entered<br>';
	unset($_REQUEST['change']);
} else {
	if ($debug) echo 'Old Password entered<br>';
	$oldpwd = $db->protect($_REQUEST['oldpwd']);
	$invalidoldpwd = 0;
	$db->query('SELECT * FROM ' . $uni . '_Users where username = \'' . $_SESSION['user'] .  '\'');
	if ($u = $db->nextObject()) {
		if ($u->password != sha1($oldpwd)) {
			if ($debug) echo 'Old Password did not match<br>';
			$invalidoldpwd = 1;
			unset($_REQUEST['change']);
		}
	}
}

$invalidpwd = 0;
$newpwdnotentered = 0;
if (strlen($_REQUEST['newpwd1']) > 0){
	if ($_REQUEST['newpwd1'] != $_REQUEST['newpwd2']) {
		if ($debug) echo 'New Passwords do not Match<br>';
		$invalidpwd = 1;
		unset($_REQUEST['change']);
	} else {
		if ($debug) echo 'New Passwords Match<br>';
		$newpwd = $_REQUEST['newpwd1'];
	}
} else {
	if ($debug) echo 'New Password not set<br>';
	if (isset($_REQUEST['newpwd1'])) { $newpwdnotentered = 1; }
	unset($_REQUEST['change']);
}


if (isset($_REQUEST['change'])) {
	$db->query('UPDATE ' . $uni . '_Users SET password = \'' . sha1($newpwd) . '\' WHERE username = \'' . $_SESSION['user'] . '\'');
	$db->close();
	header("Location: $url");
} else {

if ($debug) echo 'Change not Set<br>';
$db->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<title>Zaqwer's Pardus Map Options Page</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
		<script type="text/javascript">
			//<![CDATA[
			function loadLoc() {
				var img_pack = document.getElementById('img_pack');
				switch(img_pack.loc1.value) {
					case "1" : {
						img_pack.loc2[1].selected = "1";
						img_pack.loc3.value = "static.pardus.at/images";
						break;
					}
					case "2" : {
						img_pack.loc2[1].selected = "1";
						img_pack.loc3.value = "static.pardus.at/img/std";
						break;
					}
					case "3" : {
						img_pack.loc2[1].selected = "1";
						img_pack.loc3.value = "static.pardus.at/img/stdhq";
						break;
					}
					case "4" : {
						img_pack.loc2[1].selected = "1";
						img_pack.loc3.value = "static.pardus.at/img/kora";
						break;
					}
					case "5" : {
						img_pack.loc2[1].selected = "1";
						img_pack.loc3.value = "static.pardus.at/img/solarix";
						break;
					}
				}
			}
			//]]>
		</script>
		<script type="text/javascript">

			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-15475436-5']);
			_gaq.push(['_setDomainName', '.mhwva.net']);
			_gaq.push(['_trackPageview']);
	
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();

		</script>	
	</head>
	<body bgcolor="#ffffff" text="#000000" link="#000000" vlink="#000000" alink="#0000FF">
		<div id="header_side"><?php include('include/header_side.php'); ?></div>
		<div id="body">
		<center>
			<?php if ($logged_in) { ?>
			<h2>Change Password</h2>
			<form method="POST" action="<?php echo $base_url . '/' . $uni; ?>/options.php">
				<?php if ($invalidoldpwd) {echo 'Your Old Password was Incorrect Please Try Again.<br>'; } ?>
				Old Password : <input type="password" name="oldpwd" size="20"><br><br>
				<?php if($invalidpwd) {echo 'Your Passwords Did Not Match Please Try Again.<br>'; } ?>
				<?php if($newpwdnotentered) { echo 'You Need to Enter a New Password.<br>'; } ?>
				New Password : <input type="password" name="newpwd1" size="20"><br><br>
				New Password:  <input type="password" name="newpwd2" size="20"><br><br>
				<input type="hidden" value="<?php echo $url; ?>" name="url">
				<input type="submit" value="Change Password" name="change">
			</form>
			<br>
			<?php } ?>
			<?php if (isset($_REQUEST['image'])) { echo '<h3>Image Pack Set to ' . $loc2 . $loc3 . '</h3>'; } ?>
			<h2>Image Pack</h2>
			<form id="img_pack" method="POST" action="<?php echo $base_url . '/' . $uni; ?>/options.php">
				<select onchange="loadLoc()" name="loc1">
					<option value="0">Custom</option>
					<option value="1">Clasic</option>
					<option value="2">Standard</option>
					<option value="3">Standard HQ</option>
					<option value="4">Kora's IP</option>
					<option value="5">Solarix's IP</option>
				</select>
				<br><br>
				<select name="loc2">
					<option value="file://">file://</option>
					<option value="http://">http://</optoin>
				</select>
				<input type="text" name="loc3" value="" size="30">
				<br><br>
				<input type="hidden" value="<?php echo $url; ?>" name="url">
				<input type="submit" value="Set Image Pack" name="image">
			</form>
			<br><br>
			<a href="<?php echo $url; ?>">Return to Previous Page</a>
		</center>
		</div>
	</body>
</html>
<?php

}

?>
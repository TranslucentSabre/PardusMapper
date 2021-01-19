<?php
require_once('include/mysqldb.php');
$db = new mysqldb;
$testing = Settings::TESTING;
$debug = Settings::DEBUG;

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }

session_name($uni = $db->protect($_REQUEST['uni']));

// Start the Session
session_start();

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }

$css = $base_url . '/main.css';

$name = null;
$pwd = null;

$url = null;
if (isset($_REQUEST['url'])) { $url = $db->protect($_REQUEST['url']); }
else { $url = $_SERVER['HTTP_REFERER']; }


$invalidusr = 0;
if (isset($_REQUEST['username']))  {
	$name = $db->protect($_REQUEST['username']);
	$db->query('SELECT * FROM ' . $uni . '_Users where username = \'' . $name.  '\'');
	if ($u = $db->nextObject()) {
		if ($u->password != sha1('n0p2ssword')) {
			$url = $base_url . '/' . $uni . '/login.php?alreadysignedup=1&url=' . $_REQUEST['url'];
			header("Location: $url");
		} else {
			$invalidpwd = 0;
			if (strlen($_REQUEST['password1']) > 0){
				if ($_REQUEST['password1'] != $_REQUEST['password2']) {
					$invalidpwd = 1;
					unset($_REQUEST['signup']);
				} else {
					$pwd = $_REQUEST['password1'];
					$db->query('UPDATE ' . $uni . '_Users SET password = \'' . sha1($pwd) . '\' WHERE username = \'' . $name . '\'');
					$url = $base_url . '/' . $uni . '/login.php?signedup=1&url=' . $_REQUEST['url'];
					header("Location: $url");
				}
			}
		}
	} else {
		$invalidusr = 1;
	}
}
$db->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<title>Zaqwer's Pardus Map Sign Up Page</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
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
	<body>
		<div id="header_side">
				<?php include('include/header_side.php'); ?>
		</div>
		<div id="body">
			<h2>Sign Up</h2>
			<h3>You must have Zaqwer's Pardus Mapper User Script installed and uploaded data before signing up for an Account</h3>
			<form method="POST" action="<?php echo $base_url . '/' . $uni; ?>/signup.php">
				<?php if ($invalidusr) {echo 'Invalid User Name. Please Enter Your Pardus Name Exactly.<br>'; } ?>
				Username: <input type="text" id="username" name="username" size="20" value="<?php echo $name; ?>"><br><br>
				<?php if($invalidpwd) {echo 'Your Passwords Did Not Match Please Try Again.<br>'; } ?>
				Password : <input type="password" name="password1" size="20"><br><br>
				Password:  <input type="password" name="password2" size="20"><br><br>
				<input type="hidden" value="<?php echo $url; ?>" name="url">
				<input type="submit" value="Sign Up" name="signup">
			</form>
			<br>
		</div>
	</body>
</html>

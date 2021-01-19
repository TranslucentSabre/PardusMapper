<?php
 
 if ($_REQUEST['dl'] == 0) {
 
	/* File we want to send to the browser */
	$filename = "zaqmapper.xpi";
 
 	/**
	* The following header is required for browsers that do not
	* recognize the xpi extension. i.e all browsers other than Firefox.
	* This will display the familiar 'save/open' dialog if the xpi
	* extension is not supported.
	*/
	header("Content-Disposition: filename={$filename}");
 
	/* Tell the browser that the content that is coming is an xpinstall */
	header('Content-type: application/x-xpinstall');
 
	/* Also send the content length */
	header('Content-Length: ' . filesize($filename));
 
 	/* readfile reads the file content and echos it to the output */
	readfile($filename);
} elseif ($_REQUEST['dl'] == 1) {
	require_once('../include/mysqldb.php');
	$db = new mysqldb;

	// Set Univers Variable and Session Name
	if (!isset($_REQUEST['uni'])) { exit; }

	session_name($uni = $db->protect($_REQUEST['uni']));

	$testing = Settings::TESTING;
	$debug = Settings::DEBUG;

	$base_url = 'http://pardusmap.mhwva.net';
	if ($testing) { $base_url .= '/TestMap'; }

	$css = $base_url . '/main.css';

	// Start the Session
	session_start();

	$db->close();
?>
<html>
	<head>
		<title>Download User Script</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
	</head>
	<body>
		<div id="header_side"><?php include('../include/header_side.php'); ?></div>
		<div id="footer"><?php include('../include/footer.php'); ?></div>
		<div id="body">
		<h1>*** Note Greasemonkey 4 and Firefox 57 have caused myscript to stop working ***</h1>
		<ul>
			<li><h1>Firefox</h1>
			<ul>
				<h2>
				<li>For Firefox version 57 or higher you will need to use <a href="https://addons.mozilla.org/en-US/firefox/addon/tampermonkey/">Tamper Monkey</a> instead of  <a href="https://addons.mozilla.org/en-US/firefox/addon/748">Greasemonkey</a> to use my script.
				</h2>
			</ul>
			<li><h1>Opera</h1>
			<ul>
				<h2>
				<li>I am currently not actively trying to get the script to work with Opera and the below directions are old.
				<li>For help using User Scripts in Opera follow this <a href="http://www.opera.com/browser/tutorials/userjs/using/">Link</a>
				<li>You will need to install two other User Scripts before mine will work they are
					<a href="http://pardusmap.mhwva.net/Download/a-lib-stacktrace.js">a-lib-stacktrace.js</a>
					and
					<a href="http://pardusmap.mhwva.net/Download/a-lib-xmlhttp-cd.js">a-lib-xmlhttp-cd.js</a>
				</h2>
			</ul>
			<li><h1>Google Chrome</h1>
			<ul>
				<h2>
				<li>Install <a href="http://tampermonkey.net/">Tamper Monkey</a>
				</h2>
			</ul>
			<li><h1>Once you have done one of the above steps you can download my User Script <a href="http://pardusmap.mhwva.net/Download/pardus_mapper.user.js">Here</a></h1>
		</ul>
		</div>
	</body>
</html>
<?php } ?>

<?php
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
		<title>News Page</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
	</head>
	<body>
		<div id="header_side"><?php include('../include/header_side.php'); ?></div>
		<div id="footer"><?php include('../include/footer.php'); ?></div>
		<div id="body">
			<ul>
				<li><h1>Solarix's IP</h1>
				<ul>
					<li>I have finally gotten around to adding Solarix's IP to the choices on the Options Page. Everone enjoy these wonderful images
				</ul>
				<li><h1>Script Version 3.9</h1>
				<ul>
					<li>I have not been very good at updating this page so I am going to add all the updates between version 3.2 and 3.9 here.
					<li><h2>3.9</h2>
					<ul>
						<li>Updated script to allow remote updating of Prices and Stocks for Buildings accessable from the Overview->Buildings Tab
						<li>Fixed a bug in the mainMap function when I updated it to work with the FF Coords Addon
					</ul>
					<li><h2>3.8</h2>
					<ul>
						<li>Updated script mainMap function to work with Coords Addon for FF
					</ul>
					<li><h2>3.7</h2>
					<ul>
						<li>Added userID collection to allow for name changes without having to create new account
					</ul>
					<li><h2>3.6</h2>
					<ul>
						<li>Added in functionality to Grab Equipment Info from Starbases and Building info from Overview Screen
						<li>Changed import script names on server to help troubleshooting and hopefully speed up the script and server
					</ul>
					<li><h2>3.5</h2>
					<ul>
						<li>Updated script for new "ID" tags add by Pardus Devs added in a Testing Flag to send data to Test Site
					</ul>
					<li><h2>3.4</h2>
					<ul>
						<li>Fixed a bug for FF 3.6 and background Images
					</ul>
					<li><h2>3.3</h2>
					<ul>
						<li>Fixes the disappearing Wormhole Bug
					</ul>
				</ul>
				<li><h1>New Site Look</h1>
				<ul>
					<li>I have moved the Test Site over to Production and I am working through a few bugs that have cropped up
					<li>PM me in-game if you find anything that isn't working correctly
				</ul>
				<li><h1>Test Site</h1>
				<ul>
					<li>If you are reading this then you are viewing the New Test Site.  Enjoy.</li>
				</ul>
				<li><h1>Script Version 3.2</h1>
				<ul>
					<li><h2>Ship Combat Bug</h2>
					<ul>
						<li>Glemlin pointed out a bug in Ship combat where if you have DC or OC error messages where appearing on the bottom of the
							screen.
						<li>Fixed in version 3.2
					</ul>
				</ul>
				<li><h1>Automation Continued</h1>
				<ul>
					<li>I have changed the log out link to point towards a user options page. This page now allows you to change your password as
						well as logging out.
				</ul>
				<li><h1>Automated Account Sign Up</h1>
				<ul>
					<li>I have added a link on the Log in Page to allow a user to set their password for the web site. You can still PM me ingame
						to set your password for you if you like or if the automation fails.
					<li>I am working on an Account page where a user can change their password once it has been set. Until that time if a password needs
						changed you will need to PM me in the game.
				</ul>
				<li><h1>Gem Merchant Tracking</h1>
				<ul>
					<li>I have added a new link in the header to bring up a listing of all known Gem Merchants in a Universe
					<li>Contains Links to Cluster Maps, Sector Maps, and Building information (if available)
				</ul>
				<li><h1>Bug Found with script for Firefox users in version 3</h1>
				<ul>
					<li>I fixed a bug that was found that kept Firefox from updating the Mapper. After fixing the problem I had trouble getting
						the new script installed.  I ended up having to unistall my Greasemonkey script, restart Firefox, then reinstall my script.
					<li>Not sure why I had this difficulty, Please PM Nhyujm (Orion), Cpt Zaq (Artemis), Zaqwer (Pegasus) if you have any additional
						problems or notice the script not loading data.
				</ul>
				<li><h1> Mission Listings</h1>
				<ul>
					<li><h2>For those who have taken the time to request an Account you can now see Missions under the following criteria</h2>
					<ul>
						<li>You must log in
						<li>You will only see your Faction Missions, Syndicate Missions and Neutral Missions based on your account
						<li>You will only see Missions with +- 2 of your Current Rank/Comp Level
						<li>Additional Filtering is in the Works
					</ul>
					<li><h2>I am currently testing the newest script to handle the missions page. I will post here when the new version is ready</h2>
				</ul>
					
				<li><h1> I have moved host from http://zaqwer.comlic.com to http://mapper.pardus-alliance.com</h1>
				<ul>
					<li>I hope this move will greatly increase the stability of my site and eliminate those annoying max-connection errors
				</ul>
				<li><h1> I have stopped updating the Firefox extension to focus on the User Script</h1>
				<ul>
					<li>Since the User Script can be used in Firefox with Greasemonkey I have stopped updating so I can focus on one script that can be used by all browsers
				</ul>
				<li><h1> Version 3.0 of the User Script is now online</h1>
				<ul>
					<li><h2> This Version of the User Script incudes the following updates </h2>
					<ul> 
						<li>Updates to the New Host
						<li>Allows the user to disable portions of the script
						<li>Collect Mission Information from SBs and Planets
					</ul>
				</ul>
			</ul>
		</div>
	</body>
</html>


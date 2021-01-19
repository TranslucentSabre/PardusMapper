<?php
header('Access-Control-Allow-Origin: http://*.pardus.at');

require_once("mysqldb.php");
$db = new mysqldb;
$debug = true;

if ($debug) print_r($_REQUEST);
if ($debug) echo '<br>';

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }

$uni = $db->protect($_REQUEST['uni']);

//Set User ID
$id = 0;
if (isset($_REQUEST['id'])) { $id = $db->protect($_REQUEST['id']); }
if ($debug) echo 'User ID = ' . $id .  '<br>';

// Set User Name
$user = "Unknown";
if (isset($_REQUEST['user'])) { $user = $db->protect($_REQUEST['user']); }
if ($debug) echo 'User Name = ' . $user .  '<br>';

// Set Version
$version = 0;
if (isset($_REQUEST['version'])) { $version = $db->protect($_REQUEST['version']); }
if ($debug) echo 'Version = ' . $version .  '<br>';

// Set Browser
$browser = "Unknown";
if(isset($_REQUEST['browser'])) { $browser = $db->protect($_REQUEST['browser']); }
if ($debug) echo 'Browser = ' . $browser .  '<br>';

// Set Faction
$faction = "Unknown";
if(isset($_REQUEST['faction'])) { $faction = $db->protect($_REQUEST['faction']); }
if ($debug) echo 'Faction = ' . $faction .  '<br>';

// Set Syndicate
$syndicate = "Unknown";
if(isset($_REQUEST['syndicate'])) { $syndicate = $db->protect($_REQUEST['syndicate']); }
if ($debug) echo 'Syndicate = ' . $syndicate .  '<br>';

// Set Compentency
$comp = "Unknown";
if(isset($_REQUEST['comp'])) { $comp = $db->protect($_REQUEST['comp']); }
if ($debug) echo 'Compentency = ' . $comp .  '<br>';

// Set Rank
$rank = "Unknown";
if(isset($_REQUEST['rank'])) { $rank = $db->protect($_REQUEST['rank']); }
if ($debug) echo 'Rank = ' . $rank .  '<br>';

// Set IP
$ip = $_SERVER['REMOTE_ADDR'];

if (isset($_REQUEST['el'])) {
	if ($id) {
		if ($debug) echo 'Looking up user by ' . $id . '<br>';
		$db->query('SELECT * FROM ' . $uni . '_Users WHERE id = ' . $id);
		if ($u = $db->nextObject()) {
			if ($debug) echo $id . ' For ' . $user . ' Already in DB Updating<br>';
			if ($debug) print_r($u);
			if  ($debug) echo '<br>';
			$db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `version` = \'' . $version . '\', `browser` = \'' . $browser . '\', username = \'' . $user . '\', `ip` = \'' . $ip . '\' WHERE id = ' . $id);
		} else {
			if ($debug) echo $id . ' For ' . $user . ' Not in DB Trying Name<br>';
			$db->query('SELECT * FROM `' . $uni . '_Users` WHERE username = \'' . $user . '\'');
			if ($u = $db->nextObject()) {
				if ($debug) echo $user . ' Already in DB Updating<br>';
				if ($debug) print_r($u);
				if  ($debug) echo '<br>';
				$db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `version` = \'' . $version . '\', `browser` = \'' . $browser . '\',id = ' . $id . ', `ip` = \'' . $ip . '\' WHERE username = \'' . $user . '\'');
			} else {
				if ($debug) echo 'New user Inserting ' . $user . '<br>';
				$db->query('INSERT INTO `' . $uni . '_Users` (`user_id`,`id`,`username`,`password`,`security`,`loaded`,`version`,`browser`,`ip`) VALUES (NULL,' . $id . ',\'' . $user . '\',\'' . sha1("n0p2ssword") . '\',0,UTC_TIMESTAMP(), \'' . $version . '\', \'' . $browser . '\', \'' . $ip . '\')');
				$db->query('SELECT * FROM `' . $uni . '_Users` WHERE username = \'' . $user . '\'');
				if ($u = $db->nextObject()) {
					if ($debug) echo $user . ' Added To DB<br>';
					if ($debug) print_r($u);
					if  ($debug) echo '<br>';
				}
			}
		}
	} else {
		if ($debug) echo $user . ' Trying User Name<br>';
		$db->query('SELECT * FROM `' . $uni . '_Users` WHERE username = \'' . $user . '\'');
		if ($u = $db->nextObject()) {
			if ($debug) echo $user . ' Already in DB Updating<br>';
			if ($debug) print_r($u);
			if  ($debug) echo '<br>';
			$db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `version` = \'' . $version . '\', `browser` = \'' . $browser . '\', `ip` = \'' . $ip . '\' WHERE username = \'' . $user . '\'');
		} else {
			if ($debug) echo 'New user Inserting ' . $user . '<br>';
			$db->query('INSERT INTO `' . $uni . '_Users` (`user_id`,`username`,`password`,`security`,`loaded`,`version`,`browser`,`ip`) VALUES (NULL,\'' . $user . '\',\'' . sha1("n0p2ssword") . '\',0,UTC_TIMESTAMP(), \'' . $version . '\', \'' . $browser . '\', \'' . $ip . '\')');
			$db->query('SELECT * FROM `' . $uni . '_Users` WHERE username = \'' . $user . '\'');
			if ($u = $db->nextObject()) {
				if ($debug) echo $user . ' Added To DB<br>';
				if ($debug) print_r($u);
				if  ($debug) echo '<br>';
			}
		}
	}
}
if (isset($_REQUEST['lud'])) {
	$db->query('SELECT * FROM `' . $uni . '_Users` WHERE username = \'' . $user . '\'');
	if ($u = $db->nextObject()) {
		if ($faction == 'null' || $faction == 'Unknown') { $db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `faction` = NULL WHERE username = \'' . $user . '\''); }
		else { $db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `faction` = \'' . $faction . '\' WHERE username = \'' . $user . '\''); }
		if ($syndicate == 'null' || $syndicate == 'Unknown') { $db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `syndicate` = NULL WHERE username = \'' . $user . '\''); }
		else { $db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `syndicate` = \'' . $syndicate . '\' WHERE username = \'' . $user . '\''); }
		if ($rank == 'null' || $rank == 'Unknown') { $db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `comp` = \'' . $comp . '\', `rank` = NULL WHERE username = \'' . $user . '\''); }
		else { $db->query('UPDATE `' . $uni . '_Users` SET `loaded` = UTC_TIMESTAMP(), `comp` = \'' . $comp . '\', `rank` = \'' . $rank . '\' WHERE username = \'' . $user . '\''); }
		$db->query("UPDATE ${uni}_Users SET ip = '$ip' WHERE username = '$user'");
	}
}

$db->close();
?>
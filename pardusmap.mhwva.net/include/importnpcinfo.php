<?php

if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  {  header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  {  header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  {  header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
else { die('0,Information Not coming from Pardus'); }

require_once("mysqldb.php");
$db = new mysqldb;
$debug = true;

if ($debug) print_r($_REQUEST);
if ($debug) echo '<br>';

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }

$uni = $db->protect($_REQUEST['uni']);
// Get Version
$version = 0;
if (isset($_REQUEST['version'])) { $version = $db->protect($_REQUEST['version']); }

if ($debug) echo 'Version = ' . $version . '<br>';
if ($version < 5.8) { exit; }

// Get Location
$loc = 0;
if (isset($_REQUEST['loc'])) { $loc = $db->protect($_REQUEST['loc']); }

$image = "";
if (isset($_REQUEST['img'])) { $image = $db->protect($_REQUEST['img']); }

// Set Hull,Armor,Shield Levels
$hull = 0;
if (isset($_REQUEST['hull'])) { $hull = $db->protect($_REQUEST['hull']); }
$amor = 0;
if (isset($_REQUEST['armor'])) { $armor = $db->protect($_REQUEST['armor']); }
$shield = 0;
if (isset($_REQUEST['shield'])) { $shield = $db->protect($_REQUEST['shield']); }

$db->query('SELECT * FROM `' . $uni . '_Maps` where id = ' . $loc);
$m = $db->nextObject();
if (is_null($m->npc)) {
	if ($debug) echo 'Inserting New Info into DB<br>';
	$db->addNPC($uni,$image,$loc,"",0,0);
} elseif ($m->npc == $image) {
	if ($debug) echo 'Updating Hull, Armor, and Shield<br>';
	$db->updateNPCHealth($uni,$loc,$hull,$armor,$shield);
} else {
	if ($debug) echo 'Removing Old NPC adding New<br>';
	$db->removeNPC($uni,$loc);
	$db->addNPC($uni,$image,$loc,"",0,0);
}

$db->close();
?>
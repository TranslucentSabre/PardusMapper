<?php

if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  {  header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  {  header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  {  header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
else { die('0,Information Not coming from Pardus'); }

require_once("mysqldb.php");
$db = new mysqldb;
$debug = true;

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }

$uni = $db->protect($_REQUEST['uni']);

if ($debug) echo 'Universe = ' . $uni . '<br>';

// Get Version
$version = 0;
if (isset($_REQUEST['version'])) { $version = $db->protect($_REQUEST['version']); }

if ($version < 5.8) { exit; }

if ($debug) print_r($_REQUEST);echo '<br>';

$loc = 0;
$loc = $db->protect($_REQUEST['loc']);
if ($debug) echo 'Location = ' . $loc . '<br>';

// Delete All Crew for This Location
$db->query('DELETE FROM ' . $uni . '_Crew WHERE loc = ' . $loc);

// Get Sector Info
$s = $db->getSector($loc,"");

// Get Cluster Info
$c = $db->getCluster($s->c_id,"");

$data = explode('~',$db->protect($_REQUEST['crew']));
for ($i = 1;$i < sizeof($data);$i++) {
	if ($debug) echo $data[$i] . '<br>';
	$temp = explode(',',$data[$i]);

	// Insert New Crew into DB
	$db->query('INSERT INTO ' . $uni . '_Crew (name,cluster,sector,loc) VALUES (\'' . $temp[0] . '\',\'' . $c->code . '\',\'' . $s->name . '\',' . $loc . ')');

	// Update Image
	$db->query('UPDATE ' . $uni . '_Crew SET image = \'' . $temp[1] . '\' WHERE name = \'' . $temp[0] . '\'');

	// Update Type
	$db->query('UPDATE ' . $uni . '_Crew SET type = \'' . $temp[2] . '\' WHERE name = \'' . $temp[0] . '\'');

	if ($temp[2] == "Legendary Crew Member") {
		// Update Title
		$db->query('UPDATE ' . $uni . '_Crew SET title = \'' . $temp[3] . '\' WHERE name = \'' . $temp[0] . '\'');

		// Update 2nd Job
		$db->query('UPDATE ' . $uni . '_Crew SET job2 = \'' . $temp[5] . '\' WHERE name = \'' . $temp[0] . '\'');
	} else {
		// Update Level
		$db->query('UPDATE ' . $uni . '_Crew SET level = ' . $temp[6] . ' WHERE name = \'' . $temp[0] . '\'');
	}

	// Update 1st Job
	$db->query('UPDATE ' . $uni . '_Crew SET job1 = \'' . $temp[4] . '\' WHERE name = \'' . $temp[0] . '\'');

	// Update Fee
	$db->query('UPDATE ' . $uni . '_Crew SET fee = ' . $temp[7] . ' WHERE name = \'' . $temp[0] . '\'');

	// Update pay
	$db->query('UPDATE ' . $uni . '_Crew SET pay = ' . $temp[8] . ' WHERE name = \'' . $temp[0] . '\'');

	// Update Date
	$db->query('UPDATE ' . $uni . '_Crew SET updated = UTC_TIMESTAMP() WHERE name = \'' . $temp[0] . '\'');
}

?>
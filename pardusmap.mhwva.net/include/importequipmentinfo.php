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

$tab = $db->protect($_REQUEST['tab']);
if ($debug) echo 'Tab = ' . $tab . '<br>';

$s = $db->getSector($loc,"");
$c = $db->getCluster($s->c_id,"");

$data = explode('~',$db->protect($_REQUEST['eq']));
switch ($tab) {
	case 'weapon' :
	case 'drive' :
	case 'armor' :
	case 'shield' :
	case 'special' :
		for ($i = 1;$i < sizeof($data);$i++) {
			if ($debug) echo $data[$i] . '<br>';
			$temp = explode(',',$data[$i]);
			//Verify Item is not already in the DB
			$db->query('SELECT * FROM ' . $uni . '_Equipment WHERE loc = ' . $loc . ' AND name = \'' . $temp[1] . '\'');
			if (!$e = $db->nextObject()) {
				$db->query('INSERT INTO ' . $uni . '_Equipment (name,loc) VALUES (\'' . $temp[1] . '\',' . $loc . ')');
			}
			// Update Cluster
			$db->query('UPDATE ' . $uni . '_Equipment SET cluster = \'' . $c->code . '\' WHERE loc = ' . $loc . ' AND name = \'' . $temp[1] . '\'');
			// Update Sector
			$db->query('UPDATE ' . $uni . '_Equipment SET sector = \'' . $s->name . '\' WHERE loc = ' . $loc . ' AND name = \'' . $temp[1] . '\'');
			// Update Image
			$db->query('UPDATE ' . $uni . '_Equipment SET image = \'' . $temp[0] . '\' WHERE loc = ' . $loc . ' AND name = \'' . $temp[1] . '\'');
			// Update Price
			$db->query('UPDATE ' . $uni . '_Equipment SET price = ' . $temp[2] . ' WHERE loc = ' . $loc . ' AND name = \'' . $temp[1] . '\'');
			// Update Amount
			$db->query('UPDATE ' . $uni . '_Equipment SET amount = ' . $temp[3] . ' WHERE loc = ' . $loc . ' AND name = \'' . $temp[1] . '\'');
			// Update Type
			$db->query('UPDATE ' . $uni . '_Equipment SET type = \'' . $tab . '\' WHERE loc = ' . $loc . ' AND name = \'' . $temp[1] . '\'');
		}
		break;
}

$db->query('UPDATE ' . $uni . '_Buildings set eq_updated = UTC_TIMESTAMP() WHERE id = ' . $loc);

$db->close();
?>
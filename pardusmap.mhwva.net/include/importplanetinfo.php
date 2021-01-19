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

// Planet Main Page Variables
if (isset($_REQUEST['loc'])) {$loc = $db->protect($_REQUEST['loc']); } else { exit; }
if (isset($_REQUEST['faction'])) {
	$faction = $db->protect($_REQUEST['faction']);
	// Resize Faction Image
	$faction = str_replace('64x64','16x16',$faction);
}
if (isset($_REQUEST['img'])) { $image = $db->protect($_REQUEST['img']); }
if (isset($_REQUEST['name'])) { $name = $db->protect($_REQUEST['name']); }
if (isset($_REQUEST['pop'])) { $pop = $db->protect($_REQUEST['pop']); }
if (isset($_REQUEST['crime'])) { $crime = $db->protect($_REQUEST['crime']); }


if ($debug) echo 'Location : ' . $loc . '<br>';

// Get Map information
$db->query('SELECT * FROM ' . $uni . '_Maps WHERE id = ' . $loc);
$m = $db->nextObject();

// Verify Building is already in DB Tables Add if Not
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' .$loc);
if ($b = $db->nextObject()) {
	// Building in DB Verify Stock is in DB
	$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE id = ' . $loc);
	if ($db->numRows() < 1) { addBuildingStock($uni,$m->fg,$loc); }
} else {
	// Building not in DB
	$db->addBuilding($uni,$m->fg,$loc);
	$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' .$loc);
	$b = $db->nextObject();
}

// Get Sector and Cluster Information from Location
$s = $db->getSector($loc,"");
$c = $db->getCluster($s->c_id,"");

// Double Check that Cluster and Sector have been Set for the Building
if (is_null($b->cluster)) {
	$db->query('UPDATE ' . $uni . '_Buildings SET cluster = \'' . $c->name . '\' WHERE id = ' . $loc);
}
if (is_null($b->sector)) {
	$db->query('UPDATE ' . $uni . '_Buildings SET sector = \'' . $s->name . '\' WHERE id = ' . $loc);
}


if (isset($_REQUEST['planet'])) {
	//Visited Planet
	if ($debug) echo 'Visited Planet<br>';
	//Collect Info

	if (!$b->x && !$b->y) {
		$x = $db->getX($loc,$s->s_id,$s->rows);
		$y = $db->getY($loc,$s->s_id,$s->rows,$x);
		$db->query('UPDATE `' . $uni . '_Buildings` SET `x` = ' . $x . ', `y`= ' . $y . ' WHERE id = ' . $loc);
	}
	$db->query('UPDATE `' . $uni . '_Buildings` SET `name`= \'' . $name . '\', `image`= \'' . $image . '\', `population`= ' . $pop . ', `crime`= \'' . $crime . '\', `updated`= UTC_TIMESTAMP() WHERE id = ' . $loc);
	if (isset($_REQUEST['faction'])) {
		if ($debug) echo 'Updating Faction<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `faction`= \'' . $faction . '\' WHERE id = ' . $loc);
	} else {
		if ($debug) echo 'Nulling Faction<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `faction`= null WHERE id = ' . $loc);
	}
}

if (isset($_REQUEST['pt'])) {
	// Visited Planet Trade
	if ($debug) echo 'Visited Planet Trade<br>';
	// Find out what type of planet
	$db->query('SELECT * FROM Pardus_Buildings_Data WHERE image = \'' . $m->fg . '\'');
	$p = $db->nextObject();

	if ($debug) echo $loc . ' Planet Type = ' . $p->name . '<br>';
	// Collect Info

	$cap = 0;
	$credit = $db->protect($_REQUEST['credit']);
	$building_stock_level = 0;
	$building_stock_max = 0;

	$pt = explode('~',$db->protect($_REQUEST['pt']));
	// Loop through all pt data
	for ($i = 1; $i < sizeof($pt); $i++) {
		$temp = explode(',',$pt[$i]);
		if ($debug) { print_r($temp); echo '<br>'; }
		$cap += $temp[1];
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . $p->name . '\' AND res = \'' . $temp[0] . '\'');
		$u = $db->nextObject();
		if ($u->upkeep) {
			$building_stock_level += $temp[1];
			$building_stock_max += $temp[3];
		}
		$stock = 0;
		if ($temp[3]) {
			$stock = round(($temp[1] / $temp[3]) * 100,0);
			if ($stock > 100) { $stock = 100; }
		}

		if ($debug) echo 'Stocking for ' . $temp[0] . ' = ' . $stock . '<br>';
		$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $temp[0] . '\')'); }
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `amount` = ' . $temp[1] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `bal` = ' . $temp[2] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `min` = 0 WHERE id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `max` = ' . $temp[3] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `buy` = ' . $temp[4] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `sell` = ' . $temp[5] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `stock` = ' . $stock . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
	}
	$db->query('UPDATE `' . $uni . '_Buildings` SET `capacity`= ' . $cap . ', `credit`= ' . $credit . ' WHERE id = ' . $loc);
	// Set Building Stock level
	if ($building_stock_max) {
		$building_stock_level = round(($building_stock_level / $building_stock_max) * 100,0);
		if ($building_stock_level > 100) { $building_stock_level = 100; }
	}

	if ($debug) { echo 'Building Stock Level ' . $building_stock_level . '<br>'; }

	$db->query('UPDATE ' . $uni . '_Buildings SET stock = ' . $building_stock_level . ', stock_updated = UTC_TIMESTAMP() WHERE id = ' . $loc);
}

$db->close();
?>
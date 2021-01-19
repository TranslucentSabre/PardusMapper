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

// Building Management Main Page Variables
if (isset($_REQUEST['loc'])) {$loc = $db->protect($_REQUEST['loc']); } else { exit; }
if (isset($_REQUEST['img'])) { $image = $db->protect($_REQUEST['img']); }
if (isset($_REQUEST['name'])) { $name = $db->protect($_REQUEST['name']); }
if (isset($_REQUEST['fs'])) { 	$fs = $db->protect($_REQUEST['fs']); }

if ($debug) echo 'Location : ' . $loc . '<br>';

// Get Map information
$db->query('SELECT * FROM ' . $uni . '_Maps WHERE id = ' . $loc);
$m = $db->nextObject();

// Verify Building is already in DB Tables Add if Not
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' .$loc);
if ($b = $db->nextObject()) {
	// Building in DB Verify Stock is in DB
	if ($debug) echo 'Got Building Infomation checking Stock<br>';
	$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE id = ' . $loc);
	if ($db->numRows() < 1) { $db->addBuildingStock($uni,$m->fg,$loc,0); }
} else {
	// Building not in DB
	$db->addBuilding($uni,$m->fg,$loc,0);
	$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' .$loc);
	$b = $db->nextObject();
}

// Get Sector and Cluster Information from Location
$s = $db->getSector($loc,"");
if ($debug) echo 'Got Sector ' . $s->name . '<br>';
$c = $db->getCluster($s->c_id,"");
if ($debug) echo 'Got Cluster ' . $c->name . '<br>';

// Double Check that Cluster and Sector have been Set for the Building
if (is_null($b->cluster)) {
	$db->query('UPDATE ' . $uni . '_Buildings SET cluster = \'' . $c->name . '\' WHERE id = ' . $loc);
}
if (is_null($b->sector)) {
	$db->query('UPDATE ' . $uni . '_Buildings SET sector = \'' . $s->name . '\' WHERE id = ' . $loc);
}

if (isset($_REQUEST['bts'])) {
	// Visited Building Trade Settings Page
	if ($debug) echo 'Visited Building Trade Settings Page<br>';
	$bts = explode('~',$db->protect($_REQUEST['bts']));

	if ($debug) echo 'Building is a  ' . $name . '<br>';
	// Loop Through All Data
	$building_stock_level = 0;
	$building_stock_max = 0;

	for ($i = 1; $i < sizeof($bts); $i++) {
		$temp = explode(',',$bts[$i]);
		if ($debug) print_r($temp);
		if ($debug) echo '<br>';
		if ($debug) echo 'Looking up infor for ' . $temp[0] . '<br>';
		// Calculate Stocking Level for this Resource
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . $name . '\' AND res = \'' . $temp[0] . '\'');
		$res = $db->nextObject();
		$stock_level = 0;
		if ($res->upkeep) {
			if ($temp[3]) {
				$stock_level = round(($temp[1] / $temp[3]) * 100,0);
				if ($stock_level > 100) { $stock_level = 100; }
			}
			$building_stock_level += $temp[1];
			$building_stock_max += $temp[3];
		}
		$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $temp[0] . '\')'); }
		$db->query('UPDATE ' . $uni . '_New_Stock SET amount = ' . $temp[1] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET min = ' . $temp[2] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET max = ' . $temp[3] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET buy = ' . $temp[5] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET sell = ' . $temp[4] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		if ($debug) echo 'Stocking Level is ' . $stock_level . '<br>';
		$db->query('UPDATE ' . $uni . '_New_Stock SET stock = ' . $stock_level . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
	}
	if ($building_stock_max) {
		$building_stock_level = round(($building_stock_level / $building_stock_max) * 100,0);
		if ($building_stock_level > 100) { $building_stock_level = 100; }
	}

	if ($debug) echo 'Building Stock Level is ' . $building_stock_level . '<br>';
	$db->query('UPDATE ' . $uni . '_Buildings SET stock = ' . $building_stock_level . ',`stock_updated` = UTC_TIMESTAMP() WHERE id = ' . $loc);
}
if (isset($_REQUEST['level'])) {
	if ($debug) echo 'Getting Building Level<br>';
	if ($debug) echo 'For ' . $name . '<br>';
	$temp = explode(',',$db->protect($_REQUEST['level']));
	if ($debug) 'Looking For ' . $temp[0] . '<br>';
	$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . $name . '\' AND res = \'' . $temp[0] . '\' AND upkeep=0');
	if ($u = $db->nextObject()) {
		if ($debug) echo 'Found ' . $u->res . '<br>';
		$i = 1;
		while (($temp[1] != production($u->amount,$i)) && ($i <= 20)) { $i++; }
		if ($debug) echo 'Guessing Level ' . $i . '<br>';
		if ($i <= 20) { $db->query('UPDATE ' . $uni . '_Buildings SET `level` = ' . $i . ' WHERE id = ' . $loc); }
	}
}
if (isset($_REQUEST['bm'])) {
	// Visited Building Management Page
	if ($debug) echo 'Visited Building Management Page<br>';
	// Collect Info
	if ($debug) print_r($_REQUEST);
	if ($debug) echo '<br>';

	//if (!$b->x && !$b->y) {
		$x = $db->getX($loc,$s->s_id,$s->rows);
		$y = $db->getY($loc,$s->s_id,$s->rows,$x);
		$db->query('UPDATE `' . $uni . '_Buildings` SET `x` = ' . $x . ', `y`= ' . $y . ' WHERE id = ' . $loc);
	//}

	$cap = $fs;
	$building_stock_level = 0;
	$building_stock_max = $fs;

	if ($name == "Trading Outpost") {
		if ($debug) echo 'Trading Outpost<br>';
		$bm = explode('~',$db->protect($_REQUEST['bm']));
		if ($debug) print_r($bm);
		if ($debug) echo '<br>';
		for ($i = 0; $i < sizeof($bm); $i++) {
			$temp = explode(',',$bm[$i]);
			$cap += $temp[1];
			$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
			if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $temp[0] . '\')'); }
			$db->query('UPDATE ' . $uni . '_New_Stock SET `amount` = ' . $temp[1] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		}
	} else {
		if ($debug) echo 'Not a Trading Outpost it is a ' . $name . '<br>';
		// Get list of Resources for Building
		$db->query('SELECT * FROM Pardus_Upkeep_Data where name = \'' . $name . '\'');
		while ($u = $db->nextObject()) { $resources[] = $u; }
		// Loop through List
		foreach ($resources as $u) {
			if ($debug) echo 'Checking ' . str_replace(" ","_",$u->res) . '<br>';

			$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE name = \'' . $u->res . '\' AND id = ' . $loc);
			if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $u->res . '\')'); }
			$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $u->res . '\' AND id = ' . $loc);
			$q = $db->nextObject();

			if (isset($_REQUEST[str_replace(" ","_",$u->res)])) {
				if ($debug) echo 'We have info for ' . $u->res . '<br>';
				// We have information for this Resource Update DB
				$res = $db->protect($_REQUEST[str_replace(" ","_",$u->res)]);
				$cap += $res;
				$stock = 0;


				if ($q->max > 0) {
					if ($u->upkeep) {
						$building_stock_level += $res;
						$building_stock_max += $q->max;
					}
					$stock = round(($res / $q->max) * 100,0);
					if ($stock > 100) { $stock = 100; }
				}

				// Update `' . $uni . '_Stock with Resource Information
				$db->query('UPDATE `' . $uni . '_New_Stock` SET `amount` = ' . $res . ' WHERE name = \'' . $u->res . '\' AND id = ' . $loc);
				$db->query('UPDATE `' . $uni . '_New_Stock` SET `stock` = ' . $stock . ' WHERE name = \'' . $u->res . '\' AND id = ' . $loc);
			} else {
				if ($debug) echo 'No info for ' . $u->res . '<br>';
				// No info for this resource so set values to 0
				$db->query('UPDATE `' . $uni . '_New_Stock` SET `amount` = 0 WHERE name = \'' . $u->res . '\' AND id = ' . $loc);
				$db->query('UPDATE `' . $uni . '_New_Stock` SET `stock` = 0 WHERE name = \'' . $u->res . '\' AND id = ' . $loc);
			}
		}
	}
	if ($building_stock_max) {
		$building_stock_level = round(($building_stock_level / $building_stock_max) * 100,0);
		if ($building_stock_level > 100) { $building_stock_level = 100; }
	}
	$db->query('UPDATE ' . $uni . '_Buildings SET stock = ' . $building_stock_level . ',`stock_updated` = UTC_TIMESTAMP() WHERE id = ' . $loc);

	$db->query('UPDATE `' . $uni . '_Buildings` SET `name`= \'' . $name . '\', `image`= \'' . $image . '\', `capacity`= ' . $cap . ', `freespace`= ' . $fs . ' WHERE id = ' . $loc);
}

$db->close();
?>
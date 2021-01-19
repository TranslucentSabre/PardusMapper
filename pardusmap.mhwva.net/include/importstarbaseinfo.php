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

// Starbase Main Page Variables
if (isset($_REQUEST['loc'])) { $loc = $db->protect($_REQUEST['loc']); } else { exit; }
if (isset($_REQUEST['name'])) { $name = $db->protect($_REQUEST['name']); }
if (isset($_REQUEST['img'])) { $image = $db->protect($_REQUEST['img']); }
if (isset($_REQUEST['faction'])) { $faction = $db->protect($_REQUEST['faction']); }
if (isset($_REQUEST['owner'])) { $owner = $db->protect($_REQUEST['owner']); }
if (isset($_REQUEST['alliance'])) { $alliance = $db->protect($_REQUEST['alliance']); }
if (isset($_REQUEST['pop'])) { $pop = $db->protect($_REQUEST['pop']); }
if (isset($_REQUEST['crime'])) { $crime = $db->protect($_REQUEST['crime']); }

// Trade Page Variables (Additional)
if (isset($_REQUEST['credit'])) { $credit = $db->protect($_REQUEST['credit']); }

// Starbase Building Page Variables (additional)
if (isset($_REQUEST['x'])) { $x = $db->protect($_REQUEST['x']); }
if (isset($_REQUEST['y'])) { $y = $db->protect($_REQUEST['y']); }
if (isset($_REQUEST['condition'])) { $condition = $db->protect($_REQUEST['condition']); }

if ($debug) echo 'Location = ' . $loc . '<br>';

// Get Map information
$db->query('SELECT * FROM ' . $uni . '_Maps WHERE id = ' . $loc);
$m = $db->nextObject();
if ($debug) print_r($m);
if ($debug) echo '<br>Got Map Data<br>';

// Verify Building is already in DB Tables Add if Not
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $loc);
if ($b = $db->nextObject()) {
	// Building in DB Verify Stock is in DB
	$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE id = ' . $loc);
	if ($db->numRows() < 1) {
		$db->addBuildingStock($uni,$m->fg,$loc);
		if ($debug) echo 'Added New Stock Info<br>';
	}
} else {
	// Building not in DB
	$db->addBuilding($uni,$m->fg,$loc);
	if ($debug) echo 'Added New Building Info<br>';
}
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $loc);
$b = $db->nextObject();
if ($debug) print_r($b);
if ($debug) echo '<br>Got Building Info<br>';
// Get Sector and Cluster Information from Location
$s = $db->getSector($loc,"");
if ($debug) echo 'Got Sector Info<br>';
$c = $db->getCluster($s->c_id,"");
if ($debug) echo 'Got Cluster Info<br>';

// Double Check that Cluster and Sector have been Set for the Building
if (is_null($b->cluster)) { $db->query('UPDATE ' . $uni . '_Buildings SET cluster = \'' . $c->name . '\' WHERE id = ' . $loc); }
if (is_null($b->sector)) { $db->query('UPDATE ' . $uni . '_Buildings SET sector = \'' . $s->name . '\' WHERE id = ' . $loc); }

if (isset($_REQUEST['sb'])) {
	//Visited Starbase
	if ($debug) echo 'Visited Starbase<br>';
	// Collect Info

	// Update DB with common SB info
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
	if (isset($_REQUEST['owner'])) {
		if ($debug) echo 'Updating Owner<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `owner`= \'' . $owner . '\' WHERE id = ' . $loc);
	}
	if (isset($_REQUEST['alliance'])) {
		if ($debug) echo 'Updating Alliance<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `alliance`= \'' . $alliance . '\' WHERE id = ' . $loc);
	} else {
		if ($debug) echo 'Nulling Alliance<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `alliance`= null WHERE id = ' . $loc);
	}
}

if (isset($_REQUEST['sbt'])) {
	//Visited a Starbase
	if ($debug) echo 'Visited a Starbase<br>';
	//Collect Info
	if (isset($_REQUEST['fs'])) {
		$fs = $db->protect($_REQUEST['fs']);
		$cap = $fs;
		$db->query('UPDATE `' . $uni . '_Buildings` SET `freespace`= ' . $fs . ' WHERE id = ' . $loc);
	} else {
		$cap = 0;
	}
	$sbt = explode('~',$db->protect($_REQUEST['sbt']));
	if ($debug) print_r($sbt);
	if ($debug) echo '<br>';
	// Loop through all sbt data
	for ($i = 1;$i < sizeof($sbt); $i++) {
		$temp = explode(',',$sbt[$i]);
		if ($debug) print_r($temp);
		if ($debug) echo '<br>';
		$cap += $temp[1];
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'starbase\' AND res = \'' . $temp[0] . '\'');
		$u = $db->nextObject();
		if ($u->upkeep) {
			$building_stock_level += $temp[1];
			$building_stock_max += $temp[4];
		}
		$stock = 0;
		if ($temp[4]) {
			$stock = round(($temp[1] / $temp[4]) * 100,0);
			if ($stock > 100) { $stock = 100; }
		}

		if ($debug) echo 'Stocking for ' . $temp[0] . ' = ' . $stock . '<br>';
		$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $temp[0] . '\')'); }
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `amount` = ' . $temp[1] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `bal` = ' . $temp[2] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `min` = ' . $temp[3] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `max` = ' . $temp[4] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `buy` = ' . $temp[5] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE `' . $uni . '_New_Stock` SET `sell` = ' . $temp[6] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
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

if (isset($_REQUEST['squads'])) {
	//Visted Squadrons at a Player SB
	if ($debug) echo 'Visited Squadrons<br>';
	// Erase old Squad info from DB.
	$db->query('DELETE FROM `' . $uni . '_Squadrons` WHERE id = ' . $loc);
	//Collect Info
	$squads = explode('~',$db->protect($_REQUEST['squads']));

	for ($i = 0;$i < sizeOf($squads);$i++) {
		$temp = explode(',',$squads[$i]);
		$db->query('INSERT INTO `' . $uni . '_Squadrons` (`id`,`image`,`type`,`weapons`,`credit`,`date`) VALUES (' . $loc . ',\'' . $temp[0] . '\',\'' . $temp[1] . '\',' . $temp[2] . ',' . $temp[3] . ', UTC_TIMESTAMP())');
	}
	$db->query('UPDATE ' . $uni . '_Squadrons SET cluster = \'' . $c->name . '\' WHERE id = ' . $loc);
	$db->query('UPDATE ' . $uni . '_Squadrons SET sector = \'' . $s->name . '\' WHERE id = ' . $loc);
	$db->query('UPDATE ' . $uni . '_Squadrons SET x = ' . $b->x . ' WHERE id = ' . $loc);
	$db->query('UPDATE ' . $uni . '_Squadrons SET y = ' . $b->y . ' WHERE id = ' . $loc);
}

if (isset($_REQUEST['sbb'])) {
	//Visited SB Building
	if ($debug) echo 'Visited SB Building<br>';
	//Collect Info

	$db->query('UPDATE `' . $uni . '_Buildings` SET `image`= \'' . $image . '\',`name`= \'' . $name . '\',`condition`= ' . $condition . ' WHERE id = ' . $loc);
	if (isset($_REQUEST['faction'])) {
		if ($debug) echo 'Updating Faction<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `faction`= \'' . $faction . '\' WHERE id = ' . $loc);
	} else {
		if ($debug) echo 'Nulling Faction<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `faction`= null WHERE id = ' . $loc);
	}
	if (isset($_REQUEST['owner'])) {
		if ($debug) echo 'Updating Owner<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `owner`= \'' . $owner . '\' WHERE id = ' . $loc);
	}
	if (isset($_REQUEST['alliance'])) {
		if ($debug) echo 'Updating Alliance<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `alliance`= \'' . $alliance . '\' WHERE id = ' . $loc);
	} else {
		if ($debug) echo 'Nulling Alliance<br>';
		$db->query('UPDATE `' . $uni . '_Buildings` SET `alliance`= null WHERE id = ' . $loc);
	}
	$db->query('UPDATE `' . $uni . '_Buildings` SET `updated` = UTC_TIMESTAMP() WHERE id = ' . $loc);
}

$db->close();
?>
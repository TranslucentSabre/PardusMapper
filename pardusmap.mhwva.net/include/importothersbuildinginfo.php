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

// Building Main Page Variables
if (isset($_REQUEST['loc'])) {$loc = $db->protect($_REQUEST['loc']); } else { exit; }
if (isset($_REQUEST['faction'])) { $faction = $db->protect($_REQUEST['faction']); }
if (isset($_REQUEST['img'])) { $image = $db->protect($_REQUEST['img']); }
if (isset($_REQUEST['name'])) { $name = $db->protect($_REQUEST['name']); }
if (isset($_REQUEST['condition'])) { $condition = $db->protect($_REQUEST['condition']); }
if (isset($_REQUEST['owner'])) { $owner = $db->protect($_REQUEST['owner']); }


// Building Trade Variables
if (isset($_REQUEST['fs'])) { $fs = $db->protect($_REQUEST['fs']); }
if (isset($_REQUEST['credit'])) { $credit = $db->protect($_REQUEST['credit']); }

if ($debug) echo 'Location : ' . $loc . '<br>';

// Get Map information
$db->query('SELECT * FROM ' . $uni . '_Maps WHERE id = ' . $loc);
$m = $db->nextObject();

// Verify Building is already in DB Tables Add if Not
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $loc);
if ($b = $db->nextObject()) {
	// Building in DB Verify Stock is in DB
	if (strpos("sb_",$m->fg) === false) {
		if ($debug) echo 'Checking Stocking Info<br>';
		$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE id = ' . $loc);
		if ($db->numRows() < 1) { $db->addBuildingStock($uni,$m->fg,$loc); }
	}
} else {
	// Building not in DB
	$db->addBuilding($uni,$m->fg,$loc,0);
	$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $loc);
	$b = $db->nextObject();
}

if ($debug) print_r($b);echo '<br>';

if (strpos("sb_",$m->fg) !== false) {
	if ($debug) echo 'We are Flying Close to a SB<br>';
	$db->query('SELECT * FROM Pardus_Buildings where starbase < ' . $loc . ' ORDER BY starbase DESC LIMIT 1');
	$q = $db->nextObject();
	$x = $db->getX($loc,$q->starbase,13);
	$y = $db->getY($loc,$q->starbase,13,$x);
	$s = $db->getSector($q->id,"");
} else {
	// Get Sector and Cluster Information from Location
	$db->query('SELECT * FROM Pardus_Sectors WHERE s_id < ' . $loc . ' ORDER BY s_id DESC LIMIT 1');
	$s = $db->getSector($loc,"");
	$x = $db->getX($loc,$s->s_id,$s->rows);
	$y = $db->getY($loc,$s->s_id,$s->rows,$x);
}
$c = $db->getCluster($s->c_id,"");

// Double Check that Cluster and Sector have been Set for the Building
if (is_null($b->cluster)) { $db->query('UPDATE ' . $uni . '_Buildings SET cluster = \'' . $c->name . '\' WHERE id = ' . $loc); }
if (is_null($b->sector)) { $db->query('UPDATE ' . $uni . '_Buildings SET sector = \'' . $s->name . '\' WHERE id = ' . $loc); }

if (!$b->x && !$b->y) {
	$db->query('UPDATE `' . $uni . '_Buildings` SET `x` = ' . $x . ', `y`= ' . $y . ' WHERE id = ' . $loc);
}

if (isset($_REQUEST['building'])) {
	// Visited a Building
	if ($debug) echo 'Visited a Building<br>';
	if ($debug) print_r($_REQUEST);
	if ($debug) echo '<br>';
	// Collect Info

	$db->query('UPDATE ' . $uni . '_Buildings SET `image`= \'' . $image . '\', `name`= \'' . $name . '\', `condition`= ' . $condition . ' WHERE id = ' . $loc);
	if (isset($_REQUEST['owner'])) {
		if ($debug) echo 'Updating owner<br>';
		$db->query('UPDATE ' . $uni . '_Buildings SET `owner`= \'' . $owner . '\' WHERE id = ' . $loc);
	} else {
		if ($debug) echo 'Nulling owner<br>';
		$db->query('UPDATE ' . $uni . '_Buildings SET `owner`= NULL WHERE id = ' . $loc);
	}
	if (isset($_REQUEST['alliance'])) {
		if ($debug) echo 'Updating alliance<br>';
		$db->query('UPDATE ' . $uni . '_Buildings SET `alliance`= \'' . $alliance . '\' WHERE id = ' . $loc);
	} else {
		if ($debug) echo 'Nulling alliance<br>';
		$db->query('UPDATE ' . $uni . '_Buildings SET `alliance`= NULL WHERE id = ' . $loc);
	}
	if (isset($_REQUEST['faction'])) {
		if ($debug) echo 'Updating faction<br>';
		$db->query('UPDATE ' . $uni . '_Buildings SET `faction`= \'' . $faction . '\' WHERE id = ' . $loc);
	} else {
		if ($debug) echo 'Nulling Faction<br>';
		$db->query('UPDATE ' . $uni . '_Buildings SET `faction`= NULL WHERE id = ' . $loc);
	}
	$db->query('UPDATE ' . $uni . '_Buildings SET `updated` = UTC_TIMESTAMP() WHERE id = ' . $loc);

	// If we can see the Building then there are no NPCs at this location
	$db->removeNPC($uni,$loc);
}
if (isset($_REQUEST['bt'])) {
	// Visited Building Trade
	if ($debug) echo 'Visited Building Trade<br>';
	// Collect Info
	$db->query('UPDATE ' . $uni . '_Buildings SET `image`= \'' . $image . '\', `name`= \'' . $name . '\'  WHERE id = ' . $loc);

	//loc=327655&bt=~Food,48,0,66,9999,120~Energy,48,0,66,9999,50~Water,48,0,66,9999,100~Ore,108,0,132,9999,150~Metal,63,0,0,400,0&fs=55&credit=2826766
	$cap = $fs;
	$bt = explode('~',$db->protect($_REQUEST['bt']));

	date_default_timezone_set('UTC');

	//$date = getdate(strtotime($b->stock_updated));
	//$tick = mktime(1,25,0,$date['mon'],$date['mday'],$date['year']);
	$ts = strtotime($b->stock_updated);
	$date = new DateTime("@$ts");
	$date->setTime(1,25,0);
	$tick = $date->format('U');
	
	while ($tick < strtotime($b->stock_updated)) { $tick += (60 * 60 * 6); }
	$i = 0;
	while ($tick < strtotime($b->stock_updated)) {
		$tick += (60*60*6);
		$i++;
	}

	if ($i) { $i++; }

	$tick = $i;

	// Get Upkeep Info
	if ($debug) echo 'Building is ' . $b->name . '<br>';
	for ($x = 1;$x<=20;$x++) { $level[$x] = 0; }
	//Loop through all bt data
	$building_stock_level = 0;
	$building_stock_max = 0;

	for ($i = 1;$i < sizeof($bt);$i++) {
		$temp = explode(',',$db->protect($bt[$i]));
		if($debug) print_r($temp);
		if($debug) echo '<br>';
		$cap += $temp[1];
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . $b->name . '\' AND res = \'' . $temp[0] . '\'');
		$u = $db->nextObject();
		$amount = $u->amount;
		$upkeep = $u->upkeep;
		$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		if ($tick && $s = $db->nextObject()) {
			if ($debug) echo ' Using ' . $temp[0] . ' base amount ' . $amount . '<br>';

			if ($upkeep) {
				$diff = $s['amount'] - $temp[1];
			} else {
				$diff = $temp[1] - $s['amount'];
			}
			if ($debug) echo 'Difference is ' . $diff . '<br>';
			for ($j = 1; $j <= 20; $j++) {
				if ($upkeep) {
					if ($debug) echo 'Trying Level ' . $j . ' value of ' . upkeep($amount,$j) . '<br>';
					if ($diff == (upkeep($amount,$j) * $tick)) {
						$level[$j]++;
					}
				} else {
					if ($debug) echo 'Trying Level ' . $j . ' value of ' . production($amount,$j) . '<br>';
					if ($diff == (production($amount,$j) * $tick)) {
						$level[$j]++;
					}
				}
			}
		}
		if ($upkeep) {
			$building_stock_level += $temp[1];
			$building_stock_max += $temp[3];
		}
		$stock = 0;
		if ($temp[3]) {
			$stock = round(($temp[1] / $temp[3]) * 100,0);
			if ($stock > 100) { $stock = 100; }
		}
		$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $temp[0] . '\')'); }
		$db->query('UPDATE ' . $uni . '_New_Stock SET `amount` = ' . $temp[1] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET `bal` = 0 WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET `min` = ' . $temp[2] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET `max` = ' . $temp[3] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET `buy` = ' . $temp[4] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET `sell` = ' . $temp[5] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
		$db->query('UPDATE ' . $uni . '_New_Stock SET `stock` = ' . $stock . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
	}
	$db->query('UPDATE ' . $uni . '_Buildings SET `capacity`= ' . $cap . ', `freespace`= ' . $fs . ', `credit`= ' . $credit . ', `stock_updated`= UTC_TIMESTAMP() WHERE id = ' . $loc);


	// Set Building Stock level
	if ($building_stock_max) {
		$building_stock_level = round(($building_stock_level / $building_stock_max) * 100,0);
		if ($building_stock_level > 100) { $building_stock_level = 100; }
	}

	if ($debug) { echo 'Building Stock Level ' . $building_stock_level . '<br>'; }

	$db->query('UPDATE ' . $uni . '_Buildings SET stock = ' . $building_stock_level . ' WHERE id = ' . $loc);

	// End Test Stock Table
	if ($tick) {
		print_r($level);
		echo '<br>';
		$l = 1;
		for ($i=1;$i<=20;$i++) { if ($level[$i] > $l) { $l = $i; }}
		if($debug) echo 'Level estimate is ' . $l . ' - <br>';
		if ($l > $b->level) { $db->query('UPDATE ' . $uni . '_Buildings SET `level` = ' . $l . ' WHERE id = ' . $loc); }
	}
}

$db->close();
?>
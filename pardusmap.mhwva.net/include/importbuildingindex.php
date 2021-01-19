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

if ($version < 5.7) { exit; }

if ($debug) print_r($_REQUEST);echo '<br>';

$sector = $db->protect($_REQUEST['sector']);
if ($debug) echo 'Sector = ' . $sector . '<br>';
$x = $db->protect($_REQUEST['x']);
if ($debug) echo 'X = ' . $x . '<br>';
$y = $db->protect($_REQUEST['y']);
if ($debug) echo 'Y = ' . $y . '<br>';
$image = $db->protect($_REQUEST['img']);
if ($debug) echo 'Image = ' . $image . '<br>';
$name = $db->protect($_REQUEST['name']);
if ($debug) echo 'Name = ' . $name . '<br>';
$owner = $db->protect($_REQUEST['owner']);
if ($debug) echo 'Owner = ' . $owner . '<br>';
$date = $db->protect($_REQUEST['date']);
if ($debug) echo 'Date = ' . $date . '<br>';

$s = $db->getSector(0,$sector);

$loc = $db->getID($s->s_id,$s->rows,$x,$y);

if ($debug) echo 'Location : ' . $loc . '<br>';

// Get Map information
$db->query('SELECT * FROM ' . $uni . '_Maps WHERE id = ' . $loc);
$m = $db->nextObject();

if ($debug) print_r($m);echo '<br>Got Map Info<br>';


// Verify Building is already in DB Tables Add if Not
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' .$loc);
if ($b = $db->nextObject()) {
	// Building in DB Verify Stock is in DB
	echo 'Building in DB<br>';
	$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE id = ' . $loc);
	if ($db->numRows() < 1) { 
		if ($debug) echo 'Adding Stock Info<br>';
		$db->addBuildingStock($uni,$image,$loc); 
	}
} else {
	// Building not in DB
	if(debug) echo 'Building not in DB Adding<br>';
	$db->addBuilding($uni,$image,$loc,0);
}
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $loc);
$b = $db->nextObject();

if ($debug) print_r($b);echo '<br>Got Building Info<br>';

// Verify Index Info is newer then DB
if ($debug) echo ('Index Date ' . strtotime($date) . '<br>');
if ($debug) echo ('Building Date ' . strtotime($b->stock_updated) . '<br>');

if (strtotime($date) < strtotime($b->stock_updated)) { die('Index Date Older the Stocking Date'); }

// Get Cluster Information from Location
$c = $db->getCluster($s->c_id,"");

// Double Check that Cluster and Sector have been Set for the Building
if (is_null($b->cluster)) { $db->query('UPDATE ' . $uni . '_Buildings SET cluster = \'' . $c->name . '\' WHERE id = ' . $loc); }
if (is_null($b->sector)) { $db->query('UPDATE ' . $uni . '_Buildings SET sector = \'' . $s->name . '\' WHERE id = ' . $loc); }


if (isset($_REQUEST['bi'])) {
	if ($debug) echo 'Visitied Building Index for the Sector<br>';
	if ($debug) print_r($_REQUEST);
	if ($debug) echo '<br>';
	
	if (is_null($b->x) && is_null($b->y)) {
		$db->query('UPDATE `' . $uni . '_Buildings` SET `x` = ' . $x . ', `y`= ' . $y . ' WHERE id = ' . $loc);
	}
	
	$db->query('UPDATE `' . $uni . '_Buildings` SET `image`= \'' . $image . '\', `name`= \'' . $name . '\', `owner` = \'' . $owner . '\'  WHERE id = ' . $loc);
	
	if (isset($_REQUEST['bis'])) {
		if ($debug) echo 'Selling Resources<br>';
		$selling = explode('~',$db->protect($_REQUEST['bis']));
		if ($debug) print_r($selling);
		if ($debug) echo '<br>';
		
		for ($i = 0; $i < count($selling); $i++) {
			$s = explode(',',$selling[$i]);
			if ($debug) print_r($s);
			if ($debug) echo '<br>';
			$db->query('SELECT * FROM Pardus_Res_Data where image = \'' . $s[0] . '\'');
			$r = $db->nextObject();
			$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $r->name . '\' AND id = ' . $loc);
			if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $r->name . '\')'); }
			$db->query('UPDATE ' . $uni . '_New_Stock SET amount = ' . $s[1] . ' WHERE name = \'' . $r->name . '\' AND id = ' . $loc);
			$db->query('UPDATE ' . $uni . '_New_Stock SET buy = ' . $s[2] . ' WHERE name = \'' . $r->name . '\' AND id = ' . $loc);
		}
		
	}
	if (isset($_REQUEST['bib'])) {
		if ($debug) echo 'Buying Resources<br>';
		$buying = explode('~',$db->protect($_REQUEST['bib']));
		$db->query('SELECT * FROM `' . $uni . '_Stock` WHERE id = ' . $loc);
		$q = $db->nextArray();

		for ($i = 0; $i < count($buying); $i++) {
			$b = explode(',',$buying[$i]);
			if ($debug) print_r($b);
			if ($debug) echo '<br>';
			$db->query('SELECT * FROM Pardus_Res_Data where image = \'' . $b[0] . '\'');
			$r = $db->nextObject();
			$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE name = \'' . $r->name . '\' AND id = ' . $loc);
			if ($db->numRows() < 1) { 
				$db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $r->name . '\')');
				$db->query('SELECT * FROM `' . $uni . '_New_Stock` WHERE name = \'' . $r->name . '\' AND id = ' . $loc);
			}
			$q = $db->nextObject();
			if ($q->max > 0) {
				$amount = $q->max - $b[1];
				if ($debug) echo 'Amount : ' . $amount . '<br>';
				$db->query('UPDATE ' . $uni . '_New_Stock SET amount = ' . $amount . ' WHERE name = \'' . $r->name . '\' AND id = ' . $loc);
			}
			$db->query('UPDATE ' . $uni . '_New_Stock SET sell = ' . $b[2] . ' WHERE name = \'' . $r->name . '\' AND id = ' . $loc);
		}
	
	}
	if (isset($_REQUEST['fs'])) {
		$db->query('UPDATE `' . $uni . '_Buildings` SET `freespace` = ' . $db->protect($_REQUEST['fs']) . ' WHERE id = ' . $loc);
	}
	$db->query('UPDATE `' . $uni . '_Buildings` SET `stock_updated` = STR_TO_DATE(\'' . $date . '\',\'%a %b %e %T GMT %Y\') WHERE id = ' . $loc);
}

$db->close();
?>
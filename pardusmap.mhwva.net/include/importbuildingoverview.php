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

$owner = 'UnKnown';
if (isset($_REQUEST['owner'])) { $owner = $db->protect($_REQUEST['owner']); }
if (isset($_REQUEST['faction'])) { $faction = $db->protect($_REQUEST['faction']); }

if (isset($_REQUEST['bo'])) { $max = $db->protect($_REQUEST['bo']); }

if (isset($_REQUEST['bo'])) {
	for ($i = 1;$i < $max; $i++) {
		if (isset($_REQUEST['b' . $i])) {
			$b = explode(",",$db->protect($_REQUEST['b' . $i]));
			if ($debug) print_r($b);echo '<br>';

			$loc = $b[0];
			$name = $b[1];
			$sector = $b[2];
			$x = $b[3];
			$y = $b[4];
			$condition = $b[5];
			$level = $name == 'Trading Outpost' ? 0 : $b[6];

			$b_data = $db->query('SELECT * FROM Pardus_Buildings_Data WHERE name = \'' . $name . '\'');
			$image = $b_data->image;
			$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $loc);

			if (!($db->nextObject())) {
				if ($debug) { echo 'Adding New Building ' . $name . '<br>'; }
				$db->addBuilding($uni,$image,$loc,0);
			}

			if ($debug) { echo 'Getting Sector and Cluster Information<br>'; }
			$s = $db->getSector(0,$sector);
			$c = $db->getCluster($s->c_id,"");

			if ($debug) { echo 'Updating Cluster and Sector Info<br>'; }
			$db->query('UPDATE ' . $uni . '_Buildings SET cluster = \'' . $c->name . '\' WHERE id = ' . $loc);
			$db->query('UPDATE ' . $uni . '_Buildings SET sector = \'' . $sector . '\' WHERE id = ' . $loc);

			if ($debug) { echo 'Updating X and Y<br>'; }
			$db->query('UPDATE ' . $uni . '_Buildings SET x = ' . $x . ' WHERE id = ' . $loc);
			$db->query('UPDATE ' . $uni . '_Buildings SET y = ' . $y . ' WHERE id = ' . $loc);

			if ($debug) { echo 'Updating Name and Image<br>'; }
			$db->query('UPDATE ' . $uni . '_Buildings SET name = \'' . $name . '\' WHERE id = ' . $loc);
			$db->query('UPDATE ' . $uni . '_Buildings SET image = \'' . $image . '\' WHERE id = ' . $loc);

			if ($debug) { echo 'Updating Owner and Faction<br>'; }
			$db->query('UPDATE ' . $uni . '_Buildings SET owner = \'' . $owner . '\' WHERE id = ' . $loc);
			if (isset($_REQUEST['faction'])) { $db->query('UPDATE ' . $uni . '_Buildings SET faction = \'' . $faction . '\' WHERE id = ' . $loc); }
			else { $db->query('UPDATE ' . $uni . '_Buildings SET faction = NULL WHERE id = ' . $loc); }

			if ($debug) { echo 'Updating Condition and Level<br>'; }
			$db->query('UPDATE ' . $uni . '_Buildings SET `condition` = ' . $condition . ' WHERE id = ' . $loc);
			$db->query('UPDATE ' . $uni . '_Buildings SET level = ' . $level . ' WHERE id = ' . $loc);

			if ($debug) { echo 'Finished Updating Building Info<br>'; }
			$db->query('UPDATE ' . $uni . '_Buildings SET updated = UTC_TIMESTAMP() WHERE id = ' . $loc);

			$u = explode("~",$db->protect($_REQUEST['u' . $i]));
			if ($debug) print_r($u);echo '<br>';
			for ($x = 1;$x < sizeof($u);$x++) {
				$temp = explode(",",$u[$x]);
				if ($debug) print_r($temp);echo '<br>';
				$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
				if ($db->numRows() < 1) { $db->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $loc . ',\'' . $temp[0] . '\')'); }
				$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
				$r = $db->nextObject();

				$db->query('UPDATE ' . $uni . '_New_Stock SET amount = ' . $temp[1] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);

				if ($r->max > 0) {
					$stock = round(($temp[1] / $r->max) * 100,0);
					if ($stock > 100) { $stock = 100; }
					$db->query('UPDATE ' . $uni . '_New_Stock SET stock = ' . $stock . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
				}
			}


			if (isset($_REQUEST['p' . $i])) {
				$p = explode("~",$db->protect($_REQUEST['p' . $i]));
				if ($debug) print_r($p);echo '<br>';
				for ($x = 1;$x < sizeof($p);$x++) {
					$temp = explode(",",$p[$x]);
					if ($debug) print_r($temp);echo '<br>';
					$db->query('UPDATE ' . $uni . '_New_Stock SET amount = ' . $temp[1] . ' WHERE name = \'' . $temp[0] . '\' AND id = ' . $loc);
				}
			}
			if ($debug) { echo 'Finished Updating Stock Info<br>'; }
			$db->query('UPDATE ' . $uni . '_Buildings SET stock_updated = UTC_TIMESTAMP() WHERE id = ' . $loc);
		}
	}
}

$db->close();
?>
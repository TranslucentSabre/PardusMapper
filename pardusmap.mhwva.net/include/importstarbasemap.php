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

if ($debug) echo 'Version = ' . $version . '<br>';
if ($version < 5.8) { exit; }

if (!isset($_REQUEST['mapdata'])) { exit; }

$starbase = $db->protect($_REQUEST['sector']);
if ($debug) echo 'Starbase = ' . $starbase . '<br>';
$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE name = \'' . $starbase. '\'');
$sb = $db->nextObject();
if (is_null($sb->starbase)) {
	$id = $db->protect($_REQUEST['id']);
	$x = $db->protect($_REQUEST['x']);
	$y = $db->protect($_REQUEST['y']);
	$sb_loc = $temp[0] - ($x * 13) - $y;
	$db->query('UPDATE ' . $uni . '_Buildings SET `starbase` = ' . $sb_loc . ' WHERE id = ' . $sb->id);
}

if ($debug) print_r($sb); echo '<br>';


$mapdata = $db->protect($_REQUEST['mapdata']);

$maparray = explode('~',$mapdata);

if ($debug) print_r($maparray);
if ($debug) echo '<br>';


$cloaked[] = 'opponents/blood_amoeba.png';
$cloaked[] = 'opponents/ceylacennia.png';
$cloaked[] = 'opponents/cyborg_manta.png';
$cloaked[] = 'opponents/manifestation_developed.png';
$cloaked[] = 'opponents/dreadscorps.png';
$cloaked[] = 'opponents/drosera.png';
$cloaked[] = 'opponents/energy_minnow.png';
$cloaked[] = 'opponents/energy_sparkers.png';
$cloaked[] = 'opponents/smuggler_escorted.png';
$cloaked[] = 'opponents/pirate_experienced.png';
$cloaked[] = 'opponents/pirate_famous.png';
$cloaked[] = 'opponents/frost_crystal.png';
$cloaked[] = 'opponents/gorefanglings.png';
$cloaked[] = 'opponents/gorefangling.png';
$cloaked[] = 'opponents/gorefang.png';
$cloaked[] = 'opponents/hidden_drug_stash.png';
$cloaked[] = 'opponents/pirate_inexperienced.png';
$cloaked[] = 'opponents/infected_creature.png';
$cloaked[] = 'opponents/smuggler_lone.png';
$cloaked[] = 'opponents/lucidi_squad.png';
$cloaked[] = 'opponents/nebula_mole.png';
$cloaked[] = 'opponents/nebula_serpent.png';
$cloaked[] = 'opponents/oblivion_vortex.png';
$cloaked[] = 'opponents/manifestation_ripe';
$cloaked[] = 'opponents/sarracenia.png';
$cloaked[] = 'opponents/slave_trader.png';
$cloaked[] = 'opponents/manifestation_verdant.png';

sort($cloaked);

for($i = 1;$i < sizeof($maparray);$i++) {
	$temp = explode(',',$maparray[$i]);
	// Check to see if we got good data
	if (!strpos($temp[2],"nodata.png") && $temp[0] != 'NaN') {
		if ($debug) echo $temp[0] . ' Does Not Contain "nodata.png"<br>';
		// Check to see if we got Building Info
		if (strpos($temp[2],"foregrounds") !== false) {
			if ($debug) echo $temp[0] . ' Contains "Foreground" Info<br>';
			$r_bg = 1;
			$r_fg = 2;
			$r_npc = 0;
		// Check to see if we got Background Info
		} elseif (strpos($temp[2],"backgrounds") !== false) {
			if ($debug) echo $temp[0] . ' Contains "Background" Info Only<br>';
			$r_bg = 2;
			$r_fg = 0;
			$r_npc = 0;
		// Check to see if we got Critter info
		} elseif (strpos($temp[2],"opponents") !== false) {
			if ($debug) echo $temp[0] . ' Contains "Critter" Info<br>';
			$r_bg = 1;
			$r_fg = 0;
			$r_npc = 2;
		// Must be a Ship or something I don't want
		} elseif (strpos($temp[2],"xmas-star") !== false) {
			if ($debug) echo $temp[0] . ' Contains Xmas Info<br>';
			$r_bg = 1;
			$r_fg = 2;
			$r_npc = 0;
		} else {
			if ($debug) echo $temp[0] . ' Do not care what it contain<br>';
			$r_bg = 1;
			$r_fg = 0;
			$r_npc = 0;
		}

		// Ignore any tile that is energymax.png
		if (strpos($temp[$r_bg],"background") !== false  && strpos($temp[$r_bg],"energymax") != true) {
			// Check to see if we have Info for the current tile
			// Insert new data if there is not current info
			// Do Nothing if there is current info
			$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Maps WHERE id = ' . $temp[0]);
			if (!($r = $db->nextObject())) {
				// There is no existing information for the current tile
				if ($debug) echo $temp[0] . ' New Information Inserting into DB<br>';
				$db->addMap($uni,$temp[$r_bg],$temp[0],$sb->id);
				$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Maps WHERE id = ' . $temp[0]);
				$r = $db->nextObject();
			}

			if ($debug) print_r($r);
			if ($debug) echo '<br>';


			if (strpos($temp[$r_bg],"\\") !== false) { $temp[$r_bg] = substr($temp[$r_bg],0,strpos($temp[$r_bg],"\\")); }
			if ($debug) { echo $temp[$r_bg] . '<br>'; }
			if ($temp[$r_bg] != $r->bg) {
				if ($debug) echo $temp[0] . ' Updating BG Info<br>';
				$db->updateMapBG($uni,$temp[$r_bg],$temp[0]);
			} else {
				if ($debug) echo $temp[0] . ' Not Updating BG Info<br>';
			}

			// Check to see if we have Foreground information for the current tile
			// If we do not then we need to double check for existing info and remove it.
			if ($r_fg != 0) {
				if ($debug) echo $temp[0] . ' Building information exists for current location<br>';
				// Check to See if the DB is NULL
				if (is_null($r->fg)) {
					// DB is NULL Just Add new Info
					if ($debug) echo 'Adding Building<br>';
					$db->addBuilding($uni,$temp[$r_fg],$temp[0],$sb->id);
				} else{
					//Test to See if Map and DB match
					if (preg_replace(/[_]tradeoff/,"",$temp[$r_fg]) != preg_replace(/[_]tradeoff/,"",$r->fg)) {
						if ($debug) echo $temp[0] . ' Foreground info Does Not Matches DB<br>';
						// See if we have a Gem merchant
						if ($debug) { echo $temp[0] . ' Deleting Old Building<br>'; }
						$db->removeBuilding($uni,$temp[0],0);
						if ($debug) { echo $temp[0] . ' Inserting New Building<br>'; }
						$db->addBuilding($uni,$temp[$r_fg],$temp[0],$sb->id);
						$db->query('UPDATE ' . $uni . '_Buildings SET starbase = 1 WHERE id = ' . $temp[0]);
					} else {
						if ($debug) echo $temp[0] . ' Foreground info Matches DB<br>';
						$db->updateMapFG($uni,$temp[$r_fg],$temp[0]);

						$db->query('UPDATE ' . $uni . '_Maps SET cluster = \'' . $sb->cluster . '\' WHERE id = ' . $temp[0]);
						$db->query('UPDATE ' . $uni . '_Maps SET sector = \'' . $sb->sector . '\' WHERE id = ' . $temp[0]);
						$x = floor(($temp[0] - $sb->starbase)/13);
						$y = ($temp[0] - ($sb->starbase + ($x * 13)));
						$db->query('UPDATE ' . $uni . '_Maps SET x = ' . $x . ' WHERE id = ' . $temp[0]);
						$db->query('UPDATE ' . $uni . '_Maps SET y = ' . $y . ' WHERE id = ' . $temp[0]);
					}
				}
			} elseif (!(is_null($r->fg))) {
				if ($debug) echo $temp[0] . ' Deleting Foreground info from DB<br>';
				if (strpos($r->fg,"starbase"))  { $db->removeBuilding($uni,$temp[0],1); }
				else { $db->removeBuilding($uni,$temp[0],0); }
			} else {
				if ($debug) echo $temp[0] . ' No Foreground info to worry about<br>';
			}
		} else {
			if ($debug) echo $temp[0] . ' Energy Max<br>';
		}
	}
}

$db->close();
?>
<?php

if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  {  header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  {  header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  {  header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
else { die('0,Information Not coming from Pardus'); }

require_once("settings.php");
if (strpos($_REQUEST['mapdata'],"sb_") !== false) {
	$site = 'http://pardusmap.mhwva.net/include/importstarbasemap.php?';
	$site .= $_SERVER['QUERY_STRING'];
	header("Location: $site");
}
require_once("mysqldb.php");
$db = new mysqldb;
$debug = true;

date_default_timezone_set("UTC");

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }

$uni = $db->protect($_REQUEST['uni']);

if ($debug) echo 'Universe = ' . $uni . '<br>';

// Get Version
$version = 0;
if (isset($_REQUEST['version'])) { $version = $db->protect($_REQUEST['version']); }

if ($debug) echo 'Version = ' . $version . '<br>';
if ($version < 6.5) { exit; }

if (!isset($_REQUEST['mapdata'])) { exit; }

$loc = $db->protect($_REQUEST['id']);
if ($debug) echo 'Location = ' . $loc . '<br>';

if (isset($_REQUEST['s'])) {
	$sector = $db->protect($_REQUEST['s']);
} else {
	$sector = $db->protect($_REQUEST['sector']);
}
if ($debug) echo 'Sector = ' . $sector . '<br>';

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
$cloaked[] = 'opponents/energy_sparker.png';
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
$cloaked[] = 'opponents/locust_hive.png';

sort($cloaked);

$single[] = 'opponents/shadow.png';
$single[] = 'opponents/feral_serpent.png';
$single[] = 'opponents/preywinder.png';
				
sort($single);

$hack[] = 'opponents/shadow.png';

sort($hack);

$nonblocking[] = 'opponents/slave_trader.png';
$nonblocking[] = 'opponents/smuggler_lone.png';
$nonblocking[] = 'opponents/smuggler_escorted.png';
//$nonblocking[] = 'foreground/wormhole.png';
$nonblocking[] = 'opponents/gorefanglings.png';
$nonblocking[] = 'opponents/gorefang.png';
$nonblocking[] = 'opponents/nebula_mole.png';
$nonblocking[] = 'opponents/hidden_drug_stash.png';
$nonblocking[] = 'opponents/space_clam.png';
$nonblocking[] = 'opponents/preywinder.png';
$nonblocking[] = 'opponents/glowprawn.png';
$nonblocking[] = 'opponents/starclaw.png';
$nonblocking[] = 'opponents/eulerian.png';

sort($nonblocking);

$db->query("SELECT * FROM Pardus_Static_Locations");
while ($c = $db->nextObject()) { $static[] = $c->id; }

for($i = 1;$i < sizeof($maparray);$i++) {
	$temp = explode(',',$maparray[$i]);
	if ($temp[0] == $loc) {
		if (strpos($temp[1],"opponents") !== false && !(in_array($temp[1],$nonblocking))) {
			die("0,Fatal Error");
		}
	}
}

for($i = 1;$i < sizeof($maparray);$i++) {
	$temp = explode(',',$maparray[$i]);

	if (in_array($temp[0],$static)) { continue; }
	// Check to see if we got good data
	if (!strpos($temp[1],"nodata.png") && $temp[0] != 'NaN') {
		if ($debug) echo $temp[0] . ' Does Not Contain "nodata.png"<br>';
		// Check to see if we got Building Info
		$r_bg = 0;
		if (strpos($temp[1],"foregrounds") !== false) {
			if ($debug) echo $temp[0] . ' Contains "Foreground" Info<br>';
			$r_fg = 1;
			$r_npc = 0;
			if ((strpos($temp[1],"wormhole") !== false) || (strpos($temp[1],"xhole") !== false) || (strpos($temp[1],"yhole") !== false)) {
				if (sizeof($temp) != 3) {
					if ($debug) echo $temp[0] , ' Contains "Critter" Info not "Foreground" Info<br>';
					$r_fg = 0;
					$r_npc = 1;
				}
			}
		// Check to see if we got Critter info
		} elseif (strpos($temp[1],"opponents") !== false) {
			if ($debug) echo $temp[0] . ' Contains "Critter" Info<br>';
			$r_fg = 0;
			$r_npc = 1;
		// Must be a Ship or something I don't want
		} elseif (strpos($temp[1],"xmas-star") !== false) {
			if ($debug) echo $temp[0] . ' Contains Xmas Info<br>';
			$r_fg = 1;
			$r_npc = 0;
		} else {
			if ($debug) echo $temp[0] . ' Contains "Background" Info<br>';
			$r_fg = 0;
			$r_npc = 0;
			$r_bg = 1;
		}

		// Check to see if we have Info for the current tile
		// Insert new data if there is not current info
		// Do Nothing if there is current info
		$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Maps WHERE id = ' . $temp[0]);
		if (!($r = $db->nextObject())) {
			// There is no existing information for the current tile
			if ($debug) echo $temp[0] . ' New Information Inserting into DB<br>';
			$db->addMap($uni,$temp[$r_bg],$temp[0],0);
			$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Maps WHERE id = ' . $temp[0]);
			$r = $db->nextObject();
		}

		if ($debug) print_r($r);
		if ($debug) echo '<br>';

		if (is_null($r->cluster) || is_null($r->sector)) {
			if ($debug) echo $temp[0] . ' Sector and/or Cluster is Null<br>';
			$s = $db->getSector($temp[0],"");
			$db->query('UPDATE ' . $uni . '_Maps SET sector = \'' . $s->name . '\' WHERE id = ' . $temp[0]);
			$c = $db->getCluster($s->c_id,"");
			$db->query('UPDATE ' . $uni . '_Maps SET cluster = \'' . $c->name . '\' WHERE id = ' . $temp[0]);
		}
		if ($r->x == 0 || $r->y == 0) {
			if ($debug) echo $temp[0] . ' X and/or Y is Null<br>';
			if (!$s) { $s = $db->getSector($temp[0],""); }
			$x = $db->getX($temp[0],$s->s_id,$s->rows);
			$db->query('UPDATE ' . $uni . '_Maps SET x = ' . $x . ' WHERE id = ' . $temp[0]);
			$y = $db->getY($temp[0],$s->s_id,$s->rows,$x);
			$db->query('UPDATE ' . $uni . '_Maps SET y = ' . $y . ' WHERE id = ' . $temp[0]);
		}
		// If we are seeing the Nav Screen at the current location then there is NPC
		// Check to see if we have Wormhole destination info
		if ($debug) echo $temp[0] . ' Size = ' . sizeof($temp) . '<br>';
		if (sizeof($temp) == 3) {
			if ($temp[2] != 'unknown') {
				$db->query('UPDATE ' . $uni . '_Maps SET `wormhole` = \'' . $temp[2] . '\' WHERE id = ' . $temp[0]);
			}
			$db->query ('UPDATE ' . $uni . '_Maps SET `fg` = \'' . $temp[$r_fg] . '\' WHERE id = ' . $temp[0]);
			$db->query ('UPDATE ' . $uni . '_Maps SET `npc` = NULL WHERE id = ' . $temp[0]);
			$r->wormhole = $temp[2];
			continue;
		} 
		if ($r->wormhole) { continue; }
		if ($r_fg != 0) {
			// Check to see if we have Foreground information for the current tile
			// If we do not then we need to double check for existing info and remove it.
			if ($debug) echo $temp[0] . ' Building information exists for current location<br>';
			// Check to See if the DB is NULL
			if (is_null($r->fg)) {
				// DB is NULL Just Add new Info
				if ($debug) echo 'Adding Building<br>';
				$db->addBuilding($uni,$temp[$r_fg],$temp[0],0);
			} else{
				//Test to See if Map and DB match
				if ($debug) echo 'Testing New FG - ' . str_replace("_tradeoff","",$temp[$r_fg]) . '<br>';
				if ($debug) echo 'Testing DB FG - ' . str_replace("_tradeoff","",$r->fg) . '<br>';
				if (str_replace("_tradeoff","",$temp[$r_fg]) != str_replace("_tradeoff","",$r->fg)) {
					if ($debug) echo $temp[0] . ' Foreground info Does Not Matches DB<br>';
					// See if we have a Gem merchant
					if (strpos($temp[$r_fg],"gem_merchant") !== false) {
						$db->query('SELECT * FROM ' . $uni . '_Maps WHERE fg = \'' . $temp[$r_fg] . '\' AND cluster = \'' . $r->cluster . '\'');
						while ($g = $db->nextObject()) { $gems[] = $g; }
						foreach ($gems as $g) {
							$db->removeBuilding($uni,$g->id,0);
						}
					}
					if ($debug) { echo $temp[0] . ' Deleting Old Building<br>'; }
					$db->removeBuilding($uni,$temp[0],0);
					if ($debug) { echo $temp[0] . ' Inserting New Building<br>'; }
					$db->addBuilding($uni,$temp[$r_fg],$temp[0],0);
				} else {
					if ($debug) echo $temp[0] . ' Foreground info Matches DB<br>';
					$db->updateMapFG($uni,$temp[$r_fg],$temp[0]);
					if ($temp[$r_fg] != $r->fg) {
						if ($debug) echo $temp[0] . ' Foreground Image Changed<br>';
						$db->query('UPDATE ' . $uni . '_Buildings set image = \'' . $temp[$r_fg] . '\' WHERE id = ' . $temp[0]);
					}
				}
			}
		} else {
			if ($debug) echo $temp[0] . ' No Foreground info to worry about<br>';
		}
		if ($r_npc != 0) {
			if ($debug) echo $temp[0] . ' Checking NPC data<br>';
			if (in_array($temp[0],$static)) { continue; }
			if ($debug) echo $temp[0] . ' Not Static NPC<br>';
			if (is_null($r->npc)) {
				if ($debug) echo $temp[0] . ' No NPC Data in DB<br>';
				if (is_null($r->fg)) {
					if ($debug) echo $temp[0] . ' Adding New NPC<br>';
					$npc = $temp[$r_npc];
					if (in_array($npc,$hack)) {
						$ip = $_SERVER['REMOTE_ADDR'];
						$db->query("SELECT * FROM ${uni}_Users WHERE `ip` = '$ip'");
						if ($user = $db->nextObject()) {
							$db->query("INSERT INTO ${uni}_Hack (`id`,`username`,`version`,`image`,`date`) VALUES ($user->id,'$user->username','$version','$npc',$UTC_TIMESTAMP())");
						}
					}
					if (in_array($temp[$r_npc],$single)) {
						// NPC is only one per Cluster Find location of previous Instance
						$db->query("SELECT * FROM ${uni}_Maps WHERE cluster = '$r->cluster' AND npc = '$npc'");
						while ($t = $db->nextObject()) { $to_delete[] = $t->id; }
						if ($to_delete) { 
							for ($t = 0; $t < sizeof($to_delete);$t++) {
								// Delete NPC from Data Table
								$db->removeNPC($uni,$to_delete[$t]);
							}
						}
					}
					$db->addNPC($uni,$temp[$r_npc],$temp[0]," ",0,0);
				}
			} elseif ($temp[$r_npc] == $r->npc) {
				if ($debug) echo $temp[0] . ' NPC Uncloaked<br>';
				$db->updateMapNPC($uni,$temp[$r_npc],$temp[0],0);
			} else {
				if($debug) echo $temp[0] . ' Has a New NPC<br>';
				$db->removeNPC($uni,$temp[0]);
				$db->addNPC($uni,$temp[$r_npc],$temp[0]," ",0,0);
			}
		} else {
			if ($debug) echo $temp[0] . ' No NPC info to worry about<br>';
		}
		if ($r_bg != 0) {
			//Check to see if we have Building information for the current tile
			if (!(is_null($r->fg))) {
				// We do have Building Information
				if ($temp[0] == $_REQUEST['id']) {
					if ($debug) echo $temp[0] . ' Deleting Foreground info from DB<br>';
					if (strpos($r->fg,"starbase"))  { $db->removeBuilding($uni,$temp[0],1); }
					else { $db->removeBuilding($uni,$temp[0],0); }
				}
			}
			if (!(is_null($r->npc))) {
				if ($temp[0] == $_REQUEST['id']) {
					if ($debug) echo 'NPC has been killed<br>';
					$db->removeNPC($uni,$temp[0]);
				} else {
					if (in_array($r->npc,$cloaked)) {
						if ($debug) echo 'NPC has cloaked<br>';
						if (is_null($r->npc_cloaked)) {
							$db->updateMapNPC($uni,$temp[$r_npc],$temp[0],1);
						} else {
							$show = strtotime($r->today) - strtotime($r->npc_updated);
							if ($show > 432000) {
								$db->removeNPC($uni,$temp[0]);
							}
						}
					} else {
						if ($debug) echo 'NPC has been killed<br>';
						$db->removeNPC($uni,$temp[0]);
					}
				}
			}
		}
	}
}
$db->close();
?>
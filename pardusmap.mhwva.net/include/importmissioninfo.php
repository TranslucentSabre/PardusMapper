<?php

if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  {  header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  {  header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  {  header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
else { die('0,Information Not coming from Pardus'); }

require_once("mysqldb.php");
$db = new mysqldb;
$debug = true;

if ($debug) print_r($_REQUEST);
if ($debug) echo '<br>';

// Test Mission Table

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }

$uni = $db->protect($_REQUEST['uni']);

// Get Version
$version = 0;
if (isset($_REQUEST['version'])) { $version = $db->protect($_REQUEST['version']); }

if ($version < 5.8) { exit; }

date_default_timezone_set("UTC");

// Set Location
$loc = 0;
if (isset($_REQUEST['loc'])) { $source_id = $db->protect($_REQUEST['loc']); }
if ($debug) echo 'Source ID = ' . $source_id .  '<br>';

if (isset($_REQUEST['mid'])) {
	$mid = $db->protect($_REQUEST['mid']);
	$db->removeMission($uni,$mid,0);
	//$db->query('DELETE FROM `' . $uni . '_Test_Missions` WHERE id = ' . $mid);
} else {
	// If we don't have these two pieces of info ABORT!!!
	if (!($source_id)) { return; }

	// Set Comp
	$comp = 0;
	if (isset($_REQUEST['comp'])) { $comp = $db->protect($_REQUEST['comp']); }
	if ($debug) echo 'Comp = ' . $comp .  '<br>';

	// Set Rank
	$rank = 0;
	if (isset($_REQUEST['rank'])) { $rank = $db->protect($_REQUEST['rank']); }
	if ($debug) echo 'Rank = ' . $rank .  '<br>';


	// If we don't have these two pieces of info ABORT!!!
	if (!($comp || $rank)) { return; }

	// Set Faction
	$faction = 0;
	if (isset($_REQUEST['faction'])) { $faction = $db->protect($_REQUEST['faction']); }
	if ($debug) echo 'faction = ' . $faction .  '<br>';

	// Set Syndicate
	$syndicate = 0;
	if (isset($_REQUEST['syndicate'])) { $syndicate = $db->protect($_REQUEST['syndicate']); }
	if ($debug) echo 'syndicate = ' . $syndicate .  '<br>';

	$cstart = 0;
	if ($comp >= 2) {$cstart = $comp - 2; }
	$cend = $comp + 2;

	if ($debug) echo 'Comp Range = ' . $cstart . ' AND ' . $cend . '<br>';

	if ($debug) echo 'Deleting all Non EPS/TSS neutral missions<br>';
	// Delete all Non EPS or TSS neutral Missions with the same Comp Level or Lower
	$db->query('DELETE FROM `' . $uni . '_Test_Missions` WHERE `faction` IS NULL AND `source_id` = ' . $source_id . ' AND `comp` BETWEEN ' . $cstart . ' AND ' . $cend);

	// Check if our Pilot is a Faction Member
	if ($faction) {
		$rstart = 0;
		if ($rank >= 2) { $rstart = $rank - 2; }
		$rend = $rank + 2;

		if ($debug) echo 'Rank Range = ' . $rstart . ' AND ' . $rend . '<br>';

		// Member of a Faction
		if ($debug) echo 'Deleting Faction Missions<br>';
		$db->query('DELETE FROM `' . $uni . '_Test_Missions` WHERE `rank` between  ' . $rstart . ' AND ' . $rend . ' AND `faction` = \'' . $faction . '\' AND `source_id` = ' . $source_id);
	}
	if ($syndicate) {
		// Member of a Syndicate
		if ($debug) echo 'Deleting Syndicate Missions<br>';
		$db->query('DELETE FROM `' . $uni . '_Test_Missions` WHERE `faction` = \'' . $syndicate . '\' AND `comp` BETWEEN ' . $cstart . ' AND ' . $cend . ' AND `source_id` = ' . $source_id);
	}

	if (isset($_REQUEST['mission'])) {
		if ($debug) echo 'Mission Data Exists<br>';

		$mission = explode('~',$db->protect($_REQUEST['mission']));

		for ($i = 1; $i < count($mission); $i++) {
			$m = explode(',',$mission[$i]);

			if ($debug) print_r($m);
			if ($debug) echo '<br>';

			// Check if Mission Still Exists
			$db->query('SELECT * FROM `' . $uni . '_Test_Missions` WHERE id = ' . $m[0]);
			if ($q = $db->nextObject()) {
				if ($debug) echo 'We have Existing Mission Data<br>';
				if ($m[1]) {
					if ($debug) echo 'Faction or Syndicate Mission Mission<br>';
					if ((strpos($m[1],'uni') !== false) || (strpos($m[1],'emp') !== false) || (strpos($m[1],'fed') !== false)) {
						if ($rank - 2 <= $q->rank && $q->rank <= $rank + 2) {
							$db->query('UPDATE `' . $uni . '_Test_Missions` SET `rank` = ' . $rank . ' WHERE id = ' . $m[0]);
						}
					} else {
						if ($debug) echo 'Updating Syndicate Mission<br>';
						if ($comp - 2 <= $q->comp && $q->comp <= $comp + 2) {
							$db->query('UPDATE `' . $uni . '_Test_Missions` SET `comp` = ' . $comp . ' WHERE id = ' . $m[0]);
						}
					}
				} else {
					if ($comp - 2 <= $q->comp && $q->comp <= $comp + 2) {
						$db->query('UPDATE `' . $uni . '_Test_Missions` SET `comp` = ' . $comp . ' WHERE id = ' . $m[0]);
					}
				}
			} else {
				if ($debug) echo 'Inserting New Mission<br>';
				$db->query('INSERT INTO `' . $uni . '_Test_Missions` (`id`) VALUES (' . $m[0] . ')');

				//Set Source ID
				$db->query('UPDATE `' . $uni . '_Test_Missions` SET `source_id` = ' . $source_id . ' WHERE id = ' . $m[0]);

				// Set Sector Information
				$s = $db->getSector($source_id,"");
				$db->query('UPDATE `' . $uni . '_Test_Missions` SET `sector` = \'' . $s->name . '\' WHERE id = ' . $m[0]);

				// Get Cluster Information
				$c = $db->getCluster($s->c_id,"");
				$db->query('UPDATE `' . $uni . '_Test_Missions` SET `cluster` = \'' . $c->code . '\' WHERE id = ' . $m[0]);

				// Get Building Name,X, and Y
				$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $source_id);
				$b = $db->nextObject();
				$db->query('UPDATE `' . $uni . '_Test_Missions` SET loc = \'' . $b->name . '\' WHERE id = ' . $m[0]);

				$x = $db->getX($source_id,$s->s_id,$s->rows);
				$y = $db->getY($source_id,$s->s_id,$s->rows,$x);
				$db->query('UPDATE `' . $uni . '_Test_Missions` SET x = ' . $x . ', y = ' . $y . ' WHERE id = ' . $m[0]);

				// Update Comp & Rank
				$db->query('UPDATE ' . $uni . '_Test_Missions SET comp = ' . $comp . ', rank = ' . $rank . ' WHERE id = ' . $m[0]);

				if ($debug) echo 'Updating faction<br>';
				if ($m[1]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `faction` = \'' . $m[1] . '\' WHERE id = ' . $m[0]); }

				if (strpos($m[2],"LONG-TERM")) {
					$m[2] = substr($m[2],0,strpos($m[2],"LONG-TERM"));
				}
				if (strpos($m[2],"(")) {
					$m[2] = substr($m[2],0,strpos($m[2],"("));
				}
				if ($debug) echo 'Updating Type ' . $m[2] . '<br>';
				if ($m[2]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `type` = \'' . $m[2] . '\' WHERE id = ' . $m[0]); }

				if ($debug) echo 'Updating Type Img ' . $m[3] . '<br>';
				if ($m[3]) {
					if ($debug) echo 'STRPOS -->' . strpos($m[3],'packages') . '<br>';
					if (strpos($m[3],'packages') !== false || strpos($m[3],'smuggle') !== false || strpos($m[3],'vip') !== false || strpos($m[3],'scout') !== false || strpos($m[3],'explosives') !== false || strpos($m[3],'espionage') !== false) {
							if (strpos($m[3],'/') !== false) { $m[3] = substr($m[3],strpos($m[3],'/') + 1); }
							if ($debug) echo 'Image = ' . $m[3] . '<br>';
					}
					$db->query('UPDATE `' . $uni . '_Test_Missions` SET `type_img` = \'' . $m[3] . '\' WHERE id = ' . $m[0]);
				}
				if ($debug) echo 'Updating Amont ' . $m[4] . '<br>';
				if ($m[4]) {
					if (is_numeric($m[4])) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `amount` = ' . $m[4] . ' WHERE id = ' . $m[0]); }
					else { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `hack` = \'' . $m[4] . '\' WHERE id = ' . $m[0]); }
				}

				if ($debug) echo 'Updating Target Loc ' . $m[5] . '<br>';
				if ($m[5]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `t_loc` = \'' . $m[5] . '\' WHERE id = ' . $m[0]); }

				if ($debug) echo 'Updating Target Sector ' . $m[6] . '<br>';
				if ($m[6]) {
					$db->query('UPDATE `' . $uni . '_Test_Missions` SET `t_sector` = \'' . $m[6] . '\' WHERE id = ' . $m[0]);
					$ts = $db->getSector(0,$m[6]);
					$tc = $db->getCluster($ts->c_id,"");
					$db->query('UPDATE `' . $uni . '_Test_Missions` SET `t_cluster` = \'' . $tc->code . '\' WHERE id = ' . $m[0]);
				}

				if ($debug) echo 'Updating X ' . $m[7] . '<br>';
				if ($m[7]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `t_x` = ' . $m[7] . ' WHERE id = ' . $m[0]); }

				if ($debug) echo 'Updating Y ' . $m[8] . '<br>';
				if ($m[8]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `t_y` = ' . $m[8] . ' WHERE id = ' . $m[0]); }

				if ($debug) echo 'Updating Time ' . $m[9] . '<br>';
				if ($m[9]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `time` = ' . $m[9] . ' WHERE id = ' . $m[0]); }

				if ($debug) echo 'Updating Credits ' . $m[10] . '<br>';
				if ($m[10]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `credits` = ' . $m[10] . ' WHERE id = ' . $m[0]); }

				if ($debug) echo 'Updating War Points ' . $m[11] . '<br>';
				if ($m[11]) { $db->query('UPDATE `' . $uni . '_Test_Missions` SET `war` = \'' . $m[11] . '\' WHERE id = ' . $m[0]); }
				
				$db->query('UPDATE ' . $uni . '_Test_Missions SET spotted = UTC_TIMESTAMP() WHERE id = ' . $m[0]);

				if ($m[2] == "Assassination" && !$m[4]) {
					if ($debug) { echo 'We have Coords for a NPC lets add them to the Map<br>'; }
					$db->addNPC($uni,$m[3],0,$m[6],$m[7],$m[8]);
				}
			}
			if ($debug) echo 'Updating Dates<br>';
			$db->query('UPDATE ' . $uni . '_Test_Missions SET updated = UTC_TIMESTAMP() WHERE id = ' . $m[0]);
		}
		// Clean up any Errors
		$db->query('DELETE FROM ' . $uni . '_Test_Missions WHERE spotted IS NULL');
	}
}

$db->close();

?>
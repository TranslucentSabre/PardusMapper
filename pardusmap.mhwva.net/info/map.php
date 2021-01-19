<?php

require_once('../include/mysqldb.php');
$db = new mysqldb;

$testing = Settings::TESTING;

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }

$uni = $db->protect($_POST['uni']);
$sector = $db->protect($_POST['sector']);
$cluster = $db->protect($_POST['cluster']);
$img_url = $db->protect($_POST['img_url']);
$mode = $db->protect($_POST['mode']);
$shownpc = $db->protect($_POST['shownpc']);
$grid = $db->protect($_POST['grid']);

// Start the Session
session_name($uni);
session_start();

// Get Sector Information
$db->query('SELECT * FROM Pardus_Sectors WHERE name = \'' . $sector . '\'');
$s = $db->nextObject();

$npc_list[] = 'opponents/energy_sparker.png';
$npc_list[] = 'opponents/smuggler_escorted.png';
$npc_list[] = 'opponents/euryale.png';
$npc_list[] = 'opponents/euryale_swarmlings.png';
$npc_list[] = 'opponents/pirate_famous.png';
$npc_list[] = 'opponents/hidden_drugstash.png';
$npc_list[] = 'opponents/smuggler_lone.png';
$npc_list[] = 'opponents/medusa.png';
$npc_list[] = 'opponents/medusa_swarmling.png';
$npc_list[] = 'opponents/solar_banshee.png';
$npc_list[] = 'opponents/stheno.png';
$npc_list[] = 'opponents/stheno_swarmling.png';
$npc_list[] = 'opponents/energybees.png';
$npc_list[] = 'opponents/x993_battlecruiser.png';
$npc_list[] = 'opponents/x993_mothership.png';
$npc_list[] = 'opponents/z15_fighter.png';
$npc_list[] = 'opponents/z15_repair_drone.png';
$npc_list[] = 'opponents/z15_scout.png';
$npc_list[] = 'opponents/z15_spacepad.png';
$npc_list[] = 'opponents/z16_fighter.png';
$npc_list[] = 'opponents/z16_repair_drone.png';

// Get Map Data for Sector
$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM `' . $uni . '_Maps` WHERE sector = \'' . $sector . '\' AND starbase = 0 ORDER BY x,y');
while ($q = $db->nextObject()) {
	$m[$q->x][$q->y] = $q;
	//$m[$q->id] = $q;
}

$showfg = 0;
if ($mode == 'all' || $_POST['mode'] == 'buildings') {
	$showfg = 1;
}

$shownpc = 0;
if ($mode == 'all' || $_POST['mode'] == 'npcs') {
	$shownpc=1;
}

/*
//Get NPC Information for Sector
*/

date_default_timezone_set("UTC");

$return = '<table id="sectorTableMap" >';
$return .= '<thead><tr><th />';
for ($i = 0;$i < $s->cols;$i++) { $return .= '<th>' . $i . '</th>'; }
$return .= '<th /></tr></thead>';
$return .= '<tbody>';
for ($y = 0;$y < $s->rows;$y++) {
	$return .= '<tr><th>' . $y . '</th>';
	for ($x = 0;$x < $s->cols;$x++) {
		if ($map = $m[$x][$y]) {
			$return .= '<td id="' . $map->id . '"';
			if ($grid) { $return .= ' class="grid"'; }
			else { $return .= ' class="nogrid"'; }
			if (!$map->wormhole && (($showfg && $map->fg) || ($shownpc && $map->npc && (!in_array($map->npc,$npc_list) || isset($_SESSION['user']))))) {
				$return .= ' onClick="loadDetail(\'' . $base_url . '\',\'' . $uni . '\',' . $map->id . ');return true;" onMouseOut="closeInfo();" onMouseOver="openInfo(\'' . $base_url . '\',\'' . $uni . '\',' . $map->id . ');"';
			} 
			$return .= '>';

			// Set the Background
			$bg_img = $img_url . $map->bg;
			$return .= '<img class="bg" src="' . $bg_img . '" title=""/>';
			if (($map->security == 0) || ($security == $map->security) || ($security == 100)) {
				if ($map->fg){
					$fg_img = $img_url . $map->fg;
					// Calculate Days/Hours/Mins Since last Visited
					//$fg_diff['sec'] = strtotime($map->today) - strtotime($map->fg_date);
					$fg_diff['sec'] = strtotime($map->today) - strtotime($map->fg_updated);
					$fg_diff['days'] = $fg_diff['sec']/60/60/24;
					$fg_diff['hours'] = ($fg_diff['days'] - floor($fg_diff['days'])) * 24;
					$fg_diff['min'] = ($fg_diff['hours'] - floor($fg_diff['hours'])) * 60;
					$fg_diff['string'] = ' ' . floor($fg_diff['days']) . 'd ' . floor($fg_diff['hours']) . 'h ' . floor($fg_diff['min']) . 'm';
							
					// Set Wormhole Data if we Got it
					if ($map->wormhole) {
						$return .= '<a href="'. $base_url .'/' . $uni . '/' . $map->wormhole .'">';
						if (strpos($fg_img,"wormholeseal")) {
							if (strpos($fg_img,"open")) {
								$return .= '<img class="fg" src="' . $fg_img . '" alt="" title="' . $map->wormhole . ' {Open} [' . $fg_diff['string'] . ']" />';							
							} else {
								$return .= '<img class="fg" src="' . $fg_img . '" alt="" title="' . $map->wormhole . ' {Closed} [' . $fg_diff['string'] . ']" />';							
							}
						} else {
							$return .= '<img class="fg" src="' . $fg_img . '" alt="' . $loc . '" title=" ' . $map->wormhole . ' " />';
						}
						$return .= '</a>';
					// Set Building Info if we got it
					}
					elseif ($showfg || strpos($fg_img,"planet") || strpos($fg_img,"federation"))  {
						$return .= '<img class="fg" src="' . $fg_img . '" alt = "' . $fg_diff['string'] . '"';
						/*
						//if ($b = $building[$loc]) {
							if (($b->security == 0) || ($security == $b->security) || ($security == 100)) {
								$return .= ' onClick="loadDetail(\'' . $base_url . '\',\'' . $uni . '\',' . $loc . ');return true;" onMouseOut="closeInfo();" onMouseOver="openInfo(\'' . $base_url . '\',\'' . $uni . '\',' . $loc . ');"';
								$return .= ' alt="' . $fg_diff['string'] . '"';
							} else {
								$return .= ' title="' . $fg_diff['string'] . '"';
							}
						// Else Just set the Foreground
						//} else {
																																$return .= ' title="' . $fg_diff['string'] . '"';
						//}
						*/
						$return .= ' />';
					}
				}
				if ($map->npc && $shownpc && !$map->wormhole) {
					if (!in_array($map->npc,$npc_list) || isset($_SESSION['user'])) {
						$npc_img = $img_url . $map->npc;
						// Calculate Days/Hours/Mins Since last Visited
						//$npc_diff['sec'] = strtotime($map->today) - strtotime($map->npc_date);
						$npc_diff['sec'] = strtotime($map->today) - strtotime($map->npc_updated);
						$npc_diff['days'] = $npc_diff['sec']/60/60/24;
						$npc_diff['hours'] = ($npc_diff['days'] - floor($npc_diff['days'])) * 24;
						$npc_diff['min'] = ($npc_diff['hours'] - floor($npc_diff['hours'])) * 60;
						$npc_diff['string'] = ' ' . floor($npc_diff['days']) . 'd ' . floor($npc_diff['hours']) . 'h ' . floor($npc_diff['min']) . 'm';

						// Pilot has logged Data recently
						$npc_id = 'npc';
						if ($b = $building[$loc])  { $npc_id .= 'Small'; }
						elseif ($map->npc_cloaked == 1) { $npc_id .= 'Cloak'; }
					
						$return .= '<img class="' . $npc_id . '" src="' . $npc_img . '" alt="' . $npc_diff['string'] . '"';
						/*
						//if ($n = $npc[$loc] || $building[$loc]) { 
							$return .= ' onClick="loadDetail(\'' . $base_url . '\',\'' . $uni . '\',' . $loc . ');return false;" onMouseOut="closeInfo();" onMouseOver="openInfo(\'' . $base_url . '\',\'' . $uni . '\',' . $loc . ');"';
							$return  .= ' alt="' . $npc_diff['string'] . '"';
						//} else { 
						//	$return .= ' title="' . $npc_diff['string'] . '"';
						//}
						*/
						$return .= ' />';
					}
				}
			}
			$return .= '</td>';
		} else {
			if ($grid) { $return .= '<td class="grid">'; }
			else { $return .= '<td class="nogrid">'; }
			$return .= '<img class="bg" src="' . $img_url . 'backgrounds/energymax.png" title=""/></td>';								
		}
	}
	$return .= '<th>' . $y . '</th></tr>';
}
$return .= '</tbody>';
$return .= '<tfoot><tr><th />';
for ($i = 0;$i < $s->cols;$i++) { $return .= '<th>' . $i . '</th>'; }
$return .= '<th /></tr></tfoot></table>';

$db->close();

echo $return;
?>

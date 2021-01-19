<?php

require_once('../include/mysqldb.php');
$db = new mysqldb;

$testing = Settings::TESTING;
$debug = 0;

$base_url = 'http://mapper.pardus-alliance.com';
if ($testing) { $base_url .= '/TestMap'; }

$uni = $db->protect($_POST['uni']);
$id = $db->protect($_POST['id']);

session_name($uni);

session_start();

$security = 0;
if (isset($_SESSION['security'])) { $security = $db->protect($_SESSION['security']); }

$img_url = Settings::IMG_DIR;
if (isset($_COOKIE['imagepack'])) {
	$img_url = $_COOKIE['imagepack'];
	if ($img_url[count($img_url) - 1] != '/')	{$img_url .= '/'; }
}

$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $id);
$b_loc = $db->nextObject();

$db->query('SELECT *, UTC_TIMESTAMP() "today"  FROM ' . $uni . '_Test_Npcs WHERE id = ' . $id);
$npc_loc = $db->nextObject();

$return = '';

if ($b_loc) {
	$loc = $b_loc;
	if ($debug) { print_r($loc);echo '<br>'; }
	//Get Resource Data
	$db->query('SELECT * FROM Pardus_Res_Data');
	while ($q = $db->nextObject()) {
		$res_img[$q->name] = $q->image;
		$res_id[$q->name] = $q->r_id;
	}

	//Get Upkeep Info
	if (strpos($loc->image,"starbase")) {
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'starbase\'');
	} elseif (strpos($loc->image,"planet")) {
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . substr($loc->image,strpos($loc->image,"planet"),8) . '\'');
	} elseif (strpos($loc->image,"trade_outpost")) {
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'trade_outpost\'');
	} else {
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . $loc->name . '\'');
	}
	while($q = $db->nextObject()) { $upkeep[$res_id[$q->res]] = $q; }

	foreach ($upkeep as $q) {
		//Get Current Stock for Upkeep Resources
		$db->query('SELECT `' . $q->res . ' Amount`, `'. $q->res . ' Bal`, `'. $q->res . ' Min`,`'. $q->res . ' Max`,`'. $q->res . ' Buy`,`'. $q->res . ' Sell` FROM ' . $uni . '_Stock WHERE id = ' . $id);
		$r[$q->res] = $db->nextRow();
	}

	ksort($upkeep,SORT_NUMERIC);

	//Get Date Info
	$db->query('SELECT date, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Stock WHERE id = ' . $id);
	$datetime = $db->nextObject();

	//Calculate Ticks Passed
	$format = '%F %T';
	date_default_timezone_set('UTC');
	$date = getdate(strtotime($datetime->date));
	
	$tick = mktime(1,25,0,$date['mon'],$date['mday'],$date['year'],0);
	while ($tick < strtotime($datetime->date)) {
		$tick += (60 * 60 * 6);
	}
	$count = 0;
	while ($tick < strtotime($datetime->today)) {
		$tick += (60*60*6);
		$count++;
	}
	$tick = $count;

	$fs = $loc->freespace;

	$ticks_used = 100;

	// Calculate Days/Hours/Mins Since last Visited
	$diff['sec'] = strtotime($datetime->today) - strtotime($datetime->date);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	$row = 8;
	$i = 0;

	$return .= '<table>';
	$return .= '<tr style="background-color:#003040;"><td colspan="' . $row . '" align="center">' . $loc->name . ' [' . $loc->x . ',' . $loc->y . ']</td></tr>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">Last Updated:</td>';
	$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . $diff['string'] . '</td>';
	$return .= '</tr>';
	$return .= '<tr>';
	$return .= '<th colspan="2" style="background-color:#500000; color:#BBBBDD;">Resource</th>';
	$return .= '<th style="background-color:#330033; color:#BBBBDD;">Amount</th>';

	if (strpos($loc->image,"planet") || strpos($loc->image,"starbase")) {
		$return .= '<th style="background-color:#000000; color:#BBBBDD;">Bal</th>';
		if ($loc->owner) {
			$return .= '<th style="background-color:#000000; color:#BBBBDD;">Min</th>';
		}
	} else {
		$return .= '<th style="background-color:#000000; color:#BBBBDD;">Min</th>';
	}

	$return .= '<th style="background-color:#000000; color:#BBBBDD;">Max</th>';
	$return .= '<th style="background-color:#505000; color:#BBBBDD;">Price&nbsp;(Buy)</th>';
	$return .= '<th style="background-color:#505000; color:#BBBBDD;">Price&nbsp;(Sell)</th>';
	if (!(strpos($loc->image,"planet") || strpos($loc->image,"starbase"))) { $return .= '<th style="background-color:#330033; color:#BBBBDD;">Needed</th>'; }
	$return .= '</tr>';

	foreach ($upkeep as $u) {
		$return .=  ($i++ % 2 != 0) ? '<tr class="alternating">' : '<tr>';
		$return .= '<td><img src="' . $img_url . $res_img[$u->res] . '" height="8" width="8" alt=""></td>';
		$return .= '<td><font color="#009900"><strong>' . $u->res . '</strong></td>';
		$return .= '<td align="right">' . number_format($r[$u->res][0]) .'</td>';
		if (strpos($loc->image,"planet") || strpos($loc->image,"starbase")) {
			$return .= '<td align="right">';
			if($r[$u->res][1] != 0) {
				if ($r[$u->res][1] > 0) { $return .= '<font color="#009900"><strong>+' . number_format($r[$u->res][1]) . '</strong></font>'; }
				else { $return .= '<font color="#FFAA00"><strong>' . number_format($r[$u->res][1]) . '</strong></font>'; }
			} else { $return .= number_format($r[$u->res][1]); }
			$return .= '</td>';
			if ($loc->owner) { $return .= '<td align="right">' . number_format($r[$u->res][2]) . '</td>'; }
		} else {
			$return .= '<td align="right">' . number_format($r[$u->res][2]) . '</td>';
		}
		$return .= '<td align="right">' . number_format($r[$u->res][3]) . '</td>';
		$return .= '<td align="right">' . number_format($r[$u->res][4]) . '</td>';
		$return .= '<td align="right">' . number_format($r[$u->res][5]) . '</td>';
		if (!(strpos($loc->image,"planet") || strpos($loc->image,"starbase"))) {
			$return .= (($r[$u->res][3] - $r[$u->res][0]) > 0) ? '<td align="right">' . number_format($r[$u->res][3] - $r[$u->res][0]) . '</td>' : '<td align="right">0</td>';
		}	
	}
	$return .= '</tr>';
	$return .= '<tr><td colspan="' . $row . '"><hr></td></tr>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">Free Space:</td>';
	$return .= '<td colspan="' .ceil($row/2) . '" align="right">' . number_format($loc->freespace) . 't</td>';
	$return .= '</tr>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">Available Credits:</td>';
	$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . number_format($loc->credit) . '</td>';
	$return .= '</tr>';
	if (!strpos($loc->image,"outpost")) {
		$return .= '<tr style="background-color:#003040;">';
		$return .= '<td colspan="' . floor($row/2) . '">Ticks Past:</td>';
		$return .= '<td colspan="' . ceil($row/2) . '" align="right">';
		$return .= '<span id="ticks_passed">' . $tick . '</span>';
		$return .= '</td>';
		$return .= '</tr>';
	}
	$return .= '</table>';
	
	if ($npc_loc) { $return .= '<br>'; }
}

if ($npc_loc) {
	$row = 3;
	$loc = $npc_loc;
	$db->query('SELECT * FROM Pardus_Npcs WHERE name = \'' . $loc->name . '\'');
	$npc = $db->nextObject();
	
	$return .= '<table>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . $row . '" align="center">' . $loc->name . ' [' . $loc->x . ',' . $loc->y . ']</td>';
	$return .= '</tr>';
	$return .= '<tr>';
	$return .= '<td></td>';
	$return .= '<th style="background-color:#500000; color:#BBBBDD;">Reported</th>';
	$return .= '<th style="background-color:#505000; color:#BBBBDD;">Undamaged</th>';
	$return .= '</tr>';
	$return .= '<tr>';
	$return .= '<th>Hull</th>';
	if ($loc->hull != $npc->hull) { if (($loc->hull *= 2) == 600) { if ($loc->hull < $npc->hull) { $loc->hull = "600+"; } } }
	$return .= '<td align="center">' . $loc->hull . '</td>';
	$return .= '<td align="center">' . $npc->hull . '</td>';
	$return .= '</tr>';
	$return .= '<tr>';
	$return .= '<th>Armor</th>';
	if ($loc->armor != $npc->armor) { if (($loc->armor *= 2) == 600) { if ($loc->armor < $npc->armor) { $loc->armor = "600+"; } } }
	$return .= '<td align="center">' . $loc->armor . '</td>';
	$return .= '<td align="center">' . $npc->armor . '</td>';
	$return .= '</tr>';
	$return .= '<tr>';
	$return .= '<th>Shield</th>';
	if ($loc->shield != $npc->shield) { if (($loc->shield *= 2) == 600) { if ($loc->shield < $npc->shield) { $loc->shield = "600+"; } } }
	$return .= '<td align="center">' . $loc->shield . '</td>';
	$return .= '<td align="center">' . $npc->shield . '</td>';
	$return .= '</tr>';
	
		// Calculate Days/Hours/Mins Since last Visited
	$diff['sec'] = strtotime($loc->today) - strtotime($loc->spotted);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">First Spotted:</td>';
	$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . $diff['string'] . '</td>';
	$return .= '</tr>';

		// Calculate Days/Hours/Mins Since last Visited
	$diff['sec'] = strtotime($loc->today) - strtotime($loc->updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">Last Reported:</td>';
	$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . $diff['string'] . '</td>';
	$return .= '</tr>';
	$return .= '</table>';
}

if (!($b_loc || $npc_loc)) {
	$return = '<table><tr><td><h3>No Info in DB</h3></td></tr></table>';
}
$db->close();

echo $return;


?>
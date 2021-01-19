<?php

require_once('../include/mysqldb.php');
$db = new mysqldb;

$testing = Settings::TESTING;
$debug = 0;

$base_url = 'http://mapper.pardus-alliance.com';
if ($testing) { $base_url .= '/TestMap'; }

$uni = $db->protect($_REQUEST['uni']);
$id = $db->protect($_REQUEST['id']);

session_name($uni);

session_start();

$security = 0;
if (isset($_SESSION['security'])) { $security = $db->protect($_SESSION['security']); }

$img_url = Settings::IMG_DIR;
if (isset($_COOKIE['imagepack'])) {
	$img_url = $_COOKIE['imagepack'];
	if ($img_url[count($img_url) - 1] != '/')	{$img_url .= '/'; }
}

$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Buildings WHERE id = ' . $id);
$b_loc = $db->nextObject();

$db->query('SELECT *, UTC_TIMESTAMP() "today"  FROM ' . $uni . '_Test_Npcs WHERE id = ' . $id);
$npc_loc = $db->nextObject();

$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Maps WHERE id = ' . $id);
$m = $db->nextObject();

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

	// Get Stocking Information
	$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE id = ' . $id);
	while($q = $db->nextObject()) { $stock[$res_id[$q->name]] = $q; }
	// Make sure the Stock is in the correct order
	ksort($stock,SORT_NUMERIC);


	//Calculate Ticks Passed
	$format = '%F %T';
	date_default_timezone_set('UTC');
	$date = getdate(strtotime($loc->stock_updated));
	
	$tick = mktime(1,25,0,$date['mon'],$date['mday'],$date['year'],0);
	while ($tick < strtotime($loc->stock_updated)) {
		$tick += (60 * 60 * 6);
	}
	$count = 0;
	while ($tick < strtotime($loc->today)) {
		$tick += (60*60*6);
		$count++;
	}
	$tick = $count;

	$fs = $loc->freespace;

	$ticks_used = 100;

	// Calculate Days/Hours/Mins Since Last viewed on Nav
	$diff['sec'] = strtotime($m->today) - strtotime($m->fg_updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$map = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';
	
	// Calculate Days/Hours/Mins Since Last viewed on Building Info
	$diff['sec'] = strtotime($loc->today) - strtotime($loc->updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$visited = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';
	
	// Calculate Days/Hours/Mins Since last Stock Update
	$diff['sec'] = strtotime($loc->today) - strtotime($loc->stock_updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	$row = 8;
	$i = 0;

	$return .= '<table>';
	$return .= '<tr style="background-color:#003040;"><td colspan="' . $row . '" align="center">' . $loc->name . ' [' . $loc->x . ',' . $loc->y . ']</td></tr>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">Map Last Updated:</td>';
	$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . $map . '</td>';
	$return .= '</tr>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">Building Last Updated:</td>';
	$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . $visited . '</td>';
	$return .= '</tr>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td colspan="' . floor($row/2) . '">Stock Last Updated:</td>';
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

	foreach ($stock as $s) {
		$return .=  ($i++ % 2 != 0) ? '<tr class="alternating">' : '<tr>';
		$return .= '<td><img src="' . $img_url . $res_img[$s->name] . '" height="8" width="8" alt=""></td>';
		$return .= '<td><font color="#009900"><strong>' . $s->name . '</strong></td>';
		$return .= '<td align="right">' . number_format($s->amount) .'</td>';
		if (strpos($loc->image,"planet") || strpos($loc->image,"starbase")) {
			$return .= '<td align="right">';
			if($s->bal != 0) {
				if ($rs->bal > 0) { $return .= '<font color="#009900"><strong>+' . number_format($s->bal) . '</strong></font>'; }
				else { $return .= '<font color="#FFAA00"><strong>' . number_format($s->bal) . '</strong></font>'; }
			} else { $return .= number_format($s->bal); }
			$return .= '</td>';
			if ($loc->owner) { $return .= '<td align="right">' . number_format($s->min) . '</td>'; }
		} else {
			$return .= '<td align="right">' . number_format($s->min) . '</td>';
		}
		$return .= '<td align="right">' . number_format($s->max) . '</td>';
		$return .= '<td align="right">' . number_format($s->buy) . '</td>';
		$return .= '<td align="right">' . number_format($s->sell) . '</td>';
		if (!(strpos($loc->image,"planet") || strpos($loc->image,"starbase"))) {
			$return .= (($s->max - $s->amount) > 0) ? '<td align="right">' . number_format($s->max - $s->amount) . '</td>' : '<td align="right">0</td>';
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
	// Calculate Days/Hours/Mins Since Last viewed on Nav
	$diff['sec'] = strtotime($m->today) - strtotime($m->fg_updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$map = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	$return = '<table>';
	$return .= '<tr style="background-color:#003040;">';
	$return .= '<td align="left">Map Last Updated:</td>';
	$return .= '<td align="right">' . $map . '</td>';
	$return .= '</tr>';
	
	$return .= '<tr><td align="center" colspan="2"><h3>No Info in DB</h3></td></tr></table>';
}
$db->close();

echo $return;


?>
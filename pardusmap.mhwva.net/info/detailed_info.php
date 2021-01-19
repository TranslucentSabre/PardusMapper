<?php
	header('Access-Control-Allow-Origin: http://pardusmap.mhwva.net');

require_once('../include/mysqldb.php');
$db = new mysqldb;

$testing = Settings::TESTING;

$base_url = 'http://pardusmap.mhwva.net';
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

$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Buildings WHERE id = ' . $id);
$b_loc = $db->nextObject();

$db->query('SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Test_Npcs WHERE id = ' . $id);
$npc_loc = $db->nextObject();

$return = '';
date_default_timezone_set("UTC");

if ($b_loc) {
	$loc = $b_loc;
	//Get Resource Data
	$db->query('SELECT * FROM Pardus_Res_Data');
	while ($q = $db->nextObject()) {
		$res_img[$q->name] = $q->image;
		$res_id[$q->name] = $q->r_id;
	}

	// Get Stocking Information
	$db->query('SELECT * FROM ' . $uni . '_New_Stock WHERE id = ' . $id);
	while($q = $db->nextObject()) { $stock[$res_id[$q->name]] = $q; }
	$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . $loc->name . '\'');
	while ($q = $db->nextObject()) { $upkeep[$q->res] = $q; }
	// Make sure the Stock is in the correct order
	if ($stock) { ksort($stock,SORT_NUMERIC); }

	//Calculate Ticks Passed
	$format = '%F %T';
	date_default_timezone_set('UTC');
	$ts = strtotime($loc->stock_updated);
	$date = new DateTime("@$ts");
	$date->setTime(1,25,0);
	$tick = $date->format('U');

	
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

	// Calculate Days/Hours/Mins Since Last viewed on Nav or Building Info
	$diff['sec'] = strtotime($loc->today) - strtotime($loc->updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$viewed = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';
	
	// Calculate Days/Hours/Mins Since last Stock Update
	$diff['sec'] = strtotime($loc->today) - strtotime($loc->stock_updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	$return .= '<table>';
		$colspan = 1;
		$return .= '<tr>';
		$return .= '<td align="center">';
		$return .= '<h2><img src="' . $img_url .  $loc->image . '" width="48" height="48" style="vertical-align:middle;" alt="" />&nbsp;&nbsp;' . $loc->name . '</h2>';
		
		if (($loc->owner) && ($security == 1 || $security == 100)) {
			$colspan++;
			$owner_table = '<table cellspacing="3">';
				$owner_table .= '<tr>';
				$owner_table .= '<th colspan="2">Owned by</th>';
				$owner_table .= '</tr>';
				$owner_table .= '<tr>';
				if ($loc->faction) {
					$owner_table .= '<td valign="top" style="width: 16px;">';
					$owner_table .= '<img height="8" width="8" style="vertical-align: middle;" src="' . $img_url . $loc->faction . '"/>';
					$owner_table .= '</td>';
				}
				$owner_table .= '<td valign="top">';
				$owner_table .= '<strong>' . $loc->owner . '</strong>';
				if ($loc->alliance) {
					$owner_table .= '<br><font size="1"><strong>' . $loc->alliance . '</b></font>';
				}
				$owner_table .= '</td>';
				$owner_talbe .= '</tr>';
			$owner_table .= '</table>';
		}
		if (strpos($loc->image,"planet") || strpos($loc->image,"starbase")) {
			$colspan++;
			$worker_table = '<table>';
				$worker_table .= '<tr>';
				$worker_table .= '<td>Workers:</td>';
				$worker_table .= '<td>' . number_format($loc->population) . '</td>';
				$worker_table .= '</tr>';
				$worker_table .= '<tr>';
				$worker_table .= '<td>';
				$worker_table .= '<span style="font-size: 9px;">Crime : </span>';
				$worker_table .= '</td>';
				$worker_table .= '<td>';
				if ($loc->crime == "critical") {
					$worker_table .= '<span style="font-size: 9px; color: rgb(255, 0, 0);">';
				} elseif ($loc->crime == "high") {
					$worker_table .= '<span style="font-size: 9px; color: rgb(255, 153, 0);">';
				} elseif ($loc->crime == "medium") {
					$worker_table .= '<span style="font-size: 9px; color: rgb(255, 255, 0);">';
				} else {
					$worker_table .= '<span style="font-size: 9px; color: rgb(0, 255, 0);">';
				}
				$worker_table .= $loc->crime;
				$worker_table .= '</span>';
				$worker_table .= '</td>';
				$worker_table .= '</tr>';
				$worker_table .= '<tr>';
				$worker_table .= '<td align="center" colspan="2">';
				$worker_table .= '<a href="' . $base_url . '/' . $uni . '/' . $loc->sector . '/' . $loc->x . '/' . $loc->y . '/mission">Missions</a>';
				if (($loc->owner) && ($_SESSION['security'] == 100)) {
					$worker_table .= '<br>';
					$worker_table .= '<a href="' . $base_url . '/' . $uni . '/' . $loc->sector . '/' . $loc->x . '/' . $loc->y . '/flyclose">Fly close</a>';
					$worker_table .= '<br>';
					$worker_table .= '<a href="' . $base_url . '/' . $uni . '/' . $loc->sector . '/' . $loc->x . '/' . $loc->y . '/squadron">Squadrons</a>';
				}
				$worker_table .= '</td>';
				$worker_table .= '</tr>';
			$worker_table .= '</table>';
		}
		if ($owner_table) { $return .= '</td><td>' . $owner_table; }
		if ($worker_table) { $return .= '</td><td>' . $worker_table; }
		
		$return .= '</td></tr><tr><td colspan="' . $colspan . '">';
		
		if ($loc->level) {
			$return .= '<table width="75%">';
				$return .= '<tr>';
				$return .= '<td valign="top" width="50%" align="right">';
				$return .= '<table class="messagestyle" width="100%">';
					$return .= '<tr>';
					$return .= '<th colspan="3">Est. Upkeep</th>';
					$return .= '</tr>';
					$i = 1;
					if ($stock) {
						foreach ($stocks as $s) {
							if ($upkeep[$s->name]->upkeep == 1) {
								if ($i == 1) { $return .= '<tr>'; }
								$return .= '<td align="center">';
								$return .= '<img src="' . $img_url . $res_img[$s->name] . '">: ' . upkeep($upkeep[$s->name]->amount,$loc->level) . '</td>';
								if ($i == 3) { $i = 0; $return .= '</tr>'; }
								$i++;
							}
						}
						if ($i != 1) { $return .= '</tr>'; }
					}
				$return .= '</table>';
				$return .= '</td>';
				$return .= '<td valign="top" width="50%" align="left">';
				$return .= '<table width="100%">';
					$return .= '<tr>';
					$return .= '<th colspan="3">Est. Production</th>';
					$return .= '</tr>';
					$i = 1;
					if ($stock) {
						foreach ($stocks as $s) {
							if ($upkeep[$s->name]->upkeep == 0) {
								if ($i == 1) { $return .= '<tr>'; }
								$return .= '<td align="center">';
								$return .= '<img src="' . $img_url . $res_img[$s->name] . '">: ' . production($upkeep[$s->name]->amount,$loc->level) . '</td>';
								if ($i == 3) { $i = 0; $return .= '</tr>'; }
								$i++;
							}
						}
						if ($i != 1) { $return .= '</tr>'; }
					}
				$return .= '</table>';
				$return .= '</td>';
				$return .= '</tr>';
			$return .= '</table>';
			$return .= '<br>';
		}
		
		
		$row = 8;
		$i = 0;

		$return .= '<table>';
		$return .= '<tr style="background-color:#003040;">';
		$return .= '<td colspan="' . floor($row/2) . '">Building Last Updated:</td>';
		$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . $viewed . '</td>';
		$return .= '</tr>';
		$return .= '<tr style="background-color:#003040;">';
		$return .= '<td colspan="' . floor($row/2) . '">Stock Last Updated:</td>';
		$return .= '<td colspan="' . ceil($row/2) . '" align="right">' . $diff['string'] . '</td>';
		$return .= '</tr>';
		$return .= '<tr>';
		$return .= '<th colspan="2" style="background-color:#500000; color:#BBBBDD;">Resource</th>';
		if (strpos($loc->image,"starbase")) { $return .= '<th style="background-color:#330033; color:#BBBBDD;">Amount<br>Available</th>'; }
		else { 
			$return .= '<th style="background-color:#330033; color:#BBBBDD;">Amount</th>'; 
			if (strpos($loc->image,"planet")) {
			$return .= '<th style="background-color:#000000; color:#BBBBDD;">Bal</th>';
				if ($loc->owner) {
					$return .= '<th style="background-color:#000000; color:#BBBBDD;">Min</th>';
				}
			} else {
				$return .= '<th style="background-color:#000000; color:#BBBBDD;">Min</th>';
			}
		}

		if (strpos($loc->image,"starbase")) {
			$return .= '<th style="background-color:#000000; color:#BBBBDD;">Bal</th>';
		}
		if (strpos($loc->image,"starbase")) { $return .= '<th style="background-color:#000000; color:#BBBBDD;">Amount<br>Needed</th>'; }
		else { $return .= '<th style="background-color:#000000; color:#BBBBDD;">Max</th>'; }
		$return .= '<th style="background-color:#505000; color:#BBBBDD;">Price&nbsp;(Buy)</th>';
		$return .= '<th style="background-color:#505000; color:#BBBBDD;">Price&nbsp;(Sell)</th>';
		//if (!(strpos($loc->image,"planet") || strpos($loc->image,"starbase"))) { $return .= '<th style="background-color:#330033; color:#BBBBDD;">Needed</th>'; }
		$return .= '</tr>';

		if ($stock) {
			foreach ($stock as $s) {
				$return .=  ($i++ % 2 != 0) ? '<tr class="alternating">' : '<tr>';
				$return .= '<td><img src="' . $img_url . $res_img[$s->name] . '" height="8" width="8" alt=""></td>';
				$return .= '<td><font color="#009900"><strong>' . $s->name . '</strong></td>';
				if (strpos($loc->image,"starbase")) {
					$amount = $s->amount - $s->min;
					if ($amount < 0) $amount = 0;
					$return .= '<td align="right">' . number_format($amount) .'</td>';
				} else { 
					$return .= '<td align="right">' . number_format($s->amount) .'</td>'; 
					if (strpos($loc->image,"planet")) {
						$return .= '<td align="right">';
						if($s->bal != 0) {
							if ($s->bal > 0) { $return .= '<font color="#009900"><strong>+' . number_format($s->bal) . '</strong></font>'; }
							else { $return .= '<font color="#FFAA00"><strong>' . number_format($s->bal) . '</strong></font>'; }
						} else { $return .= number_format($s->bal); }
						$return .= '</td>';
						if ($loc->owner) { $return .= '<td align="right">' . number_format($s->min) . '</td>'; }
					} else {
						$return .= '<td align="right">' . number_format($s->min) . '</td>';
					}
				}
		
				if (strpos($loc->image,"starbase")) {
					$return .= '<td align="right">';
					if($s->bal != 0) {
						if ($rs->bal > 0) { $return .= '<font color="#009900"><strong>+' . number_format($s->bal) . '</strong></font>'; }
						else { $return .= '<font color="#FFAA00"><strong>' . number_format($s->bal) . '</strong></font>'; }
					} else { $return .= number_format($s->bal); }
					$return .= '</td>';
				}
				if (strpos($loc->image,"starbase")) {
					$amount = $s->max - $s->amount;
					if ($amount < 0) $amount = 0;
					$return .= '<td align="right">' . number_format($amount) . '</td>';
				} else { $return .= '<td align="right">' . number_format($s->max) . '</td>'; }
				$return .= '<td align="right">' . number_format($s->buy) . '</td>';
				$return .= '<td align="right">' . number_format($s->sell) . '</td>';
				//if (!(strpos($loc->image,"planet") || strpos($loc->image,"starbase"))) {
				//	$return .= (($s->max - $s->amount) > 0) ? '<td align="right">' . number_format($s->max - $s->amount) . '</td>' : '<td align="right">0</td>';
				//}	
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
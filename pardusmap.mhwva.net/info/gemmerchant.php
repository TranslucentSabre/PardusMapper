<?php

require_once('../include/mysqldb.php');
$db = new mysqldb;

$testing = Settings::TESTING;
$debug = Settings::DEBUG;

if ($testing || $debug) { 
	error_reporting(E_STRICT | E_ALL | E_NOTICE);
}

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }

$uni = $db->protect($_POST['uni']);
session_name($uni);

session_start();

$security = 0;
if (isset($_SESSION['security'])) { $security = $db->protect($_SESSION['security']); }

$img_url = Settings::IMG_DIR;
if (isset($_COOKIE['imagepack'])) {
	$img_url = $_COOKIE['imagepack'];
	if ($img_url[count($img_url) - 1] != '/')	{$img_url .= '/'; }
}

$db->query('SELECT *,  UTC_TIMESTAMP() "today" FROM `' . $uni . '_Maps` WHERE fg LIKE \'%gem_merchant%\'');
while ($b = $db->nextObject()) { $building[] = $b; }

$db->query('SELECT * from Pardus_Sectors');
while ($s = $db->nextObject()) { $sector[] = $s; }
	
foreach ($building as $b) {
	foreach ($sector as $s) {
		$start = $s->s_id;
		$end = $start + ($s->rows * $s->cols);
		if ($start <= $b->id && $b->id <= $end) {
			$db->query('SELECT * FROM Pardus_Clusters where c_id = ' . $s->c_id);
			$gem[$b->id][0] = $b;
			$gem[$b->id][1] = $s;
			$gem[$b->id][2] = $db->nextObject();
		}
	}
}

$db->close();

$return = '<table>';
$return .= '<tr>';
$return .= '<th>Cluster</th>';
$return .= '<th>Sector</th>';
$return .= '<th>Location</th>';
$return .= '<th>Merchant</th>';
$return .= '<th>Last Spotted</th>';
$return .= '</tr>';

$i = 0;
foreach ($gem as $key => $g) {
	$c = $g[2];
	$s = $g[1];
	$g = $g[0];
				
	// Calculate Days/Hours/Mins Since last Visited
	$diff['sec'] = strtotime($g->today) - strtotime($g->fg_updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	if ($i++ % 2 == 0) {
		$return .= '<tr class="alternating">';
	} else {
		$return .= '<tr>';
	}
	$return .= '<td align="center">';
		$return .= '<a href="' . $base_url . '/' . $uni . '/' . $c->code . '">'. $g->cluster . '</a>';
	$return .= '</td>';
	$return .= '<td align="center">';
		$return .= '<a href="' . $base_url . '/' . $uni . '/' . $g->sector . '">' . $g->sector . '</a>';
	$return .= '</td>';
	$return .= '<td align="center">[' . $g->x . ',' . $g->y . ']</td>';
	$return .= '<td align="center">';
		$return .= '<a href="' . $base_url . '/' . $uni . '/' . $g->sector . '/' . $g->x . '/' . $g->y . '" />';
			$return .= '<img src="' . $img_url . $g->fg . '" onMouseOut="closeInfo();" onMouseOver="openInfo(\'' . $base_url . '\',\'' . $uni . '\',' . $key . ');"/>';
		$return .= '</a>';
	$return .= '</td>';
	$return .= '<td align="center">' . $diff['string'] . '</td>';
	$return .= '</tr>';
}
$return .= '</table>';
echo $return;
?>
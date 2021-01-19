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
$faction = $db->protect($_POST['faction']);
$pilot = $db->protect($_POST['pilot']);
$pilot_s = $db->protect($_POST['pilot_s']);
$mission_filter = $db->protect($_POST['type']);
$sort = $db->protect($_POST['sort']);
$order = $db->protect($_POST['order']);
$mode = $db->protect($_POST['mode']);

session_name($uni);

session_start();

$security = 0;
if (isset($_SESSION['security'])) { $security = $db->protect($_SESSION['security']); }

$comp = 0;
if (isset($_SESSION['comp'])) { $comp = $db->protect($_SESSION['comp']); }
$cstart = 0;
if ($comp >= 2) {$cstart = $comp - 2; }
$cend = $comp + 2;

$rank = 0;
if (isset($_SESSION['rank'])) { $rank = $db->protect($_SESSION['rank']); }
$rstart = 0;
if ($rank >= 2) {$rstart = $rank - 2; }
$rend = $rank + 2;

$img_url = Settings::IMG_DIR;
if (isset($_COOKIE['imagepack'])) {
	$img_url = $_COOKIE['imagepack'];
	if ($img_url[count($img_url) - 1] != '/')	{$img_url .= '/'; }
}

$return = '';

$sort_by = '';

date_default_timezone_set("UTC");

if (strlen($sort)) {
	$sort_by = ' ORDER BY ';
	for($i=0;$i<strlen($sort);$i++) {
		switch(substr($sort,$i,1)) {
			case "A" :
				$sort_by .= "cluster ";
				if ($order & 1) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "B" :
				$sort_by .= "sector ";
				if ($order & 2) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "C" :
				$sort_by .= "loc ";
				if ($order & 4) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "D" :
				$sort_by .= "type_img ";
				if ($order & 8) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "E" :
				$sort_by .= "amount ";
				if ($order & 16) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				$sort_by .= "hack ";
				if ($order & 16) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "F" :
				$sort_by .= "time ";
				if ($order & 32) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "G" :
				$sort_by .= "t_cluster ";
				if ($order & 64) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "H" :
				$sort_by .= "t_sector ";
				if ($order & 128) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "I" :
				$sort_by .= "t_loc ";
				if ($order & 256) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "J" :
				$sort_by .= "credits ";
				if ($order & 512) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "K" :
				$sort_by .= "updated ";
				if ($order & 1024) { $sort_by .= " DESC, "; }
				else { $sort_by .= " ASC, "; }
				break;
		}
	}
	$sort_by = substr($sort_by,0,-2);
}

$query = 'SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Test_Missions';
if ($faction == 0) {
	$query .= ' WHERE ((faction is NULL';
	if ($mode) {
		$query .= ' AND comp BETWEEN ' . $cstart . ' AND ' . $cend;
	}
	$query .= ')';
	if ($pilot_s == 'tss' || $pilot_s == 'eps') {
		$query .= ' OR (faction LIKE \'%' . $pilot_s . '%\' ';
		if ($mode) {
			$query .= ' AND comp BETWEEN ' . $cstart . ' AND ' . $cend;
		}
		$query .= ')';
	} else {
		$query .= ' OR (faction LIKE \'%' . $pilot . '%\'';
		if ($mode) {
			$query .= ' AND rank BETWEEN ' . $rstart . ' AND ' . $rend;
		}
		$query .= ')';
		if ($pilot_s == 'eps') {
			$query .= ' OR (faction LIKE \'%' . $pilot_s . '%\'';
			if ($mode) {
				$query .= ' AND comp BETWEEN ' . $cstart . ' AND ' . $cend;
			}
			$query .= ')';
		}
	}
	$query .= ')';
}
if ($faction == 1) {
	if (strlen($pilot) == 0) { $query .= ' WHERE (faction LIKE \'%Nothing%\''; }
	else {
		$query .= ' WHERE (faction LIKE \'%' . $pilot . '%\'';
		if ($mode) {
			if ($pilot_s == 'tss' || $pilot_s == 'eps') { $query .= ' AND comp BETWEEN ' . $cstart . ' AND ' . $cend; }
			else { $query .= ' AND rank BETWEEN ' . $rstart . ' AND ' . $rend; }
		}
	}
	$query .= ')';
}	
if ($faction == 2) {
	$query .= ' WHERE (faction IS NULL';
	if ($mode) { $query .= ' AND comp BETWEEN ' . $cstart . ' AND ' . $cend; }
	$query .= ')';
}
if ($faction == 3) {
	if (strlen($pilot_s) == 0) { $query .= ' WHERE (faction LIKE \'%Nothing%\''; }
	else {
		$query .= ' WHERE (faction LIKE \'%' . $pilot_s . '%\'';
		if ($mode) {
			$query .= ' AND comp BETWEEN ' . $cstart . ' AND ' . $cend;
		}
	}
	$query .= ')';
}	
if (strtolower($mission_filter) != 'all') {
	$query .= ' AND type = \'' . $mission_filter . '\'';
}
if (isset($_POST['loc'])) {
	$source_id = $db->protect($_POST['loc']);
	$query .= ' AND source_id = ' . $source_id;
} elseif (isset($_POST['sector'])) {
	$sector = $db->protect($_POST['sector']);
	$query .= ' AND sector = \'' . $sector . '\'';
} elseif (isset($_POST['cluster'])) {
	$cluster = $db->protect($_POST['cluster']);
	$query .= ' AND cluster = \'' . $cluster . '\'';
}

if (strlen($sort_by)) { $query .= $sort_by; }

$db->query($query);

while ($q = $db->nextObject()) {
	// Calculate Days/Hours/Mins Since last Visited
	$diff['sec'] = strtotime($q->today) - strtotime($q->updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';

	$q->tick = $diff['string'];
		
	if ($diff['days'] > 1) { $delete[] = $q->id; }
	else { $mission[] = $q; }
}

if ($delete) { foreach ($delete as $d) { $db->removeMission($uni,$d,0); } }

$return = '<table id="mission_table">';
$return .= '<tr>';
$return .= '<th rowspan="2">Faction</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"A") !== false)  {
	if ($order & 1) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'A\');return false;">&nbsp;Cluster&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'A\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'A\');return false;">Cluster</a>';
}
$return .= '</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"B") !== false)  {
	if ($order & 2) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'B\');return false;">&nbsp;Sector&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'B\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'B\');return false;">Sector</a>';
}
$return .= '</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"C") !== false)  {
	if ($order & 4) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'C\');return false;">&nbsp;Location&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'C\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'C\');return false;">Location</a>';
}
$return .= '</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"D") !== false)  {
	if ($order & 8) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'D\');return false;">&nbsp;Type&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'D\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'D\');return false;">Type</a>';
}
$return .= '</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"E") !== false)  {
	if ($order & 16) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'E\');return false;">&nbsp;Amount&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'E\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'E\');return false;">Amount</a>';
}
$return .= '</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"F") !== false)  {
	if ($order & 32) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'F\');return false;">&nbsp;Time&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'F\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'F\');return false;">Time</a>';
}
$return .= '</th>';
$return .= '<th colspan="3">Target</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"J") !== false)  {
	if ($order & 512) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'J\');return false;">&nbsp;Reward&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'J\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'J\');return false;">Reward</a>';
}
$return .= '</th>';
$return .= '<th rowspan="2">';
if (strpos($sort,"K") !== false)  {
	if ($order & 1024) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'K\');return false;">&nbsp;Loaded&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'K\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'K\');return false;">Loaded</a>';
}
$return .= '</th>';
$return .= '</tr>';
$return .= '<tr>';
$return .= '<th>';
if (strpos($sort,"G") !== false)  {
	if ($order & 64) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'G\');return false;">&nbsp;Cluster&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'G\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'G\');return false;">Cluster</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"H") !== false)  {
	if ($order & 128) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'H\');return false;">&nbsp;Sector&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'H\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'H\');return false;">Sector</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"I") !== false)  {
	if ($order & 256) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'I\');return false;">&nbsp;Location&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'I\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'I\');return false;">Location</a>';
}
$return .= '</th>';
$return .= '</tr>';


$i = 0;

if ($mission) { foreach ($mission as $m) {
	if ($i++ % 2 == 0) {
		$return .= '<tr class="alternating">';
	} else {
		$return .= '<tr>';
	}
	if ($m->faction) {
		$return .= '<td align="center"><img src="' . $img_url . $m->faction . '" /></td>';
	} else { $return .= '<td align="center"> - </td>'; }
	$c = $db->getCluster(0,$m->cluster);
	$return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $m->cluster . '">' . $c->name . '</td>';
	$return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $m->sector . '">' . $m->sector . '</a></td>';
	$return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $m->sector . '/' . $m->x . '/' . $m->y . '">' . $m->loc . '<br>[' . $m->x . ',' . $m->y . ']</a></td>';
	$return .= '<td align="center"><img height="32" src="' . $img_url . $m->type_img . '" title="' . $m->type . '"/></td>';
	if ($m->amount) { $return .= '<td align="center">' . $m->amount . '</td>'; }
	elseif ($m->hack) { 
		if (stripos($m->hack,"<br>")) { 
			$xp = explode("<br>",$m->hack);
			$return .= '<td align="center">' . $xp[0] . '</td>';
		} else { $return .= '<td align="center"><img src="' . $img_url . '/' . $m->hack . '" /></td>'; }
	}
	else { $return .= '<td align="center"> - </td>'; }
	if ($m->time) { $return .= '<td align="center">' . $m->time . '</td>'; }
	else { $return .= '<td align="center"> - </td>'; }
	if ($m->t_cluster) {
		$tc = $db->getCluster(0,$m->t_cluster);
		$return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $m->t_cluster . '">' . $tc->name . '<a></td>'; 
	} else { $return .= '<td align="center"> - </td>'; }	
	if ($m->t_sector) { $return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $m->t_sector . '">' . $m->t_sector . '<a></td>'; }
	else { $return .= '<td align="center"> - </td>'; }
	if ($m->t_loc) { $return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $m->t_sector . '/' . $m->t_x . '/' . $m->t_y . '">' . $m->t_loc . '<br>[' . $m->t_x . ',' . $m->t_y . ']</a></td>'; }
	else { 
		if ($m->t_x) { $return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $m->t_sector . '/' . $m->t_x . '/' . $m->t_y . '">[' . $m->t_x . ',' . $m->t_y . ']</a></td>'; }
		else { $return .= '<td align="center"> - </td>';  }
	}
	if ($m->war) { 
		$return .= '<td align="center" class="war">' . $m->war . '</td>'; 
	}
	else if ($m->credits) { $return .= '<td align="center">' . $m->credits . '</td>'; }
	else { $return .= '<td align="center"> - </td>'; }
	$return .= '<td align="center">' . $m->tick . '</td>';
	$return .= '</tr>';
} } else {
	$return .= '<tr><td colspan="12" align="center"><h1>No Mission Data Available</h1></td></tr>';
}

$return .= '</table>';

$db->close();

echo $return;

?>
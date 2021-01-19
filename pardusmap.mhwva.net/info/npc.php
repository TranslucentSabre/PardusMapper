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
$npc_filter = $db->protect($_POST['npc']);
$sort = $db->protect($_POST['sort']);
$order = $db->protect($_POST['order']);

session_name($uni);

session_start();

$security = 0;
if (isset($_SESSION['security'])) { $security = $db->protect($_SESSION['security']); }

$img_url = Settings::IMG_DIR;
if (isset($_COOKIE['imagepack'])) {
	$img_url = $_COOKIE['imagepack'];
	if ($img_url[count($img_url) - 1] != '/')	{$img_url .= '/'; }
}

$return = '';

$sort_by = '';

if (strlen($sort)) {
	$sort_by = ' ORDER BY ';
	for($i=0;$i<strlen($sort);$i++) {
		switch(substr($sort,$i,1)) {
			case "C" :
				$sort_by .= "cluster ";
				if ($order & 1) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "S" :
				$sort_by .= "sector ";
				if ($order & 2) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "L" :
				$sort_by .= "x ";
				if ($order & 4) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				$sort_by .= "y ";
				if ($order & 4) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "N" :
				$sort_by .= "name ";
				if ($order & 8) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "A" :
				$sort_by .= "spotted ";
				if ($order & 16) { $sort_by .= " DESC, "; }
				else { $sort_by .= " ASC, "; }
				break;
			case "T" :
				$sort_by .= "updated ";
				if ($order & 32) { $sort_by .= " DESC, "; }
				else { $sort_by .= " ASC, "; }
				break;
		}
	}
	$sort_by = substr($sort_by,0,-2);
}

//if (strlen($sort_by)) { $return .= $sort_by . '<br>'; }
$db->query("SELECT * FROM Pardus_Static_Locations");
while ($c = $db->nextObject()) { $static[] = $c->id; }


$query = 'SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Test_Npcs';
if (isset($_POST['sector'])) {
	$sector = $db->protect($_POST['sector']);
		
	$query .= ' WHERE sector = \'' . $sector. '\'';
	if (strtolower($npc_filter) != 'all') {
		$query .= '  AND name = \'' . $npc_filter . '\'';
	}
	if (strlen($sort_by)) { $query .= $sort_by; }
		
} elseif (isset($_POST['cluster'])) {
	$cluster .= $db->protect($_POST['cluster']);
	if ($cluster != 'CORE') {
		$db->query('SELECT * FROM Pardus_Clusters WHERE code = \'' . $cluster . '\'');
		$cluster = $db->nextObject()->name;
	
		$query .= ' WHERE cluster = \'' . $cluster . '\'';
	} else {
		$query .= ' WHERE cluster LIKE \'Pardus%Contingent\'';
	}	
	if (strtolower($npc_filter) != 'all') {
		$query .= ' AND name = \'' . $npc_filter . '\'';
	}	
	if (strlen($sort_by)) { $query .= $sort_by; }
} else {
	if (strtolower($npc_filter) != 'all') {
		$query .= ' WHERE name = \'' . $npc_filter . '\'';
	}
	if (strlen($sort_by)) { $query .= $sort_by; }	
		
}

//$return .= $query;

$db->query($query);

while ($q = $db->nextObject()) {
	// Calculate Days/Hours/Mins Since last seen
	$diff['sec'] = strtotime($q->today) - strtotime($q->updated);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';
	
	$q->tick = $diff['string'];
		
	unset($diff);
		
	// Calculate Days/Hours/Mins Since First Spotted
	$diff['sec'] = strtotime($q->today) - strtotime($q->spotted);
	$diff['days'] = $diff['sec']/60/60/24;
	$diff['hours'] = ($diff['days'] - floor($diff['days'])) * 24;
	$diff['min'] = ($diff['hours'] - floor($diff['hours'])) * 60;
	$diff['string'] = floor($diff['days']) . 'd ' . floor($diff['hours']) . 'h ' . floor($diff['min']) . 'm';
		
	$q->age = $diff['string'];

	if ($diff['days'] > 7) { $delete[] = $q->id; }
	else { if (!(in_array($q->id,$static))) { $npc[] = $q; } }
	
	unset($diff);
}

if ($delete) { foreach ($delete as $d) { $db->removeNPC($uni,$d); } }

$db->close();

$return .= '<table id="npc_table">';
$return .= '<tr>';
$return .= '<th>';
if (strpos($sort,"C") !== false)  {
	if ($order & 1) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'C\');return false;">&nbsp;Cluster&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'C\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'C\');return false;">Cluster</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"S") !== false) { 
	if ($order & 2) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'S\');return false;">&nbsp;Sector&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'S\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'S\');return false;">Sector</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"L") !== false) { 
	if ($order & 4) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'L\');return false;">&nbsp;Location&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'L\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'L\');return false;">Location</a>';
}
$return .= '</th>';
$return .= '<th colspan="2">';
if (strpos($sort,"N") !== false) { 
	if ($order & 8) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'N\');return false;">&nbsp;NPC&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'N\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'N\');return false;">NPC</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"A") !== false) { 
	if ($order & 16) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'A\');return false;">&nbsp;First Spotted&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'A\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'A\');return false;">First Spotted</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"T") !== false) { 
	if ($order & 32) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'T\');return false;">&nbsp;Lost Spotted&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'T\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'T\');return false;">Last Spotted</a>';
}
$return .= '</th>';
$return .= '</tr>';

$i = 0;
if ($npc) {
	foreach ($npc as $n) {
		if ($i++ % 2 == 0) {
			$return .= '<tr class="alternating">';
		} else {
			$return .= '<tr>';
		}
		$return .= '<td align="center">' . $n->cluster . '</td>';
		$return .= '<td align="center"><a href="' . $base_url . '/' . $uni . '/' . $n->sector . '">' . $n->sector . '</a></td>';
		$return .= '<td align="center">[' . $n->x . ',' . $n->y . ']</td>';
		$return .= '<td align="left">';
		if ($n->npc_cloaked) { $return .= '<img class="cloaked" src="' . $img_url . $n->image . '" />';}
		else { $return .= '<img src="' . $img_url . $n->image . '" />'; }
		$return .= '</td>';
		$return .= '<td align="left">' . $n->name . '</td>';
		$return .= '<td align="center">' . $n->age . '</td>';
		$return .= '<td align="center">' . $n->tick . '</td>';
		$return .= '</tr>';
	}
}

$return .= '</table>';

echo $return;

?>
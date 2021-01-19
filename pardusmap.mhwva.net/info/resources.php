<?php
	header('Access-Control-Allow-Origin: http://pardusmap.nfshost.com');

require_once('../include/mysqldb.php');
$db = new mysqldb;

$testing = Settings::TESTING;
$debug = Settings::DEBUG;

if ($testing || $debug) { 
	error_reporting(E_STRICT | E_ALL | E_NOTICE);
}

$base_url = 'http://pardusmap.nfshost.com';
if ($testing) { $base_url .= '/TestMap'; }

$uni = $db->protect($_POST['uni']);
$sector = $db->protect($_POST['sector']);
$resource = $db->protect($_POST['resource']);
$sort = $db->protect($_POST['sort']);
$order = $db->protect($_POST['order']);
if (isset($_POST['pilot'])) { $pilot = $db->protect($_POST['pilot']); }

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
			case "L" :
				$sort_by .= "x ";
				if ($order & 1) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				$sort_by .= "y ";
				if ($order & 1) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "B" :
				$sort_by .= "name ";
				if ($order & 2) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "S" :
				if (strtolower($resource) != 'all') { $sort_by .= "res_stock "; }
				else { $sort_by .= "stock "; }
				if ($order & 4) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "T" :
				$sort_by .= "stock_updated ";
				if ($order & 8) { $sort_by .= " DESC, "; }
				else { $sort_by .= " ASC, "; }
				break;
			case "O" :
				$sort_by .= "owner ";
				if ($order & 16) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
			case "A" :
				$sort_by .= "alliance ";
				if ($order & 32) { $sort_by .= " ASC, "; }
				else { $sort_by .= " DESC, "; }
				break;
		}
	}
	$sort_by = substr($sort_by,0,-2);
}

if (isset($_POST['pilot']) && $pilot == $_SESSION['user']) {
	if (strtolower($resource) != 'all') {
		$query = 'SELECT *, UTC_TIMESTAMP() "today",  (SELECT stock FROM ' . $uni . '_New_Stock s WHERE b.id = s.id AND name = \'' . $resource . '\') "res_stock", (SELECT amount FROM ' . $uni . '_New_Stock s WHERE b.id = s.id AND name = \'' . $resource . '\') "amount", (SELECT max FROM ' . $uni . '_New_Stock s WHERE b.id = s.id AND name = \'' . $resource . '\') "max" FROM ' . $uni . '_Buildings b WHERE id IN (SELECT loc FROM ' . $uni . '_Personal_Resources WHERE id = ' . $_SESSION['id'] . ')';
		$query .= ' AND b.name IN (SELECT name from Pardus_Upkeep_Data WHERE res = \'' . $resource . '\' AND upkeep = 1)';
	} else {
		$query = 'SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Buildings b WHERE b.id IN (SELECT loc FROM ' . $uni . '_Personal_Resources WHERE id = ' . $_SESSION['id'] . ')';
	}
} else {
	if (strtolower($resource) != 'all') {
		$query = 'SELECT *, UTC_TIMESTAMP() "today",  (SELECT stock FROM ' . $uni . '_New_Stock s WHERE b.id = s.id AND name = \'' . $resource . '\') "res_stock", (SELECT amount FROM ' . $uni . '_New_Stock s WHERE b.id = s.id AND name = \'' . $resource . '\') "amount", (SELECT max FROM ' . $uni . '_New_Stock s WHERE b.id = s.id AND name = \'' . $resource . '\') "max" FROM ' . $uni . '_Buildings b WHERE sector = \'' . $sector . '\'';
		$query .= ' AND b.name IN (SELECT name from Pardus_Upkeep_Data WHERE res = \'' . $resource . '\' AND upkeep = 1)';
	} else {
		$query = 'SELECT *, UTC_TIMESTAMP() "today" FROM ' . $uni . '_Buildings b WHERE b.sector = \'' . $sector . '\'';
	}
}

if (strlen($sort_by)) { $query .= $sort_by; }

$db->query($query);


while ($q = $db->nextObject()) { 
	//Calculate Ticks Passed
	$format = '%F %T';
	date_default_timezone_set('UTC');
	$ts = strtotime($q->stock_updated);
	$date = new DateTime("@$ts");
	$date->setTime(1,25,0);
	$tick = $date->format('U');

	while ($tick < strtotime($q->stock_updated)) {
		$tick += (60 * 60 * 6);
	}
	$count = 0;
	while ($tick < strtotime($q->today)) {
		$tick += (60*60*6);
		$count++;
	}

	$q->tick = $count;

	$buildings[$q->id] = $q;
}	

if (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
	$db->query('SELECT * FROM ' . $uni . '_Personal_Resources WHERE id = ' . $_SESSION['id']);
	while ($a = $db->nextObject()) { $checked[] = $a->loc; }
	if ($checked) { sort($checked); }
}
$db->close();

$i = 0;
$return .= '<table id="resource_table">';
$return .= '<tr>';
if (isset($_SESSION['id']) && $_SESSION['id'] > 0) { $return .= '<th></th>'; }
$return .= '<th>';
if (strpos($sort,"L") !== false) { 
	if ($order & 1) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'L\');return false;">&nbsp;Location&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'L\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'L\');return false;">Location</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"B") !== false) { 
	if ($order & 2) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'B\');return false;">&nbsp;Building&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'B\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'B\');return false;">Building</a>';
}
$return .= '</th>';
if ($security == 1 || $security == 100) {
	$return .= '<th colspan="2">';
	if (strpos($sort,"O") !== false) { 
		if ($order & 16) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
		$return .= '<a href="#" onClick="multiSort(\'O\');return false;">&nbsp;Owner&nbsp;</a>';
		$return .= '<a href="#" onClick="removeSort(\'O\');return false;"><span class="symbol">&times;</span></a>';
	} else {
		$return .= '<a href="#" onClick="multiSort(\'O\');return false;">Owner</a>';
	}
	$return .= '</th>';
	$return .= '<th>';
	if (strpos($sort,"A") !== false) { 
		if ($order & 32) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
		$return .= '<a href="#" onClick="multiSort(\'A\');return false;">&nbsp;Alliance&nbsp;</a>';
		$return .= '<a href="#" onClick="removeSort(\'A\');return false;"><span class="symbol">&times;</span></a>';
	} else {
		$return .= '<a href="#" onClick="multiSort(\'A\');return false;">Alliance</a>';
	}
	$return .= '</th>';
}
$return .= '<th>';
if (strpos($sort,"S") !== false) { 
	if ($order & 4) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'S\');return false;">&nbsp;Stock Level&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'S\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'S\');return false;">Stock Level</a>';
}
$return .= '</th>';
$return .= '<th>';
if (strpos($sort,"T") !== false) { 
	if ($order & 8) { $return .= '<span class="symbol">&uarr;</span>'; } else { $return .= '<span class="symbol">&darr;</span>'; }
	$return .= '<a href="#" onClick="multiSort(\'T\');return false;">&nbsp;Last Updated&nbsp;</a>';
	$return .= '<a href="#" onClick="removeSort(\'T\');return false;"><span class="symbol">&times;</span></a>';
} else {
	$return .= '<a href="#" onClick="multiSort(\'T\');return false;">Last Updated</a>';
}
$return .= '</th>';
$return .= '</tr>';

foreach ($buildings as $b) {
	if ($i++ % 2 == 0) {
		$return .= '<tr class="alternating">';
	} else {
		$return .= '<tr>';
	}
	if (isset($_SESSION['id']) && $_SESSION['id'] > 0) { 
		if ($checked && in_array($b->id,$checked)) {
			$return .= '<td align="center"><input type="checkbox" id="' . $b->id . '" onClick="addInterest(this,\'' . $uni . '\',' . $_SESSION['id'] . ',' . $b->id . ');" checked></td>'; 
		} else {
			$return .= '<td align="center"><input type="checkbox" id="' . $b->id . '" onClick="addInterest(this,\'' . $uni . '\',' . $_SESSION['id'] . ',' . $b->id . ');"></td>';
		}
	}
	$return .= '<td align="center">[' . $b->x . ',' . $b->y . ']</td>';
	$return .= '<td align="center">';
	if ($b->x) {
		$return .= '<a href="#" onClick="loadDetail(\'' . $base_url . '\',\'' . $uni . '\',' . $b->id . ');return false;">' . $b->name . '</a></td>';
	} else {
		$return .= $b->name . '</td>';
	}
	if ($security == 1 || $security == 100) {
		$return .= '<td align="left">';
		if (is_string($b->faction)) {
			$return .= '<img src="' . $img_url . $b->faction . '" />';
		}
		$return .= '</td>';
		$return .= '<td align="left">' . $b->owner . '</td>';
		$return .= '<td align="center">' . $b->alliance . '</td>';
	}
	$return .= '<td align="center">';
	$return .= '<div class="bar-wrap" style="width: 100px;">';
	
	if (strtolower($resource) != 'all') { $stock_level = $b->res_stock; }
	else { $stock_level = $b->stock; }
		
	if ($stock_level == 100) {
		$return .= '<div class="bar-bar" style="width:' . $stock_level . 'px;background-color:green;"></div>';
	} elseif ($stock_level >= 75) {
		$return .= '<div class="bar-bar" style="width:' . $stock_level . 'px;background-color:blue;"></div>';
	} elseif ($stock_level >= 50) {
		$return .= '<div class="bar-bar" style="width:' . $stock_level . 'px;background-color:yellow;"></div>';
	} else {
		$return .= '<div class="bar-bar" style="width:' . $stock_level . 'px;background-color:red;"></div>';
	}
	if ($b->max) {
		$amount_needed = $b->max - $b->amount;
		if ($amount_needed < 0 ) { $amount_needed = 0; }
		$return .= '<div class="bar-text" style="width: 100px;">' . $amount_needed . '</div>';
	} else {
		$return .= '<div class="bar-text" style="width: 100px;">' . $stock_level . ' %</div>';
	}
	$return .= '</div>';
	$return .= '</td>';
	$return .= '<td align="center">' . $b->tick . '</td>';
	$return .= '</tr>';
}

$return .= '</table>';
echo $return;
?>
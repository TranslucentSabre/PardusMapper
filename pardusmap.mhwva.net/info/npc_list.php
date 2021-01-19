<?php
require_once('../include/mysqldb.php');
$db = new mysqldb;

$uni = $db->protect($_POST['uni']);

$return = '';

$db->query("SELECT * FROM Pardus_Static_Locations");
while ($c = $db->nextObject()) { $static[] = $c->id; }

if (isset($_POST['sector'])) {
	$db->query('SELECT DISTINCT name, id FROM ' . $uni . '_Test_Npcs WHERE sector = \'' . $db->protect($_POST['sector']) . '\' GROUP BY name');
} elseif (isset($_POST['cluster'])) {
	if ($_POST['cluster'] != 'CORE') {
		$db->query('SELECT * FROM Pardus_Clusters WHERE code = \'' . $db->protect($_POST['cluster']) . '\'');
		$c = $db->nextObject();
		$db->query('SELECT DISTINCT name, id FROM ' . $uni . '_Test_Npcs WHERE cluster = \'' . $c->name . '\' GROUP BY name');
	} else {
		$db->query('SELECT DISTINCT name, id FROM ' . $uni . '_Test_Npcs WHERE cluster LIKE \'Pardus%Contingent\' GROUP BY name');
	}
} else {
	$db->query('SELECT DISTINCT name, id FROM ' . $uni . '_Test_Npcs GROUP BY name');
}
while ($n = $db->nextObject()) { if (!(in_array($n->id,$static))) { $npc_list[] = $n->name; } }

array_unshift($npc_list,'All');

$return .= '<table><tr><th>NPCs</th></tr>';
foreach ($npc_list as $n) {
	$return .= '<tr><td><a href=# onclick="loadNPC(\'' . $n . '\',1);">' . $n . '</a></td></tr>';
}
$return .= '</table>';

echo $return;

$db->close();
?>
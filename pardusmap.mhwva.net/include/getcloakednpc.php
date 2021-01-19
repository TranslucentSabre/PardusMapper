<?php
/*
if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  {  header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
if($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  {  header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
if($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  {  header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
*/
header('Access-Control-Allow-Origin: http://*.pardus.at');

require_once('mysqldb.php');
$db = new mysqldb;

$base_url = 'http://mapper.pardus-alliance.com';

$uni = $db->protect($_REQUEST['uni']);
$data = explode("~",$db->protect($_REQUEST['data']));

$return = '';
for ($i = 1;$i < sizeof($data);$i++) {
	$loc = $data[$i];
	$db->query('SELECT * FROM ' . $uni . '_Maps WHERE id = ' . $loc);
	$m = $db->nextObject();

	if ($m->npc_cloaked) {  $return .= '~' . $loc . ',' . $m->npc; }
}
echo $return;
?>
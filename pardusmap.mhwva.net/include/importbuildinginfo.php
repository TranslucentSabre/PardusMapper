<?php

if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  {  header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  {  header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
else if($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  {  header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
else { die('0,Information Not coming from Pardus'); }

require_once("settings.php");

$site = 'http://pardusmap.mhwva.net/';
if (Settings::TESTING) { $site .= 'TestMap/'; }
$site .= 'include/';

if (isset($_REQUEST['bi'])) {
	$site .= 'importbuildingindex.php?';
}
if (isset($_REQUEST['building']) || isset($_REQUEST['bt'])) {
	$site .= 'importothersbuildinginfo.php?';
}
if (isset($_REQUEST['bts']) || isset($_REQUEST['level']) || isset($_REQUEST['bm'])) {
	$site .= 'importownbuildinginfo.php?';
}
if (isset($_REQUEST['planet']) || isset($_REQUEST['pt'])) {
	$site .= 'importplanetinfo.php?';
}
if (isset($_REQUEST['sb']) || isset($_REQUEST['sbt']) || isset($_REQUEST['squads']) || isset($_REQUEST['sbb'])) {
	$site .= 'importstarbaseinfo.php?';
}
$site .= $_SERVER['QUERY_STRING'];
header("Location: $site");

?>
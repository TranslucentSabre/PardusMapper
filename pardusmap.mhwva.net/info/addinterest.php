<?php

require_once('../include/mysqldb.php');
$db = new mysqldb;

$testing = Settings::TESTING;
$debug = Settings::DEBUG;

if ($testing || $debug) { 
	error_reporting(E_STRICT | E_ALL | E_NOTICE);
}

$uni = $db->protect($_POST['uni']);
$id = $db->protect($_POST['id']);
$loc = $db->protect($_POST['loc']);

session_name($uni);

session_start();

if ($id != $_SESSION['id']) { return; }

if (isset($_POST['add'])) { $db->query('INSERT INTO ' . $uni . '_Personal_Resources (id,loc) VALUES (' . $id . ',' . $loc . ')'); }
else { $db->query('DELETE FROM ' . $uni . '_Personal_Resources WHERE id = ' . $id . ' AND loc = ' . $loc); }

?>
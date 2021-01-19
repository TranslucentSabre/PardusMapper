<?php
require_once('include/mysqldb.php');
$db = new mysqldb;

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { include('index.html'); exit; }

session_name($uni = $db->protect($_REQUEST['uni']));

session_start();

$testing = Settings::TESTING;
$debug = Settings::DEBUG;

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }


$db->query('UPDATE ' . $uni . '_Users SET logout = UTC_TIMESTAMP() WHERE username = \'' . $name . '\'');
$db->close();

session_destroy();
session_write_close();
$url = $base_url . '/' . $uni . '/index.php';
header("Location: $url");
?>
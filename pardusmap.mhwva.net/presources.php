<?php
require_once('include/mysqldb.php');
$db = new mysqldb;

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }
if (!isset($_REQUEST['sector'])) { exit; }

session_name($uni = $db->protect($_REQUEST['uni']));

$testing = Settings::TESTING;
$debug = Settings::DEBUG;

if ($testing || $debug) {
	error_reporting(E_STRICT | E_ALL | E_NOTICE);
}

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }

$css = $base_url . '/main.css';
$r_css = $base_url. '/resources.css';

// Start the Session
session_start();
$sector = $db->protect($_REQUEST['sector']);
$s = $db->getSector(0,$sector);
$c = $db->getCluster($s->c_id,"");

$db->query('SELECT * FROM Pardus_Clusters WHERE c_id = (SELECT c_id FROM Pardus_Sectors WHERE name = \'' . $sector . '\')');
$cluster = $db->nextObject();
$cluster = $c->code;

if (!(isset($_REQUEST['pilot']) && $_REQUEST['pilot'] == $_SESSION['user'])) {
	$url = $base_url . '/' . $uni . '/' . $sector . '/resources';
	header("Location: $url");
} else {
	$pilot = $db->protect($_REQUEST['pilot']);
}
$security = 0;
if (isset($_SESSION['security'])) { $security = $db->protect($_SESSION['security']); }

$img_url = Settings::IMG_DIR;
if (isset($_COOKIE['imagepack'])) {
	$img_url = $_COOKIE['imagepack'];
	if ($img_url[count($img_url) - 1] != '/')	{$img_url .= '/'; }
}

$x1 = $y1= 0;
if (isset($_REQUEST['x1'])) { $x1 = $db->protect($_REQUEST['x1']); }
if (isset($_REQUEST['y1'])) { $y1 = $db->protect($_REQUEST['y1']); }

if (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
	$db->query('SELECT * FROM ' . $uni . '_Buildings WHERE id IN (SELECT loc FROM ' . $uni . '_Personal_Resources WHERE id = ' . $_SESSION['id'] . ')');
	while ($r_single = $db->nextObject()) { $r_list[] = $r_single->name; }
	$r_list = array_unique($r_list);
	foreach ($r_list as $r_single) {
		$db->query('SELECT * FROM Pardus_Upkeep_Data WHERE name = \'' . $r_single . '\' AND upkeep = 1');
		while ($u = $db->nextObject()) { $res_list[] = $u->res; }
	}
	if ($res_list) { 
		sort($res_list);
		$res_list = array_unique($res_list);
		array_unshift($res_list,'All');
	} else {
		$res_list[] = 'All';
	}
}
$db->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<title><?php echo $pilot; ?>'s Upkeep Tables</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $r_css; ?>" />
		<script type="text/javascript" src="<?php echo $base_url; ?>/include/main.js"></script>
		<script language="Javascript">
			//<![CDATA[
			function multiSort(value) {
				var i = sort_var.indexOf(value);
				if (i == -1) {
					sort_var += value;
				}
				if (value == "L") {
					if (sort_order & 1) { sort_order -= 1; }
					else { sort_order += 1; }
				}
				if (value == "B") {
					if (sort_order & 2) { sort_order -= 2; }
					else { sort_order += 2; }
				}
				if (value == "S") {
					if (sort_order & 4) { sort_order -= 4; }
					else { sort_order += 4; }
				}
				if (value == "T") {
					if (sort_order & 8) { sort_order -= 8; }
					else { sort_order += 8; }
				}
				<?php if ($security == 1 || $security == 100) { ?>
					if (value == "O") {
						if (sort_order & 16) { sort_order -= 16; }
						else { sort_order += 16; }
					}
					if (value == "A") {
						if (sort_order & 32) { sort_order -= 32; }
						else { sort_order += 32; }
					}
				<?php } ?>
				updateResources(resource,0);
			}
			function removeSort(value) {
				var i = sort_var.indexOf(value);
				if (i !== false) {
					sort_var = sort_var.substr(0,i) + sort_var.substr(i+1);
				} else { sort_var = ''; }
				if (value == "L") {
					if (sort_order & 1) { sort_order -= 1; }
				}
				if (value == "B") {
					if (sort_order & 2) { sort_order -= 2; }
				}
				if (value == "S") {
					if (sort_order & 4) { sort_order -= 4; }
				}
				if (value == "T") {
					if (sort_order & 8) { sort_order -= 8; }
				}
				<?php if ($security == 1 || $security == 100) { ?>
					if (value == "O") {
						if (sort_order & 16) { sort_order -= 16; }
					}
					if (value == "A") {
						if (sort_order & 32) { sort_order -= 32; }
					}
				<?php } ?>
				updateResources(resource,0);
			}
			function loadResources() {
				updateResources(resource,1);
				//setTimeout('updateResources(resource,0)',60000);
			}
			function updateResources(key,reset) {
				if (reset) {
					sort_var = "";
					sort_order = 0;
				}
				closeDetail();
				resource = key;
				var url = <?php echo '"' . $base_url . '"'; ?> + "/info/resources.php";
				var params = "uni=" + <?php echo '"' . $uni . '"'; ?> + "&sector=" + <?php echo '"' . $sector . '"'; ?> + "&resource=" + resource + "&sort=" + sort_var + "&order=" + sort_order + "&pilot=" + <?php echo '"' . $pilot . '"'; ?>;
				xmlhttp.open("POST",url,true);
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xmlhttp.setRequestHeader("Content-length", params.length);
				xmlhttp.setRequestHeader("Connection" , "close");

				xmlhttp.onreadystatechange = function () {
					if (xmlhttp.readyState == 4) {
						document.getElementById("resource_body").innerHTML = xmlhttp.responseText;
					} else {
						document.getElementById("resource_body").innerHTML = "<img src=\"http://mapper.pardus-alliance.com/images/ajax-loader.gif\" />";
					}
				}
				xmlhttp.send(params);
			}
			var sort_var = '';
			var sort_order = 0;
			var resource = 'all';
			var xmlhttp = getXMLHttpObject();
			var detailhttp = getXMLHttpObject();
			window.onload=loadResources;
			//]]>
		</script>
		<?php if (isset($_SESSION['id']) && $_SESSION['id'] > 0) { ?>
		<script language="Javascript">
			function addInterest(box,uni,id,loc) {
				var http = getXMLHttpObject();
				var url = <?php echo '"' . $base_url . '"'; ?> + "/info/addinterest.php";
				if (box.checked === true) { var params = "uni=" + uni + "&id=" + id + "&loc=" + loc + "&add=1"; }
				else { var params = "uni=" + uni + "&id=" + id + "&loc=" + loc; }
				http.open("POST",url,true);
				http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				http.setRequestHeader("Content-length", params.length);
				http.setRequestHeader("Connection" , "close");

				http.onreadystatechange = function () {
					if (http.readyState == 4) {
					}
				}
				http.send(params);


			}
		</script>
		<?php } ?>
		<script type="text/javascript">

			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-15475436-5']);
			_gaq.push(['_setDomainName', '.mhwva.net']);
			_gaq.push(['_trackPageview']);
	
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();

		</script>	
	</head>
	<body>
		<div id="header_side"><?php include('include/header_side.php'); ?></div>
		<div id="body">
			<div id="resource_list">
				<table>
					<tr><th colspan="2">Resource</th></tr>
					<?php foreach ($res_list as $key) { echo '<tr><td><a href="#" onClick="updateResources(\'' . $key . '\',1);return false;">' . $key . '<a></td></tr>'; }?>
				</table>
			</div>
			<div id="resource_body"></div>
			<div id="details" name="resources">
				<div id="close_detail"><center><h3><a href=# onClick="closeDetail();return false;">Close Detail</a></h3></center></div>
				<div id="d_con"></div>
			
			</div>
		</div>
		<div id="footer"><?php include('include/footer.php'); ?></div>
	</body>
</html>

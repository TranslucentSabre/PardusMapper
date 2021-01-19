<?php
header("Cache-Control: private, max-age=604800");
header("Expires: ".gmdate('r', time()+604800));

require_once('include/mysqldb.php');
$db = new mysqldb;

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { exit; }

session_name($uni = $db->protect($_REQUEST['uni']));

$testing = Settings::TESTING;
$debug = Settings::DEBUG;

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }

$css = $base_url . '/main.css';
$game_css = $base_url . '/game.css';
$cluster_css = $base_url . '/cluster.css';

// Start the Session
session_start();

$security = 0;
if (isset($_SESSION['security'])) { $security = $db->protect($_SESSION['security']); }

$img_url = Settings::IMG_DIR;
if (isset($_COOKIE['imagepack'])) {
	$img_url = $_COOKIE['imagepack'];
	if ($img_url[count($img_url) - 1] != '/')	{$img_url .= '/'; }
}

if (isset($_REQUEST['sector'])) { 
	$sector = $db->protect(urldecode($_REQUEST['sector']));
	$db->query('SELECT * FROM Pardus_Clusters WHERE c_id = (SELECT c_id FROM Pardus_Sectors WHERE name = \'' . $sector . '\')'); 
	$cluster = $db->nextObject();
	$cluster = $cluster->code;
	$db->query('SELECT * FROM Pardus_Sectors WHERE name = \'' . $sector . '\'');
	$s = $db->nextObject();
}
if (isset($_REQUEST['x2']) && isset($_REQUEST['y2'])) {
	$x2 = $db->protect($_REQUEST['x2']);
	$y2 = $db->protect($_REQUEST['y2']);

	$id = $s->s_id + ($x2 * $s->rows) + $y2;
}

$title = $sector . ' Sector Map';

$shownpc = 0;

$db->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<title><?php echo $title;?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $game_css; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $cluster_css; ?>" />
		<script type="text/javascript" src="<?php echo $base_url; ?>/include/main.js"></script>
		<script type="text/javascript">
			//<![CDATA[
			function getCheckedValue(radioObj) {
				if(!radioObj)
					return "all";
				var radioLength = radioObj.length;
				if(radioLength == undefined)
					if(radioObj.checked)
						return radioObj.value;
					else
						return "";
				for(var i = 0; i < radioLength; i++) {
					if(radioObj[i].checked) {
						return radioObj[i].value;
					}
				}
				return "";
			}
			function loadMap() {
			<?php 
				if (isset($_REQUEST['x1']) && isset($_REQUEST['y1'])) {
					$loc = $s->s_id + $_REQUEST['y1'] + ($s->rows * $_REQUEST['x1']);
					echo 'loadDetail(\'' . $base_url . '\',\'' . $uni . '\',' . $loc . ');';
				}
			?>
			
				updateMap();
				//setTimeout('updateMap()',60000);
			}
			function updateMap() {
				var url = <?php echo '"' . $base_url . '"'; ?> + "/info/map.php";
				var mode = getCheckedValue(document.getElementById('rf').mode);
				var grid = getCheckedValue(document.getElementById('gl').mode);
				var params = "uni=" + <?php echo '"' . $uni . '"'; ?> + "&sector=" + <?php echo '"' . $sector . '"'; ?> + "&img_url=" + <?php echo '"' . $img_url . '"'; ?> + "&shownpc=" + <?php echo '"' . $shownpc . '"'; ?> + "&mode=" + mode + "&grid=" + grid;
				<?php if (isset($_REQUEST['x2']) && isset($_REQUEST['y2'])) { echo 'params += "&loc=" + \'' . $id . '\''; } ?>
				maphttp.open("POST",url,true);
				maphttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				maphttp.setRequestHeader("Content-length", params.length);
				maphttp.setRequestHeader("Pragma", "cache");
				maphttp.setRequestHeader("Connection" , "close");
				
				maphttp.onreadystatechange = function () {
					if (maphttp.readyState == 4) {
						var el = document.getElementById("sectorMapDiv");
						el.innerHTML = maphttp.responseText;
						el.style.width = document.getElementById("sectorTableMap").clientWidth + "px";
						el.style.height = document.getElementById("sectorTableMap").clientHeight + "px";
					}
				}
				maphttp.send(params);
			}
			var maphttp = getXMLHttpObject();
			var overviewhttp = getXMLHttpObject();
			var detailhttp = getXMLHttpObject();
			window.onload = loadMap;
			//]]>
		</script>
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
		<div id="footer"><?php include('include/footer.php'); ?></div>
		<div id="details" name="game"><div id="close_detail"><a href="#" onClick="closeDetail();return false;">Close Detail</a></div><div id="d_con"></div></div>
		<div id="overview" name="game"></div>
		<div id="body">
			<div id="clusterMapDiv" onmouseover="this.style.zIndex=20" onmouseout="this.style.zIndex=0"><?php include('clusters/' . strtolower($cluster) . '.php');?></div>
			<div id="mapSelection">
				<form id="rf" action="">
					<input type="radio" onclick="updateMap()" name="mode" value="all" checked />All
					<input type="radio" onclick="updateMap()" name="mode" value="buildings" />Buildings
					<input type="radio" onclick="updateMap()" name="mode" value="npcs" />NPCs
					<input type="radio" onclick="updateMap()" name="mode" value="none" />None
				</form>
				<form id="gl" action="">
					<input type="radio" onclick="updateMap()" name="mode" value="1" checked />Grid Lines
					<input type="radio" onclick="updateMap()" name="mode" value="0" />No Grid Lines
				</form>
			</div>
			<div id="sectorMapDiv"></div>
		</div>
	</body>
</html>

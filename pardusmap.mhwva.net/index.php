<?php
require_once('include/mysqldb.php');
$db = new mysqldb;

// Set Univers Variable and Session Name
if (!isset($_REQUEST['uni'])) { include('index.html'); exit; }

session_name($uni = $db->protect($_REQUEST['uni']));

session_start();

$testing = Settings::TESTING;

$base_url = 'http://pardusmap.mhwva.net';
if ($testing) { $base_url .= '/TestMap'; }

$css = $base_url . '/main.css';
$index_css = $base_url . '/index.css';
$cluster_css = $base_url . '/cluster.css';

if (isset($_REQUEST['cluster'])) { $cluster = $db->protect($_REQUEST['cluster']); }
$db->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<title>Pardus Image Map</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $index_css; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo $cluster_css; ?>" />
		<script type="text/javascript" src="<?php echo $base_url; ?>/include/main.js"></script>
		<script type="text/javascript">
			function getGemMerchant(uni) {
				var url = <?php echo '"' . $base_url . '"'; ?> + "/info/gemmerchant.php";
				var params = "uni=" + uni;
				xmlhttp.open("POST",url,true);
				xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xmlhttp.setRequestHeader("Content-length", params.length);
				xmlhttp.setRequestHeader("Connection" , "close");
				
				xmlhttp.onreadystatechange = function () {
					if (xmlhttp.readyState == 4) {
						var el = document.getElementById("gem_merchant");
						el.innerHTML = xmlhttp.responseText;
					}
				}
				
				xmlhttp.send(params);
			}
			var xmlhttp = getXMLHttpObject();
			var overviewhttp = getXMLHttpObject();
			<?php if (isset($_REQUEST['gems'])) {
				echo "window.onload = getGemMerchant('" . $uni . "');";
			} ?>
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
		<div id="imgmap">
			<div id="header_side"><?php include('include/header_side.php'); ?></div>
			<div id="imgmap-img" onmouseover="this.style.zIndex=200" onmouseout="this.style.zIndex=0">
				<a href="<?php echo $base_url . '/' . $uni; ?>/FSH" id="fsh"><i>FSH</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/GAP" id="gap"><i>GAP</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/UNR" id="unr"><i>UNR</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/LANE" id="lane"><i>LANE</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/URC" id="urc"><i>URC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/UKC" id="ukc"><i>UKC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/FHC" id="fhc"><i>FHC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/FRC" id="frc"><i>FRC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/NPR" id="npr"><i>NPR</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/WPR" id="wpr"><i>WPR</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/EPR" id="epr"><i>EPR</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/SPLIT" id="split"><i>SPLIT</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/EKC" id="ekc"><i>EKC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/ESC" id="esc"><i>ESC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/EWS" id="ews"><i>EWS</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/SPR" id="spr"><i>SPR</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/CORE" id="puc"><i>PUC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/CORE" id="pfc"><i>PFC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/CORE" id="pec"><i>PEC</i></a>
				<a href="<?php echo $base_url . '/' . $uni; ?>/CORE" id="pc"><i>PC</i></a>
				<a href="<?php echo $base_url; ?>/index.php" id="home"><i>HOME</i></a>
				<a href="http://www.pardus.at" id="pardus"><i>PARDUS</i></a>
			</div>
			<div id="cluster-map">
				<?php if (isset($_REQUEST['cluster'])) { include('clusters/' . strtolower($cluster) . '.php'); } ?>
			</div>
			<div id="gem_merchant"></div>
			<div id="overview" name="gem"></div>
		</div>
		<div id="footer"><center><?php include('include/footer.php'); ?></center></div>
	</body>
</html>

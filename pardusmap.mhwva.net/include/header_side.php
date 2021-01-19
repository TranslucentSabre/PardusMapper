<table id="header_table" border="2" cellspacing="5" cellpadding="5">
	<?php 
		if (isset($_REQUEST['uni'])) { echo '<tr><th><a href="' . $base_url . '/' . $_REQUEST['uni'] . '">' . $_REQUEST['uni'] . '</a></th></tr>'; }
		if (isset($_REQUEST['cluster'])) { echo '<tr><th><a href="' . $base_url . '/' . $_REQUEST['uni'] . '/' . $_REQUEST['cluster'] . '">' . $_REQUEST['cluster'] . '</a></th></tr>'; }
		if (isset($_REQUEST['sector'])) { 
			echo '<tr><th><a href="' . $base_url . '/' . $_REQUEST['uni'] . '/' . $cluster . '">' . $cluster . '</a></th></tr>';
			echo '<tr><th><a href="' . $base_url . '/' . $_REQUEST['uni'] . '/' . $_REQUEST['sector'] . '">' . str_replace(" ","<br />",$_REQUEST['sector']) . '</a></th></tr>';
		}
			
		if (isset($_REQUEST['sector'])) {
			// Set Upkeep Link
			echo '<tr><td><a href="' . $base_url;
			echo (isset($_REQUEST['uni'])) ? '/' . $_REQUEST['uni'] : '';
			echo (isset($_REQUEST['sector'])) ?  '/' . $_REQUEST['sector'] : '';
			echo '/resources">Upkeep<br />Table</a></td></tr>';
		}
					
		// Set NPC Link
		echo '<tr><td><a href="' . $base_url;
		echo (isset($_REQUEST['uni'])) ? '/' . $_REQUEST['uni'] : '';
		echo (isset($_REQUEST['cluster'])) ? '/' . $_REQUEST['cluster'] : '';
		echo (isset($_REQUEST['sector'])) ? '/' . $_REQUEST['sector'] : '';
		echo '/npc">NPC<br />List</a></td></tr>';
						
		// Set Mission Link
		echo '<tr><td><a href="' . $base_url;
		echo (isset($_REQUEST['uni'])) ? '/' . $_REQUEST['uni'] : '';
		echo (isset($_REQUEST['cluster'])) ? '/' . $_REQUEST['cluster'] : '';
		echo (isset($_REQUEST['sector'])) ? '/' . $_REQUEST['sector'] : '';
		echo (isset($_REQUEST['x1']) && isset($_REQUEST['y1'])) ? '/' . $_REQUEST['x1'] . '/' . $_REQUEST['y1'] : '';
		echo '/mission">Mission<br />List</a></td></tr>';
						
		if (isset($_SESSION['security']) && $_SESSION['security'] == 100) {
			// Set Owner Link
			echo '<tr><td><a href="' . $base_url;
			echo (isset($_REQUEST['uni'])) ? '/' . $_REQUEST['uni'] : '';
			echo (isset($_REQUEST['cluster'])) ? '/' . $_REQUEST['cluster'] : '';
			echo (isset($_REQUEST['sector'])) ? '/' . $_REQUEST['sector'] : '';
			echo '/owners">Owner<br />List</a></td></tr>';
		}
		
		if (!(isset($_REQUEST['sector']) || isset($_REQUEST['cluster']))) {
			echo '<tr><td><a href=# onClick="getGemMerchant(\'' . $_REQUEST['uni'] . '\');return false;">Gem<br />Merchant<br />List</a></td></tr>';
		}
		if (!isset($_SESSION['user'])) { echo '<tr><td><a href="' . $base_url . '/' . $_REQUEST['uni']. '/login.php">Log In</a></td></tr>'; }
		else { echo '<tr><td><a href="' . $base_url . '/' . $_REQUEST['uni'] . '/logout.php">Log Out</a></td></tr>'; }
		echo '<tr><td><a href="' . $base_url . '/' . $_REQUEST['uni'] . '/options.php">Options</a></td></tr>';
		echo '<tr><td><a href="' . $base_url . '/' . $_REQUEST['uni']. '/info">News</a></td></tr>';
	?>
</table>

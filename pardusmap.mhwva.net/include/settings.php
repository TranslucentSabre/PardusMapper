<?php

	date_default_timezone_set("UTC");

	class Settings {
		const IMG_DIR = "http://static.pardus.at/img/std/";
		const L_IMG_DIR = "http://pardusmap.mhwva.net/images/";
		const DB_SERVER = "DBServr";
		const DB_USER = "DBUser";
		const DB_PWD = "DBPassword";
		const DB_NAME = "DBName";
		const TESTING = 0;
		const DEBUG = 0;		
	}
	function upkeep($base,$level) {
		return round($base * (1 + .4 * ( $level - 1)));
	}
	function production($base,$level) {
		return round($base * (1 + .5 * ( $level - 1)));
	}

	function compareLoc($x,$y) {
		// Compare X
		if ( $x->x == $y->x) {
			// Compare Y
			if ($x->y == $y->y) { return 0; }
			elseif ( $x->y < $y->y) { return -1; }
			else { return 1; }
		} elseif ($x->x < $y->x) { return -1; }
		else { return 1; }
	}
	function compareLocRev($x,$y) {
		// Compare X
		if ( $x->x == $y->x) {
			// Compare Y
			if ($x->y == $y->y) { return 0; }
			elseif ( $x->y < $y->y) { return 1; }
			else { return -1; }
		} elseif ($x->x < $y->x) { return 1; }
		else { return -1; }
	}
	function compareName($x,$y) {
		if ($x->name == $y->name) { return 0; }
		elseif ($x->name < $y->name) { return -1; }
		else { return 1; }
	}
	function compareNameRev($x,$y) {
		if ($x->name == $y->name) { return 0; }
		elseif ($x->name < $y->name) { return 1; }
		else { return -1; }
	}
	function compareOwner($x,$y) {
		if ($x->owner == $y->owner) { return 0; }
		elseif ($x->owner < $y->owner) { return -1; }
		else { return 1; }
	}
	function compareOwnerRev($x,$y) {
		if ($x->owner == $y->owner) { return 0; }
		elseif ($x->owner < $y->owner) { return 1; }
		else { return -1; }
	}
	function compareAlliance($x,$y) {
		if ($x->alliance == $y->alliance) { return 0; }
		elseif ($x->alliance < $y->alliance) { return -1; }
		else { return 1; }
	}
	function compareAllianceRev($x,$y) {
		if ($x->alliance == $y->alliance) { return 0; }
		elseif ($x->alliance < $y->alliance) { return 1; }
		else { return -1; }
	}
	function compareStock($x,$y) {
		if ($x->stock == $y->stock) { return 0; }
		elseif ($x->stock < $y->stock) { return -1; }
		else { return 1; }
	}
	function compareStockRev($x,$y) {
		if ($x->stock == $y->stock) { return 0; }
		elseif ($x->stock < $y->stock) { return 1; }
		else { return -1; }
	}
	function compareTick($x,$y) {
		if ($x->tick == $y->tick) { return 0; }
		elseif ($x->tick < $y->tick) { return -1; }
		else { return 1; }
	}
	function compareTickRev($x,$y) {
		if ($x->tick == $y->tick) { return 0; }
		elseif ($x->tick < $y->tick) { return 1; }
		else { return -1; }
	}
	function compareSpotted($x,$y) {
		if ($x->spotted == $y->spotted) { return 0; }
		elseif ($x->spotted < $y->spotted) { return -1; }
		else { return 1; }
	}
	function compareSpottedRev($x,$y) {
		if ($x->spotted == $y->spotted) { return 0; }
		elseif ($x->spotted < $y->spotted) { return 1; }
		else { return -1; }
	}
	function compareUpdated($x,$y) {
		if ($x->updated == $y->updated) { return 0; }
		elseif ($x->updated < $y->updated) { return -1; }
		else { return 1; }
	}
	function compareUpdatedRev($x,$y) {
		if ($x->updated == $y->updated) { return 0; }
		elseif ($x->updated < $y->updated) { return 1; }
		else { return -1; }
	}
	function compareCluster($x,$y) {
		if ($x->cluster == $y->cluster) { return 0; }
		elseif ($x->cluster < $y->cluster) {return -1; }
		else { return 1; }
	}
	function compareClusterRev($x,$y) {
		if ($x->cluster == $y->cluster) { return 0; }
		elseif ($x->cluster < $y->cluster) {return 1; }
		else { return -1; }
	}
	function compareAge($x,$y) {
		if ($x->age == $y->age) { return 0; }
		elseif ($x->age < $y->age) { return -1; }
		else { return 1; }
	}
	function compareAgeRev($x,$y) {
		if ($x->age == $y->age) { return 0; }
		elseif ($x->age < $y->age) { return 1; }
		else { return -1; }
	}
	function compareType($x,$y) {
		if ($x->type_img == $y->type_img) { return 0; }
		elseif ($x->type_img < $y->type_img) {return -1; }
		else { return 1; }
	}
	function compareTypeRev($x,$y) {
		if ($x->type_img == $y->type_img) { return 0; }
		elseif ($x->type_img < $y->type_img) {return 1; }
		else { return -1; }
	}
	function compareAmount($x,$y) {
		if ($x->amount == $y->amount) { return 0; }
		elseif ($x->amount < $y->amount) {return -1; }
		else { return 1; }
	}
	function compareAmountRev($x,$y) {
		if ($x->amount == $y->amount) { return 0; }
		elseif ($x->amount < $y->amount) {return 1; }
		else { return -1; }
	}
	function compareTime($x,$y) {
		if ($x->time == $y->time) { return 0; }
		elseif ($x->time < $y->time) {return -1; }
		else { return 1; }
	}
	function compareTimeRev($x,$y) {
		if ($x->time == $y->time) { return 0; }
		elseif ($x->time < $y->time) {return 1; }
		else { return -1; }
	}
	function compareTobject($x,$y) {
		if ($x->t_loc == $y->t_loc) { return 0; }
		elseif ($x->t_loc < $y->t_loc) {return -1; }
		else { return 1; }
	}
	function compareTobjectRev($x,$y) {
		if ($x->t_loc == $y->t_loc) { return 0; }
		elseif ($x->t_loc < $y->t_loc) {return 1; }
		else { return -1; }
	}
	function compareTsector($x,$y) {
		if ($x->t_sector == $y->t_sector) { return 0; }
		elseif ($x->t_sector < $y->t_sector) {return -1; }
		else { return 1; }
	}
	function compareTsectorRev($x,$y) {
		if ($x->t_sector == $y->t_sector) { return 0; }
		elseif ($x->t_sector < $y->t_sector) {return 1; }
		else { return -1; }
	}
	function compareTloc($x,$y) {
		// Compare X
		if ( $x->t_x == $y->t_x) {
			// Compare Y
			if ($x->t_y == $y->t_y) { return 0; }
			elseif ( $x->t_y < $y->t_y) { return -1; }
			else { return 1; }
		} elseif ($x->t_x < $y->t_x) { return -1; }
		else { return 1; }
	}
	function compareTlocRev($x,$y) {
		// Compare X
		if ( $x->t_x == $y->t_x) {
			// Compare Y
			if ($x->t_y == $y->t_y) { return 0; }
			elseif ( $x->t_y < $y->t_y) { return 1; }
			else { return -1; }
		} elseif ($x->t_x < $y->t_x) { return 1; }
		else { return -1; }
	}
	function compareReward($x,$y) {
		if ($x->credits == $y->credits) { return 0; }
		elseif ($x->credits < $y->credits) {return -1; }
		else { return 1; }
	}
	function compareRewardRev($x,$y) {
		if ($x->credits == $y->credits) { return 0; }
		elseif ($x->credits < $y->credits) {return 1; }
		else { return -1; }
	}
	
?>

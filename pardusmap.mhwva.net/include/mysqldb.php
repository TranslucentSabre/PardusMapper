<?php
	require_once("settings.php");

	class mysqldb {

		var $db;
		var $queryID;
		var $a_record;
		var $o_record;

		function connect() {
			$this->db = mysql_connect(
				Settings::DB_SERVER,
				Settings::DB_USER,
				Settings::DB_PWD,
				Settings::DB_NAME
			);
			if (!$this->db) {
				echo mysql_errno() . " : " . mysql_error();
				exit;
			} else {
				$status = mysql_select_db(Settings::DB_NAME, $this->db);
				if (!status) {
					echo mysql_errno() . " : " . mysql_error();
					exit;
				}
			}
		}
		function query($sql) {
			if (empty($this->db)) { $this->connect(); }
			$this->queryID = @mysql_query($sql, $this->db);

			if (!$this->queryID) {
				echo mysql_errno() . " : " . mysql_error();
				exit;
			}
		}
		function nextArray() {
			if (empty($this->db)) { $this->connect(); }
			return @mysql_fetch_assoc($this->queryID);
		}
		function nextObject() {
			if (empty($this->db)) { connect(); }
			return @mysql_fetch_object($this->queryID);
		}
		function nextRow() {
			if (empty($this->db)) { $this->connect(); }
			return @mysql_fetch_row($this->queryID);
		}
		function numRows() {
			if (empty($this->db)) { $this->connect(); }
			return @mysql_num_rows($this->queryID);
		}
		function seek($seek) {
			if (empty($this->db)) { $this->connect(); }
			return @mysql_data_seek($this->queryID,$seek);
		}
		function protect($string) {
			if (empty($this->db)) { $this->connect(); }
			return @mysql_real_escape_string($string);
		}
		function close() {
			if (!empty($this->db)) { mysql_close($this->db); }
		}
		function getSector($id,$sector) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('SELECT * FROM Pardus_Sectors WHERE s_id <= ' . $id . ' ORDER BY s_id DESC LIMIT 1');
			} else {
				$this->query('SELECT * FROM Pardus_Sectors WHERE name = \'' . $sector . '\'');
			}
			return $this->nextObject();
		}
		function getCluster($id,$code) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('SELECT * FROM Pardus_Clusters WHERE c_id = ' . $id);
			} else {
				$this->query('SELECT * FROM Pardus_Clusters WHERE code = \'' . $code . '\'');
			}
			return $this->nextObject();
		}
		function getX($id,$s_id,$rows) {
			return floor(($id - $s_id)/$rows);
		}
		function getY($id,$s_id,$rows,$x) {
			return $id - ($s_id + ($x * $rows));
		}
		function getID($s_id,$rows,$x,$y) {
			return $s_id + ($rows * $x) + $y;		
		}
		function addMap($uni,$image,$id,$sb) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('INSERT INTO ' . $uni . '_Maps (`id`, `bg`, `security`) VALUES (' . $id . ',\'' . $image . '\' , 0)');
				if ($sb) {
					$this->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $sb);
					$b = $this->nextObject();
					$this->query('UPDATE ' . $uni . '_Maps SET cluster = \'' . $b->cluster . '\' WHERE id = ' . $id);
					$this->query('UPDATE ' . $uni . '_Maps SET sector = \'' . $b->sector . '\' WHERE id = ' . $id);
					$x = $this->getX($id,$b->starbase,13);
					$y = $this->getY($id,$b->starbase,13,$x);
					$this->query('UPDATE ' . $uni . '_Maps SET x = ' . $x . ' WHERE id = ' . $id);
					$this->query('UPDATE ' . $uni . '_Maps SET y = ' . $y . ' WHERE id = ' . $id);					
				} else {
					$s = $this->getSector($id,"");
					$c = $this->getCluster($s->c_id,"");
					$this->query('UPDATE ' . $uni . '_Maps SET cluster = \'' . $c->name . '\' WHERE id = ' . $id);
					$this->query('UPDATE ' . $uni . '_Maps SET sector = \'' . $s->name . '\' WHERE id = ' . $id);
					$x = $this->getX($id,$s->s_id,$s->rows);
					$y = $this->getY($id,$s->s_id,$s->rows,$x);
					$this->query('UPDATE ' . $uni . '_Maps SET x = ' . $x . ' WHERE id = ' . $id);
					$this->query('UPDATE ' . $uni . '_Maps SET y = ' . $y . ' WHERE id = ' . $id);
				}
			}
		}
		function addNPC($uni,$image,$id,$sector,$x,$y) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				// Get Sector Info
				$s = $this->getSector($id,"");
				// Get Cluster Info
				$c = $this->getCluster($s->c_id,"");
				// Calculate X and Y of NPC
				$x = $this->getX($id,$s->s_id,$s->rows);
				$y = $this->getY($id,$s->s_id,$s->rows,$x);
			} else {
				// Get Sector Info
				$s = $this->getSector(0,$sector);
				// Get Cluster Info
				$c = $this->getCluster($s->c_id,"");
				// Calculate $id
				$id = $this->getID($s->s_id,$s->rows,$x,$y);
			}
			// Get Default NPC Info
			$this->query('SELECT * FROM Pardus_Npcs WHERE image = \'' . $image . '\'');
			$npc = $this->nextObject();
			$this->query('SELECT * FROM ' . $uni . '_Test_Npcs WHERE id = ' . $id);
			if (!$n = $this->nextObject()) {
				$this->query('INSERT INTO ' . $uni . '_Test_Npcs (`id`) VALUES (' . $id . ')');
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `cluster` = \'' . $c->name . '\' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `sector` = \'' . $s->name . '\' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `cloaked` = NULL WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `x` = \'' . $x . '\' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `y` = \'' . $y . '\' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `name` = \'' . $npc->name . '\' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `image` = \'' . $image . '\' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `hull` = ' . $npc->hull . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `armor` = ' . $npc->armor . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `shield` = ' . $npc->shield . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `spotted` = UTC_TIMESTAMP() WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Maps SET npc = \''. $image . '\' , `npc_cloaked` = NULL, `npc_spotted` = UTC_TIMESTAMP() WHERE id = '. $id);				
			} else {
				if ($n->image != $image) {
					$this->removeNPC($uni,$id);
					$this->addNPC($uni,$image,$id,$sector,$x,$y);
					return;
				}
			}
			$this->query('UPDATE ' . $uni . '_Test_Npcs SET `updated` = UTC_TIMESTAMP() WHERE id = ' . $id);
			$this->query('UPDATE ' . $uni . '_Maps SET `npc_updated` = UTC_TIMESTAMP() WHERE id = '. $id);				
		}
		function removeNPC($uni,$id) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('UPDATE ' . $uni . '_Maps SET `npc` = NULL , `npc_cloaked` = NULL,npc_updated = UTC_TIMESTAMP() WHERE id = ' . $id);
				$this->query('DELETE FROM ' . $uni . '_Test_Npcs WHERE id = ' . $id);
			}
		}
		function updateNPCHealth($uni,$id,$hull,$armor,$shield) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `hull` = ' . $hull . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `armor` = ' . $armor . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `shield` = ' . $shield . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Test_Npcs SET `updated` = UTC_TIMESTAMP() WHERE id = ' . $id);				
				$this->query('UPDATE ' . $uni . '_Maps SET `npc_updated` = UTC_TIMESTAMP() WHERE id = '. $id);				
			}
		}
		function addBuilding($uni,$image,$id,$sb) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('SELECT * FROM ' . $uni. '_Buildings WHERE id = ' . $id);
				if (!$b = $this->nextObject()) { $this->query('INSERT INTO ' . $uni . '_Buildings (`id`,`security`) VALUES (' . $id . ', 0)'); }
				else { $this->removeBuildingStock($uni,$id); }
				if ($sb) {
					$this->query('SELECT * FROM ' . $uni . '_Buildings WHERE id = ' . $id);
					$b = $this->nextObject();
					$x = $this->getX($id,$b->starbase,13);
					$y = $this->getY($id,$b->starbase,13,$x);
					$this->query('UPDATE ' . $uni . '_Buildings SET `cluster` = \'' . $b->cluster . '\' WHERE id = ' . $id);
					$this->query('UPDATE ' . $uni . '_Buildings SET `sector` = \'' . $b->sector . '\' WHERE id = ' . $id);
				} else {
					// Get Sector Info
					$s = $this->getSector($id,"");
					// Get Cluster Info
					$c = $this->getCluster($s->c_id,"");
					// Calculate X and Y of NPC
					$x = $this->getX($id,$s->s_id,$s->rows);
					$y = $this->getY($id,$s->s_id,$s->rows,$x);
					$this->query('UPDATE ' . $uni . '_Buildings SET `cluster` = \'' . $c->name . '\' WHERE id = ' . $id);
					$this->query('UPDATE ' . $uni . '_Buildings SET `sector` = \'' . $s->name . '\' WHERE id = ' . $id);
					$this->addBuildingStock($uni,$image,$id);
				}
				$this->query('UPDATE ' . $uni . '_Buildings SET `x` = ' . $x . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Buildings SET `y` = ' . $y . ' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Buildings SET `image` = \'' . $image . '\' WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Buildings SET `spotted` = UTC_TIMESTAMP() WHERE id = ' . $id);
				$this->query('UPDATE ' . $uni . '_Buildings SET `updated` = UTC_TIMESTAMP() WHERE id = ' . $id);
				
				
				$this->query('UPDATE ' . $uni . '_Maps SET `fg` = \''. $image . '\', `fg_spotted` = UTC_TIMESTAMP(), `fg_updated` = UTC_TIMESTAMP() WHERE id = '. $id);				
			}
		}
		function addBuildingStock($uni,$image,$id) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('SELECT res,upkeep FROM Pardus_Buildings_Data b, Pardus_Upkeep_Data u WHERE b.name = u.name AND b.image = \'' . $image . '\'');
				while ($r = $this->nextObject()) { $res[] = $r; }
				if ($res) {
					foreach ($res as $r) {
						$this->query('INSERT INTO ' . $uni . '_New_Stock (id,name) VALUES (' . $id . ',\'' . $r->res . '\')');
					}
				}
				$this->query('UPDATE ' . $uni . '_Buildings SET stock_updated = UTC_TIMESTAMP() WHERE id = ' . $id);
			}
		}
		function removeBuildingStock($uni,$id) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) { $this->query('DELETE FROM ' . $uni . '_New_Stock WHERE id = ' . $id); }
		}
		function removeBuilding($uni,$id,$sb) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('UPDATE ' . $uni . '_Maps SET `fg` = NULL , `fg_spotted` = UTC_TIMESTAMP(), `fg_updated` = UTC_TIMESTAMP() WHERE id = '. $id);
				$this->query('DELETE FROM ' . $uni .'_Buildings WHERE id = ' . $id);
				$this->query('DELETE FROM ' . $uni .'_New_Stock WHERE id = ' . $id);
			}
			if ($sb) {
				$this->query('DELETE FROM ' . $uni . '_Test_Missions WHERE source_id = ' . $id);
				$this->query('DELETE FROM ' . $uni . '_Squadrons WHERE id = ' . $id);
				//$this->query('DELETE FROM ' . $uni . '_Equipment WHERE id = ' . $id);
			}
		}
		function updateMapFG($uni,$image,$id) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('UPDATE ' . $uni . '_Maps SET `fg` = \''. $image . '\' , `fg_updated` = UTC_TIMESTAMP() WHERE id = '. $id);
			}
		}
		function updateMapBG($uni,$image,$id) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('UPDATE ' . $uni . '_Maps SET `bg` = \''. $image . '\' WHERE id = '. $id);
			}
		}
		function updateMapNPC($uni,$image,$id,$cloaked) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				if ($cloaked) {
					$this->query('UPDATE ' . $uni . '_Maps SET `npc_cloaked` = 1,`npc_updated` = UTC_TIMESTAMP() WHERE id = ' . $id);
					$this->query('UPDATE ' . $uni . '_Test_Npcs SET cloaked = 1, updated = UTC_TIMESTAMP() WHERE id = ' . $id);
				} else {
					$this->query('UPDATE ' . $uni . '_Maps SET npc = \'' . $image . '\' , `npc_cloaked`= NULL, `npc_updated` = UTC_TIMESTAMP() WHERE id = '. $id);
					$this->query('UPDATE ' . $uni . '_Test_Npcs SET cloaked = NULL, updated = UTC_TIMESTAMP() WHERE id = ' . $id);
				}
			}
		}
		function removeMission($uni,$id) {
			if (empty($this->db)) { $this->connect(); }
			if ($id) {
				$this->query('DELETE FROM ' . $uni . '_Test_Missions WHERE id = ' . $id);
			} else {
				$this->query('DELETE FROM ' . $uni . '_Test_Missions WHERE source_id = ' . $id);
			}
		}
	}
?>
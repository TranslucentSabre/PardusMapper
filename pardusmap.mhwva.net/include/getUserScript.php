<?php
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	//print_r($_SERVER);
	die("This HTTP resource is designed to handle POSTed input only");
} elseif ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
	if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  { header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
	elseif($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  { header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
	elseif($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  { header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
	else { die ("You cannot repeat this request"); }
	
	header('Access-Control-Allow-Methods : POST, OPTIONS');
	header('Access-Control-Allow-Headers: x-requested-with');
	header('Cache-Control : no-cache');
	header('Pragma : no-cache');
	header('Content-Type: text/plain');
} elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_SERVER['HTTP_ORIGIN'] == "http://orion.pardus.at")  { header('Access-Control-Allow-Origin: http://orion.pardus.at'); }
	elseif($_SERVER['HTTP_ORIGIN'] == "http://artemis.pardus.at")  { header('Access-Control-Allow-Origin: http://artemis.pardus.at'); }
	elseif($_SERVER['HTTP_ORIGIN'] == "http://pegasus.pardus.at")  { header('Access-Control-Allow-Origin: http://pegasus.pardus.at'); }
	else { die("POSTing Only Allowed from *.pardus.at"); }
	
	header('Access-Control-Allow-Headers: x-requested-with');
	header('Pragma : no-cache');
	header('Cache-Control : no-cache');
	header('Content-Type: text/plain');
 } else { die ("No Other Methods Allowed"); }

$request = filter_input(INPUT_POST,'request',FILTER_SANITIZE_SPECIAL_CHARS);

try {
	switch($request) {
		case 'version' :
			$response = '0.0.1';
			break;
		case 'navversion' :
			$response = '1.0.2';
			break;
		case 'nav' :
			$response = "function trimImage(img) {
					'use strict';
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.'));
				}
				var starbase = 0;
				var transmit = {
					useruni : sessionStorage.universe,
					userid : window.userid,
					userloc : window.userloc
				};
				transmit.map = [];
				var navtds = (document.getElementById('navareatransition')) ? document.getElementById('navareatransition').getElementsByTagName('td') : document.getElementById('navarea').getElementsByTagName('td');
				for (var i = 0; i < navtds.length; i++) {
					var tile = {};
					if (navtds[i].className.match(/navBuilding|navStarbase|navPlanet/)) {
						var children = navtds[i].getElementsByTagName('*');
						tile.id = children[0].getAttribute('onclick');
						tile.id = tile.id.substring(tile.id.indexOf('(') + 1, tile.id.indexOf(')'));
						tile.image = trimImage(children[1].getAttribute('src'));
						if (!starbase && tile.image.substring(0, 2) == 'sb') { starbase = 1; }
					} else if (navtds[i].className.match(/navNpc/)) {
						var children = navtds[i].getElementsByTagName('*');
						tile.id = children[0].getAttribute('onclick');
						tile.id = tile.id.substring(tile.id.indexOf('(') + 1, tile.id.indexOf(')'));
						tile.npc = trimImage(children[1].getAttribute('src'));
					} else if (navtds[i].className.match(/navClear|navShip/)) {
						var children = navtds[i].getElementsByTagName('*');
						if (children.length == 2) {
							tile.id = children[0].getAttribute('onclick');
							tile.id = tile.id.substring(tile.id.indexOf('(') + 1, tile.id.indexOf(')'));
						} else {
							tile.id = children[0].id;
							tile.id = tile.id.substring(2,tile.id.length);
						}
					} else {
						continue;
					}
					transmit.map.push(tile);
				}
				if (starbase) {
					transmit.name= document.getElementById('sector').text();
					transmit.xy = document.getElementById('coords').text();
				}
				sessionStorage.navData = JSON.stringify(transmit);";
			break;
		case 'testnav' :
			$response = "function trimImage(img) {
					'use strict';
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.'));
				}
				var starbase = 0;
				var transmit = {
					useruni : sessionStorage.universe,
					userid : unsafeWindow.userid,
					userloc : unsafeWindow.userloc
				};
				transmit.test = 1;
				transmit.map = [];
				var navtds = (document.getElementById('navareatransition')) ? document.getElementById('navareatransition').getElementsByTagName('td') : document.getElementById('navarea').getElementsByTagName('td');
				for (var i = 0; i < navtds.length; i++) {
					var tile = {};
					if (navtds[i].className.match(/navBuilding|navStarbase|navPlanet/)) {
						var children = navtds[i].getElementsByTagName('*');
						tile.id = children[0].getAttribute('onclick');
						tile.id = tile.id.substring(tile.id.indexOf('(') + 1, tile.id.indexOf(')'));
						tile.image = trimImage(children[1].getAttribute('src'));
						if (!starbase && tile.image.substring(0, 2) == 'sb') { starbase = 1; }
					} else if (navtds[i].className.match(/navNpc/)) {
						var children = navtds[i].getElementsByTagName('*');
						tile.id = children[0].getAttribute('onclick');
						tile.id = tile.id.substring(tile.id.indexOf('(') + 1, tile.id.indexOf(')'));
						tile.npc = trimImage(children[1].getAttribute('src'));
					} else if (navtds[i].className.match(/navClear|navShip/)) {
						var children = navtds[i].getElementsByTagName('*');
						if (children.length == 2) {
							tile.id = children[0].getAttribute('onclick');
							tile.id = tile.id.substring(tile.id.indexOf('(') + 1, tile.id.indexOf(')'));
						} else {
							tile.id = children[0].id;
							tile.id = tile.id.substring(2,tile.id.length);
						}
					} else {
						continue;
					}
					transmit.map.push(tile);
				}
				if (starbase) {
					transmit.name= document.getElementById('sector').text();
					transmit.xy = document.getElementById('coords').text();
				}
				sessionStorage.navData = JSON.stringify(transmit);";
			break;
		case 'binfo' :
			$response = "function trimImage(img) {
					'use strict';
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.'));
				}
				var doc = document;
				var loc = doc.location.href;
				var res = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','50','51','105','150'];
				var transmit = {
					useruni : sessionStorage.universe,
					userid : window.userid,
					userloc : window.userloc
				};
				// Get Building Type
				transmit.type = trimImage($('table').eq(2).find('img').eq(0).attr('src'));
			
				if (loc.match(/building.php/)) {
					var tile = {};
					// Get Building Name
					transmit.name = $('table').eq(2).find('b').eq(0).text();
				
					// Get Building Condition
					tile.condition = $('table').eq(3).find('td').eq(0).attr('width');
				
					// Check to see if Player Building
					if((playerTable = $('table').eq(2).find('td').eq(0).children().eq(8)).is('table')) {
						if (playerTable.find('img').length > 0) {
							tile.faction = trimImage(playerTable.find('img').attr('src'));
						}
						tile.owner = playerTable.find('a').eq(0).text();
						tile.alliance = playerTable.find('a').eq(1).text();
					} else {
						tile.owner = $('table').eq(2).find('td').eq(0).children().eq(8).text();
					}
					transmit.info = tile;
				}
				if (loc.match(/energy_well.php/)) {
					var tile = {};
					transmit.type = transmit.type.replace('_big','');
					var bolds = $('form').find('b');
					tile.charge = bolds.eq(0).text();
					tile.cost = bolds.eq(4).parent().text();
					transmit.special = tile;
				}
				if (loc.match(/building_trade.php/)) {
					transmit.trade = [];
					for (var i = 0; i < res.length ;i++) {
						var brid = 'baserow' + res[i];
						var srid = 'shiprow' + res[i];
						if($('#' + brid).exists()) {
							if (!transmit.free) { 
								transmit.free = $('#' + brid).parents('table').eq(0).find('tr').eq(-2).find('td').eq(1).text().replace(/\,/g,'');
								transmit.credit = $('#' + brid).parents('table').eq(0).find('tr').last().find('td').eq(1).text().replace(/\,/g,'');
							}
							btd = $('#' + brid).find('td');
							std = $('#' + srid).find('td');							
							var tile = { 
								resource : btd[1].textContent, 
								amount : btd[2].textContent.replace(/\,/g,''), 
								min : btd[3].textContent.replace(/\,/g,''),
								max : btd[4].textContent.replace(/\,/g,''),
								buy : btd[5].childNodes[1].textContent.replace(/\,/g,''),
								sell : std[3].childNodes[0].textContent.replace(/\,/g,'') 
							};
							if ($('#' + brid).css('display') == 'none') {
								tile.hide = 1;
							} else {
								tile.hide = 0;
							}
							transmit.trade.push(tile); 
						}
					}
				}
				sessionStorage.bInfo = JSON.stringify(transmit);";
			break;
		case 'obinfo' :
			$response = "function trimImage(img) {
					'use strict';
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.'));
				}
				var doc = document;
				var loc = doc.location.href;
				var res = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','50','51','105','150'];
				var transmit = {
					useruni : sessionStorage.universe,
					userid : window.userid,
					userloc : window.userloc
				};
				// Get Building Type
				transmit.type = trimImage($('table').eq(2).find('img').eq(0).attr('src'));
				// Get Building Name
				transmit.name = $('table').eq(2).find('b').eq(0).text();
			
				if (loc.match(/building_management.php/)) {
					transmit.function = 'bm';
					transmit.free = $('table').eq(2).find('b').eq(2).text();
					transmit.free = transmit.free.substring(transmit.free.lastIndexOf(':') + 2, transmit.free.length);
					transmit.trade = [];
					var stockrows = $('#1_stock').parents('table').eq(0).find('tr');
					var comrows = $('#1_stock').parents('table').eq(1).find('table').eq(1).find('tr');
					for (var i = 1; i < stockrows.length;i++) {
						std = $('td', stockrows[i]);
						var tile = {
							resource : std[1].textContent,
							amount : std[2].textContent,
						};
						transmit.trade.push(tile);
					}
					for (var i = 1; i < comrows.length;i++) {
						ctd = $('td', comrows[i]);
						var tile = {
							resource : ctd[1].textContent,
							amount : ctd[2].textContent,
						};
						transmit.trade.push(tile);
					}
				}
				if (loc.match(/building_trade_settings.php/)) {
					transmit.function = 'bt';
					transmit.userloc = $('form[name=\'tradesettings\']').attr('action');
					transmit.userloc = transmit.userloc.substring(transmit.userloc.lastIndexOf('=')+1,transmit.userloc.length);
					transmit.trade = [];
					for (var i = 0; i < res.length; i++) {
						if ($('input[name=\'' + res[i] + '_amount_min\']').exists()) {
							var tile = {
								resource : $('input[name=\'' + res[i] + '_amount_min\']').parents().eq(0).prev().prev().text(),
								amount : $('input[name=\'' + res[i] + '_amount_min\']').parents().eq(0).prev().text().replace(/\,/g,''),
								min : $('input[name=\'' + res[i] + '_amount_min\']').val().replace(/\,/g,''),
								max : $('input[name=\'' +res[i]i + '_amount_max\']').val().replace(/\,/g,''),
								buy : $('input[name=\'' + res[i] + '_pr_sell\']').val().replace(/\,/g,''),
								sell : $('input[name=\'' +res[i]i + '_pr_buy\']').val().replace(/\,/g,'')
							};
							transmit.trade.push(tile);
						}
					}
				}
				sessionStorage.obInfo = JSON.stringify(transmit);";
			break;
		case 'sinfo' :
			$response =  "
				function trimImage(img) { 
					'use strict'; 
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.')); 
				} 
				var doc = document; 
				var loc = doc.location.href; 
				var transmit = { 
					useruni : sessionStorage.universe, 
					userid : window.userid,
					userloc : window.userloc
				}; 
				// Get Building Type 
				transmit.type = trimImage($('table').eq(2).find('img').eq(0).attr('src'));
				
				if (loc.match(/starbase.php/)) {
					var tile = {};
					// Get Building Name 
					transmit.name = $('table').eq(2).find('span').eq(0).text(); 
					if (transmit.type.match(/sign_uni|sign_fed|sign_emp/)) { 
						tile.faction = transmit.type; 
						transmit.type = trimImage($('table').eq(2).find('img').eq(1).attr('src')); 
					} 
					// Check to see if Player Building 
					if ((playerTable = $('table[cellspacing=3]')).length > 0) { 
						if (playerTable.find('img').length > 0) { 
							tile.faction = trimImage(playerTable.find('img').attr('src')); 
						} 
						tile.owner = playerTable.find('a').eq(0).text(); 
						tile.alliance = playerTable.find('a').eq(1).text(); 
						tile.workers = $('div[style=\'width: 340px; float: left;\']').eq(0).text();
						tile.workers = tile.workers.substring(tile.workers.indexOf(':') + 1, tile.workers.indexOf('|')).replace(/\,/g,'');
						tile.crime = $('div[style=\'width: 340px; float: left;\']').eq(0).find('span').eq(1).text(); 
					} else { 
						tile.workers = $('div[style=\'width: 340px; float: left;\']').eq(0).text();
						tile.workers = tile.workers.substring(tile.workers.indexOf(':') + 1, tile.workers.indexOf('|')).replace(/\,/g,'');
						tile.crime = $('div[style=\'width: 340px; float: left;\']').eq(0).find('span').eq(1).text(); 
					}
					transmit.info = tile;
				} 
				if (loc.match(/starbase_trade.php/)) { 
					transmit.trade = []; 
					// Get Rows for the buying and selling of goods	
					for (var i = 1; i < 35 ;i++) {
						var brid = 'baserow' + i;
						var srid = 'shiprow' + i;
						if($('#' + brid).exists()) {
							if (!transmit.free) { 
								transmit.free = $('#' + brid).parents('table').eq(0).find('tr').eq(-2).find('td').eq(1).text().replace(/\,/g,'');
								transmit.credit = $('#' + brid).parents('table').eq(0).find('tr').last().find('td').eq(1).text().replace(/\,/g,'');
							}
							btd = $('#' + brid).find('td');
							std = $('#' + srid).find('td');							
							var tile = { 
								resource : btd[1].textContent, 
								amount : btd[2].textContent.replace(/\,/g,''), 
								bal : btd[3].textContent.replace(/\,/g,''), 
								sell :  std[3].childNodes[0].textContent.replace(/\,/g,'') 
							}; 
							if (btd.length == 7) {
								tile.max = btd[4].textContent.replace(/\,/g,'');
								tile.buy = btd[5].childNodes[1].textContent.replace(/\,/g,'');
							} else {
								tile.min = btd[4].textContent.replace(/\,/g,'');
								tile.max = btd[5].textContent.replace(/\,/g,''); 
								tile.buy = btd[6].childNodes[1].textContent.replace(/\,/g,'');
							}
							if ($('#' + brid).css('display') == 'none') {
								tile.hide = 1;
							} else {
								tile.hide = 0;
							}
							transmit.trade.push(tile); 
						}
					}
				} 
				sessionStorage.sInfo = JSON.stringify(transmit);";
			break;
		case 'pinfo' :
			$response =  "function trimImage(img) { 
					'use strict'; 
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.')); 
				} 
				var doc = document; 
				var loc = doc.location.href; 
				var res = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','50','51','105','150'];
				var transmit = { 
					useruni : sessionStorage.universe, 
					userid : window.userid,
					userloc : window.userloc
				}; 
				// Get Building Type 
				transmit.type = trimImage($('table').eq(2).find('img').eq(0).attr('src'));
				
				if (loc.match(/planet.php/)) { 
					// Get Planet Name 
					transmit.name = $('table').eq(2).find('span').eq(0).text(); 
					var tile = {};
					if (transmit.type.match(/sign_uni|sign_fed|sign_emp/)) { 
						tile.faction = transmit.type; 
						transmit.type = trimImage($('table').eq(2).find('img').eq(1).attr('src')); 
					} 
					tile.workers = $('div[style=\'width: 340px; float: left;\']').eq(0).text();
					tile.workers = tile.workers.substring(tile.workers.indexOf(':') + 1, tile.workers.indexOf('|')).replace(/\,/g,'');
					tile.crime = $('div[style=\'width: 340px; float: left;\']').eq(0).find('span').eq(1).text();
					transmit.info = tile;
				} 
				if (loc.match(/planet_trade.php/)) { 
					transmit.trade = []; 
					// Get Rows for the buying and selling of goods	
					for (var i = 1; i < 35 ;i++) {
						var brid = 'baserow' + i;
						var srid = 'shiprow' + i;
						if($('#' + brid).exists()) {
							if (!transmit.credit) { 
								transmit.credit = $('#' + brid).parents('table').eq(0).find('tr').last().find('td').eq(1).text().replace(/\,/g,'');
							}
							btd = $('#' + brid).find('td');
							std = $('#' + srid).find('td');							
							var tile = { 
								resource : btd[1].textContent, 
								amount : btd[2].textContent.replace(/\,/g,''), 
								bal : btd[3].textContent.replace(/\,/g,''), 
								sell :  std[3].childNodes[0].textContent.replace(/\,/g,'')
							}; 
							if (btd.length == 7) {
								tile.max = btd[4].textContent.replace(/\,/g,'');
								tile.buy = btd[5].childNodes[1].textContent.replace(/\,/g,'');
							} else {
								tile.min = btd[4].textContent.replace(/\,/g,'');
								tile.max = btd[5].textContent.replace(/\,/g,''); 
								tile.buy = btd[6].childNodes[1].textContent.replace(/\,/g,'');
							}
							transmit.trade.push(tile); 
						}
					}
				} 
				sessionStorage.pInfo = JSON.stringify(transmit);";
			break;
		case 'oinfo' :
			$response = "function trimImage(img) {
					'use strict';	
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.'));
				}
				var doc = document;
				var loc = doc.location.href;
				var transmit = {
					useruni : sessionStorage.universe,
					userid : window.userid,
					userloc : window.userloc
				};
				transmit.nid = $('form').attr('action');
				transmit.nid = transmit.nid.substring(transmit.nid.indexOf('=') + 1);
				var npc_table = $('table[width=\\'50%\\'] td[align=\\'right\\']');
				transmit.name = npc_table.find('b').eq(0).text();
				transmit.type = trimImage(npc_table.find('img').eq(0).attr('src'));
				transmit.length = npc_table.find('font').length;
				if (npc_table.find('font').length > 0) {
					transmit.stats = [];
					npc_table.find('font').siblings('table').each(function (index) {
						var tile = {
							stat : npc_table.find('font').eq(index).text(),
							color : npc_table.find('font').eq(index).attr('color'),
							points : $(this).attr('width')
						};
						transmit.stats.push(tile);
					});
				}
				sessionStorage.oInfo = JSON.stringify(transmit);";
			break;
		case 'iinfo' :
			$response = "function trimImage(img) {
					'use strict';	
					return img.substring(img.lastIndexOf('/') + 1, img.lastIndexOf('.'));
				}
				var doc = document;
				var loc = doc.location.href;
				var res = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','50','51','105','150'];
				var transmit = {
					useruni : sessionStorage.universe,
					userid : window.userid,
					userloc : window.userloc
				};
				var sector = doc.getElementsByTagName('h1')[0].textContent;
				transmit.sector = sector.substring(0,sector.indexOf(' Building Index'));
				transmit.date = doc.getElementsByClassName('cached')[0].childNodes[2].textContent;
				transmit.list = [];
				$('tr[onmouseout]').each(function(index) {
					var building = { };
					building.image = trimImage($('td', this).eq(0).find('img').attr('src'));
					building.name = $('td', this).eq(0).find('img').attr('title');
					var c = $('td', this).eq(1).text().split(/[\[,\]]/);
					building.x = c[1];
					building.y = c[2];
					building.owner = $('td', this).eq(2).text();
					$('table', this).each(function(i) {
						if ($('td', this).eq(0).text().match(/selling/)) {
							building.sell = [];
							for (var x = 1;x < $('td',this).length;x++) {
								var tile = {};
								tile.image = trimImage($('td',this).eq(x).find('img').attr('src'));
								var a = $('td',this).eq(x).text().split(' ');
								tile.amount = a[2];
								tile.price = a[3];
								building.sell.push(tile);
							}
						}
						if ($('td', this).eq(0).text().match(/buying/)) {
							building.buy = [];
							for (var x = 1;x < $('td',this).length;x++) {
								var tile = {};
								tile.image = trimImage($('td',this).eq(x).find('img').attr('src'));
								var a = $('td',this).eq(x).text().split(' ');
								tile.amount = a[2];
								tile.price = a[3];
								building.buy.push(tile);
							}
						}
						if ($('td', this).eq(0).text().match(/free capacity/)) {
							building.free = $('td', this).eq(1).text().replace('t','');
						}
						if ($('td', this).eq(0).text().match(/supplied for/)) {
							building.ticks = $('td', this).eq(1).text().replace(' production rounds','');
						}
					});
					transmit.list.push(building);
				});
				sessionStorage.iInfo = JSON.stringify(transmit);";
			break;
		
	}
	echo '1~' . $request . '~' . $response;
} catch (Exception $e) {
	$db->close();
	echo '0~' . $e->getMessage();
}
?>
<?php

$url = 'http://pardusmap.mhwva.net/';
if ($testing) { $url .= 'TestMap/'; }
$url .= $uni . '/';

?>
<table id="clusterMapTable">
<tr>
<td class="blank puc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/r-b-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Quaack">Quaack</a></td>
<td class="blank puc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/r-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Labela">Labela</a></td>
<td class="blank puc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/r-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Cor Caroli">Cor<br />Caroli</a></td>
<td class="blank puc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/b-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Gomeisa">Gomeisa</a></td>
<td class="blank"></td>
</tr>
<tr>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank puc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-rb-lb.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Cebece">Cebece</a></td>
<td class="blank"></td>
</tr>
<tr>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank puc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/rt-b-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Nhandu">Nhandu</a></td>
<td class="blank"></td>
<td class="blank puc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/lt-r.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Etamin">Etamin</a></td>
</tr>
</table>


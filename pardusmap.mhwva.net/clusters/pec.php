<?php

$url = 'http://pardusmap.mhwva.net/';
if ($testing) { $url .= 'TestMap/'; }
$url .= $uni . '/';

?>
<table id="clusterMapTable">
<tr>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank pec" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-b.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Baham">Baham</a></td>
</tr>
<tr>
<td class="blank pec" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-r-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Nari">Nari</a></td>
<td class="blank pec" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/r-b-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Heze">Heze</a></td>
<td class="blank pec" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-r-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Procyon">Procyon</a></td>
<td class="blank pec" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/r-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Menkar">Menkar</a></td>
<td class="blank pec" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Rigel">Rigel</a></td>
</tr>
</table>


<?php

$url = 'http://pardusmap.mhwva.net/';
if ($testing) { $url .= 'TestMap/'; }
$url .= $uni . '/';

?>
<table id="clusterMapTable">
<tr>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank pfc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/r-b.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Fornacis">Fornacis</a></td>
<td class="blank pfc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-r-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Sargas">Sargas</a></td>
</tr>
<tr>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank pfc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-b.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Ras elased">Ras<br />Elased</a></td>
<td class="blank"></td>
</tr>
<tr>
<td class="blank"></td>
<td class="blank pfc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/r-lb.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Dubhe">Dubhe</a></td>
<td class="blank pfc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/t-r-b-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Enif">Enif</a></td>
<td class="blank"></td>
</tr>
<tr>
<td class="blank pfc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/rt-rb-l.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Mintaka">Mintaka</a></td>
<td class="blank"></td>
<td class="blank"></td>
<td class="blank"></td>
</tr>
<tr>
<td class="blank"></td>
<td class="blank pfc" style="background-image: url(<?php echo Settings::L_IMG_DIR; ?>wormhole/lt-r.png);background-repeat:no-repeat;"><a class="slink" href="<?php echo $url; ?>Aya">Aya</a></td>
<td class="blank"></td>
<td class="blank"></td>
</tr>
</table>


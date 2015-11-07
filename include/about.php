<?php
// About pannel
// F. Bardin 12/04/2015
//-----------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

?>
<SCRIPT language="javascript">
$('#detail').hide("slide");
</SCRIPT>
<DIV ID=about>phpMyHue 1.3<BR>
&copy; <A HREF="mailto:bardin.frederic@gmail.com" CLASS=about>F. Bardin</A> 09-2015<BR>
<small>
Bridge IP : <?php echo $bridgeip?><BR>
Current language : <?php echo $lang?>
</small></DIV>

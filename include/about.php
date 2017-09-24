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
<DIV ID=about>phpMyHue 1.6<BR>
&copy; <A HREF="mailto:bardin.frederic@gmail.com" CLASS=about>F. Bardin</A> 12-2016/01-2017<BR>
<small>
Bridge IP : <?php echo $bridgeip?><BR>
Current language : <?php echo $lang?>
<BR><DIV ID=histo>
<H3><?php echo $trs['Histo']?></H3>
<DIV>
<?php
$histo = @file("histo.txt");
foreach ($histo as $num => $line){
	$line = preg_replace("/^(v.*) - /","<B>$1</B> - ",$line);
	echo "$line<BR>\n";
}
?>
</DIV></DIV>
</small></DIV>
<SCRIPT>
$('#histo').accordion({
	collapsible: true,
	heightStyle: "content",
	active: false
});
</SCRIPT>

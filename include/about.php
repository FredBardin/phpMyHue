<?php
//----------------------------------------------
// About pannel
// F. Bardin 12/04/2015
//----------------------------------------------
// 30/09/2017 : add language update capability
//----------------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';
?>
<SCRIPT language="javascript">
$('#detail').hide("slide");
</SCRIPT>
<DIV ID=about>phpMyHue 1.6.1<BR>
&copy; <A HREF="mailto:bardin.frederic@gmail.com" CLASS=about>F. Bardin</A> 04-2015/01-2018<BR>
<small>
<?php echo $trs['Bridge_IP']?> : <?php echo $bridgeip?><BR>
<SPAN CLASS=aligntxt><?php echo $trs['Current_language']?> : </SPAN><?php choose_lang() ?>
<BR><DIV ID=histo>
<SPAN><?php echo $trs['Histo']?></SPAN>
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
<SCRIPT language="javascript">
$("#c_lang").selectmenu({width : 'auto'});
$("#c_lang").on("selectmenuchange", function(){
	location.assign('index.php?updconf=y&lang='+$(this).val());
});
$('#histo').accordion({
	collapsible: true,
	heightStyle: "content",
	active: false
});
</SCRIPT>

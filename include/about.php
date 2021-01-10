<?php
//----------------------------------------------
// About pannel
// F. Bardin 12/04/2015
//----------------------------------------------
// 30/09/2017 : add language update capability
// 12/11/2020 : add new lamp discovery button
//----------------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';
?>
<SCRIPT language="javascript">
$('#detail').hide("slide");
</SCRIPT>
<DIV ID=about>phpMyHue 1.7.4<BR>
&copy; <A HREF="mailto:bardin.frederic@gmail.com" CLASS=about>F. Bardin</A> 04-2015/01-2021<BR>
<small>
<?php echo $trs['Bridge_IP']?> : <?php echo $bridgeip?><BR>
<SPAN CLASS=aligntxt><?php echo $trs['Current_language']?> : </SPAN>
<?php
choose_lang();
if (isset($trs['TranslationAuthor']) & isset($trs['Translation'])){
	echo "<BR>".$trs['Translation']." :<BR>".$trs['TranslationAuthor']."<BR>";
}
?>
<BR><BUTTON ID=discovery><?php echo $trs['NewLampDiscovery']?></BUTTON>
<BR>
<BR><DIV ID=histo>
<SPAN>
<?php 
echo $trs['Histo']; if ($lang != "en"){echo " (english only)";}
?>
</SPAN>
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
$("#discovery").button();
$('#discovery').click(function(){lampDiscovery();});
$('#histo').accordion({
	collapsible: true,
	heightStyle: "content",
	active: false
});
</SCRIPT>

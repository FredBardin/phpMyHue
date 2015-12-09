<?php
// Set the div details for effects
// F. Bardin 2015/12/05
// ------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

@$effect = $_REQUEST['effect']; 

// Get effect file and add colored syntax before display
ini_set('highlight.html', '#FFFFFF;');
$content = highlight_file('effects/'.$effect.'.xml', true);
$content = preg_replace('/&lt;([^&]*)&nbsp;/','&lt;<SPAN CLASS=effect-tag>\1</SPAN>&nbsp;',$content);
$content = preg_replace("/&lt;\/([^&]*)/","&lt;/<SPAN CLASS=effect-tag>\\1</SPAN>",$content);
$content = preg_replace('/&nbsp;([^&"]*)="([^"]*)"/','&nbsp;<SPAN CLASS=effect-attribute>\1</SPAN>=<SPAN CLASS=effect-value>"\2"</SPAN>',$content);

echo $content;

echo "<BR><BUTTON ID=rundbg>".$trs["Run_in_debug_mode"]."</BUTTON>";

echo "<DIV ID=runout TITLE=\"".$trs["Debug_output"]."\"></DIV>";
?>
<SCRIPT language="javascript">
scrollCurrentTab('#detail');
$("#rundbg").button();
$("#runout").dialog({
  autoOpen: false,
  width : 500,
  height: 500
});
effectsDetail("<?php echo $effect?>");
</SCRIPT>

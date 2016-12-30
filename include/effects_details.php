<?php
// Set the div details for effects
// F. Bardin 2015/12/05
// ------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

@$effect = $_REQUEST['effect']; 

// Parse effect file as code
ini_set('highlight.html', '#FFFFFF;');
$content = highlight_file('effects/'.$effect.'.xml', true);
// Color comments
$content = preg_replace("/(&lt;!--[^-]*--&gt;)/",'<SPAN CLASS=effect-comment>\1</SPAN>',$content);
// Color start tags
$content = preg_replace('/&lt;([^&!]*)&nbsp;/','&lt;<SPAN CLASS=effect-tag>\1</SPAN>&nbsp;',$content);
// Color end tags
$content = preg_replace("/&lt;\/([^&]*)/","&lt;/<SPAN CLASS=effect-tag>\\1</SPAN>",$content);
// Color attributes and values
$content = preg_replace('/&nbsp;([^&"]*)="([^"]*)"/','&nbsp;<SPAN CLASS=effect-attribute>\1</SPAN>=<SPAN CLASS=effect-value>"\2"</SPAN>',$content);

// Replace inner &nbsp; by a space to allow automatic wordwrap
$content = preg_replace('/([^;])&nbsp;([^&])/','\1 \2',$content);

echo $content;

echo "<BR><BUTTON ID=rundbg>".$trs["Launch_with_execution_trace"]."</BUTTON>";

echo "<DIV ID=runout title=\"".$trs["Execution_trace"]."\"></DIV>";
?>
<SCRIPT language="javascript">
scrollCurrentTab('#detail');
$("#rundbg").button();
$("#runout").dialog({
  closeText: trs.Close,
  autoOpen: false,
  width : 500,
  height: 500
});
effectsDetail("<?php echo $effect?>");
</SCRIPT>

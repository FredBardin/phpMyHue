<?php
//============================================================================
// Index phpMyHue
//----------------------------------------------------------------------------
// F. Bardin  06/02/2015
// 12/09/2015 : add init_conf
//============================================================================
// Anti-hack
define('ANTI_HACK', true);

// Catch current git branch to display in title if not master
@list($ref,$dir,$cur_branch) = explode("/",trim(@file(".git/HEAD")[0]));
if ($cur_branch == "master"){$cur_branch = "";}
else {$cur_branch = " (".$cur_branch.")";}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
<TITLE>phpMyHue<?php echo $cur_branch?></TITLE>
</HEAD>
<LINK REL="stylesheet" TYPE="text/css" HREF="js/jquery-ui/jquery-ui.min.css">
<LINK REL="stylesheet" TYPE="text/css" HREF="js/nouislider/jquery.nouislider.min.css">
<LINK REL="stylesheet" TYPE="text/css" HREF="js/nouislider/jquery.nouislider.pips.min.css">
<LINK REL="stylesheet" TYPE="text/css" HREF="js/jquery-minicolors/jquery.minicolors.css">
<LINK REL="stylesheet" TYPE="text/css" HREF="themes/style.css">
<SCRIPT TYPE="text/javascript" SRC="js/jquery-ui/external/jquery/jquery.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="js/jquery-ui/jquery-ui.min.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="js/nouislider/jquery.nouislider.all.min.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="js/jquery-minicolors/jquery.minicolors.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="js/functions.js"></SCRIPT>
<BODY>
<DIV ID=msg></DIV>
<DIV ID=title><IMG SRC="img/phpmyhue.png"></DIV>
<DIV ID=page>

<?php
// Init env
include "include/init_conf.php";

// Load translations (lf+cr+<+>+' are removed)
$trs_json = preg_replace("/[\n\r<>']/","", implode(file('lang/text_'.$lang.'.json')));
$trs = json_decode($trs_json,true);
?>

<!-- left pane -->
<DIV ID=sel>
<DIV ID=tabs>
<UL>
	<LI><A HREF="main.php?rt=lights" TITLE="<?php echo $trs["Lights_&_Groups"]?>"><SPAN CLASS="tabicon ui-icon ui-icon-lightbulb"></SPAN><?php echo $trs["Lights"]?></A></LI>
	<LI><A HREF="main.php?rt=scenes" TITLE="<?php echo $trs["Scenes_management"]?>"><SPAN CLASS="tabicon ui-icon ui-icon-image"></SPAN><?php echo $trs["Scenes"]?></A></LI>
	<LI><A HREF="main.php?rt=effects" TITLE="<?php echo $trs["Effects_management"]?>"><SPAN CLASS="tabicon ui-icon ui-icon-script"></SPAN><?php echo $trs["Effects"]?></A></LI>
	<LI><A HREF="main.php?rt=rules" TITLE="<?php echo $trs["Rules_management"]?>"><SPAN CLASS="tabicon ui-icon ui-icon-wrench"></SPAN><?php echo $trs["Rules"]?></A></LI>
	<LI><A HREF="main.php?rt=about" TITLE="<?php echo $trs["About_phpMyHue"]?>"><?php echo $trs["About"]?></A></LI>
</UL> 
</DIV><!-- /div tabs -->
</DIV><!-- /div sel -->

<!-- right pane -->
<DIV ID=content>
<DIV ID=detail>
<UL>
	<LI><A HREF="details.php"><?php echo $trs["Selection_details"]?></A></LI>
</UL> 
</DIV><!-- /div detail -->
</DIV><!-- /div content -->

</DIV><!-- div page -->
<SCRIPT>
// Make transalations available in javascript
var trs = jQuery.parseJSON('<?php echo $trs_json?>');

// Enable tooltip if not on mobile (= touch interface)
if (! isMobile.any()){
$(document).tooltip({
	track:true
});
}

// Prepare content depending on selection (=hidden when no selection)
$("#detail").tabs();
$("#detail").hide();
$("#tabs").tabs();
</SCRIPT>
</BODY></HTML>

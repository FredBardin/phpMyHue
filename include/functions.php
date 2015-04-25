<?php
/*=============================
 Functions library for phpMyHue
===============================*/
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include "huecolor.php"; // Function to process color from hue

// -------------------------------------------------------------
// Display brillance slider
// -------------------------------------------------------------
// use 2 <TD> : one for the slide, one to display the value
// -------------------------------------------------------------
// Parameters : id, gnum[, lnum]
// -------------------------------------------------------------
function display_bri_slider($id,$gnum,$lnum=""){
	echo "<TD CLASS=bslider>";
	echo "<DIV ID=$id CLASS=brislider gnum=$gnum";
	if ($lnum != ""){echo " lnum=$lnum";}
	echo "></DIV>";
	echo "<TD><DIV ID=".$id."_val CLASS=bsval></DIV>";
} // display_bri_slider

// -------------------------------------------------------------
// Display checkbox for selecting groups or lights
// -------------------------------------------------------------
// parameters : checkbox ID, prefix id, checkbox class, light id, grp id
// -------------------------------------------------------------
function display_lg_checkbox($id, $prefid="", $class="", $gnum="", $lnum=""){
	echo "<TD>&nbsp;";
	echo "<SPAN ID=".$prefid."cb$id>";
	echo "<INPUT TYPE=checkbox ID=$prefid$id";
	if ($class != ""){
		echo " CLASS=\"$class\"";
		if ($gnum != ""){
			echo " grp=$gnum";
			if ($lnum != ""){echo " lnum=$lnum";}
		}
	}
	echo ">";
	echo "</SPAN>";
} // display_lg_checkbox

// -------------------------------------------------------------
// Display a light row
// -------------------------------------------------------------
// Parameters = light number, group number, prefix id, check box position, brislider
// prefix id = prefix to apply to tag id
// check box position : B/E, Begin/End of light row
// brislider : true/false
// -------------------------------------------------------------
function display_light_row($lnum,$gnum,$prefid="",$cbpos="E",$brislider=false){
	global $HueAPI;

	$unreachable = false;
	if ($HueAPI->info['lights'][$lnum]['state']['reachable'] == ""){$unreachable = true;}

	// Display icon+name
	echo "<TR CLASS=\"light grp$gnum\" lnum=$lnum gnum=$gnum>";
	echo "<TD>";
	if ($cbpos == "B"){display_lg_checkbox($gnum."_$lnum", $prefid, "sellight", $gnum, $lnum);}
	echo "<TD>";
	if (! $unreachable){echo "<A HREF=lights CLASS=switch lnum=$lnum>";}
	display_light($lnum);
	if (! $unreachable){echo "</A>";}
	echo "<TD CLASS=sellight lnum=$lnum><LABEL FOR=".$prefid.$gnum."_$lnum lnum=$lnum>".$HueAPI->info['lights'][$lnum]['name']."</LABEL>";
	if ($cbpos == "E"){display_lg_checkbox($gnum."_$lnum", $prefid, "sellight", $gnum, $lnum);}
	if ($brislider){display_bri_slider($prefid."bs_".$gnum."_$lnum",$gnum,$lnum);}
} // display_light_row

// -------------------------------------------------------------
// Display groups and lights
// -------------------------------------------------------------
// Parameters (all optional) = prefix id, check box position , brislider
// prefix id = prefix to apply tag id
// check box position : B/E, Begin/End of light row
// brislider : optional, if true display a brightness slider at the end of a selected row
// -------------------------------------------------------------
function display_lights_groups($prefid="",$cbpos="E",$brislider=false){
	global $HueAPI;

	echo "<TABLE CLASS=det_table>";
	echo "<THEAD>";
	echo "<TR CLASS=all>";
	echo "<TD>";
	if ($cbpos == "B"){display_lg_checkbox("selall",$prefid);}
	echo "<TD CLASS=label><LABEL FOR=".$prefid."selall>All</LABEL>";
	echo "<TD><BUTTON CLASS=allon>On</BUTTON><BUTTON CLASS=alloff>Off</BUTTON>";
	if ($cbpos == "E"){display_lg_checkbox("selall",$prefid);}
	if ($brislider){display_bri_slider($prefid."bs_selall","all");}

	echo "<TBODY>";
	foreach ($HueAPI->info['groups'] as $gnum => $gval){ // Existing groups
		echo "<TR CLASS=grp gnum=$gnum>";
		echo "<TD><SPAN CLASS=\"grp ui-icon ui-icon-circle-minus\" grp=$gnum open></SPAN>";
		if ($cbpos == "B"){display_lg_checkbox("sg$gnum", $prefid, "selgroup", $gnum);}
		echo "<TD CLASS=label gnum=$gnum><LABEL FOR=".$prefid."sg$gnum>".$gval['name']."</LABEL>";
		echo "<TD><BUTTON CLASS=gron grp=$gnum>On</BUTTON><BUTTON CLASS=groff grp=$gnum>Off</BUTTON>";
		if ($cbpos == "E"){display_lg_checkbox("sg$gnum", $prefid, "selgroup", $gnum);}
		if ($brislider){display_bri_slider($prefid."bs_sg$gnum",$gnum);}
		foreach ($gval['lights'] as $internal => $lnum){display_light_row($lnum,$gnum,$prefid,$cbpos,$brislider);}
	}

	// Lamps without group
	echo "<TR CLASS=grp gnum=other>";
	echo "<TD><SPAN CLASS=\"grp ui-icon ui-icon-circle-minus\" grp=other open></SPAN>";
	if ($cbpos == "B"){display_lg_checkbox("sgother", $prefid, "selgroup", "other");}
	echo "<TD CLASS=label gnum=other><LABEL FOR=".$prefid."sgother>Lamps</LABEL>";
	echo "<TD><BUTTON CLASS=otheron>On</BUTTON><BUTTON CLASS=otheroff>Off</BUTTON>";
	if ($cbpos == "E"){display_lg_checkbox("sgother", $prefid, "selgroup", "other");}
	if ($brislider){display_bri_slider($prefid."bs_sgother","other");}
	foreach ($HueAPI->info['lights'] as $lnum => $lval){if (! isset($lval['grp'])){display_light_row($lnum,"other",$prefid,$cbpos,$brislider);}}
	echo "</DIV>";

	echo "</TABLE>";
} // display_lights_groups
?>

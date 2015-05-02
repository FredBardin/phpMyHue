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
// Parameters : prefix id, id, gnum[, lnum]
// -------------------------------------------------------------
function display_bri_slider($prefid,$id,$gnum,$lnum=""){
	echo "<TD CLASS=bslider>";
	echo "<DIV ID=".$prefid."bs_$id CLASS=brislider gnum=$gnum";
	if ($lnum != ""){echo " lnum=$lnum";}
	echo "></DIV>";
	echo "<TD><DIV ID=".$prefid."bs_".$id."_val CLASS=bsval></DIV>";
} // display_bri_slider

// -------------------------------------------------------------
// Display checkbox for selecting groups or lights
// -------------------------------------------------------------
// parameters : prefix id, checkbox ID, checkbox class, light id, grp id
// -------------------------------------------------------------
function display_lg_checkbox($prefid, $id, $class="", $gnum="", $lnum=""){
	echo "<TD>&nbsp;";
	echo "<SPAN ID=".$prefid."s_$id>";
	echo "<INPUT TYPE=checkbox ID=".$prefid."cb_$id";
	if ($class != ""){
		echo " CLASS=\"$class\"";
		if ($gnum != ""){
			echo " gnum=$gnum";
			if ($lnum != ""){echo " lnum=$lnum";}
		}
	}
	echo ">";
	echo "</SPAN>";
} // display_lg_checkbox

// -------------------------------------------------------------
// Display a light row
// -------------------------------------------------------------
// Parameters = prefix id, light number, group number, check box position, brislider
// prefix id = prefix to apply to tag id
// check box position : B/E, Begin/End of light row
// brislider : true/false
// -------------------------------------------------------------
function display_light_row($prefid,$lnum,$gnum,$cbpos="E",$brislider=false){
	global $HueAPI;

	$lid = $gnum."_$lnum"; // add group to light num to have unique id
	$unreachable = false;
	if ($HueAPI->info['lights'][$lnum]['state']['reachable'] == ""){$unreachable = true;}

	// Display icon+name
	echo "<TR CLASS=\"light grp$gnum\" lnum=$lnum gnum=$gnum>";
	echo "<TD>";
	if ($cbpos == "B"){display_lg_checkbox($prefid, $lid, "sellight", $gnum, $lnum);}
	echo "<TD>";
	if (! $unreachable){echo "<A HREF=lights CLASS=switch lnum=$lnum>";}
	display_light($lnum);
	if (! $unreachable){echo "</A>";}
	echo "<TD CLASS=\"label sellight\" lnum=$lnum><LABEL FOR=".$prefid."cb_$lid lnum=$lnum>".$HueAPI->info['lights'][$lnum]['name']."</LABEL>";
	if ($cbpos == "E"){display_lg_checkbox($prefid, $lid, "sellight", $gnum, $lnum);}
	if ($brislider){display_bri_slider($prefid,$lid,$gnum,$lnum);}
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
	echo "<TR>";
	echo "<TD>";
	if ($cbpos == "B"){display_lg_checkbox($prefid,"all");}
	echo "<TD CLASS=\"label selall\"><LABEL FOR=".$prefid."cb_all>All</LABEL>";
	echo "<TD><BUTTON CLASS=allon>On</BUTTON><BUTTON CLASS=alloff>Off</BUTTON>";
	if ($cbpos == "E"){display_lg_checkbox($prefid,"all");}
	if ($brislider){display_bri_slider($prefid,"all","all");}

	echo "<TBODY>";
	foreach ($HueAPI->info['groups'] as $gnum => $gval){ // Existing groups
		echo "<TR CLASS=grp gnum=$gnum>";
		echo "<TD><SPAN CLASS=\"grp ui-icon ui-icon-circle-minus\" grp=$gnum open></SPAN>";
		if ($cbpos == "B"){display_lg_checkbox($prefid, "sg$gnum", "selgroup", $gnum);}
		echo "<TD CLASS=\"label selgrp\" gnum=$gnum><LABEL FOR=".$prefid."cb_$gnum>".$gval['name']."</LABEL>";
		echo "<TD><BUTTON CLASS=gron gnum=$gnum>On</BUTTON><BUTTON CLASS=groff gnum=$gnum>Off</BUTTON>";
		if ($cbpos == "E"){display_lg_checkbox($prefid, $gnum, "selgroup", $gnum);}
		if ($brislider){display_bri_slider($prefid,$gnum,$gnum);}
		foreach ($gval['lights'] as $internal => $lnum){display_light_row($prefid,$lnum,$gnum,$cbpos,$brislider);}
	}

	// Lamps without group
	echo "<TR CLASS=grp gnum=other>";
	echo "<TD><SPAN CLASS=\"grp ui-icon ui-icon-circle-minus\" grp=other open></SPAN>";
	if ($cbpos == "B"){display_lg_checkbox($prefid, "other", "selgroup", "other");}
	echo "<TD CLASS=\"label selgrp\" gnum=other><LABEL FOR=".$prefid."cb_other>Lamps</LABEL>";
	echo "<TD><BUTTON CLASS=otheron>On</BUTTON><BUTTON CLASS=otheroff>Off</BUTTON>";
	if ($cbpos == "E"){display_lg_checkbox($prefid, "other", "selgroup", "other");}
	if ($brislider){display_bri_slider($prefid,"other","other");}
	foreach ($HueAPI->info['lights'] as $lnum => $lval){if (! isset($lval['grp'])){display_light_row($prefid,$lnum,"other",$cbpos,$brislider);}}
	echo "</DIV>";

	echo "</TABLE>";
} // display_lights_groups
?>

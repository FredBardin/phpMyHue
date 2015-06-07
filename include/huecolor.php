<?php
// Functions to manipulate color with hue
// F. Bardin 07/02/2015
//-----------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

// Test if point xy is within a triangle
// Remark : only works for hue familly lamps 
function checkPointInLampGamut($x,$y,$type){
	// Init triangle gamut
	if ($type == "bulb"){ // hue bulb
		$rx = 0.675; $ry = 0.322;
		$gx = 0.4091; $gy = 0.518;
		$bx = 0.167; $by = 0.04;
	} else { // Friends of hue
		$rx = 0.704; $ry = 0.296;
		$gx = 0.2151; $gy = 0.7106;
		$bx = 0.138; $by = 0.08;
	}

	// Calculation with red point
	$v1x = $gx - $rx; $v1y = $gy - $ry;
	$v2x = $bx - $rx; $v2y = $by - $ry;
	$qx  = $x  - $rx; $qy  = $y  - $ry;

	$s = (($qx * $v2y) - ($qy * $v2x)) / (($v1x * $v2y) - ($v1y * $v2x));
	$t = (($v1x * $qy) - ($v1y * $qx)) / (($v1x * $v2y) - ($v1y * $v2x));

	if (($s >= 0) && ($t >= 0) && ($s + $t <= 1)){return true;}
	else {return false;}

//return accuratePointInTriangle($rx,$ry,$gx,$gy,$bx,$by,$x,$y);
} // checkPointInLampGamut

function side($x1, $y1, $x2, $y2, $x, $y)
{
	return ($y2 - $y1)*($x - $x1) + (-$x2 + $x1)*($y - $y1);
}

function naivePointInTriangle($x1, $y1, $x2, $y2, $x3, $y3, $x, $y)
{
	$checkSide1 = side($x1, $y1, $x2, $y2, $x, $y) >= 0;
	$checkSide2 = side($x2, $y2, $x3, $y3, $x, $y) >= 0;
	$checkSide3 = side($x3, $y3, $x1, $y1, $x, $y) >= 0;
	return $checkSide1 && $checkSide2 && $checkSide3;
}

function pointInTriangleBoundingBox($x1, $y1, $x2, $y2, $x3, $y3, $x, $y)
{
	$EPSILON = 0.001;
	$xMin = min($x1, min($x2, $x3)) - $EPSILON;
    $xMax = max($x1, max($x2, $x3)) + $EPSILON;
    $yMin = min($y1, min($y2, $y3)) - $EPSILON;
    $yMax = max($y1, max($y2, $y3)) + $EPSILON;

    if ( $x < $xMin || $xMax < $x || $y < $yMin || $yMax < $y ){return false;}
    else {return true;}
}

function distanceSquarePointToSegment($x1, $y1, $x2, $y2, $x, $y)
{
    $p1_p2_squareLength = ($x2 - $x1)*($x2 - $x1) + ($y2 - $y1)*($y2 - $y1);
    $dotProduct = (($x - $x1)*($x2 - $x1) + ($y - $y1)*($y2 - $y1)) / $p1_p2_squareLength;
    if ( $dotProduct < 0 )
    {
        return ($x - $x1)*($x - $x1) + ($y - $y1)*($y - $y1);
    }
    else if ( $dotProduct <= 1 )
    {
        $p_p1_squareLength = ($x1 - $x)*($x1 - $x) + ($y1 - $y)*($y1 - $y);
        return $p_p1_squareLength - $dotProduct * $dotProduct * $p1_p2_squareLength;
    }
    else
    {
        return ($x - $x2)*($x - $x2) + ($y - $y2)*($y - $y2);
    }
}

function accuratePointInTriangle($x1, $y1, $x2, $y2, $x3, $y3, $x, $y)
{
    $EPSILON = 0.001;
    $EPSILON_SQUARE = $EPSILON*$EPSILON;

    if (! pointInTriangleBoundingBox($x1, $y1, $x2, $y2, $x3, $y3, $x, $y)){return false;}

    if (naivePointInTriangle($x1, $y1, $x2, $y2, $x3, $y3, $x, $y)){return true;}

    if (distanceSquarePointToSegment($x1, $y1, $x2, $y2, $x, $y) <= $EPSILON_SQUARE){return true;}
    if (distanceSquarePointToSegment($x2, $y2, $x3, $y3, $x, $y) <= $EPSILON_SQUARE){return true;}
    if (distanceSquarePointToSegment($x3, $y3, $x1, $y1, $x, $y) <= $EPSILON_SQUARE){return true;}

    return false;
}

// Convert xy to rgb color
function xyToRGB($x,$y,$bri,$type){
	// Check if point in lamp gamut triangle
/*
	if (checkPointInLampGamut($x,$y,$type)){
		echo "x=$x y=$y OK";
	} else {
		echo "x=$x y=$y KO";
	}
*/

	// Calculate XYZ values
 	$z = 1 - $x - $y;
 	$Y = $bri / 254; // Brightness coeff.
	if ($y == 0){
		$X = 0;
		$Z = 0;
	} else {
 		$X = ($Y / $y) * $x;
 		$Z = ($Y / $y) * $z;
	}

	// Convert to sRGB D65 (official formula on meethue)
 	$r = $X * 3.2406 - $Y * 1.5372 - $Z * 0.4986;
	$g = - $X * 0.9689 + $Y * 1.8758 + $Z * 0.0415;
 	$b = $X * 0.0557 - $Y * 0.204 + $Z * 1.057;

	// Apply reverse gamma correction
    $r = ($r <= 0.0031308 ? 12.92 * $r : (1.055) * pow($r, (1 / 2.4)) - 0.055);
    $g = ($g <= 0.0031308 ? 12.92 * $g : (1.055) * pow($g, (1 / 2.4)) - 0.055);
    $b = ($b <= 0.0031308 ? 12.92 * $b : (1.055) * pow($b, (1 / 2.4)) - 0.055);

	// Calculate final RGB
	$r = ($r < 0 ? 0 : round($r * 255));
	$g = ($g < 0 ? 0 : round($g * 255));
	$b = ($b < 0 ? 0 : round($b * 255));

	$r = ($r > 255 ? 255 : $r);
	$g = ($g > 255 ? 255 : $g);
	$b = ($b > 255 ? 255 : $b);

	// Create a web RGB string
	$RGB = "#".substr("0".dechex($r),-2).substr("0".dechex($g),-2).substr("0".dechex($b),-2);

	return $RGB;
} // xyToRGB

// ------------------------------------------
// Convert RGB to xy color + bri
// parameter : RGB in #XXXXXX format (hexa values)
// return json string : {"x":"xval","y":"yval","bri":"brival"}
// ------------------------------------------
function RGBToXy($RGB,$type="bulb"){
	// Get decimal RGB
	$r = hexdec(substr($RGB,1,2));
	$g = hexdec(substr($RGB,3,2));
	$b = hexdec(substr($RGB,5,2));

	// Calculate rgb as coef
	$r = $r / 255;
	$g = $g / 255;
	$b = $b / 255;

	// Apply gamma correction
	$r = ($r > 0.04055 ? pow(($r + 0.055) / 1.055, 2.4) : ($r / 12.92));
	$g = ($g > 0.04055 ? pow(($g + 0.055) / 1.055, 2.4) : ($g / 12.92));
	$b = ($b > 0.04055 ? pow(($b + 0.055) / 1.055, 2.4) : ($b / 12.92));

	// Convert to XYZ
	$X = $r * 0.649926 + $g * 0.103455 + $b * 0.197109;
 	$Y = $r * 0.234327 + $g * 0.743075 + $b * 0.022598;
 	$Z = $r * 0        + $g * 0.053077 + $b * 1.035763;

	// Calculate xy and bri
	if (($X+$Y+$Z) == 0){
		$x = 0;
		$y = 0;
	} else { // round to 4 decimal max (=api max size)
		$x = round($X / ($X + $Y + $Z),4);	
		$y = round($Y / ($X + $Y + $Z),4);
	}
	$bri = round($Y * 254);
	if ($bri > 254){$bri = 254;}

	// Check if xy is in the gamut
/*
	if (checkPointInLampGamut($x,$y,$type)){
		echo "x=$x y=$y OK";
	} else {
		echo "x=$x y=$y KO";
	}
*/
	// ajust to closest point if not

	return '{"xy": ['.$x.','.$y.'],"bri": '.$bri.'}';
} // RGBToXy

// ------------------------------------------
// Display a ligh with icon and color
// Remark : HueAPI->info must be already set
// ------------------------------------------
function display_light($lnum){
	global $HueAPI, $trs;

	$linfo = &$HueAPI->info['lights'][$lnum];

	// Init color type
	if ($linfo['type'] == "Extended color light"){$type="bulb";}
	else {$type="other";}
	
	// Init on/off + lamp color
	$unreachable = false;
	$popup = "";
	$lstate = &$linfo['state'];
	if ($lstate['on'] == "" || $lstate['reachable'] == ""){
		$onoff = "off";
		$lcolor = "transparent";
		if ($lstate['reachable'] == ""){
			$unreachable = true;
			$popup = " TITLE=\"".$trs["Unreachable"]."\"";
		}
	} else { // light on : get rgb color
		$onoff = "on";
		$lcolor=xyToRGB($lstate['xy']['0'],$lstate['xy']['1'],$lstate['bri'],$type);
	}

	// Init lamp class
	$lclass = "sw".$onoff." ".$linfo['modelid'];

	// ==> Ajouter trt msl si modelid = LLM001
	// cf css --> nécessite peut-être de revoir l'affichage des groupes
	
	// display lamp
	echo "<DIV STYLE=\"background-color:$lcolor;\" CLASS=\"$lclass\"$popup>";
	if ($unreachable){echo "<SPAN CLASS=\"ui-state-focus unreachable\"><SPAN CLASS=\"ui-icon ui-icon-alert unreachable\"></SPAN></SPAN>";}
	echo "</DIV>";
} // display_light
?>

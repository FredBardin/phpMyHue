<?php
// Initialize configuration
//------------------------------------------------
// If not exists (=first install) : create it
// If exists : load it
//------------------------------------------------
// F. Bardin 06/09/2015
//------------------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

$conf_file = "config.php";
$template_file = "config.tpl.php";

// If config file does not exist : copy template
if (! file_exists("include/$conf_file")){
	if (! copy("include/$template_file","include/$conf_file"))
	{
		echo "<B>Fatal Error</B> : Copy template file 'include/$template_file' to 'include/$conf_file' failed.<BR>";
		echo "Try to copy 'include/$template_file' to 'include/$conf_file' manually.";
		die;
	}
	else {echo "Initializing configuration<BR>";}
}

// Read config
include "include/config.php";

// If config not complete : initialize parameters
?>

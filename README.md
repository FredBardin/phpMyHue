# phpMyHue
Php web interface to manage Philips Hue lights in a lan.

## Main Functionnalities
* **Full group management**
* **Scenes update or creation**
* Manage and run **scripted effects** (debug mode available) ([see wiki](https://github.com/FredBardin/phpMyHue/wiki/Effects-scripts)) 
* Set color and/or brightness for a light or several at once
* Copy color settings between lights (copy to, copy from, switch with)
* Switch lights on/off
* Run simple effects
* Fully touch device compatible
* Multi Lang
* **Hue API class available** in 'include/hueapi.php' (see comments in file)
* **Hue cmd web service available** with 'hueapi_cmd.php' (see comments in file)
* Hue effects web service available with 'main.php'
* ...

ie: lights screen in english            
![screenshot](screen1_pmh.jpg)

ie: scenes screen in french            
![screenshot](screen2_pmh.jpg)

ie: effects screen in english            
![screenshot](screen3_pmh.gif)

## Installation
1. Copy 'phpMyHue' directory and its content in your web server.
2. Open a browser on your installation url, it must be something like "http://my_web_server/phpMyHue"
	* Automatic configuration begins : follow configuration informations in your browser

If you're asked to proceed manually because automatic setup failed to complete (ie. : local file writing not allowed from your web server), do as follow :

1. Rename 'include/config.tpl.php' as 'include/config.php'
2. Edit 'include/config.php' and put correct values for '$bridgeip', '$username' and, if needed, for '$lang'.  
	* 'bridgeip' is the ip address of your hue bridge in your lan.  
	* 'username' is a registered user in your hue bridge (cf http://www.developers.meethue.com/documentation/api-core-concepts).  
	* 'lang' references an existing 'include/text_"lang".json' file ('en' by default).  

### Licence
MIT

Includes the following plugins also used with MIT licence : jquery-ui, jquery-minicolors


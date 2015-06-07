# phpMyHue
Php web interface to manage Philips Hue lights in a lan.

## Installation
1. Copy 'phpMyHue' directory and its content in your web server.
2. Rename 'include/config.tpl.php' as 'include/config.php'
3. Edit 'include/config.php' and put correct values for '$bridgeip', '$username' and, if needed, for '$lang'.  
'bridgeip' is the ip address of your hue bridge in your lan.  
'username' is a registered user in your hue bridge (cf http://www.developers.meethue.com/documentation/api-core-concepts).  
'lang' references an exiting 'include/text_"lang".json' file ('en' by default).  

## Main Functionnalities
* Full group management
* Scenes update or creation
* Set color and/or brightness for a light or several at once
* Copy color settings between lights (copy to, copy from, switch with)
* Switch lights on/off
* Run effects
* Hue API class available in 'include/hueapi.php' (see comments in file)
* Fully touch device compatible
* Multi Lang
* ...

ie: lights screen in english language
![screenshot](screen1_pmh.jpg)

ie: scenes screen in french language
![screenshot](screen2_pmh.jpg)

### Licence
MIT

Includes the following plugins also used with MIT licence : jquery-ui, jquery-minicolors


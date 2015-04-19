# phpMyHue
Php web interface to manage Philips Hue lights in a lan.

# Installation
- Copy 'phpMyHue' directory and its content in your web server.
- Rename 'include/config.tpl.php' as 'include/config.php'
- Edit 'include/config.php' and put correct values for '$bridgeip' and '$username'.
brigeip is the ip address of your hue bridge in your lan.
username is a registered user in your hue bridge (cf http://www.developers.meethue.com/documentation/api-core-concepts).

# Main Functionnalities
- Full group management
- Scenes update or creation
- Copy color settings between lights (copy to, copy from, switch)
- Set color and/or brightness for a light or several at once
- Switch lights on/off
- Fully touch device compatible
- ...


# Centreon Weathermap
Network Weathermap module for [Centreon](https://github.com/centreon/centreon) adapted from Cacti's [PHP Weathermap](http://www.network-weathermap.com)

## Requirements
* Centreon >= 22.0.4
* PHP Network Weathermap 0.98-php8 (already included in this source with necessary tweaks)

## Installation
* Copy centreon-weathermap directory into centreon's modules location `/usr/share/centreon/www/modules/`
* Grant write permission to **apache** at `/usr/share/centreon/www/modules/centreon-weathermap/src/configs`

## Similar Projects
* [howardjones/network-weathermap](https://github.com/howardjones/network-weathermap)
* [amousset/php-weathermap-zabbix-plugin](https://github.com/amousset/php-weathermap-zabbix-plugin)

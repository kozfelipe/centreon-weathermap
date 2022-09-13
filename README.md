# Centreon Weathermap
Network Weathermap module for [Centreon](https://github.com/centreon/centreon) adapted from Cacti's [PHP Weathermap](http://www.network-weathermap.com)

## Requirements
* Centreon >= 22.0.4
* PHP Network Weathermap 0.98-php8 (already included in this source with necessary tweaks)

## Installation
* Copy **centreon-weathermap** directory into centreon's modules location `/usr/share/centreon/www/modules/` and set **apache** as its owner for all subfolders
* Grant write permission to **apache** at `/usr/share/centreon/www/modules/centreon-weathermap/src/configs`
* Enable module on Centreon's extension manager

> The module installation script will try to find the localhost (127.0.0.1) in order to insert the weathermap poller service.

## Getting Started
```
Configuration > Weathermap
```
You may create, edit, delete, duplicate, enable/disable maps and groups by centreon's object manager
```
Reporting > Maps > Views
```
You can preview active maps tabbed by groups with filter utility
## Similar Projects
* [howardjones/network-weathermap](https://github.com/howardjones/network-weathermap)
* [amousset/php-weathermap-zabbix-plugin](https://github.com/amousset/php-weathermap-zabbix-plugin)

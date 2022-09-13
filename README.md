# Centreon Weathermap
Network Weathermap module for [Centreon](https://github.com/centreon/centreon) adapted from Cacti's [PHP Weathermap](http://www.network-weathermap.com)

## Requirements
* Centreon >= 22.0.4
* PHP Network Weathermap 0.98-php8 (already included in this source with necessary tweaks)

## Installation
* Copy **centreon-weathermap** directory into centreon's modules location `/usr/share/centreon/www/modules/` and set **apache** as its owner for all subfolders
* Grant write permission to **apache** at `/usr/share/centreon/www/modules/centreon-weathermap/src/configs`
* Grant write permission to **centreon-engine** at `/usr/share/centreon/www/modules/centreon-weathermap/src/output`
* Enable module on Centreon's extension manager

> The module installation script will try to find the localhost (127.0.0.1) in order to insert the weathermap poller service. Export the configuration files to activate this service.

## Getting Started
```
Configuration > Weathermap
```
You may create, edit, delete, duplicate, enable/disable maps and groups by centreon's object manager
```
Reporting > Maps > Views
```
You can preview active maps tabbed by groups with filter utility

## Gallery

<img src='https://user-images.githubusercontent.com/25208457/190007848-092daa65-9eba-4e92-adbe-887f930c1921.png' height=200> &nbsp;
<img src='https://user-images.githubusercontent.com/25208457/190008969-2b45fcf3-0c57-4821-a310-bd122abc9be0.png' height=200> &nbsp;
<img src='https://user-images.githubusercontent.com/25208457/190010041-3cfa8c65-ef72-418f-b411-7a486e6866dc.png' height=200> &nbsp;
<img src='https://user-images.githubusercontent.com/25208457/190010566-123ceb74-732c-4a10-9a73-cf1a57ec32bd.png' height=200> &nbsp;
<img src='https://user-images.githubusercontent.com/25208457/190011758-240b4aef-dea5-41f5-be01-96293962892a.png' height=200> &nbsp;

## Similar Projects
* [howardjones/network-weathermap](https://github.com/howardjones/network-weathermap)
* [amousset/php-weathermap-zabbix-plugin](https://github.com/amousset/php-weathermap-zabbix-plugin)

#!/usr/bin/php
<?php
/*
 * centreon-engine user has to be the owner of output directory
 */

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

require dirname(__FILE__) . '/../../class/centreonDB.class.php';
require_once 'src/lib/Weathermap.class.php';

$pearDB = new CentreonDB();
$weathermap_path = "/usr/share/centreon/www/modules/centreon-weathermap/src";

$DBRESULT = $pearDB->query("SELECT value AS path FROM `centreon`.`options` WHERE `key` LIKE 'rrdtool_path_bin'");
$rrdtool = $DBRESULT->fetchRow();

$DBRESULT = $pearDB->query("SELECT configfile, weathermap_groups.name AS groupname 
	FROM centreon.weathermap_maps 
	JOIN centreon.weathermap_groups ON weathermap_maps.group_id = weathermap_groups.id 
	WHERE active = 1
");
$maps = $DBRESULT->fetchAll();

foreach($maps as $map) {

	print "processing $map[configfile]\n";	
		
	$configfile = "$weathermap_path/configs/$map[configfile].conf";
	$imagefile = "$weathermap_path/output/$map[configfile].png";
	$htmlfile = "$weathermap_path/output/$map[configfile].html";
	
	$wmap = new Weathermap;
	$wmap->context = "cli";
	$wmap->htmlstyle = "overlib";
	$wmap->rrdtool  = $rrdtool['path'];
	$wmap->add_hint("mapgroup", $map['groupname']);
	$wmap->imageuri = "./modules/centreon-weathermap/src/output/$map[configfile].png";
	if($wmap->ReadConfig($configfile)) {
		
		$wmap->ReadData();
		$wmap->DrawMap($imagefile, "$weathermap_path/output/$map[configfile]_thumb.png");
		$wmap->imagefile=$imagefile;
				
		$fd = fopen($htmlfile, 'w') or die('Could not open file');
		fwrite($fd,
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head>');
		if($wmap->htmlstylesheet != '') 
			fwrite($fd, '<link rel="stylesheet" type="text/css" href="'.$wmap->htmlstylesheet.'" />');
		fwrite($fd,'<meta http-equiv="refresh" content="300" /><title>' . $wmap->ProcessString($wmap->title, $wmap) . '</title></head><body>');
		
		fwrite($fd, "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>\n");
		//fwrite($fd, "<script type=\"text/javascript\" src=\"../overlib.js\"><!-- overLIB (c) Erik Bosrup --></script> \n");
			
		fwrite($fd, $wmap->MakeHTML());
		fclose ($fd);
	}
	
}

print date('Y-m-d H:i') . " - " . count($maps) . " processed";
exit(0);

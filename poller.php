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

$DBRESULT = $pearDB->query("SELECT weathermap_maps.name AS map_name, weathermap_groups.name AS group_name, weathermap_maps.id 
	FROM centreon.weathermap_maps 
	JOIN centreon.weathermap_groups ON weathermap_maps.group_id = weathermap_groups.id 
	WHERE active = 1
	ORDER BY last_poll ASC
	LIMIT 200
");
$maps = $DBRESULT->fetchAll();

foreach($maps as $map) {

	print "processing $map[map_name]\n";	
		
	$configfile = "$weathermap_path/configs/$map[id].conf";
	$imagefile = "$weathermap_path/output/$map[id].png";
	$htmlfile = "$weathermap_path/output/$map[id].html";
	
	$wmap = new Weathermap;
	$wmap->context = "cli";
	$wmap->htmlstyle = "overlib";
	$wmap->rrdtool  = $rrdtool['path'];
	$wmap->add_hint("mapgroup", $map['group_name']);
	$wmap->imageuri = "./modules/centreon-weathermap/src/output/$map[id].png";
	if($wmap->ReadConfig($configfile)) {
		
		$wmap->ReadData();
		$wmap->DrawMap($imagefile, "$weathermap_path/output/$map[id]_thumb.png");
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
	
	$pearDB->query("UPDATE weathermap_maps SET last_poll = CURRENT_TIMESTAMP() WHERE id = $map[id]");
	
}

print date('Y-m-d H:i') . " - " . count($maps) . " processed";
exit(0);

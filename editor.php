<?php

#Configuration Files Path
$confpath = __DIR__ . "/src/configs/";

/*
 * Database retrieve information for Map
 */
$query = "SELECT weathermap_maps.name AS map_name, weathermap_groups.name AS group_name, weathermap_maps.id AS id " .
	"FROM weathermap_maps " .
	"LEFT JOIN weathermap_groups ON weathermap_maps.group_id = weathermap_groups.id " .
	"WHERE weathermap_maps.id = '" . $pearDB->escape($_GET['id']) . "'";
$DBRESULT = $pearDB->query($query);
$map = $DBRESULT->fetchRow();

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign('groupname', $map['group_name']);
$tpl->assign('name', $map['map_name']);
$tpl->assign('mapid', $map['id']);

$tpl->display("editor.ihtml");
<?php

#Configuration Files Path
$confpath = __DIR__ . "/src/configs/";

/*
 * Database retrieve information for Map
 */
$query = "SELECT * FROM weathermap_maps " .
	"JOIN weathermap_groups ON weathermap_maps.group_id = weathermap_groups.id " .
	"WHERE weathermap_maps.id = '" . $pearDB->escape($_GET['id']) . "'";
$DBRESULT = $pearDB->query($query);
$map = $DBRESULT->fetchRow();

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign('groupname', $map['name']);
$tpl->assign('configname', $map['configfile']);

$tpl->display("editor.ihtml");
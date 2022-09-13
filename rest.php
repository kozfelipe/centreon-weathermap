<?php

require_once '../../class/centreonDB.class.php';
$pearDB = new CentreonDB();

if($_GET['object'] == 'weathermap_groups') {
	
	header('Content-type: application/json;charset=utf-8');
	
	if($_GET['action'] == 'list') {
		$query = "SELECT id, name AS text FROM weathermap_groups ORDER BY name";
		$DBRESULT = $pearDB->query($query);
		$groups['items'] = array();
		foreach($DBRESULT->fetchAll() as $key => $item) {
			array_push($groups['items'], $item);
		}
		$groups['total'] = count($groups['items']);
		echo json_encode($groups, TRUE);
	}
	if($_GET['action'] == 'defaultValues') {
		$query = "SELECT id, name AS text FROM weathermap_groups WHERE id = ".$pearDB->escape($_GET['id']);
		$DBRESULT = $pearDB->query($query);
		$groups = array();
		foreach($DBRESULT->fetchAll() as $key => $item) {
			array_push($groups, $item);
		}
		echo json_encode($groups, TRUE);
	}
}

if($_GET['object'] == 'weathermap_maps') {
	
	header('Content-type: application/json;charset=utf-8');

	if($_GET['action'] == 'list') {
		
		if(isset($_GET['group_id']))
			$filter = " AND weathermap_groups.id = ".$pearDB->escape($_GET['group_id'])." ";
		
		$query = "SELECT weathermap_maps.id, configfile AS text
			FROM weathermap_maps
			LEFT JOIN weathermap_groups ON weathermap_groups.id = weathermap_maps.group_id
			WHERE active = 1 
			$filter
			ORDER BY configfile";
		$DBRESULT = $pearDB->query($query);
		$groups['items'] = array();
		foreach($DBRESULT->fetchAll() as $key => $item) {
			array_push($groups['items'], $item);
		}
		$groups['total'] = count($groups['items']);
		echo json_encode($groups, TRUE);
	}

}

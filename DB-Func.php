<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
 
if (!isset($centreon)) {
    exit();
}

require_once 'src/lib/Weathermap.class.php';

#Configuration Files Path
$confpath = __DIR__ . "/src/configs/";
$htmlpath = __DIR__ . "/src/output/";

function testMapExistence($name = null)
{
    global $confpath, $pearDB, $form;
	
    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('map_id');
    }
    $query = "SELECT id FROM weathermap_maps WHERE configfile = '" .
		$pearDB->escape(htmlentities($name, ENT_QUOTES, "UTF-8")) . "'";
    $DBRESULT = $pearDB->query($query);
    $item = $DBRESULT->fetchRow();
    # Modif case
    if (
		$DBRESULT->rowCount() >= 1 && 
		$item["id"] == $id && 
		file_exists($confpath . $name . '.conf') && 
		is_readable( $confpath . $name . '.conf' ) 
	) { 
        return true;
    } #Duplicate entry
    elseif (
		$DBRESULT->rowCount() >= 1 && 
		$item["id"] != $id && 
		file_exists($confpath . $name . '.conf') && 
		is_readable( $confpath . $name . '.conf' )
	) {
        return false;
    } else {
        return true;
    }

}

function testMapGroupExistence($name = null)
{
    global $confpath, $pearDB, $form;
	
    $id = null;

    if (isset($form)) {
        $id = $form->getSubmitValue('id');
    }
    $query = "SELECT id FROM weathermap_groups WHERE name = '" .
        $pearDB->escape(htmlentities($name, ENT_QUOTES, "UTF-8")) . "'";
    $DBRESULT = $pearDB->query($query);
    $item = $DBRESULT->fetchRow();
    # Modif case
    if (
		$DBRESULT->rowCount() >= 1 && 
		$item["id"] == $id ) { 
        return true;
    } #Duplicate entry
    elseif (
		$DBRESULT->rowCount() >= 1 && 
		$item["id"] != $id ) {
        return false;
    } else {
        return true;
    }

}

function insertMapGroup($ret = array())
{
	global $form, $pearDB, $oreon;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
	
    $rq = "INSERT INTO weathermap_groups ";
    $rq .= "(name) ";
    $rq .= "VALUES ";
    $rq .= "(' " . trim($pearDB->escape(htmlentities($ret["groupname"], ENT_QUOTES, "UTF-8"))) . "' )";
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(id) as max_id FROM weathermap_groups");
    $map_id = $DBRESULT->fetchRow();

    $fields = array();
    $fields = CentreonLogAction::prepareChanges($ret);
    $oreon->CentreonLogAction->insertLog("weathermap_groups", $map_id['max_id'], $fields['groupname'], 'a', $fields);

    return ($map_id["max_id"]);
}

function insertMapConf($ret = array())
{
    $id = insertMap($ret);
    return ($id);
}

function insertMap($ret = array())
{
    global $form, $pearDB, $oreon, $confpath;

    if (!count($ret)) {
        $ret = $form->getSubmitValues();
    }
	
	$filename = $confpath . trim($pearDB->escape(htmlentities($ret["name"], ENT_QUOTES, "UTF-8"))) . '.conf';
		
	$map = new WeatherMap;
	$map->context = 'editor';
	$map->htmlstyle = 'overlib';
	
	$map->WriteConfig($filename);
	
	if(!isset($ret["group_id"]) || $ret["group_id"] == 0)
		$ret["group_id"] = 1; #default group

    $rq = "INSERT INTO weathermap_maps ";
    $rq .= "(group_id, configfile) ";
    $rq .= "VALUES ";
    $rq .= "(' " . $pearDB->escape(htmlentities($ret["group_id"], ENT_QUOTES, "UTF-8")) . "' ,'" 
	. $pearDB->escape(htmlentities($ret["name"], ENT_QUOTES, "UTF-8")) . "')";
    $DBRESULT = $pearDB->query($rq);
    $DBRESULT = $pearDB->query("SELECT MAX(id) as max_id FROM weathermap_maps");
    $map_id = $DBRESULT->fetchRow();

    $fields = array();
    $fields = CentreonLogAction::prepareChanges($ret);
    $oreon->CentreonLogAction->insertLog("weathermap_maps", $map_id['max_id'], $fields['name'], 'a', $fields);

    return ($map_id["max_id"]);
}

function enableMap($map_id = null, $map_arr = array())
{
    global $pearDB, $centreon;

    if (!$map_id && !count($map_arr)) {
        return;
    }

    if ($map_id) {
        $map_arr = array($map_id => "1");
    }
    foreach ($map_arr as $key => $value) {
        $DBRESULT = $pearDB->query("UPDATE weathermap_maps SET active = '1' WHERE id = '" . intval($key) . "'");
        $DBRESULT2 = $pearDB->query("SELECT configfile FROM `weathermap_maps` WHERE id = '" . intval($key) . "' LIMIT 1");
        $row = $DBRESULT2->fetchRow();
        $centreon->CentreonLogAction->insertLog("weathermap_maps", $key, $row['configfile'], "enable");
    }
}

function disableMap($map_id = null, $map_arr  = array()) 
{
	global $pearDB, $centreon;
	
    if (!$map_id && !count($map_arr)) {
        return;
    }

    if ($map_id) {
        $map_arr = array($map_id => "1");
    }
    foreach ($map_arr as $key => $value) {
        $DBRESULT = $pearDB->query("UPDATE weathermap_maps SET active = '0' WHERE id = '" . intval($key) . "'");
        $DBRESULT2 = $pearDB->query("SELECT configfile FROM `weathermap_maps` WHERE id = '" . intval($key) . "' LIMIT 1");
        $row = $DBRESULT2->fetchRow();
        $centreon->CentreonLogAction->insertLog("weathermap_maps", $key, $row['configfile'], "disable");
    }
}

function deleteMap($maps = array())
{
    global $pearDB, $oreon, $confpath, $htmlpath;
	
    foreach ($maps as $key => $value) {
        $query = "SELECT configfile FROM `weathermap_maps` WHERE `id` = '" .
            $pearDB->escape($key) . "' LIMIT 1";
        $DBRESULT2 = $pearDB->query($query);
        $row = $DBRESULT2->fetchRow();

		$filename = $confpath . $row["configfile"] . '.conf';
		
		if(is_file($filename)) 
			unlink($filename);
		
		@unlink($htmlpath . $row["configfile"] . '.html');
		@unlink($htmlpath . $row["configfile"] . '.png');
		
        $pearDB->query("DELETE FROM weathermap_maps WHERE id = '" . $pearDB->escape($key) . "'");
        $oreon->CentreonLogAction->insertLog("weathermap_maps", $key, $row['configfile'], "d");
    }
}

function deleteMapGroup($mapgroups = array())
{
    global $pearDB, $oreon, $confpath;

    foreach ($mapgroups as $key => $value) {
		$query = "SELECT name FROM `weathermap_groups` WHERE `id` = '" .
            $pearDB->escape($key) . "' LIMIT 1";
        $DBRESULT2 = $pearDB->query($query);
        $row = $DBRESULT2->fetchRow();
		
        $pearDB->query("DELETE FROM weathermap_groups WHERE id = '" . $pearDB->escape($key) . "'");
        $oreon->CentreonLogAction->insertLog("weathermap_groups", $key, $row['name'], "d");
    }
}

function updateMap($id = null)
{
    global $confpath, $form, $pearDB, $oreon;

    if (!$id) {
        return;
    }
	
	$DBRESULT = $pearDB->query("SELECT * FROM weathermap_maps WHERE id = " . $pearDB->escape($id));
	$map = $DBRESULT->fetch();
	$DBRESULT->closeCursor();

    $ret = array();
    $ret = $form->getSubmitValues();

    $rq = "UPDATE weathermap_maps ";
    $rq .= "SET configfile = '" . $pearDB->escape(htmlentities($ret["name"], ENT_QUOTES, "UTF-8")) . "', ";
    $rq .= "group_id = " . $pearDB->escape(htmlentities($ret["group_id"], ENT_QUOTES, "UTF-8")) . " ";
    $rq .= "WHERE id = '" . $pearDB->escape($id) . "'";
    $pearDB->query($rq);

    $fields = CentreonLogAction::prepareChanges($ret);
    $oreon->CentreonLogAction->insertLog("weathermap_maps", $id, $fields["name"], "c", $fields);

	if($map["configfile"] != $ret["name"])
		rename($confpath . $pearDB->escape(htmlentities($map["configfile"], ENT_QUOTES, "UTF-8")) . '.conf', $confpath . $pearDB->escape(htmlentities($ret["name"], ENT_QUOTES, "UTF-8") . '.conf'));

}

function updateMapGroup($id = null)
{
    global $confpath, $form, $pearDB, $oreon;

    if (!$id) {
        return;
    }

    $ret = array();
    $ret = $form->getSubmitValues();

    $rq = "UPDATE weathermap_groups ";
    $rq .= "SET name = '" . $pearDB->escape(htmlentities($ret["groupname"], ENT_QUOTES, "UTF-8")) . "' ";
    $rq .= "WHERE id = '" . $pearDB->escape($id) . "'";
    $pearDB->query($rq);

    $fields = CentreonLogAction::prepareChanges($ret);
    $oreon->CentreonLogAction->insertLog("weathermap_groups", $id, $fields["groupname"], "c", $fields);
}

function multipleMap($maps = array(), $nbrDup = array())
{
    global $confpath, $pearDB, $oreon;

    foreach ($maps as $key => $value) {
        $query = "SELECT * FROM weathermap_maps WHERE id = '" . $pearDB->escape($key) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        $row["id"] = '';

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "configfile" ? ($name = $value2 = $value2 . "_" . $i) : null;
                $val
                    ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "id") {
                    $fields[$key2] = $value2;
                }
                @$fields["configfile"] = $name;
            }

            if (testMapExistence($name)) {
				
                $val ? $rq = "INSERT INTO weathermap_maps VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $oreon->CentreonLogAction->insertLog("weathermap_maps", $key, $name, "a", $fields);
				
				$filename = $confpath . $pearDB->escape(htmlentities($name, ENT_QUOTES, "UTF-8")) . '.conf';
	
				$map = new WeatherMap;
				$map->context = 'editor';
				
				$map->WriteConfig($filename);
				
				file_put_contents($filename, file_get_contents($confpath . $row["configfile"] . '.conf'));
				
            }
        }
    }
}

function multipleMapGroup($mapgroups = array(), $nbrDup = array())
{
    global $pearDB, $oreon;

    foreach ($mapgroups as $key => $value) {
        $query = "SELECT * FROM weathermap_groups WHERE id = '" . $pearDB->escape($key) . "' LIMIT 1";
        $DBRESULT = $pearDB->query($query);
        $row = $DBRESULT->fetchRow();
        $row["id"] = '';

        for ($i = 1; $i <= $nbrDup[$key]; $i++) {
            $val = null;
            foreach ($row as $key2 => $value2) {
                $key2 == "name" ? ($name = $value2 = $value2 . "_" . $i) : null;
                $val
                    ? $val .= ($value2 != null ? (", '" . $value2 . "'") : ", NULL")
                    : $val .= ($value2 != null ? ("'" . $value2 . "'") : "NULL");
                if ($key2 != "id") {
                    $fields[$key2] = $value2;
                }
                @$fields["name"] = $name;
            }

            if (testMapGroupExistence($name)) {
                $val ? $rq = "INSERT INTO weathermap_groups VALUES (" . $val . ")" : $rq = null;
                $pearDB->query($rq);
                $oreon->CentreonLogAction->insertLog("weathermap_groups", $key, $name, "a", $fields);
            }
        }
    }
}

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

$map_id = filter_var(
    $_GET['map_id'] ?? $_POST['map_id'] ?? null,
    FILTER_VALIDATE_INT
);

/*
 * Module Path
 */
 
global $path;

$path = "./modules/centreon-weathermap/";
 
/*
 * PHP functions
 */

require_once $path . "DB-Func.php";

$select = filter_var_array(
    getSelectOption(),
    FILTER_VALIDATE_INT
);
$dupNbr = filter_var_array(
    getDuplicateNumberOption(),
    FILTER_VALIDATE_INT
);

if (isset($_POST["o1"]) && isset($_POST["o2"])) {
    if ($_POST["o1"] != "") {
        $o = $_POST["o1"];
    }
    if ($_POST["o2"] != "") {
        $o = $_POST["o2"];
    }
}

/* Set the real page */
if (isset($ret2) && is_array($ret2) && $ret2['topology_page'] != "" && $p != $ret2['topology_page']) {
    $p = $ret2['topology_page'];
} elseif (isset($ret) && is_array($ret) && $ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

const MAP_ADD = 'a';
const MAP_MODIFY = 'c';
const MAP_ACTIVATION = 's';
const MAP_MASSIVE_ACTIVATION = 'ms';
const MAP_DEACTIVATION = 'u';
const MAP_MASSIVE_DEACTIVATION = 'mu';
const MAP_DUPLICATION = 'm';
const MAP_DELETION = 'd';
const MAP_EDITOR = 'e';

switch ($o) {
    case MAP_ADD:
        require_once($path . "formMaps.php");
        break; #Add a Map
    case MAP_MODIFY:
        require_once($path . "formMaps.php");
        break; #Modify a Map
    case MAP_DUPLICATION:
        multipleMap(isset($select) ? $select : array(), $dupNbr);
        require_once($path . "listMaps.php");
        break; #Duplicate n Maps
    case MAP_DELETION:
        deleteMap(isset($select) ? $select : array());
        require_once($path . "listMaps.php");
        break; #Delete n Maps
	case MAP_DEACTIVATION:
        disableMap($map_id);
        require_once($path."listMaps.php");
        break; #Desactivate a Map
	case MAP_MASSIVE_DEACTIVATION:
        disableMap(null, isset($select) ? $select : array());
        require_once($path."listMaps.php");
        break;
	case MAP_ACTIVATION:
        enableMap($map_id);
        require_once($path."listMaps.php");
        break; #Activate a Map
	case MAP_MASSIVE_ACTIVATION:
        enableMap(null, isset($select) ? $select : array());
        require_once($path."listMaps.php");
        break;
	case MAP_EDITOR:
        require_once($path . "editor.php");
        break; #Editor
    default:
        require_once($path . "listMaps.php");
        break;
}
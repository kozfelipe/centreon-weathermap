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

/*
 * Database retrieve information for Map
 */

function decodeMap($arg) {
    $arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
    return ($arg);
}

$map = array();
if ($o === MAP_MODIFY && isset($map_id)) {

	$statement = $pearDB->prepare(
        'SELECT * FROM weathermap_maps WHERE id = :map_id LIMIT 1'
    );
    $statement->bindValue(':map_id', $map_id, \PDO::PARAM_INT);
    $statement->execute();
	
	// Set base value
    $map_list = $statement->fetch();
    $map = array_map("myDecode", $map_list);
	
}

$attrGroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './modules/centreon-weathermap/rest.php?object=weathermap_groups&action=list',
    'multiple' => false,
);

if ($o === MAP_MODIFY && isset($map_id)) {
	$route = './modules/centreon-weathermap/rest.php?object=weathermap_groups&action=defaultValues&id='.$map['group_id'];
		$attrGroups1 = array_merge(
		$attrGroups,
		array('defaultDatasetRoute' => $route)
	);
}
else
	$attrGroups1 = $attrGroups;

#
## Form begin
#

$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);

if ($o === MAP_ADD) {
    $form->addElement('header', 'title', _("Add Map"));
} elseif ($o === MAP_MODIFY) {
    $form->addElement('header', 'title', _("Modify Map"));
} 

#
## Map information
#
$form->addElement('text', 'name', _("Name"), array("size" => "30"));
$form->addElement('select2', 'group_id', _("Group"), array(), $attrGroups1);

#
## Further informations
#
$form->addElement('hidden', 'map_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */

$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('name', _("Compulsory Name"), 'required');
$form->addRule('group_id', _("Compulsory Name"), 'required');
$form->registerRule('exist', 'callback', 'testMapExistence');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<i style='color: red;'>*</i>&nbsp;" . _("Required fields"));

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);
$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", ' .
    'TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, ' .
    '-300, SHADOW, true, TEXTALIGN, "justify"'
);

# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

# Modify a Map 
if ($o === MAP_MODIFY) {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults(array('name' => $map['name'], 'map_id' => $map['id']));
} # Add a Map
elseif ($o === MAP_ADD) {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $MapObj = $form->getElement('map_id');
    if ($form->getSubmitValue("submitA")) {
        $MapObj->setValue(insertMapConf());
    } elseif ($form->getSubmitValue("submitC")) {
        updateMap($MapObj->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path . "listMaps.php");
} else {
    ##Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<i style="color: red;" size="1">*</i>');
    $renderer->setErrorTemplate('<i style="color: red;">{$error}</i><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formMaps.ihtml");
}

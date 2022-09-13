<?php

$id = null;
if(isset($_GET['id']))
	$id = $_GET['id'];
if(isset($_POST['id']))
	$id = $_POST['id'];

/*
 * Database retrieve information for Map
 */

function decodeMap($arg)
{
    $arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
    return ($arg);
}

$mapgroup = array();
if (($o == "c") && $id) {
    $query = "SELECT * FROM weathermap_groups WHERE id = " . $pearDB->escape($id);
    $DBRESULT = $pearDB->query($query);
    # Set base value
    $mapgroup = array_map("decodeMap", $DBRESULT->fetchRow());
    $DBRESULT->closeCursor();
}

##########################################################
# Var information to format the element
#

$attrsText = array("size" => "55");

#
## Form begin
#
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add Map Group"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify Map Group"));
} 

#
## Map information
#
$form->addElement('text', 'groupname', _("Group Name"), $attrsText);

#
## Further informations
#
$form->addElement('hidden', 'id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

#
## Form Rules
#
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('groupname', _("Compulsory Name"), 'required');
$form->registerRule('exist', 'callback', 'testMapGroupExistence');
$form->addRule('groupname', _("Name is already in use"), 'exist');
$form->setRequiredNote("<i style='color: red;'>*</i>&nbsp;" . _("Required fields"));

#
##End of form definition
#

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
if ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults(array('groupname' => $mapgroup['name'], 'id' => $mapgroup['id']));
} # Add a Map
elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if ($form->validate()) {
    $MapObj = $form->getElement('id');
    if ($form->getSubmitValue("submitA")) {
        $MapObj->setValue(insertMapGroup());
    } elseif ($form->getSubmitValue("submitC")) {
        updateMapGroup($MapObj->getValue());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once($path . "listMapGroups.php");
} else {
    ##Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<i style="color: red;" size="1">*</i>');
    $renderer->setErrorTemplate('<i style="color: red;">{$error}</i><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formMapGroups.ihtml");
}

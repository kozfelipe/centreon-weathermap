<?php

/*
 * Copyright 2005-2019 Centreon
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

include_once './class/centreonUtils.class.php';
require_once './include/common/autoNumLimit.php';

$SearchTool = null;
$search = null;
$group = 0;
$status = -1;

/*
 * Search Filter
 */

if (isset($_POST['searchM'])) {
	$centreon->historySearch[$url] = array();
    $search = $_POST['searchM'];
    $centreon->historySearch[$url]['searchM'] = $search;
} elseif (isset($_GET['searchM'])) {
	$centreon->historySearch[$url] = array();
    $search = $_GET['searchM'];
    $centreon->historySearch[$url] = $search;
} elseif (isset($centreon->historySearch[$url]['searchM'])) {
    $search = $centreon->historySearch[$url]['searchM'];
}

if ($search) {
    $SearchTool = " WHERE (name LIKE '%" . htmlentities($search, ENT_QUOTES, "UTF-8") . "%')";
}

$query = "SELECT SQL_CALC_FOUND_ROWS *
	FROM weathermap_groups
	$SearchTool 
	ORDER BY name 
	LIMIT " . $num * $limit . ", " . $limit;
$DBRESULT = $pearDB->query($query);

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include("./include/common/checkPagination.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/* Access level */
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Map Group Name"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * List of elements - Depends on different criteria
 */

$form = new HTML_QuickFormCustom('form', 'POST', "?p=" . $p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
for ($i = 0; $item = $DBRESULT->fetchRow(); $i++) {
	$moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[" . $item['id'] . "]");
    $form->addElement('checkbox', "select[" . $item['id'] . "]");
	
	$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" " .
        "maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
        $item['id'] . "]' />";
	
    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => myDecode($item["name"]),
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&id=" . $item['id'],
		"RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign(
    'msg',
    array("addL" => "main.php?p=" . $p . "&o=a", "addT" => _("Add"), "delConfirm" => _("Do you confirm the deletion ?"))
);

/*
 * Toolbar select
 */
?>
    <script type="text/javascript">
        function setO(_i) {
            document.forms['form'].elements['o'].value = _i;
        }
    </SCRIPT>
<?php
$attrs1 = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o1'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        "   setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        "   setO(this.form.elements['o1'].value); submit();} "
);
$form->addElement(
    'select',
    'o1',
    null,
    array(
		null => _("More actions..."), 
		"m" => _("Duplicate"), 
		"d" => _("Delete"),
	),
    $attrs1
);
$form->setDefaults(array('o1' => null));

$attrs2 = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o2'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        "   setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        "   setO(this.form.elements['o2'].value); submit();} "
);
$form->addElement(
    'select',
    'o2',
    null,
    array(
		null => _("More actions..."), 
		"m" => _("Duplicate"), 
		"d" => _("Delete"),
	),
    $attrs2
);
$form->setDefaults(array('o2' => null));

$o1 = $form->getElement('o1');
$o1->setValue(null);
$o1->setSelected(null);

$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchM', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listMapGroups.ihtml");
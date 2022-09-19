<?php

if (!isset($centreon)) {
    exit();
}

include_once './class/centreonUtils.class.php';

const VIEW_MAP = 'v';
const LIST_MAPS = 'l';

if (isset($_GET["o"]) && $_GET["o"] == 'v' && isset($_GET["id"])) {
    $o = VIEW_MAP;
}
else {
	$o = LIST_MAPS;
}

switch ($o) {
	case VIEW_MAP:
		$DBRESULT = $pearDB->query("SELECT * FROM weathermap_maps WHERE id = " . $pearDB->escape($_GET['id']) . " AND active = 1");
		$map = $DBRESULT->fetchRow();
		$map['content'] = file_get_contents( "./modules/centreon-weathermap/src/output/$map[id].html");
		$tpl = new Smarty();
		$tpl = initSmartyTpl(__DIR__, $tpl);
		$tpl->assign('map', $map);
		$tpl->display("map.ihtml");		
        break;
	case LIST_MAPS:
    default:
		$dbResult = $pearDB->query("SELECT id, name FROM weathermap_groups ORDER BY name");
		$tabs = array();
		$filters = array();
		$form = new HTML_QuickFormCustom('form', 'post', "?p=".$p);
		$i = 1;
		
		/*
		 * Smarty template Init
		 */
		$tpl = new Smarty();
		$tpl = initSmartyTpl(__DIR__, $tpl);

		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		while ($group = $dbResult->fetch()) {
			
			$dataset = './modules/centreon-weathermap/rest.php?object=weathermap_maps&action=list&group_id=' . $group['id'];
			$attrMap = array(
				'datasourceOrigin' => 'ajax',
				'availableDatasetRoute' => $dataset,
				'multiple' => false,
			);
			
			$select = $form->addElement('select2', "map_$i", _("Search"), array(), $attrMap);
			
			$select->addJsCallback(
				'change',
				"weathermap_highlight($(this).val())"
			);
			
			$filters[$i] = $select->toHtml();
			
			$tpl->assign("sort" . $group['id'], _($group['name']));
			$group['maps'] = array();
			$maps = $pearDB->query("SELECT * FROM weathermap_maps WHERE group_id = $group[id] AND active = 1 ORDER BY name");
			while ($map = $maps->fetch()) {
				array_push($group['maps'], $map);
			}
			array_push($tabs, $group);
			$i++;
		}
		
		$tpl->assign('filters', $filters);
		$tpl->assign('tabs', $tabs);
		$tpl->display("views.ihtml");
		
        break;
}
?>

<script>
function weathermap_highlight(id) {
	$("select[name*='map']").each(function( index ) {
		$(this).select2('close');
	});
	$('html, body').animate({
		scrollTop: $("#map-anchor-"+id).offset().top
	}, 0);
	$("#map-anchor-"+id).delay(150).animate({
		'background-color': '#10069F'
	}, 350, function () {
		$(this).animate({
			'background-color': 'transparent'
		}, 800);
	});
}
</script>
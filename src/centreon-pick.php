<html>
<head>
	<script src="../../../include/common/javascript/jquery/jquery.min.js"></script>
	<script src="../../../include/common/javascript/jquery/plugins/select2/js/select2.min.js"></script>
	<script type="text/javascript">
	function update_datasource(host_id, service_id, datasource, description) {
		var hoverUrl = "./modules/centreon-weathermap/generateCentreonHoverGraph.php?action=viewgraph&host_id=" + host_id + "&service_id=" + service_id;
		if (typeof window.opener == "object") { 
			opener.document.forms["frmMain"].link_target.value = datasource;
			opener.document.forms["frmMain"].link_hover.value = hoverUrl; 
			opener.document.forms["frmMain"].link_infourl.value = "?p=204&svc_id="+description; 
		} 
		self.close();
	}
	 
	$(document).ready( function () {
		$( 'select[name="host"]' ).select2({
			dropdownAutoWidth : true,
			placeholder : 'Host',
			allowClear: true,
			width: '280px',	
		});
	});
	</script>
	<link type="text/css" rel="stylesheet" href="../../../include/common/javascript/jquery/plugins/select2/css/select2.min.css">
	<style type="text/css">
	body { font-family: sans-serif; font-size: 10pt; }
	.table-ds { border: 1px solid black; width: 100% }
	.table-ds td { padding: 5px;}
	.table-ds tr.row0 { background: #ddd;}
	.table-ds tr.row1 { background: #ccc;}
	.table-ds tr { border-bottom: 1px solid #aaa; border-top: 1px solid #eee; padding: 2px;}
	</style>
	<title>Pick a data source</title>
</head>
<body>
<?php

include_once ("../../../class/centreonDB.class.php");

$pearDB = new CentreonDB("centstorage");

$base_url = "/var/lib/centreon/metrics/";

if (isset($_GET['service_id'])) {
    $sql = "SELECT metrics.metric_id, index_data.host_id FROM metrics, index_data, services WHERE services.service_id=" . $pearDB->escape($_GET['service_id']) . " AND metrics.index_id=index_data.id and index_data.service_id=services.service_id ORDER BY metrics.metric_id";

    $res = $pearDB->query($sql);
    $rows = $res->fetchAll();
	
    $dataSource = "";
    $i = 0;
    foreach ($rows as $row) {
		if (!$i)
			$dataSource .= "'gauge:" . $base_url . $row['metric_id'] . ".rrd:value:- ";
        else
            $dataSource .= "gauge:" . $base_url . $row['metric_id'] . ".rrd:-:value "; 
        $i++;
    }
    $id_host = 0;
    if ($i) {
		$dataSource .= "'";
		$id_host = $rows[0]['host_id'];
    }

    echo "success.<br>";
	echo '<script type="text/javascript">',
     'update_datasource(\'' . $id_host . '\',\'' . $_GET['service_id'] . '\',' .  $dataSource .',\'' .  $_GET['description'] .'\');',
     '</script>';

    echo "</body></html>";
	
    die();
}
?>

<h3>Pick a data source:</h3>
<form name="hosts" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
Host:
<select name='host'>
<?php
$res = $pearDB->query("SELECT host_id, name from hosts WHERE enabled = 1");
$rows = $res->fetchAll();
foreach ($rows as $row)
	echo "<option value=" . $row['host_id'] . ">" . $row['name'] . "</option>";
?>
</select>
   <input type="submit" name="submit" value="Filter" />
</form>

<?php
if (isset($_POST['submit'])) {

	$res = $pearDB->query ("SELECT hosts.name, services.service_id, services.description 
		FROM services, hosts, metrics, index_data 
		WHERE hosts.host_id=services.host_id 
		AND hosts.host_id=" . $pearDB->escape($_POST['host']) . " 
		AND metrics.index_id=index_data.id 
		AND index_data.service_id=services.service_id 
		AND (metrics.unit_name LIKE \"b/s\" OR metrics.metric_name LIKE \"%traffic%\")
		GROUP BY service_id");
	$rows = $res->fetchAll();
	
	echo "<script>$('select[name=\"host\"]').select2().val(" . $_POST['host'] . ").trigger('change');</script>";

	if($rows) {
		echo "<table class='table-ds'>";
		foreach ($rows as $i => $row) {
			echo "<tr class=\"row".($i%2)."\">";
			echo "<td>" . $row['name'] . "</td><td> <a href='centreon-pick.php?service_id=" . $row['service_id'] . "&description=" . urlencode($row['name'] . ";" . $row['description']) . "'>" . $row['description'] . "</a></td>";
			echo "</tr>";
		}
		echo "</table>";
		die();
	}
	else
		echo "no traffic metrics from host";
}
?>

</body>

</html>
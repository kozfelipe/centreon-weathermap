<?php
include_once ("../../class/centreonDB.class.php");

function random_color($resolution) {
     $fixedColors = Array ("#ff0000", "#00ff00", "#0000ff", "#1adbd1", "#a950fc");
      if ($resolution < count ($fixedColors))
         return $fixedColors[$resolution];
      else
         return $fixedColors[0];
}

$pearDB = new CentreonDB("centstorage");
$weathermap_path = "/usr/share/centreon/www/modules/centreon-weathermap/src";

switch($_GET['action']) {
	case 'viewthumb':
		header("Content-type: image/png");
		readfile("$weathermap_path/output/". $pearDB->escape($_GET['id']) ."_thumb.png");
		break;
	case 'viewgraph':
		if (!isset($_GET['host_id']) || !isset($_GET['service_id']))
			die ("Invalid data.");
		
		$sql = "SELECT metrics.metric_id, metrics.metric_name, services.description 
		FROM metrics, index_data, services 
		WHERE index_data.service_id = " . $pearDB->escape($_GET['service_id']) . "
		AND index_data.host_id = " . $pearDB->escape($_GET['host_id']) . "
		AND metrics.index_id = index_data.id 
		AND index_data.service_id = services.service_id 
                AND services.host_id = " . $pearDB->escape($_GET['host_id']) . "
		AND metrics.metric_name LIKE '%traffic%'
		GROUP BY metrics.metric_id
		ORDER BY metrics.metric_id";
		$res = $pearDB->query($sql);
		$rows = $res->fetchAll();
		
		$options = "";
		foreach ($rows as $i => $row) { 
			$rrdfile = "/var/lib/centreon/metrics/" . $row['metric_id'] . ".rrd";
			$lineColor = random_color($i);
			$areaColor = $lineColor . "3f";
			$options .= "'DEF:v$i=$rrdfile:value:AVERAGE' 'AREA:v$i" . $areaColor . "'  'LINE1:v$i" . $lineColor . ":" . $row['metric_name'] . " (b/s)  ' 'VDEF:v" . $i . "Last=v$i,LAST' 'VDEF:v" . $i . "Max=v$i,MAXIMUM'  'VDEF:v" . $i . "Average=v$i,AVERAGE' 'VDEF:v" .  $i . "Min=v$i,MINIMUM' 'GPRINT:v" . $i . "Last:Last\:%7.2lf%s' 'GPRINT:v" . $i . "Min:Min\:%7.2lf%s' 'GPRINT:v" . $i . "Max:Max\:%7.2lf%s'  'GPRINT:v" . $i . "Average:Average\:%7.2lf%s'  'COMMENT:\l' ";
		}

		$service_description = $rows[0]['description'];
		$startTime = time() - (24*60*60);
		$endTime = time();
		$comment = "From: " . date("Y-m-d h:i:s", $startTime) . " to: " .  date("Y-m-d h:i:s", $endTime);
		$comment = str_replace (":", "\:", $comment);

		header("Content-type: image/png");

		$png = system ("/usr/bin/rrdtool graph - --width='550' --height='140' --title='$service_description' --start='$startTime' --end='$endTime' --interlaced --imgformat='PNG' --vertical-label='bits/second' --slope-mode --base='1000' --lower-limit='0' --rigid --alt-autoscale-max --color 'BACK#FFFFFF' --color 'FRAME#FFFFFF' --color 'SHADEA#EFEFEF' --color 'SHADEB#EFEFEF' --color 'ARROW#FF0000' 'COMMENT: $comment \c' $options");

		print $png;
		
		break;
	default:
		exit('error');
}

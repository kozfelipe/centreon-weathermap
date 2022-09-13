<?php
use App\Kernel;

$pearDB = new CentreonDB();

function fetch($sql) {
	global $pearDB;
	$DBRESULT = $pearDB->query($sql);
	return $DBRESULT->fetchRow();
}

$host = fetch("SELECT * FROM centreon.host WHERE host_address = '127.0.0.1' AND host_activate = '1' LIMIT 1");

if(!$host) {
	# insert weathermap host
	$pearDB->query("INSERT INTO centreon.host (host_name, host_alias, host_address, host_register, host_locked, host_active_checks_enabled, host_passive_checks_enabled, host_checks_enabled, host_obsess_over_host, host_check_freshness, host_event_handler_enabled, 	host_flap_detection_enabled, host_retain_status_information, host_retain_nonstatus_information, host_notifications_enabled, host_location) VALUES ('weathermap', 'weathermap', '127.0.0.1', '1', '1', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', NULL)");
	# get main poller
	$poller = fetch("SELECT * FROM centreon.nagios_server WHERE name = 'Central' OR id = (SELECT MIN(id) FROM centreon.nagios_server) LIMIT 1");
	# get weathermap host inserted id
	$host = fetch("SELECT * FROM centreon.host WHERE host_id = ( SELECT MAX(host_id) AS host_id FROM centreon.host WHERE host_address = '127.0.0.1' AND host_activate = '1' )");
	$pearDB->query("INSERT INTO centreon.extended_host_information (host_host_id) VALUES (" . $host['host_id'] . ")");
	# associate weathermap host to main poller
	$pearDB->query("INSERT INTO centreon.ns_host_relation (nagios_server_id, host_host_id) VALUES (" . $poller['id'] . ", " . $host['host_id'] . ")");
}

# check if $PHP$ exists
$resource = fetch("SELECT * FROM centreon.cfg_resource WHERE resource_name = '\$PHP$'");
if(!$resource)
	//$resource['resource_name'] = PHP_BINARY;
	$resource['resource_name'] = 'php';
# create command
//centreon-engine user has to be the owner of output directory
$pearDB->query("INSERT INTO centreon.command (command_name, command_line, command_type, command_locked) VALUES ('weathermap_poller', '" . $resource['resource_name'] . " " . dirname(__FILE__) . "/../poller.php', 2, 1)");
# get command id
$command = fetch("SELECT * FROM centreon.command WHERE command_name = 'weathermap_poller' OR command_id = (SELECT MAX(command_id) FROM centreon.command) LIMIT 1");
# create weathermap service
$pearDB->query("INSERT INTO centreon.service (command_command_id, timeperiod_tp_id, service_description, service_max_check_attempts, service_normal_check_interval, service_retry_check_interval, service_register, service_locked, timeperiod_tp_id2) VALUES (" . $command['command_id'] . ", '1', 'weathermap poller', '3', '1', '1', '1', '1', '1')");
# get service id
$service = fetch("SELECT * FROM centreon.service WHERE service_description = 'weathermap poller' OR service_id = (SELECT MAX(service_id) FROM centreon.service) LIMIT 1");
# associate host to service
$pearDB->query("INSERT INTO centreon.host_service_relation (host_host_id, service_service_id) VALUES (" . $host['host_id'] . ", " . $service['service_id'] . ")");
<?php
use App\Kernel;

$confpath = realpath(dirname(__FILE__)) . '/../src/configs/';
$htmlpath = realpath(dirname(__FILE__)) . '/../src/output/';

# remove config files
$files = glob($confpath . '*');
foreach($files as $file) 
	if(is_file($file)) 
		@unlink($file);
$files = glob($htmlpath . '*');
foreach($files as $file) 
	if(is_file($file)) 
		@unlink($file);

$pearDB = new CentreonDB();

# remove assets
$pearDB->query("DELETE FROM centreon.command WHERE command_name = 'weathermap_poller'");
$pearDB->query("DELETE FROM centreon.host WHERE host_name = 'weathermap'");
$pearDB->query("DELETE FROM centreon.service WHERE service_description = 'weathermap poller'");
$pearDB->query("DELETE FROM centreon_storage.hosts WHERE name = 'weathermap'");
$pearDB->query("DELETE FROM centreon_storage.services WHERE check_command = 'weathermap_poller'");
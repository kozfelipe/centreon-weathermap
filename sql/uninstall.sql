DELETE FROM topology WHERE topology_page = '61101' AND topology_name = 'Maps';
DELETE FROM topology WHERE topology_page = '61102' AND topology_name = 'Map Groups';
DELETE FROM topology WHERE topology_page = '611' AND topology_name = 'Weathermap';
DELETE FROM topology WHERE topology_page = '311' AND topology_name = 'Maps';
DELETE FROM topology WHERE topology_page = '31101' AND topology_name = 'Views';
DROP TABLE IF EXISTS `weathermap_groups`, `weathermap_maps`;
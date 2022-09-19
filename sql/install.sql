--
-- create tables
--

CREATE TABLE `weathermap_groups` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(128) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `weathermap_maps` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `group_id` int(11) NOT NULL DEFAULT '1',
 `active` tinyint(1) NOT NULL DEFAULT '1',
 `name` text NOT NULL,
 `last_poll` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- insert page route
--

INSERT INTO `topology` (`topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`) VALUES
('Weathermap', '6', '611', NULL, '100', NULL),
('Maps', '611', '61101', '1', NULL, './modules/centreon-weathermap/maps.php'),
('Map Groups', '611', '61102', '2', NULL, './modules/centreon-weathermap/mapGroups.php'),
('Maps', '3', '311', NULL, '100', NULL),
('Views', '311', '31101', '1', NULL, './modules/centreon-weathermap/views.php');
INSERT INTO `weathermap_groups` (`id`, `name`) VALUES ('1', 'Weathermaps');
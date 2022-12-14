<?php

/*
 * Copyright 2020 Centreon (http://www.centreon.com/)
 *
 * Centreon is a full-fledged industry-strength solution that meets
 * the needs in IT infrastructure and application monitoring for
 * service performance.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,*
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
$module_conf['centreon-weathermap'] = [
	'name' => "centreon-weathermap",
	'rname' => "Centreon Weathermap",
	'mod_release' => "22.04.0",
	'infos' => "PHP Weathermap for Centreon",
	'is_removeable' => "1",
	'author' => "Luiz Felipe Aranha",
	'stability' => "development",
	'last_update' => "2022-07-19",
	'lang_files' => "0",
	'sql_files' => "1",
	'php_files' => "1",
	'images' => [
		'images/thumb1.png',
		'images/thumb2.png',
		'images/thumb3.png',
		'images/thumb4.png',
	],
];
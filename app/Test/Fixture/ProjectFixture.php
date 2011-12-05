<?php
/* Project Fixture generated on: 2011-11-09 01:58:45 : 1320800325 */

/**
 * ProjectFixture
 *
 */
class ProjectFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'title' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'primary_domain' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'additional_domains' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 2000, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'dev_domain' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'charset' => 'latin1'),
		'use_cache' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'current_version' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'collate' => NULL, 'comment' => ''),
		'screenshot' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'exclude' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'errors' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'reboot_needed_date' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP', 'collate' => NULL, 'comment' => ''),
		'created' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'collate' => NULL, 'comment' => ''),
		'modified' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'collate' => NULL, 'comment' => ''),
		'created_by' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'modified_by' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 'Lorem ipsum dolor sit amet',
			'title' => 'Lorem ipsum dolor sit amet',
			'primary_domain' => 'Lorem ipsum dolor sit amet',
			'additional_domains' => 'Lorem ipsum dolor sit amet',
			'dev_domain' => 'Lorem ipsum dolor sit amet',
			'use_cache' => 1,
			'current_version' => 1,
			'screenshot' => 1,
			'exclude' => 1,
			'errors' => 1,
			'reboot_needed_date' => 1320800325,
			'created' => 1320800325,
			'modified' => 1320800325,
			'created_by' => 1,
			'modified_by' => 1
		),
	);
}

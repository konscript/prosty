<?php
/* Project Test cases generated on: 2011-11-09 01:58:45 : 1320800325*/
App::uses('Project', 'Model');

/**
 * Project Test Case
 *
 */
class ProjectTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.project', 'app.commit', 'app.deployment');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Project = ClassRegistry::init('Project');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Project);

		parent::tearDown();
	}

}

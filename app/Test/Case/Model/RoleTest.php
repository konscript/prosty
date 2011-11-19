<?php
/* Role Test cases generated on: 2011-11-09 02:00:19 : 1320800419*/
App::uses('Role', 'Model');

/**
 * Role Test Case
 *
 */
class RoleTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.role', 'app.user');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Role = ClassRegistry::init('Role');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Role);

		parent::tearDown();
	}

}

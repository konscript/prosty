<?php
/* Commit Test cases generated on: 2011-11-09 01:58:45 : 1320800325*/
App::uses('Commit', 'Model');

/**
 * Commit Test Case
 *
 */
class CommitTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.commit', 'app.project', 'app.deployment');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Commit = ClassRegistry::init('Commit');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Commit);

		parent::tearDown();
	}

}

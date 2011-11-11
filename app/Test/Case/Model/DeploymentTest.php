<?php
/* Deployment Test cases generated on: 2011-11-09 01:58:45 : 1320800325*/
App::uses('Deployment', 'Model');

/**
 * Deployment Test Case
 *
 */
class DeploymentTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.deployment', 'app.commit', 'app.project');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Deployment = ClassRegistry::init('Deployment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Deployment);

		parent::tearDown();
	}

}

<?php
App::uses('AppModel', 'Model');
/**
 * DeploymentError Model
 *
 * @property Deployment $Deployment
 */
class DeploymentError extends AppModel {
	public $actsAs = array('Prosty');
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'deployment_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'calling_function' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'message' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
function afterFind($results) {

	foreach($results as $id=>$result){
		$request = $result["DeploymentError"]["request"];
		if($this->is_json($request)){
			$results[$id]["DeploymentError"]["request"] = json_decode( $request);
		}		
	}
	
	return $results;
}	

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Deployment' => array(
			'className' => 'Deployment',
			'foreignKey' => 'deployment_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}

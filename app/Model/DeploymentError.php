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
			),
		),
		'calling_function' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'message' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);
	
function afterFind($results) {

	foreach($results as $id=>$result){
	
		// decode json request and responses
		$request = $result["DeploymentError"]["request"];
		if($this->is_json($request)){
			$results[$id]["DeploymentError"]["request"] = json_decode($request);
		}		
		$response = $result["DeploymentError"]["response"];
		if($this->is_json($response)){
			$results[$id]["DeploymentError"]["response"] = json_decode($response);
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

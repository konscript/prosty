<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');

class Deployment extends AppModel {
	public $actsAs = array('Prosty');
	public $validate = array(
		'project_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Project id must be numeric',
			)				
		)
	);
		
	/*******************
	* beforeSave: deploy project, clone folders and run git
	*******************/			
	function beforeSave(){
	
		$project_id = $this->data["Deployment"]["project_id"];
			
		if($this->validates()){	
						
			// TODO: make curl request to Brutus - receive status code and response

			// set error status - errors might have occured during git
			$this->data["Deployment"]["status"] = count($this->getErrors()) === 0 ? true : false;		
		}else{
			// remove invalid fields from array
			foreach($this->invalidFields() as $errorName => $error){		
				unset($this->data["Deployment"][$errorName]);
			}		
		
			// set error status
			$this->data["Deployment"]["status"]	= false;			
		}				
		
		return true;
	}
	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
	
		$project_id = $this->data["Deployment"]["project_id"];
				
		// errors occured during deployment
		if($this->data["Deployment"]["status"]	== false){

			// log Prosty errors
			foreach($this->getErrors() as $error){		
				// set values
				$this->data["DeploymentError"]["deployment_id"] = $this->data["Deployment"]["id"];			
				$this->data["DeploymentError"]["message"] = $error["message"];
				$this->data["DeploymentError"]["calling_function"] = $error["calling_function"];
	
				// save errors
				$this->DeploymentError->create();
				$this->DeploymentError->save($this->data);		
			}				
	
			// log cake validation errors
			foreach($this->invalidFields() as $errorName => $error){		
				// set values
				$this->data["DeploymentError"]["deployment_id"] = $this->data["Deployment"]["id"];			
				$this->data["DeploymentError"]["message"] = $error[0];
				$this->data["DeploymentError"]["calling_function"] = $errorName;
	
				// save errors
				$this->DeploymentError->create();
				$this->DeploymentError->save($this->data);		
			}		
			
		// success: no errors occured during deployment
		}else{
		
			// TODO: make curl to NewRelic
							
		}		
	}
	
/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Project' => array(
			'className' => 'Project',
			'foreignKey' => 'project_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'created_by',
		),
		'ModifiedBy' => array(
			'className' => 'User',
			'foreignKey' => 'modified_by',
		)	
	);
	
	public $hasMany = array(
		'DeploymentError' => array(
			'className' => 'DeploymentError',
			'foreignKey' => 'deployment_id',
			'dependent' => false,
		)		
	);			
}

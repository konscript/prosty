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
						
			// set variables				
			$project_alias = $this->getProjectAlias($project_id);
		
			// curl to force git pull			
			$this->curl_wrapper(array(
				"url" => "http://deployment.konscript.com",
				"data" => array(
					"project_alias" => $project_alias
				)
			));
							   	      			
			// get production URL
			$Project = $this->Project->find('first', array(
				'conditions' => array('Project.id' => $project_id),
				'fields' => 'prod_url',
				'recursive' => -1
			));						
						
			// Clear cache on Brutus			
			$this->curl_wrapper(array(
				"url" => $Project["Project"]["prod_url"],
				"request_method" => "BAN"
			));						

			// set error status - errors might have occured during deployment
			$this->data["Deployment"]["status"] = count($this->getErrors()) === 0 ? true : false;		
		}else{
		
			$this->data["Deployment"]["invalidFields"] = json_encode($this->invalidFields());
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
		
			// curl NewRelic to notify about deployment
			$this->newrelic_hook($project_id);
							
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
	
	
	
	
	/***************************
	* add deployment to NewRelic		
	***************************/			
	function newrelic_hook($project_id){
	
		// get values
		$project_alias = $this->getProjectAlias($project_id);		
		$projects = $this->Project->Commit->find('first', array(
			'conditions' => array('Commit.project_id' => $project_id)
		));

		// make curl request
		$this->curl_wrapper(array(
			"url" => "https://rpm.newrelic.com/deployments.xml",
			"headers" => array('x-api-key: '. Configure::read('NewRelic.rest')),
			"data" => array(
				'app_name' => $project_alias,
				'user' => $_SESSION["Auth"]["User"]["username"],
				'description' => $projects["Commit"]["last_commit_msg"]
			)
		));						
	}	 	
	
	
	
}

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
								
			// curl to force git pull			
			$this->curl_wrapper(array(
				"url" => "http://deployment.konscript.com",
				"data" => array(
					"project_alias" => $this->getProjectAlias($project_id)
				)
			));

			// get production URL
			$Project = $this->Project->find('first', array(
				'conditions' => array('Project.id' => $project_id),
				'fields' => 'prod_url',
				'recursive' => -1
			));						
						
			// Clear cache on Brutus			
			// TODO: Install Varnish on Brutus and enable this again!
			/*
			$this->curl_wrapper(array(
				"url" => $Project["Project"]["prod_url"],
				"request_method" => "BAN"
			));
			*/
			
		// validation failed: remove invalid fields from array
		}else{			
			foreach($this->invalidFields() as $errorName => $error){		
				unset($this->data["Deployment"][$errorName]);
			}
		}		
		
		// update error status
		$this->data["Deployment"]["status"] = count( $this->getErrors() ) === 0 ? true : false;				
		
		return true;
	}
	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
									
		// Deployment successful: Add to NewRelic
		if(count($this->getErrors()) === 0){		
			$project_id = $this->data["Deployment"]["project_id"];
			$this->newrelic_hook($project_id);
		}
		
		$this->saveErrorLogs();

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

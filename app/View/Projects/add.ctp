<div class="projects form">
<?php echo $this->Form->create('Project');?>
	<fieldset>
		<legend><?php echo __('Add Project'); ?></legend>
	<?php
		echo $this->Form->input('project_alias');
		echo $this->Form->input('title', array('description'=>'asd'));
		echo $this->Form->input('installWordpress', array('label' => 'Download and install Wordpress', 'type'=>'checkbox'));
		echo $this->Form->input('skipGithub', array('label' => 'Don\'t push to GitHub after creation', 'type'=>'checkbox'));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>

<div class="deploymentErrors form">
<?php echo $this->Form->create('DeploymentError');?>
	<fieldset>
		<legend><?php echo __('Edit Deployment Error'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('deployment_id');
		echo $this->Form->input('calling_function');
		echo $this->Form->input('message');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('DeploymentError.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('DeploymentError.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Deployment Errors'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Deployments'), array('controller' => 'deployments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment'), array('controller' => 'deployments', 'action' => 'add')); ?> </li>
	</ul>
</div>

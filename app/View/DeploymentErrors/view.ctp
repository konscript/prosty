<div class="deploymentErrors view">
<h2><?php  echo __('Deployment Error');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($deploymentError['DeploymentError']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deployment'); ?></dt>
		<dd>
			<?php echo $this->Html->link($deploymentError['Deployment']['id'], array('controller' => 'deployments', 'action' => 'view', $deploymentError['Deployment']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Calling Function'); ?></dt>
		<dd>
			<?php echo h($deploymentError['DeploymentError']['calling_function']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Message'); ?></dt>
		<dd>
			<?php echo h($deploymentError['DeploymentError']['message']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Deployment Error'), array('action' => 'edit', $deploymentError['DeploymentError']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Deployment Error'), array('action' => 'delete', $deploymentError['DeploymentError']['id']), null, __('Are you sure you want to delete # %s?', $deploymentError['DeploymentError']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployment Errors'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment Error'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployments'), array('controller' => 'deployments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment'), array('controller' => 'deployments', 'action' => 'add')); ?> </li>
	</ul>
</div>

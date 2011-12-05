<div class="deployments view">
<h2><?php  echo __('Deployment');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Project'); ?></dt>
		<dd>
			<?php echo $this->Html->link($deployment['Project']['title'], array('controller' => 'projects', 'action' => 'view', $deployment['Project']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created By'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['created_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified By'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['modified_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Deployment'), array('action' => 'edit', $deployment['Deployment']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Deployment'), array('action' => 'delete', $deployment['Deployment']['id']), null, __('Are you sure you want to delete # %s?', $deployment['Deployment']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployments'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Projects'), array('controller' => 'projects', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Project'), array('controller' => 'projects', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployment Errors'), array('controller' => 'deployment_errors', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment Error'), array('controller' => 'deployment_errors', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Deployment Errors');?></h3>
	<?php if (!empty($deployment['DeploymentError'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Deployment Id'); ?></th>
		<th><?php echo __('Calling Function'); ?></th>
		<th><?php echo __('Message'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($deployment['DeploymentError'] as $deploymentError): ?>
		<tr>
			<td><?php echo $deploymentError['id'];?></td>
			<td><?php echo $deploymentError['deployment_id'];?></td>
			<td><?php echo $deploymentError['calling_function'];?></td>
			<td><?php echo $deploymentError['message'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'deployment_errors', 'action' => 'view', $deploymentError['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'deployment_errors', 'action' => 'edit', $deploymentError['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'deployment_errors', 'action' => 'delete', $deploymentError['id']), null, __('Are you sure you want to delete # %s?', $deploymentError['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Deployment Error'), array('controller' => 'deployment_errors', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>

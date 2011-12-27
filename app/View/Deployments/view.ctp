<div class="deployments view">
	<dl>
		<dt><?php echo __('Project'); ?></dt>
		<dd>
			<?php echo $this->Html->link($deployment['Project']['title'], array('controller' => 'projects', 'action' => 'view', $deployment['Project']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deployed by'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['created_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deployed at'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['created']); ?>
			&nbsp;
		</dd>
	</dl>
</div>

<div class="related">
	<h3><?php echo __('Errors during deployment to Brutus');?></h3>
	<?php if (!empty($deployment['DeploymentError'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Request'); ?></th>
		<th><?php echo __('Response'); ?></th>		
		<th><?php echo __('Exit status'); ?></th>			
	</tr>
	<?php
		$i = 0;
		foreach ($deployment['DeploymentError'] as $deploymentError): ?>
		<tr>
			<td><?php print_r($deploymentError['request']);?></td>
			<td><?php echo $deploymentError['response'];?></td>
			<td><?php echo $deploymentError['return_code'];?></td>			
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>

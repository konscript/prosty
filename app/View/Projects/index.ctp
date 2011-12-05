<div class="projects index">
	<h2><?php echo __('Projects');?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>

			<th><?php echo $this->Paginator->sort('title');?></th>
			<th><?php echo $this->Paginator->sort('primary_domain');?></th>

			<th><?php echo $this->Paginator->sort('dev_domain');?></th>
			<th><?php echo $this->Paginator->sort('use_cache');?></th>
			<th><?php echo $this->Paginator->sort('current_version');?></th>
			<th><?php echo $this->Paginator->sort('errors');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = 0;
	foreach ($projects as $project): ?>
	<tr>

		<td><?php echo h($project['Project']['title']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['primary_domain']); ?>&nbsp;</td>

		<td><?php echo h($project['Project']['dev_domain']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['use_cache']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['current_version']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['errors']); ?>&nbsp;</td>

		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $project['Project']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $project['Project']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $project['Project']['id']), null, __('Are you sure you want to delete %s?', $project['Project']['title'])); ?>
			<?php echo $this->Form->postLink(__('Deploy to new'), array('controller'=>'deployments', 'action' => 'add', $project['Project']['id'], true), null, __('Do you really want to deploy %s?', $project['Project']['title'])); ?>			
			<?php echo $this->Form->postLink(__('Deploy to existing'), array('controller'=>'deployments', 'action' => 'add', $project['Project']['id'], 0), null, __('Do you really want to deploy %s?', $project['Project']['title'])); ?>						
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Project'), array('action' => 'add')); ?></li>
	</ul>
</div>

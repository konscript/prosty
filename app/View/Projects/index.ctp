<div class="projects index">
	<h2><?php echo __('Projects');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('project_alias');?></th>
			<th><?php echo $this->Paginator->sort('title');?></th>
			<th><?php echo $this->Paginator->sort('primary_domain');?></th>
			<th><?php echo $this->Paginator->sort('additional_domains');?></th>
			<th><?php echo $this->Paginator->sort('dev_domain');?></th>
			<th><?php echo $this->Paginator->sort('use_cache');?></th>
			<th><?php echo $this->Paginator->sort('current_version');?></th>
			<th><?php echo $this->Paginator->sort('screenshot');?></th>
			<th><?php echo $this->Paginator->sort('exclude');?></th>
			<th><?php echo $this->Paginator->sort('errors');?></th>
			<th><?php echo $this->Paginator->sort('reboot_needed_date');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
			<th><?php echo $this->Paginator->sort('created_by');?></th>
			<th><?php echo $this->Paginator->sort('modified_by');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($projects as $project): ?>
	<tr>
		<td><?php echo h($project['Project']['id']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['project_alias']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['title']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['primary_domain']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['additional_domains']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['dev_domain']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['use_cache']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['current_version']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['screenshot']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['exclude']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['errors']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['reboot_needed_date']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['created']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['modified']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['created_by']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['modified_by']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $project['Project']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $project['Project']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $project['Project']['id']), null, __('Are you sure you want to delete # %s?', $project['Project']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
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
		<li><?php echo $this->Html->link(__('List Commits'), array('controller' => 'commits', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Commit'), array('controller' => 'commits', 'action' => 'add')); ?> </li>
	</ul>
</div>

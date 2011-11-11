<div class="commits index">
	<h2><?php echo __('Commits');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('project_id');?></th>
			<th><?php echo $this->Paginator->sort('commit_hash');?></th>
			<th><?php echo $this->Paginator->sort('last_commit_msg');?></th>
			<th><?php echo $this->Paginator->sort('number_of_commits');?></th>
			<th><?php echo $this->Paginator->sort('ip_addr');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
			<th><?php echo $this->Paginator->sort('created_by');?></th>
			<th><?php echo $this->Paginator->sort('modified_by');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($commits as $commit): ?>
	<tr>
		<td><?php echo h($commit['Commit']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($commit['Project']['title'], array('controller' => 'projects', 'action' => 'view', $commit['Project']['id'])); ?>
		</td>
		<td><?php echo h($commit['Commit']['commit_hash']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['last_commit_msg']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['number_of_commits']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['ip_addr']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['created']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['modified']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['created_by']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['modified_by']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $commit['Commit']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $commit['Commit']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $commit['Commit']['id']), null, __('Are you sure you want to delete # %s?', $commit['Commit']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Commit'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Projects'), array('controller' => 'projects', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Project'), array('controller' => 'projects', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployments'), array('controller' => 'deployments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment'), array('controller' => 'deployments', 'action' => 'add')); ?> </li>
	</ul>
</div>

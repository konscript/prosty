<div class="deployments">
	<div class="actions testCommit"><ul>
		<li><?php echo $this->Html->link(__('Test Commit'), array('action' => 'add')); ?></li>
	</ul></div>
	<h2><?php echo __('Commits');?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('project_id');?></th>	
			<th><?php echo $this->Paginator->sort('last_commit_msg');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('created_by');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = 0;

	foreach ($commits as $commit): 	
		$tr_class = $commit["Commit"]["status"] == 1 ? "success" : "error";	?>	
	
	<tr class="<?=$tr_class; ?>">
		<td>
			<?php echo $this->Html->link($commit['Project']['title'], array('controller' => 'projects', 'action' => 'view', $commit['Project']['id'])); ?>
		</td>
		<td><?php echo h($commit['Commit']['last_commit_msg']); ?>&nbsp;</td>
		<td><?php echo h($commit['Commit']['created']); ?>&nbsp;</td>
		<td><?php echo $this->Html->link($commit['User']['username'], array('controller' => 'users', 'action' => 'view', $commit['User']['id'])); ?></td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $commit['Commit']['id'])); ?>
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

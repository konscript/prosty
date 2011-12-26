<div class="projects index">
	<h2><?php echo __('Projects');?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>

			<th><?php echo $this->Paginator->sort('title');?></th>
			<th><?php echo $this->Paginator->sort('prod_url');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = 0;
	foreach ($projects as $project): ?>
	<tr>

		<td><?php echo h($project['Project']['title']); ?>&nbsp;</td>
		<td><?php echo h($project['Project']['prod_url']); ?>&nbsp;</td>

		<td class="actions">
			<?php echo $this->Html->link(__('Download'), array('action' => 'download', $project['Project']['id'])); ?>
			<?php echo $this->Html->link(__('SQL dump'), array('action' => 'download', $project['Project']['id'], "sql")); ?>			
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $project['Project']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $project['Project']['id']), null, __('Are you sure you want to delete %s?', $project['Project']['title'])); ?>
			<?php echo $this->Html->link(__('Deploy'), array('controller'=>'deployments', 'action' => 'add', $project['Project']['id']), null, __('Do you really want to deploy %s?', $project['Project']['title'])); ?>						
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

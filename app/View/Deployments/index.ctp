<div class="deployments">
	<h2><?php echo __('Deployments');?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('project_id');?></th>
			<th><?php echo $this->Paginator->sort('hash');?></th>
			<th><?php echo $this->Paginator->sort('last_commit_msg');?></th>			
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('server');?></th>			
			<th><?php echo $this->Paginator->sort('created_by');?></th>			
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	
	$i = 0;
	foreach ($deployments as $deployment): 
		$tr_class = $deployment["Deployment"]["status"] == 1 ? "success" : "error";	?>	
	<tr class="<?=$tr_class; ?>">
		<td>
			<?php echo $this->Html->link($deployment['Project']['title'], array('controller' => 'projects', 'action' => 'view', $deployment['Project']['id'])); ?>
		</td>
		<td><?php echo $deployment['Deployment']['hash']; ?>&nbsp;</td>
		<td><?php echo $deployment['Deployment']['last_commit_msg']; ?>&nbsp;</td>				
		<td><?php echo $this->Time->timeAgoInWords($deployment['Deployment']['created']); ?>&nbsp;</td>
		<td><?php echo $deployment['Deployment']['server']; ?>&nbsp;</td>				
		<td><?php echo $this->Html->link($deployment['User']['username'], array('controller' => 'users', 'action' => 'view', $deployment['User']['id'])); ?></td>		
		<td class="actions"><?php echo $this->Html->link(__('View'), array('action' => 'view', $deployment['Deployment']['id'])); ?></td>
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


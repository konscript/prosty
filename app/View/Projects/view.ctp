<div class="projects view">
<div class="related">
	<h3><?php echo __('Deployments for '. $project['Project']['title']);?></h3>
	<?php if (!empty($project['Deployment'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Commit Hash'); ?></th>
		<th><?php echo __('Last Commit Msg'); ?></th>
		<th><?php echo __('Comitted'); ?></th>
		<th><?php echo __('Comitter'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($project['Deployment'] as $deployment): ?>
		<tr>
			<td><?php echo $deployment['hash'];?></td>
			<td><?php echo $deployment['last_commit_msg'];?></td>
			<td><?php echo $deployment['created'];?></td>
			<td><?php echo @$deployment['User']['username'];?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>

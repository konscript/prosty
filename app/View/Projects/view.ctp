<div class="projects view">
<div class="related">
	<h3><?php echo __('Commits for '. $project['Project']['title']);?></h3>
	<?php if (!empty($project['Commit'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Commit Hash'); ?></th>
		<th><?php echo __('Last Commit Msg'); ?></th>
		<th><?php echo __('Comitted'); ?></th>
		<th><?php echo __('Comitter'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($project['Commit'] as $commit): ?>
		<tr>
			<td><?php echo $commit['hash'];?></td>
			<td><?php echo $commit['last_commit_msg'];?></td>
			<td><?php echo $commit['created'];?></td>
			<td><?php echo $commit['User']['username'];?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>

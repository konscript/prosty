<div class="deployments view">
	<dl>
		<dt><?php echo __('Project'); ?></dt>
		<dd>
			<?php echo $this->Html->link($deployment['Project']['title'], array('id'=>'asda', 'controller' => 'projects', 'action' => 'view', $deployment['Project']['id'])); ?>
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

<?php if(isset($unstagedFiles)): ?>
	<?php echo $this->Form->create(null, array('url' => '/dev_deployments/resolveUnstagedFilesInit/' . $deployment['Project']['project_alias'], 'id'=>'resolveUnstagedFiles', 'class' => 'resolveDialog')); ?>	
		<div id="unresolvedFiles">
			<?php if(isset($unstagedFiles["untracked"])): ?>
				<p class="header">New and untracked files:</p>
				<?php foreach ($unstagedFiles["untracked"] as $id=>$filename): ?>
					<?php $name = "file_untracked_" . $id; ?>
					<p class="file"><input type="checkbox" data-filename="<?php echo $filename ?>" name="<?php echo $name ?>" id="<?php echo $name ?>" /> <label for="<?php echo $name ?>"> <?php echo $filename ?> </label> </p>
				<?php endforeach; ?>		
			<?php endif; ?>
		
			<?php if(isset($unstagedFiles["uncommited"])): ?>
				<p class="header">Changed and uncommited files:</p>
				<?php foreach ($unstagedFiles["uncommited"] as $id=>$filename): ?>			
					<?php $name = "file_uncommited_" . $id; ?>
					<p class="file"><input type="checkbox" data-filename="<?php echo $filename ?>" name="<?php echo $name ?>" id="<?php echo $name ?>" /> <label for="<?php echo $name ?>"> <?php echo $filename ?> </label> </p>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div id="resolvedFiles">
			<p class="header">Files to be ignored:</p>		
			<div id="ignoreFiles"></div>
			<p class="header">Files to be commited:</p>			
			<div id="commitFiles"></div>
		</div>
	
		<input type="submit" value="Ignore" id="ignoreFiles" class="addFiles">
		<input type="submit" value="Commit" id="commitFiles" class="addFiles">
		<input type="submit" value="Re-deploy" id="redeploy">
		<div class="clear"></div>		

	<?php echo $this->Form->end(); ?>
<?php endif; ?>						

<?php if(isset($conflictingFiles)): ?>
	<?php echo $this->Form->create(null, array('url' => '/dev_deployments/resolveConflictingFilesInit/' . $deployment['Project']['project_alias'], 'id'=>'resolveConflictingFiles', 'class' => 'resolveDialog')); ?>	
		<div id="unresolvedFiles">

				<p class="header">Unmerged files:</p>
				<?php foreach ($conflictingFiles as $id=>$filename): ?>
					<?php $name = "file_untracked_" . $id; ?>
					<p class="file"><input type="checkbox" data-filename="<?php echo $filename ?>" id="<?php echo $name ?>" /> <label for="<?php echo $name ?>"> <?php echo $filename ?> </label> </p>
				<?php endforeach; ?>		
		</div>

		<div id="resolvedFiles">
			<p class="header">Use GitHub's version:</p>		
			<div id="theirFiles"></div>
			<p class="header">Use Caesar's (local) version:</p>			
			<div id="ourFiles"></div>
		</div>
	
		<input type="submit" value="Use Github version" id="theirFiles" class="addFiles">
		<input type="submit" value="Use local version" id="ourFiles" class="addFiles">
		<input type="submit" value="Re-deploy" id="redeploy">			
		<div class="clear"></div>		
	<?php echo $this->Form->end(); ?>
<?php endif; ?>							
	
	
	
<div id="debugger">	

</div>
	
	

<div class="related">
	<h3><?php echo __('Errors during deployment');?></h3>
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
			<td><pre><?php print_r($deploymentError['request']);?></pre></td>
			<td><pre><?php print_r($deploymentError['response']);?></pre></td>			
			<td><?php echo $deploymentError['return_code'];?></td>			
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $title_for_layout?></title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<?php echo $this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js'); ?>
	<?php echo $this->Html->script('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js'); ?>
	<?php echo $this->Html->script('screen'); ?>
	<?php echo $this->Html->css('cake.generic'); ?>	
	<?php echo $this->Html->css('main'); ?>		
	<?php echo $scripts_for_layout ?>

</head>

<body>
<div id="header">
    <div id="menu">
	<?php echo $this->Html->link(__('Projects'), array('controller'=>'projects', 'action' => 'index'));?>
	<?php echo $this->Html->link(__('Commits (Dev)'), array('controller'=>'commits', 'action' => 'index'));?>	
	<?php echo $this->Html->link(__('Deployments (Prod)'), array('controller'=>'deployments', 'action' => 'index'));?>	
	<?php echo $this->Html->link(__('Users'), array('controller'=>'users', 'action' => 'index'));?>			
	<?php echo $this->Html->link(__('Log out'), array('controller'=>'users', 'action' => 'logout'));?>		
    </div>
</div>


<div id="content">
<?php echo $this->Session->flash(); ?>
<?php echo $this->Session->flash('auth'); ?>
<?php echo $content_for_layout ?>
</div>

<div id="footer">
	<?php //echo $this->element('sql_dump'); ?>
	Prosty (C)
</div>

</body>
</html>

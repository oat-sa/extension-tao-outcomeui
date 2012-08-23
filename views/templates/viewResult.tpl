<?include(TAO_TPL_PATH . 'header.tpl')?>

<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default"><?=__('View result')?></div>
<div id="form-container" class="ui-widget-content ui-corner-bottom">

	<?if(get_data('errorMessage')):?>
		<fieldset class='ui-state-error'>
			<legend><strong><?=__('Error')?></strong></legend>
			<?=get_data('errorMessage')?>
		</fieldset>
	<?endif?>

	<?=get_data('myForm')?>
	
	<div>
	<table>
	<?  foreach (get_data('variables') as $group){ ?>
		<tr><th colspan="3"><?=$group['label']?></th></tr>
	<?  foreach ($group['vars'] as $variable){ ?>
		<tr>
		<td>
		<?=array_pop($variable[PROPERTY_VARIABLE_IDENTIFIER])?>
		</td><td>
		<?=array_pop($variable[RDF_VALUE])?>
		</td><td>
		<?=array_pop($variable[RDF_TYPE])->getLabel()?>
		</td>
		</tr>
	<? }} ?>
	<table>
	</div>
</div>

<?include(TAO_TPL_PATH . 'footer.tpl')?>

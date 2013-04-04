<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default"><?=__('View result')?></div>
<div class="ui-widget-content ui-corner-bottom">
    <link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoResults/views/css/result.css" />
<script type="text/javascript">
	require(['require', 'jquery', '/taoResults/views/js/viewResult.js'], function () {
	    $('.dataResult').html(function(index, oldhtml) {return layoutResponse(oldhtml);});
	    }
	);
</script>
    
<script>
//substitute score variables and response variables with rendered html 

</script>

		<div id="content">
			<h2><?=get_data('deliveryResultLabel')?></h2>
			<p>
				<span id="TestTakerIdentification"><?=__('Performed by :')?> 
				    <span id="name"><?=get_data('TestTakerLabel')?></span> 
				    <span id=login>(<?=get_data('login')?><?=get_data('TestTakerLogin')?>)</span>
				</span>
			</p>
			<table class="resultsTable" border="1">
			<?  foreach (get_data('variables') as $group){ ?>
			<tr >
			        <td class="headerRow" colspan="3"><span class="itemName"><?=__('Item')?> : <?=$group['label']?></span> <span class="itemModel">(<?=$group['itemModel']?>)</span></td>
			</tr>
			    <?  foreach ($group['vars'] as $key => $variable){ ?>
				<?php $rowOdd = $key % 2;?>
				<tr class="row<?php echo $rowOdd ?>">
				<td><?=array_pop($variable[PROPERTY_VARIABLE_IDENTIFIER])?> (<?=array_pop($variable[RDF_TYPE])->getLabel()?> ) :</td>
				<td class="dataResult"><?=array_pop($variable[RDF_VALUE])?></td>
				<td class="epoch"><?=array_pop($variable[PROPERTY_VARIABLE_EPOCH])?></td>
				</tr>
			    <? } ?>
			</p>			
			<? } ?></table>
		</div>

	


</div>
<div id="form-container" >

	<?if(get_data('errorMessage')):?>
		<fieldset class='ui-state-error'>
			<legend><strong><?=__('Error')?></strong></legend>
			<?=get_data('errorMessage')?>
		</fieldset>
	<?endif?>
		
</div>
<?include(TAO_TPL_PATH . 'footer.tpl')?>

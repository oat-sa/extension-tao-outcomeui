<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default"><?=__('View result')?> - <?=get_data('deliveryResultLabel')?></div>
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
			
				<span id="TestTakerIdentificationBox"><strong><?=__('Results for:')?></strong>
				    <table class="mini">
					<tr><td class="field"><?=__('Login:')?></td><td class="fieldValue"><?=get_data('userLogin')?></td></tr>
					<tr><td class="field"><?=__('Label:')?></td><td class="fieldValue"><?=get_data('userLabel')?></td></tr>
					<tr><td class="field"><?=__('Last Name:')?></td><td class="fieldValue"><?=get_data('userLastName')?></td></tr>
					<tr><td class="field"><?=__('First Name:')?></td><td class="fieldValue"><?=get_data('userFirstName')?></td></tr>
					<tr><td class="field"><?=__('Email:')?></td><td class="fieldValue userMail"><?=get_data('userEmail')?></td></tr>
				    </table>
				</span>
				<span id="ScoresSummaryBox">
				    <span id="correctScoresBox">
					   
						<img src="/taoResults/views/img/dialog-clean.png" />3/5<?=__('Correct')?>
					    
				    </span>
				    <span id="incorrectScoresBox"><img src="/taoResults/views/img/dialog-error-5.png" />1/5<?=__('Incorrect')?>
				    </span>
				    <span id="naScoresBox"><img src="/taoResults/views/img/dialog-important-2.png" />1/5 <?=__('Not Scored')?>
				    </span>
				</span>
			<span>
			<table class="resultsTable" border="1">
			<?  foreach (get_data('variables') as $group){ ?>
			<tr >
			        <td class="headerRow" colspan="3"><span class="itemName"><?=__('Item')?> : <?=$group['label']?></span> <span class="itemModel">(<?=$group['itemModel']?>)</span></td>
			</tr>
			    <?  foreach ($group['vars'] as $key => $variable){ ?>
				<?php $rowOdd = $key % 2;?>
				<tr class="row<?php echo $rowOdd ?>">
				<td><?=array_pop($variable[PROPERTY_IDENTIFIER])?> (<?=array_pop($variable[RDF_TYPE])->getLabel()?> ) :</td>
				<td class="dataResult"><?=array_pop($variable[RDF_VALUE])?></td>
				<td class="epoch"><?=array_pop($variable[PROPERTY_VARIABLE_EPOCH])?></td>
				</tr>
			    <? } ?>
			</p>			
			<? } ?></table>
			    </span>
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

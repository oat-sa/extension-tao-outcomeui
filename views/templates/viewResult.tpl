<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default"><?=__('View result')?></div>
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/custom-theme/jquery-ui-1.8.22.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoResults/views/css/result.css" />
<script>
    
   
    
    // needs strong refactoring. 
    function layoutdata(data){
    try{
	var jsData = $.parseJSON(data);
	var formattedData = "";
	if (jsData instanceof Array) {
	    formattedData = '<UL >';
	    for (key in jsData){
		formattedData += '<li >';
		formattedData += jsData[key];
		 formattedData += "</li>";
		}
	     formattedData += "</UL>";
	} else {
	formattedData = data;
	}
	}
	catch(err){formattedData = data;}
	return formattedData;
	}
	
	//substitute score variables and response variables with rendered html 
    $('.dataResult').html(function(index, oldhtml) {
    return layoutdata(oldhtml);
    });
    </script>
<div id="main" class="ui-widget-content ui-corner-all" style="padding:10px" >
		<div id="content" style="width:100%">
			<h2><?=get_data('deliveryResultLabel')?></h2>
			<p>
				<span id="TestTakerIdentification"><?=__('Performed by :')?> 
				    <span id="name"><?=get_data('TestTakerLabel')?></span> 
				    <span id=login>(<?=get_data('login')?><?=get_data('TestTakerLogin')?>)</span>
				</span>
			</p>
			<table class="resultsTable" >
			<?  foreach (get_data('variables') as $group){ ?>
			<tr >
			        <td class="headerRow" colspan="2"><span class="itemName"><?=__('Item')?> : <?=$group['label']?></span> <span class="itemModel">(<?=$group['itemModel']?>)</span></td>
			</tr>
			    <?  foreach ($group['vars'] as $key => $variable){ ?>
				<?php $rowOdd = $key % 2;?>
				<tr class="row<?php echo $rowOdd ?>">
				<td><?=array_pop($variable[PROPERTY_VARIABLE_IDENTIFIER])?> (<?=array_pop($variable[RDF_TYPE])->getLabel()?> ) :</td>
				<td class="dataResult"><?=array_pop($variable[RDF_VALUE])?></td>
				</tr>
			    <? } ?>
			</p>			
			<? } ?></table>
		</div>
</div>

	
	
<div id="form-container" class="ui-widget-content ui-corner-bottom">

	<?if(get_data('errorMessage')):?>
		<fieldset class='ui-state-error'>
			<legend><strong><?=__('Error')?></strong></legend>
			<?=get_data('errorMessage')?>
		</fieldset>
	<?endif?>
		
</div>



<?include(TAO_TPL_PATH . 'footer.tpl')?>

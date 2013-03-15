<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default"><?=__('View result')?></div>
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/custom-theme/jquery-ui-1.8.22.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/result.css" />
<script>
    function layoutdata(data){
    try{
	var jsData = $.parseJSON(data);
	var formattedData = "";
	if (jsData instanceof Array) {
	    //the formatter callback expects a string to be returned, normal DOM modifications seems not to work.
	    formattedData = '<UL class="cellDataList">';
	    for (key in jsData){
		formattedData += '<li class="cellDataListElement">';
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
    </script>
<div id="main" class="ui-widget-content ui-corner-all" style="padding:30px" >
		<div id="content">
			<h2><?=get_data('deliveryResultLabel')?></h2>
			<p>
				<span id="TestTakerIdentification"><?=__('Performed by :')?> 
				    <span id="name"><?=get_data('TestTakerLabel')?></span> 
				    <span id=login>(<?=get_data('login')?><?=get_data('TestTakerLogin')?>)</span>
				</span>
			</p>
			<?  foreach (get_data('variables') as $group){ ?>
			<div>
			<p>
			    <span class="itemName"><?=__('Item')?> : <?=$group['label']?></span><span class="itemModel">(<?=$group['itemModel']?>)</span>
			</p>
			<p> <table>
			    <?  foreach ($group['vars'] as $variable){ ?>
				<tr>
				<td><?=array_pop($variable[PROPERTY_VARIABLE_IDENTIFIER])?> (<?=array_pop($variable[RDF_TYPE])->getLabel()?> ) :</td>
				<td><span class="dataResult"><?=array_pop($variable[RDF_VALUE])?></script></span></td>
				</tr>
			    <? } ?>
			</p>
			
			<? } ?>
		</div>
</div>

	
	
<div id="form-container" class="ui-widget-content ui-corner-bottom">

	<?if(get_data('errorMessage')):?>Performed by
		<fieldset class='ui-state-error'>
			<legend><strong><?=__('Error')?></strong></legend>
			<?=get_data('errorMessage')?>
		</fieldset>
	<?endif?>
		
</div>



<?include(TAO_TPL_PATH . 'footer.tpl')?>

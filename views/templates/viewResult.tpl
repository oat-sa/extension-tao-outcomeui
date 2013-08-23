<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoResults/views/css/result.css" />
<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default"><?=__('View result')?> - <?=get_data('deliveryResultLabel')?></div>
<div class="ui-widget-content ui-corner-bottom">



<script type="text/javascript">
	
	var data;
	data.uri = '<?=get_data("uri")?>';
	data.classUri = '<?=get_data("classUri")?>';
    /**/
	$(function () {

	require(['require', 'jquery', '/taoResults/views/js/viewResult.js'], function () {
	    $('.dataResult').html(function(index, oldhtml) {
		return layoutResponse(oldhtml);
		});
		
	    $('#filter').change(function(e) {
		url = root_url + 'taoResults/Results/viewResult';
		data.filter = $( this ).val();
		helpers._load(helpers.getMainContainerSelector(uiBootstrap.tabs), url, data);
		});
		$('#filter').val('<?=get_data("filter")?>');
		});
	      $('.traceDownload').click(function (e) {
				var variableUri = $( this ).val();
				
		    		require([root_url  + 'tao/views/js/jquery.fileDownload.js'],
				function(data){
				    $.fileDownload(root_url + 'taoResults/Results/getTrace', {
					preparingMessageHtml: __("We are preparing your report, please wait..."),
					failMessageHtml: __("There was a problem generating your report, please try again."),
					successCallback: function () { },
					httpMethod: "POST",
					 ////This gives the current selection of filters (facet based query) and the list of columns selected from the client (the list of columns is not kept on the server side class.taoTable.php
					data: {'variableUri': variableUri}
				    });

				});



		});
		
	    
	});


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
		<table class="mini">
		    <tr><td colspan="2"><?=__('Responses Evaluation:')?></td><td></td></tr>
		    <tr>
			<td><center><img src="/taoResults/views/img/dialog-clean.png" /><br/>
		    <?=get_data("nbCorrectResponses")?>/<?=get_data('nbResponses')?> <?=__('Correct')?></center></td>

			<td><center><img src="/taoResults/views/img/dialog-error-5.png" /><br/>
		    <?=get_data("nbIncorrectResponses")?>/<?=get_data('nbResponses')?> <?=__('Incorrect')?></center></td>

			 <td><center><img src="/taoResults/views/img/dialog-important-2.png" /><br/>
		    <?=get_data("nbUnscoredResponses")?>/<?=get_data('nbResponses')?> <?=__('Not Scored')?></center></td>
		    </tr>
		</table>
		<br/>
		<span id="Settings"><?=__('Filter values:')?>
			<select id="filter">
			    <option  value="all"><?=__('All collected values')?></option>
			    <option  value="firstSubmitted"><?=__('First submitted responses only')?></option>
			    <option  value="lastSubmitted"><?=__('Last submitted responses only')?></option>
			</select>

		</span>
	</span>

	    <span id="resultsBox">

	    <table class="resultsTable" border="1">
	    <?  foreach (get_data('variables') as $item){ ?>
	    <tr >
		    <td class="headerRow" colspan="4"><span class="itemName"><?=__('Item')?> : <?=$item['label']?></span> <span class="itemModel">(<?=$item['itemModel']?>)</span></td>
	    </tr>
	    <!--<tr><td class="headerColumn"><?=__('Variable Name')?></td><td class="headerColumn"><?=__('Collected Value')?></td><td class="headerColumn"><?=__('Correctness')?></td><td class="headerColumn"><?=__('Timestamp')?></td></tr>!-->

	     <? if (isset($item['sortedVars'][CLASS_RESPONSE_VARIABLE])) {?>
	    <tr ><td class="subHeaderRow" colspan="4"><?=__('Responses')?> :</td></tr>
	    <?

		    foreach ($item['sortedVars'][CLASS_RESPONSE_VARIABLE] as $variableIdentifier  => $observations){
			$rowspan = 'rowspan="'.count($observations).'"';
			foreach ($observations as $key=>$observation) {
	    ?>

		    <tr >
		    <? if ($key === key($observations)) {?>
			 <td <?=$rowspan?>><?=$variableIdentifier?>:</td>
		    <?}?>
		    <td class="dataResult"><?=nl2br(array_pop($observation[RDF_VALUE]))?></td>
		    <td class="<?=$observation['isCorrect']?>" />
		    <td class="epoch"><?=array_pop($observation["epoch"])?></td>
		    </tr>
	    <?
			}
		    }
	    ?>
	<? } ?>
	     <? if (isset($item['sortedVars'][CLASS_OUTCOME_VARIABLE])) {?>
	    <tr> <td class="subHeaderRow" colspan="4"><?=__('Grades')?> :</td></tr>
	    <?

		    foreach ($item['sortedVars'][CLASS_OUTCOME_VARIABLE] as $variableIdentifier  => $observations){
		       $rowspan = 'rowspan="'.count($observations).'"';
			foreach ($observations as $observation) {
	    ?>

		    <tr>
		    <td ><?=$variableIdentifier?>:</td>
		    <td class="dataResult"><?=nl2br(array_pop($observation[RDF_VALUE]))?></td>
		    <td class="" />
		    <td class="epoch"><?=array_pop($observation["epoch"])?></td>
		    </tr>
	    <?
			}
		    }
	    ?>
	<?} ?>
	    <? if (isset($item['sortedVars'][CLASS_TRACE_VARIABLE])) {?>
	    <tr> <td class="subHeaderRow" colspan="4"><?=__('Traces')?> :</td></tr>
	    <?

		    foreach ($item['sortedVars'][CLASS_TRACE_VARIABLE] as $variableIdentifier  => $observations){
		       $rowspan = 'rowspan="'.count($observations).'"';
			foreach ($observations as $observation) {
	    ?>

		    <tr>
		    <td ><?=$variableIdentifier?>:</td>
		    <td class="dataResult"><button class="traceDownload" value="<?=$observation["uri"]?>"><?=__('download')?></button></td>
		    <td class="" />
		    <td class="epoch"><?=array_pop($observation["epoch"])?></td>
		    </tr>
	    <?
			}
		    }
	    ?>
	    <?} ?>
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

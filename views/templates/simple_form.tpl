<?include(TAO_TPL_PATH . 'header.tpl')?>

<div class="ui-helper-reset" style="height:100%;" >
	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		<?=get_data('reportTitle')?>
	</div>
	<div class="ui-widget-content ui-corner-right">
		
		
		<div>
			<ul>			
				
				<li>Collected Results: <strong><?=get_data('nbExecutions')?></strong><br /><em>*The number of Tests delivery being executed and collected so far</em>
				<li>Type of variables: <strong><?=get_data('numberVariables')?></strong><br /><em>*The number of different type of single score variables collected in this delivery definition</em>
				<li>Collected Score Variables: <strong><?=get_data('#')?></strong><br /><em>*The number of Score variables collected so far</em>
				<li>Average: <strong><?=get_data('average')?></strong><br /><em>*The score average considering all collected Tests Delivery executions score variables</em> 
				<li>Standard Deviation: <strong><?=get_data('std')?></strong><br /><em></em>
				<li>Remaining Tokens: <strong><?=get_data('tokensLeft')?></strong><br /><em>*The number of remaining Tests delivery executions (according to the number of attempts granted)</em>
				
				
			</ul>
		</div>
		<div style="border:1px;">
			<img src="<?=get_data('deliveryBarChart');?>"/>
		</div>
		<div style="border:1px;">
			<img src="<?=get_data('compareVariablesPlot');?>"/>
		</div>
	</div>
	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		Results per Single Score Variables
	</div>
	<div class="ui-widget-content ui-corner-right">
		
		Results per Single Score Variables
		
		<?foreach (get_data('listOfVariables') as $variable) :?>
		<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
			Results distribution for <? echo $variable["label"];?>
		</div>
		<div class="ui-widget-content ui-corner-right">
		<ul>			
				
				<li>Collected Results: <strong><?=$variable["infos"]["#"]?></strong>
				<li>Average: <strong><?=$variable["infos"]["avg"]?></strong>
				<li>Standard Deviation: <strong><?=$variable["infos"]["std"]?></strong><br /><em></em>
				
				
			</ul>
		<div style="border:1px;">
			<img src="<? echo $variable["url"];?>"/>
		</div>
	
		<? endforeach ?>
	</div>
</div>
</div>


<?include(TAO_TPL_PATH . 'footer.tpl')?>

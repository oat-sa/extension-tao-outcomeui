<?include(TAO_TPL_PATH . 'header.tpl')?>

<div class="ui-helper-reset" style="height:100%;" >
	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		<?=get_data('reportTitle')?>
	</div>
	<div class="ui-widget-content ui-corner-right">
		<div>
			<ul>			
				
				<li><?=__('# Collected Results')?>: <strong><?=get_data('nbExecutions')?></strong><br /><em>*The number of Tests delivery being executed and collected so far</em>
				<li><?=__('# Distinct variable types')?>: <strong><?=get_data('numberVariables')?></strong><br /><em>*The number of different type of single score variables collected in this delivery definition</em>
				<li><?=__('# Collected Score Variables')?>: <strong><?=get_data('#')?></strong><br /><em>*The number of Score variables collected so far</em>
				<li><?=__('# Distinct Test Takers')?>: : <strong><?=get_data('numberOfDistinctTestTaker')?></strong><br />
				<li><?=__('Total Average')?>: <strong><?=get_data('average')?></strong><br /><em>*The score average considering all collected Tests Delivery executions score variables</em> 
				
				    <!--<li><li><?=__('Total Standard Deviation')?>: <strong><?=get_data('std')?></strong><br /><em></em>-->
				<!--<li>Remaining Tokens: <strong><?=get_data('tokensLeft')?></strong><br /><em>*The number of remaining Tests delivery executions (according to the number of attempts granted)</em>-->
				
				
			</ul>
		</div>
		<div style="border:1px;">
			<!-- not very relevant yet without measurement boudaries<img src="<?=get_data('deliveryBarChart');?>"/>!-->
		</div>
		<div style="border:1px;">
			<!-- requries upper version of jpgraph<img src="<?=get_data('compareVariablesPlot');?>"/>-->
		</div>
	</div>
	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		<?=__('Observed performances per distinc score variable types ')?>
	</div>
	<div class="ui-widget-content ui-corner-right">
				
		<?foreach (get_data('listOfVariables') as $variable) :?>
		<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
			<?=__('Results distribution')?> : <? echo $variable["label"];?>
		</div>
		<div class="ui-widget-content ui-corner-right">
		<ul>			
				
				<?=__('Collected Results')?>: <strong><?=$variable["infos"]["#"]?></strong>
				<?=__('Average')?>: <strong><?=$variable["infos"]["avg"]?></strong>
				
				
				
			</ul>
		<div style="border:1px;">
			<img src="<? echo $variable["urlScores"];?>"/>
			<img src="<? echo $variable["urlFrequencies"];?>"/>
			     
		</div>
	
		<? endforeach ?>
	</div>
</div>
</div>

<?include(TAO_TPL_PATH . 'footer.tpl')?>

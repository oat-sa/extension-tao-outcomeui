<?include(TAO_TPL_PATH . 'header.tpl')?>
<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>tao/views/css/custom-theme/jquery-ui-1.8.22.custom.css" />
<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoResults/views/css/result.css" />
<div class="ui-helper-reset" style="height:100%;" >
	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		<?=get_data('reportTitle')?>
	</div>
	<div class="ui-widget-content ui-corner-right">
		    <table><!--TODO CSS the table-->
			<tr>
			    <td >
			<ul>			
				
				<li><?=__('# Collected Results')?>: <strong><?=get_data('nbExecutions')?></strong><br /><em>*The number of Tests delivery being executed and collected so far</em>
				<li><?=__('# Distinct variable types')?>: <strong><?=get_data('numberVariables')?></strong><br /><em>*The number of different type of single score variables collected in this delivery definition</em>
				<li><?=__('# Collected Score Variables')?>: <strong><?=get_data('#')?></strong><br /><em>*The number of Score variables collected so far</em>
				<li><?=__('# Distinct Test Takers')?>: : <strong><?=get_data('numberOfDistinctTestTaker')?></strong><br />
				<li><?=__('Total Average')?>: <strong><?=get_data('average')?></strong><br /><em>*The score average considering all collected Tests Delivery executions score variables</em> 
				<!--<li><li><?=__('Total Standard Deviation')?>: <strong><?=get_data('std')?></strong><br /><em></em>-->
				<!--<li>Remaining Tokens: <strong><?=get_data('tokensLeft')?></strong><br /><em>*The number of remaining Tests delivery executions (according to the number of attempts granted)</em>-->
				
			</ul>
				<table class="minimal">
					<tr><td><?=__('VariableName')?></td><td><?=__('Average')?></td><td><?=__('St.Dev.')?></td><td>#</td></tr></strong>
				    <?foreach (get_data('listOfVariables') as $variable) :?>
				
				    <tr><td><?=$variable["label"]?></td><td><?=round($variable["infos"]["avg"],2)?></td><td><?=$variable["infos"]["std"]?></td><td><?=$variable["infos"]["#"]?></td></tr>
				    <? endforeach ?>
				</table>
			    </td>
			    <td >	
			        <img src="<? echo get_data('variablesAvgComparison');?>"/>
			    </td>
			</tr>
			<tr>
				<td><i>Data extracted in <? echo get_data('dataExtractionTime').__(" seconds");?></i><br/>
				    <i>Report built in <? echo get_data('reportBuildTime').__(" seconds");?></i>
				</td>
				<td><img src="<? echo get_data('variablesFreqComparison');?>"/></td>
			</tr>
		    </table>
		<!-- not very relevant yet without measurement boudaries<div style="border:1px;">
			<img src="<?=get_data('deliveryBarChart');?>"/>
		</div>!-->
		<!-- requries upper version of jpgraph<div style="border:1px;">
			<img src="<?=get_data('compareVariablesPlot');?>"/>
		</div>-->
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

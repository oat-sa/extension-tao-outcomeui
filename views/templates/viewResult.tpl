<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoResults/views/css/result.css" />

<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
    <?=__('View result')?> - <?=get_data('deliveryResultLabel')?></div>
<div class="ui-widget-content ui-corner-bottom">
    <script type="text/javascript">
        requirejs.config({
            config: {
                'taoResults/controller/viewResult': {
                    uri: '<?=get_data("uri")?>',
                    classUri: '<?=get_data("classUri")?>',
                    filter: '<?=get_data("filter")?>'
                }
            }
        });
    </script>
    <div id="content" class="tao-scope">
        <div id="resultsViewTools">
            <select id="filter" class="select2" data-has-search="false">
                <option  value="all" ><?=__('All collected variables')?></option>
                <option  value="firstSubmitted" ><?=__('First submitted variable only')?></option>
                <option  value="lastSubmitted" ><?=__('Last submitted variable only')?></option>
            </select>
            <button class="btn-info small" id="btnFilter"><?=__('Filter');?></button>
        </div>
        <div id="resultsHeader">
            <div class="tile testtaker">
                <strong>
                    <span class="icon-test-taker"/>
                    <?=__('Test Taker')?>
                </strong>
                <table class="mini">
                    <tr><td class="field"><?=__('Login:')?></td><td class="fieldValue"><?=get_data('userLogin')?></td></tr>
                    <tr><td class="field"><?=__('Label:')?></td><td class="fieldValue"><?=get_data('userLabel')?></td></tr>
                    <tr><td class="field"><?=__('Last Name:')?></td><td class="fieldValue"><?=get_data('userLastName')?></td></tr>
                    <tr><td class="field"><?=__('First Name:')?></td><td class="fieldValue"><?=get_data('userFirstName')?></td></tr>
                    <tr><td class="field"><?=__('Email:')?></td><td class="fieldValue userMail"><?=get_data('userEmail')?></td></tr>
                </table>
            </div>
            <!--
            <div class="tile statistics">
                <strong><span class="icon-result"/>
                    <?=__('Responses Evaluation')?>
                </strong>
                <table class="mini">
                    <tr>
                        <td><span class="valid"><?=__('Correct')?>: </span></td>
                        <td><?=get_data("nbCorrectResponses")?>/<?=get_data('nbResponses')?></td>
                        <td><span class="icon-result-ok"/></td>
                    </tr>
                    <tr>
                        <td><span class="invalid"><?=__('Incorrect')?>: </span></td><td><?=get_data("nbIncorrectResponses")?>/<?=get_data('nbResponses')?></td>
                        <td><span class="icon-result-nok"/></td>
                    </tr>
                    <tr>
                        <td><span class="uneval"><?=__('Not Evaluated')?>: </span></td><td><?=get_data("nbUnscoredResponses")?>/<?=get_data('nbResponses')?></td>
                        <td><span class="icon-not-evaluated"/></td>
                    </tr>
                </table>
            </div>
            -->
        </div>
        <div id="resultsBox">
            <table class="matrix">
                <thead>
                <tr >
                    <th class="headerRow" colspan="4">
                        <span class="itemName">
                            <?=__('Test Variables')?> (<?=count(get_data("deliveryVariables"))?>)
                        </span>
                    </th>
                </tr>
                </thead>
                <?php foreach (get_data("deliveryVariables") as $testVariable){
                $baseType = $testVariable[PROPERTY_VARIABLE_BASETYPE];
                $cardinality = $testVariable[PROPERTY_VARIABLE_CARDINALITY];
                ?>
                <tbody>
                <tr>
                    <td><?=$testVariable[PROPERTY_IDENTIFIER]?></td>
                    <td><?=$testVariable[RDF_VALUE]?></td>
                    <td> 
                        <?php 
                        echo $cardinality;
                        ?>
                    </td>
                    <td> 
                        <?php 
                        echo $baseType;
                        ?>
                    </td>
                </tr>
                </tbody>
                <?php
                }
                ?>
            </table>
            <?php  foreach (get_data('variables') as $itemUri => $item){
           ?>
           
            <table class="matrix">
                <thead>
                    <tr >
                        <th colspan="5" class="bold">
                            <b>
                                <?=$item['label']?>
                                (<?=$item['itemModel']?>)
                            </b>
                        </th>
                        <th>
                            <a href="<?=_url(
                               'fullScreenPreview', 'Items', 'taoItems',
                               array(
                                    'uri' => tao_helpers_Uri::encode($itemUri),
                                    'fullScreen' => true
                                    )
                                    )?>" target="preview">
                                <?=__('Preview')?>
                            </a>
                            
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($item['sortedVars'][CLASS_RESPONSE_VARIABLE])) {?>
                    <tr>
                        <th colspan="6" class="italic">
                            <i><?=__('Responses')?> (<?=count($item['sortedVars'][CLASS_RESPONSE_VARIABLE]) ?>)</i>
                        </th>
                    </tr>
                <?php
		foreach ($item['sortedVars'][CLASS_RESPONSE_VARIABLE] as $variableIdentifier  => $observations){
		    $rowspan = 'rowspan="'.count($observations).'"';
		    foreach ($observations as $key=>$observation) {
                        $baseType = $observation[PROPERTY_VARIABLE_BASETYPE];
                        $cardinality = $observation[PROPERTY_VARIABLE_CARDINALITY];
        	?>
		<tr>
		<?php if ($key === key($observations)) {?>
		     <td <?=$rowspan?> class="variableIdentifierField"><?=$variableIdentifier?></td>
		<?php }?>
		<td class="dataResult" colspan="2">
		    <?php
                        if (isset($observation[RDF_VALUE]) and is_array($observation[RDF_VALUE])){
                            $rdfValue = array_pop($observation[RDF_VALUE]);
                            if (is_array($rdfValue)) {
                                echo "<OL>";
                                foreach ($rdfValue as $value) {
                                    echo "<LI>";
                                        echo tao_helpers_Display::htmlEscape(nl2br($value));
                                    echo "</LI>";
                    }
                    echo "</OL>";
                    } elseif (is_string($rdfValue)) {
                    echo tao_helpers_Display::htmlEscape(nl2br($rdfValue));
                    } else {
                    echo tao_helpers_Display::htmlEscape($rdfValue);
                    }
                    }
                    ?>
                    <?php
                    if ($baseType=="file") {
                    echo '<button class="download" value="'.$observation["uri"].'">'.__('download').'</button>';
                    }
                    ?>
                <span class="    
                      <?php
                      switch ($observation['isCorrect']){
                          case "correct":{ echo "icon-result-ok";break;}
                          case "incorrect":{ echo "icon-result-nok"; break;}
                          default: { echo "icon-not-evaluated";break;}
                          }
                          ?>
                          rgt" />
                          </td>
                          <td> 
                              <?php 
                              echo $cardinality;
                              ?>
                          </td>
                          <td> 
                              <?php 
                              echo $baseType;
                              ?>
                          </td>

                          <td class="epoch"><?=array_pop($observation["epoch"])?></td>
                          </tr>
                          <?php
                          }
                          }
                          ?>
                          <?php } ?>
                          <?php if (isset($item['sortedVars'][CLASS_OUTCOME_VARIABLE])) {?>
                <tr>
                    <th colspan="6" class="italic">
                        <i><?=__('Grades')?>  (<?=count($item['sortedVars'][CLASS_OUTCOME_VARIABLE]) ?>)</i>
                    </th>
                </tr>
                <?php
		foreach ($item['sortedVars'][CLASS_OUTCOME_VARIABLE] as $variableIdentifier  => $observations){
		   $rowspan = 'rowspan="'.count($observations).'"';
		    foreach ($observations as $key=>$observation) {
                         $baseType = $observation[PROPERTY_VARIABLE_BASETYPE];
                         $cardinality = $observation[PROPERTY_VARIABLE_CARDINALITY];
        	?>
		<tr>
		<?php if ($key === key($observations)) {?>
		     <td <?=$rowspan?> class="variableIdentifierField"><?=$variableIdentifier?></td>
		<?php }?>
		<td colspan="2" class="dataResult">
                    <?=tao_helpers_Display::htmlEscape(nl2br(array_pop($observation[RDF_VALUE])))?>
                    <?php
                        if ($baseType=="file") {
                        echo '<button class="download" value="'.$observation["uri"].'">'.__('download').'</button>';
                          }
                          ?>
                          </td>
                          <td> 
                              <?php 
                              echo $cardinality;
                              ?>
                          </td>
                          <td> 
                              <?php 
                              echo $baseType;
                              ?>
                          </td>
                          <td class="epoch"><?=array_pop($observation["epoch"])?></td>
                          </tr>
                          <?php
                          }
                          }
                          ?>
                          <?php } ?>
                          <?php if (isset($item['sortedVars'][CLASS_TRACE_VARIABLE])) {?>
                <tr>
                    <th colspan="6" class="italic">
                    <i><?=__('Traces')?></i></th>
                </tr>
                <?php

		foreach ($item['sortedVars'][CLASS_TRACE_VARIABLE] as $variableIdentifier  => $observations){
		   $rowspan = 'rowspan="'.count($observations).'"';
		    foreach ($observations as $observation) {
                         $baseType = array_pop($observation[PROPERTY_VARIABLE_BASETYPE]);
                         $cardinality = array_pop($observation[PROPERTY_VARIABLE_CARDINALITY]);
                ?>

		<tr>
		<td ><?=$variableIdentifier?></td>
		<td colspan="2" class="dataResult"><button class="traceDownload" value="<?=$observation["uri"]?>"><?=__('download')?></button></td>
                <td> 
                    <?php 
                        echo $cardinality;
                    ?>
                </td>
                <td> 
                    <?php 
                        echo $baseType;
                    ?>
                </td>
		<td class="epoch"><?=array_pop($observation["epoch"])?></td>
		</tr>
	<?php
                          }
                          }
                          ?>
                          <?php } ?>
                          </tbody>
                </table>
                <br />
                <?php } ?>
            </div>
        </div>
    </div>
    <div id="form-container" >

        <?php if(get_data('errorMessage')):?>
        <fieldset class='ui-state-error'>
            <legend><strong><?=__('Error')?></strong></legend>
            <?=get_data('errorMessage')?>
        </fieldset>
        <?php endif?>

    </div>
    <?php
    Template::inc('footer.tpl', 'tao');
    ?>
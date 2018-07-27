<?php
use oat\tao\helpers\Template;
use oat\taoOutcomeUi\model\ResultsService;
?>
<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoOutcomeUi/views/css/result.css" />

<header class="section-header flex-container-full">
</header>
<div class="main-container flex-container-full">

    <div id="view-result">
        <div id="resultsViewTools">
            <div class="tile">
                <select class="result-filter">
                    <option  value="all" ><?=__('All collected variables')?></option>
                    <option  value="<?= ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED ?>" ><?=__('First submitted variables only')?></option>
                    <option  value="<?= ResultsService::VARIABLES_FILTER_LAST_SUBMITTED ?>" ><?=__('Last submitted variables only')?></option>
                </select>
                <label>
                    <input type="checkbox" name="class-filter" value="<?=\taoResultServer_models_classes_ResponseVariable::class?>">
                    <span class="icon-checkbox cross"></span>
                    <?=__('Responses')?>
                </label>
                <label>
                    <input type="checkbox" name="class-filter" value="<?=\taoResultServer_models_classes_OutcomeVariable::class?>">
                    <span class="icon-checkbox cross"></span>
                    <?=__('Grades')?>
                </label>
                <label>
                    <input type="checkbox" name="class-filter" value="<?=\taoResultServer_models_classes_TraceVariable::class?>">
                    <span class="icon-checkbox cross"></span>
                    <?=__('Traces')?>
                </label>
            <button class="btn-info small result-filter-btn"><?=__('Filter');?></button>
            </div>
        </div>
        <div id="resultsHeader">
            <div class="tile testtaker">
                <strong>
                    <span class="icon-test-taker"></span>
                    <?=__('Test Taker')?>
                </strong>
                <table class="mini">
                    <tr><td class="field"><?=__('Login:')?></td><td class="fieldValue"><?= _dh(get_data('userLogin'))?></td></tr>
                    <tr><td class="field"><?=__('Label:')?></td><td class="fieldValue"><?= _dh(get_data('userLabel'))?></td></tr>
                    <tr><td class="field"><?=__('Last Name:')?></td><td class="fieldValue"><?= _dh(get_data('userLastName'))?></td></tr>
                    <tr><td class="field"><?=__('First Name:')?></td><td class="fieldValue"><?= _dh(get_data('userFirstName'))?></td></tr>
                    <tr><td class="field"><?=__('Email:')?></td><td class="fieldValue userMail"><?= _dh(get_data('userEmail'))?></td></tr>
                </table>
            </div>
        </div>
        <div id="resultsBox">
            <!-- Test Variable Table -->
            <?php if(!empty(get_data("deliveryVariables"))):?>
            <table class="matrix">
                <thead>
                <tr>
                    <th class="headerRow" colspan="5">
                        <span class="itemName">
                            <?=__('Test Variables')?> (<?=count(get_data("deliveryVariables"))?>)
                        </span>
                    </th>
                </tr>
                </thead>
                <?php foreach (get_data("deliveryVariables") as $testVariable){
                    $baseType = $testVariable->getBaseType();
                    $cardinality = $testVariable->getCardinality();
                ?>
                    <tbody>
                    <tr>
                        <td><?=$testVariable->getIdentifier()?></td>
                        <td><?=$testVariable->getValue()?></td>
                        <td><?=$cardinality;?></td>
                        <td><?=$baseType;?></td>
                        <td class="epoch"><?=tao_helpers_Date::displayeDate(tao_helpers_Date::getTimeStamp($testVariable->getEpoch()), tao_helpers_Date::FORMAT_VERBOSE)?></td>
                    </tr>
                    </tbody>
                <?php
                }
                ?>
            </table>
            <?php endif;?>
            <!-- End of Test Variable Table -->

            <!-- Item Result Tables -->
            <?php  foreach (get_data('variables') as $item){ ?>

            <table class="matrix">
                <thead>
                <tr>
                    <th colspan="5" class="bold">
                        <b><?= _dh($item['label']) ?></b>
                    </th>
                    <th>
                        <a href="#" data-delivery-id="<?=get_data('classUri')?>" data-result-id="<?=get_data('id')?>" data-type="<?=get_data('itemType')?>" data-uri="<?=$item['uri']?>" data-definition="<?=$item['internalIdentifier']?>" data-state="<?=htmlspecialchars($item['state'])?>" class="btn-info small preview" target="preview">
                            <span class="icon-preview"></span><?=__('Review')?>
                        </a>
                    </th>
                </tr>
                </thead>
                <tbody>
                    <?php if (isset($item[\taoResultServer_models_classes_ResponseVariable::class])) { ?>
                        <!-- Response Variable section row -->
                        <tr>
                            <th colspan="6" class="italic">
                                <i><?=__('Responses')?> (<?=count($item[\taoResultServer_models_classes_ResponseVariable::class]) ?>)</i>
                            </th>
                        </tr>
                        <!-- Response Variable list -->
                        <?php foreach ($item[\taoResultServer_models_classes_ResponseVariable::class] as $variableIdentifier  => $observation){
                            $variable = $observation["var"];
                        ?>
                        <tr>
                            <td class="variableIdentifierField"><?=$variableIdentifier?></td>
                            <!-- Variable value cell -->
                            <td class="dataResult" colspan="2">
                        <?php
                        if ($variable->getBaseType() === "file" && $variable->getCandidateResponse() !== '') {
                            echo '<button class="download btn-info small" value="'.htmlspecialchars($observation["uri"]).'"><span class="icon-download"></span> '.__('Download').'</button>';
                        }
                        else{
                            $rdfValue = $variable->getValue();
                            if (is_array($rdfValue)) { ?>
                                <OL>
                            <?php foreach ($rdfValue as $value) { ?>
                                    <LI>
                                        <?=tao_helpers_Display::htmlEscape(nl2br($value))?>
                                    </LI>
                            <?php } ?>
                                </OL>
                            <?php
                            } elseif (is_string($rdfValue)) {
                                echo tao_helpers_Display::htmlEscape(nl2br($rdfValue));
                            } else {
                                echo tao_helpers_Display::htmlEscape($rdfValue);
                            }
                        }
                        ?>

                        <span class="rgt
                              <?php
                              switch ($observation['isCorrect']){
                                  case "correct":{ ?>icon-result-ok <?php break;}
                                  case "incorrect":{ ?>icon-result-nok<?php break;}
                                  default: { ?>icon-not-evaluated<?php break;}
                              }
                              ?>
                        "></span>
                        </td>
                        <!-- End of Variable value cell -->
                        <td class="cardinalityField"><?=$variable->getCardinality()?></td>
                        <td class="basetypeField"><?=$variable->getBaseType()?></td>
                        <td class="epoch"><?=tao_helpers_Date::displayeDate(tao_helpers_Date::getTimeStamp($variable->getEpoch()), tao_helpers_Date::FORMAT_VERBOSE)?></td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                    <!-- End of Response Variable List -->

                    <?php if (isset($item[\taoResultServer_models_classes_OutcomeVariable::class])) { ?>
                        <!-- Outcome Variable section row-->
                        <tr>
                            <th colspan="6" class="italic">
                            <i><?=__('Grades')?>  (<?=count($item[\taoResultServer_models_classes_OutcomeVariable::class]) ?>)</i>
                            </th>
                        </tr>
                        <!-- Outcome Variable section list-->
                        <?php
		                foreach ($item[\taoResultServer_models_classes_OutcomeVariable::class] as $variableIdentifier  => $observation){
                            $variable = $observation["var"];
        	            ?>
		                    <tr>
		                        <td class="variableIdentifierField"><?=$variableIdentifier?></td>
                                <td colspan="2" class="dataResult">
                                    <?= tao_helpers_Display::htmlEscape($variable->getValue())?>
                                </td>
                                <td class="cardinalityField"><?=$variable->getCardinality();?></td>
                                <td class="basetypeField"><?= $variable->getBaseType();?></td>
                                <td class="epoch">
                                    <?=tao_helpers_Date::displayeDate(tao_helpers_Date::getTimeStamp($variable->getEpoch()), tao_helpers_Date::FORMAT_VERBOSE)?>
                                </td>
                            </tr>
                        <?php
                        }
                    }
                    ?>
                    <!-- End of Outcome Variable List -->

                    <?php if (isset($item[\taoResultServer_models_classes_TraceVariable::class])) { ?>
                    <!-- Trace Variable section row-->
                    <tr>
                        <th colspan="6" class="italic">
                            <i><?=__('Traces')?>  (<?=count($item[\taoResultServer_models_classes_TraceVariable::class]) ?>)</i>
                        </th>
                    </tr>
                    <!-- Trace Variable section list-->
                    <?php
		                foreach ($item[\taoResultServer_models_classes_TraceVariable::class] as $variableIdentifier  => $observation){
                    $variable = $observation["var"];
                    ?>
                    <tr>
                        <td class="variableIdentifierField"><?=$variableIdentifier?></td>
                        <td colspan="2" class="dataResult">
                            <?= tao_helpers_Display::htmlEscape($variable->getValue())?>
                        </td>
                        <td class="cardinalityField"><?=$variable->getCardinality();?></td>
                        <td class="basetypeField"><?= $variable->getBaseType();?></td>
                        <td class="epoch">
                            <?=tao_helpers_Date::displayeDate(tao_helpers_Date::getTimeStamp($variable->getEpoch()), tao_helpers_Date::FORMAT_VERBOSE)?>
                        </td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                    <!-- End of Trace Variable List -->

                </tbody>
            </table>
            <br/>
            <?php } ?>
            <!-- End of Item Result Tables-->
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

<script type="text/javascript">
    requirejs.config({
        config: {
            'taoOutcomeUi/controller/viewResult': {
                id: '<?=get_data("id")?>',
                classUri: '<?=get_data("classUri")?>',
                filterSubmission: '<?=get_data("filterSubmission")?>',
                filterTypes: '<?=json_encode(get_data("filterTypes"))?>',
            }
        }
    });
</script>

<?php
Template::inc('footer.tpl', 'tao');
?>

<?php
use oat\tao\helpers\Template;
?>
<div class="result-table flex-container-full">
    <div class="grid-row clearfix">
        <div class="col-12">
            <button class="btn-info small hidden" data-group="testtaker" data-action="add" data-url="<?=_url('getResultOfSubjectColumn')?>">
                <span class="icon-add"></span><?=__('Add Test Taker')?>
            </button>
            <button class="btn-error small" data-group="testtaker" data-action="remove" data-url="<?=_url('getResultOfSubjectColumn')?>" >
                <span class="icon-bin"></span><?=__('Anonymise')?>
            </button>
            <button class="btn-info small" data-group="grade" data-action="add" data-url="<?=_url('getGradeColumns')?>" >
                <span class="icon-add"></span><?=__('Add All grades')?>
            </button>
            <button class="btn-error small hidden" data-group="grade" data-action="remove" data-url="<?=_url('getGradeColumns')?>"  >
                <span class="icon-bin"></span><?=__('Remove All grades')?>
            </button>
            <button class="btn-info small" data-group="response" data-action="add" data-url="<?=_url('getResponseColumns')?>" >
                <span class="icon-add"></span><?=__('Add All responses')?>
            </button>
            <button class="btn-error small hidden" data-group="response" data-action="remove" data-url="<?=_url('getResponseColumns')?>"  >
                <span class="icon-bin"></span><?=__('Remove All responses')?>
            </button>
        </div>
    </div>
    <div class="grid-row">
        <div class="col-12">
            <select class="result-filter">
                <option  value="all"><?=__('All collected values')?></option>
                <option  value="firstSubmitted"><?=__('First submitted responses only')?></option>
                <option  value="lastSubmitted"><?=__('Last submitted responses only')?></option>
            </select>
            <button class="btn-info small result-filter-btn"><?=__('Filter');?></button>
        </div>
    </div>
    <div class="result-table-container"></div>
    <div class="grid-row">
        <button class="result-export btn-info disabled small"><span class="icon-export"></span><?=__('Export CSV File')?></button>
 	</div>
</div>
<script>
requirejs.config({
    config : {
        'taoOutcomeUi/controller/resultTable' : {
            'filter' : <?=json_encode(get_data('filter'))?>
        }
    }
});
</script>
<?php
Template::inc('footer.tpl', 'tao');
?>

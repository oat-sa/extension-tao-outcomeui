<?php
use oat\tao\helpers\Template;
use oat\taoOutcomeUi\model\ResultsService;
?>
<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoOutcomeUi/views/css/result.css" />
<div class="result-table flex-container-full">
    <div class="grid-row clearfix">
        <div class="col-12">
            <button class="btn-info small hidden" data-group="testtaker" data-action="add" data-url="<?=_url('getTestTakerColumns')?>">
                <span class="icon-add"></span><?=__('Add Test Taker')?>
            </button>
            <button class="btn-error small" data-group="testtaker" data-action="remove" data-url="<?=_url('getTestTakerColumns')?>" >
                <span class="icon-bin"></span><?=__('Anonymise')?>
            </button>
            <button class="btn-info small" data-group="delivery" data-action="add" data-url="<?=_url('getDeliveryColumns')?>">
                <span class="icon-add"></span><?=__('Add Delivery')?>
            </button>
            <button class="btn-error small hidden" data-group="delivery" data-action="remove" data-url="<?=_url('getDeliveryColumns')?>" >
                <span class="icon-bin"></span><?=__('Remove Delivery')?>
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
                <option  value="all"><?=__('All collected variables')?></option>
                <option  value="<?= ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED ?>"><?=__('First submitted variables only')?></option>
                <option  value="<?= ResultsService::VARIABLES_FILTER_LAST_SUBMITTED ?>"><?=__('Last submitted variables only')?></option>
            </select>
            <button class="btn-info small result-filter-btn"><?=__('Filter');?></button>
        </div>
    </div>
    <div class="result-table-container"></div>
    <div class="grid-row actions"></div>
</div>
<script>
requirejs.config({
    config : {
        'taoOutcomeUi/controller/resultTable' : {
            'filter' : <?=json_encode(get_data('filter'))?>,
            'uri' : '<?=get_data("uri")?>'
        }
    }
});
</script>
<?php
Template::inc('footer.tpl', 'tao');
?>

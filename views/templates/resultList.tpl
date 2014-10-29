<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" type="text/css" href="<?= Template::css('resultList.css') ?>" />

<div class="grid-row">
	<div class="col-3">
		<div class="ui-widget ui-state-default ui-widget-header ui-corner-top container-title">
			<?=__('Results Selection Filters')?>
		</div>
		<div id="tabs-1">
			<div id="facet-filter"></div>
		</div>
	</div>
	<div class="col-9">
		<table id="inspect-results-grid"></table>
		<div id="pagera1"></div>
		<div align="right">
			<button id="buildTableButton" class="btn-neutral" type="button"><?=__('Export Table')?></button>
		</div>
	</div>
</div>
<script>
requirejs.config({
    config: {
        'taoOutcomeUi/controller/inspectResults': {
            model : <?= json_encode(get_data('model')) ?>,
            filterNodes : <?= json_encode(get_data('filterNodes')) ?>
        }
    }
});
</script>
<?php
Template::inc('footer.tpl', 'tao');
?>

<?php
use oat\tao\helpers\Template;
?>
<div id="inspect-result" class="flex-container-full">

	<div class="grid-row">
	<div class="col-3">
		<h3><?=__('Results Selection Filters')?></h3>
        <div class="facet-filter"></div>
	</div>

	<div class="col-9">
		<div class="inspect-results-grid"></div>
        <button class="btn-info small export-table disabled"><span class="icon-export"></span><?=__('Export Table')?></button>
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

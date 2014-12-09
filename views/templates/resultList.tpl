<?php
use oat\tao\helpers\Template;
?>
<div id="inspect-result" class="flex-container-full">

	<div class="grid-row">
	<div class="col-12">
		<div class="inspect-results-grid"></div>

        <button class="btn-info small export-table disabled"><span class="icon-export"></span><?=__('Export Table')?></button>
	</div>
</div>
<script type="text/javascript">
    requirejs.config({
        config: {
            'taoOutcomeUi/controller/inspectResults': {
                model : <?= json_encode(get_data("model")) ?>,
                implementation : '<?= get_data("implementation") ?>',
                uri : '<?= get_data("classUri") ?>'
            }
        }
    });
</script>

<?php
Template::inc('footer.tpl', 'tao');
?>

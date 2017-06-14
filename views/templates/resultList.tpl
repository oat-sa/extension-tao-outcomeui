<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" href="<?= Template::css('icon.css') ?>" />

<div class="results-headings flex-container-full">
    <header>
        <h2><?=get_data("title")?></h2>
    </header>
</div>

<div id="inspect-result" class="flex-container-full" data-uri="<?= tao_helpers_Display::encodeAttrValue(get_data("uri")) ?>">
	<div class="grid-row">
    	<div class="col-12">
    		<div class="inspect-results-grid"></div>
    	</div>
	</div>
</div>

<?php
Template::inc('footer.tpl', 'tao');
?>

<script>
    requirejs.config({
        config: {
            'taoOutcomeUi/controller/inspectResults' : <?= json_encode(get_data('config')) ?>
        }
    });
</script>

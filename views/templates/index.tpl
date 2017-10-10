<?php
use oat\tao\helpers\Template;
?>
<div class="main-container">
    <div class="feedback-<?= get_data('type')?>">
        <?= get_data("error") ?>
    </div>
</div>
<?php
Template::inc('footer.tpl', 'tao');
?>

<script>
    require([
            'jquery',
            'jquery.fileDownload'
        ],
        function($) {
            if (typeof '<?=get_data("url")?>' !== 'undefined' && typeof '<?=get_data("uri")?>' !== 'undefined') {
                $.fileDownload('<?=get_data("url")?>', {
                    httpMethod: 'GET',
                    data: {uri: '<?=get_data("uri")?>'}
                });
            }
        });
</script>
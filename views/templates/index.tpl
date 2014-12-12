<?php
use oat\tao\helpers\Template;
?>
<div class="main-container">
    <div class="feedback-info">
		<?=__('No tests have been taken yet. As soon as a test-taker will take a test his results will be diplay here.')?>
    </div>
</div>
<?php
Template::inc('footer.tpl', 'tao');
?>
<?php
use oat\tao\helpers\Template;
?>
<div class="main-container">
    <div class="feedback-info">
		<?=__('No tests have been passed. As soon as one test taker will pass a test his results will be diplay here.')?>
    </div>
</div>
<?php
Template::inc('footer.tpl', 'tao');
?>
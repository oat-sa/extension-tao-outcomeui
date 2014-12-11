<?php
use oat\tao\helpers\Template;
?>
<div class="main-container">
	<div class="ext-home-container ui-state-highlight">
		<h1><img src="<?=BASE_WWW?>img/taoResults.png" /> <?=__('Results')?></h1>
		<p><?=__('No test has been passed. As soon as one test taker will pass a test his results will be diplay here.')?></p>
	</div>
</div>
<?php
Template::inc('footer.tpl', 'tao');
?>
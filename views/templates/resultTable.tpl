<?if(get_data('message')):?>
	<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
		<span><?=get_data('message')?></span>
	</div>
<?endif?>
<div class="main-container">
	<div style='padding: 20px'>
	<span class="ui-state-default ui-corner-all">
		<a href="#" onclick="helpers.selectTabByName('add_user');">
			<img src="<?=TAOBASE_WWW?>img/add.png" alt="add" /> <?=__('Add grades')?>
		</a>
	</span>
	</div>
	<table id="result-table-grid"></table>
	<div id="result-table-pager"></div>
</div>
<script type="text/javascript">
require(['require', 'jquery', 'grid/tao.grid'], function(req, $) {
	
	$(function(){
		var myGrid = $("#result-table-grid").jqGrid({
			url: "<?=_url('data', 'ResultTable', 'taoResults')?>",
			postData: <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>,
			mtype: "post",
			datatype: "json",
			colNames:[],
			colModel:[],
			rowNum:20,
			height:300,
			width: (parseInt($("#result-table-grid").width()) - 2),
			pager: '#user-list-pager',
			sortname: 'login',
			viewrecords: false,
			sortorder: "asc",
			caption: __("Delivery results"),
			gridComplete: function(){

				$(".user_deletor").click(function(){
					removeUser(this.id.replace('user_deletor_', ''));
				});

				$(window).unbind('resize').bind('resize', function(){
					myGrid.jqGrid('setGridWidth', (parseInt($("#user-list").width()) - 2));
				});
			}
		});
		myGrid.navGrid('#result-table-grid',{edit:false, add:false, del:false});

		helpers._autoFx();
	});
});
</script>

<?include(TAO_TPL_PATH.'/footer.tpl');?>
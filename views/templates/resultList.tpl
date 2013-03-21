<style>
	#filter-container { width:19%;  height:561px; }
	.main-container { height:584px; padding:0; margin:0; overflow:auto !important; }
	#process-details-tabs { height:269px; }
	#monitoring-processes-container, #process-details-container { padding:0; height:50%; overflow:auto; }
</style>

<div id="filter-container" class="data-container tabs-bottom">
	<div class="ui-widget ui-state-default ui-widget-header ui-corner-top container-title" >
		<?=__('Results Selection Filters')?>
	</div>
	<div id="tabs-1">
		<div id="facet-filter">
		</div>
	</div>
</div>

<div class="main-container">
	<div id="monitoring-processes-container">
		<table id="monitoring-processes-grid">
		</table>
	</div>
	<div align="right">
		<span class="ui-state-default ui-corner-all">
			<a href="#" id="buildTableButton"><?=__('Build/Export data table')?></a>
		</span>
	</div>
</div>

<script type="text/javascript">
//Global variable
var monitoringGrid = null;
//Selected process id
//quick hack to test, to replace quickly
var selectedProcessId = null;
//var selectedActivityExecutionId = null;

//load the monitoring interface functions of the parameter filter
function loadResults(filter) {
	$.getJSON('<?=_url('getResults')?>',
		{
			'filter':filter
		},
		function (DATA) {
			monitoringGrid.empty();
			monitoringGrid.add(DATA);
			selectedProcessId = null;
		}
	);
}

$(function(){
	require(['require', 'jquery', 'generis.facetFilter', 'grid/tao.grid'], function(req, $, GenerisFacetFilterClass) {
		//the grid model
		model = <?=$model?>;

		/*
		 * Instantiate the tabs
		 */
		var filterTabs = new TaoTabsClass('#filter-container', {'position':'bottom'});
		var processDetailsTabs = new TaoTabsClass('#process-details-tabs', {'position':'bottom'});

		/*
		 * instantiate the facet based filter widget
		 */
		var getUrl = '<?=_url('getFilteredInstancesPropertiesValues')?>';

		//the facet filter options
		var facetFilterOptions = {
			template: 'accordion',
			callback: {
				'onFilter': function(facetFilter) {
					loadResults(facetFilter.getFormatedFilterSelection());
				}
			},
			itemActions: {
				createTab: {
					iconUrl: root_url + '/tao/views/img/table.png',
					callback: {
						click: function(e) {
							//console.log(e);
						}
					}
				}
			}
		};

		//set the filter nodes
		var filterNodes = [
<?foreach($properties as $property):?>
			{
				id: '<?=md5($property->uriResource)?>',
				label: '<?=$property->getLabel()?>',
				url: getUrl,
				options: {
					'propertyUri': '<?= $property->uriResource ?>',
					'classUri': '<?= $clazz->uriResource ?>',
	        'filterItself': false
				}
			},
<?endforeach;?>
		];
		//instantiate the facet filter
		var facetFilter = new GenerisFacetFilterClass('#facet-filter', filterNodes, facetFilterOptions);

		/*
		 * instantiate the monitoring grid
		 */
		//the monitoring grid options
		var resultsGridOptions = {
			'height': $('#monitoring-processes-grid').parent().height(),
			'title': __('Delivery results'),
			'callback': {
				'onSelectRow': function(rowId) {
					label = monitoringGrid.data[rowId]['http://www.w3.org/2000/01/rdf-schema#label'];
					if (!label) {
						label = __('Delivery Result');
					}
					helpers.openTab(label, '<?=_url('viewResult')?>?uri='+escape(rowId));
				}
			}
		};

		//instantiate the grid widget
		monitoringGrid = new TaoGridClass('#monitoring-processes-grid', model, '', resultsGridOptions);
		//load monitoring grid
		loadResults(null);

		//width/height of the subgrids
		var subGridWith = $('#current_activities_container').width() - 12 /* padding */;
		var subGridHeight = $('#current_activities_container').height() - 45;

		$('#buildTableButton').click(function(e) {
			e.preventDefault();
			filterSelection = facetFilter.getFormatedFilterSelection();
			uri = '<?=_url('index','ResultTable')?>?'+$.param({'filter': filterSelection});
			helpers.openTab(__('custom table'), uri);
		});

	});
});
</script>

<?include(TAO_TPL_PATH.'footer.tpl');?>
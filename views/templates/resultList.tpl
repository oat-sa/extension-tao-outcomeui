<style>
	#filter-container { width:19%;  height:561px; }
	.main-container { height:584px; padding:0; margin:0; overflow:auto !important; }
	#process-details-tabs { height:269px; }
	#monitoring-processes-container, #process-details-container { padding:0; height:50%; overflow:auto; }
</style>

<div id="filter-container" class="data-container tabs-bottom">
	<div class="ui-widget ui-state-default ui-widget-header ui-corner-top container-title" >
		<?=__('Filter')?>
	</div>
	<div id="tabs-1">
		<div id="facet-filter">
		</div>
	</div>
	<div style="position: absolute; bottom: 10px">
		<?foreach($deliveries as $uri => $label):?>
			<ul>
				<li><a href="#" data-uri="<?=$uri?>" class="resultTableLink"><?=$label?></a></li>
			</ul>
		<?endforeach;?>
	</div>
</div>

<div class="main-container">
	<div id="monitoring-processes-container">
		<table id="monitoring-processes-grid">
		</table>
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
function loadResults(filter)
{
	$.getJSON(root_url+'/taoResults/results/getResults'
		,{
			'filter':filter
		}
		, function (DATA) {
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
		var getUrl = root_url + '/taoResults/results/getFilteredInstancesPropertiesValues';
		//the facet filter options
		var facetFilterOptions = {
			'template' : 'accordion',
			'callback' : {
				'onFilter' : function(filter, filterNodesOpt){
					var formatedFilter = {};
					for(var filterNodeId in filter){
						var propertyUri = filterNodesOpt[filterNodeId]['propertyUri'];
						typeof(formatedFilter[propertyUri])=='undefined'?formatedFilter[propertyUri]=new Array():null;
						for(var i in filter[filterNodeId]){
							formatedFilter[propertyUri].push(filter[filterNodeId][i]);
						}
					}
					loadResults(formatedFilter);
				}
			}
		};
		//set the filter nodes
		var filterNodes = [
			<?foreach($properties as $property):?>
			{
				id					: '<?=md5($property->uriResource)?>'
				, label				: '<?=$property->getLabel()?>'
				, url				: getUrl
				, options 			:
				{
					'propertyUri' 	: '<?= $property->uriResource ?>'
					, 'classUri' 	: '<?= $clazz->uriResource ?>'
	                , 'filterItself': false
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
			'height' : $('#monitoring-processes-grid').parent().height()
			, 'title' 	: __('Delivery results')
			, 'callback' : {
				'onSelectRow' : function(rowId)
				{
					label = monitoringGrid.data[rowId]['http://www.w3.org/2000/01/rdf-schema#label'];
					if (!label) {
						label = __('Delivery Result');
					}
					helpers.openTab(label, root_url+'/taoResults/results/viewResult?uri='+escape(rowId));
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

		$('.resultTableLink').click(function() {
			uri = root_url+'/taoResults/ResultTable/index?filter[<?=urlencode(PROPERTY_RESULT_OF_DELIVERY)?>][]='+escape($(this).data('uri'));
			name = $(this).text();
			helpers.openTab(name, uri)});
		});
});
</script>

<?include(TAO_TPL_PATH.'footer.tpl');?>
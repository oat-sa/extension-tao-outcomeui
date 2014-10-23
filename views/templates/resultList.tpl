<?php
use oat\tao\helpers\Template;
?>
<link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>taoResults/views/css/resultList.css" />

<div class="grid-row">
	<div class="col-3">
		<div class="ui-widget ui-state-default ui-widget-header ui-corner-top container-title">
			<?=__('Results Selection Filters')?>
		</div>
		<div id="tabs-1">
			<div id="facet-filter"></div>
		</div>
	</div>
	<div class="col-9">
		<table id="inspect-results-grid"></table>
		<div id="pagera1"></div>
		<div align="right">
			<button id="buildTableButton" class="btn-neutral" type="button"><?=__('Export Table')?></button>
		</div>
	</div>
</div>

<script type="text/javascript">
//Global variable
var deliveryResultGrid = null;

//load the results interface functions of the parameter filter
function loadResults(filter) {
	$.getJSON('<?=_url("getResults")?>',
		{
			'filter':filter
		},
		function (DATA) {
			deliveryResultGrid.empty();
			deliveryResultGrid.add(DATA);
			$('#lui_' + deliveryResultGrid.jqGrid[0].p.id).hide();
		}
	);
}

$(function(){
	require(['jquery', 'i18n', 'helpers', 'layout/section', 'generis.facetFilter', 'grid/tao.grid'], function($, __, helpers, section, GenerisFacetFilterClass) {

		//the grid model
		model = <?=$model?>;
		/*
		 * instantiate the facet based filter widget
		 */
		var getUrl = '<?=_url("getFilteredInstancesPropertiesValues")?>';

		//the facet filter options
		var facetFilterOptions = {
			template: 'accordion',
			callback: {
				'onFilter': function(facetFilter) {
					loadResults(facetFilter.getFormatedFilterSelection());
				}
			}

		};

		//set the filter nodes
		var filterNodes = [
			<?php foreach($properties as $property):?>
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
			<?php endforeach;?>
		];
		//instantiate the facet filter
		var facetFilter = new GenerisFacetFilterClass('#facet-filter', filterNodes, facetFilterOptions);

		$( "#facet-filter" ).tabs( "option", "show", { effect: "blind", duration: 10000 });

		/*
		*  Results Delviery Grid
		 */

		/*
		 * instantiate the delivery results grid
		 */
		//the delivery results grid options
		var resultsGridOptions = {
			'height': 'auto',
			'width': (parseInt($("#inspect-results-grid").width()-40)),
			'title': __('Delivery results'),
			'callback': {
				'onSelectRow': function(rowId) {
					var label = deliveryResultGrid.data[rowId]['http://www.w3.org/2000/01/rdf-schema#label'];
					if (!label) {
						label = __('Delivery Result');
					}
					helpers.openTab(label, '<?=_url('viewResult', 'Results')?>?uri='+escape(rowId));
				}
			}
		};
		//tao grid class override
		TaoGridClass.prototype.editRow = function(rowId){ ;};
		//instantiate the grid widget
		deliveryResultGrid = new TaoGridClass('#inspect-results-grid', model, '', resultsGridOptions);
		//load delivery results grid
		loadResults(null);

		//width/height of the subgrids
//		var subGridWith = $('#current_activities_container').width() - 12 /* padding */;
//		var subGridHeight = $('#current_activities_container').height() - 45;

		$('#buildTableButton').click(function(e) {
			e.preventDefault();
			filterSelection = facetFilter.getFormatedFilterSelection();
			uri = helpers._url('index', 'ResultTable', 'taoResults', {'filter': filterSelection});

			$section = section.create({
                id      : 'buildTableTab',
                name    : __('Export Delivery Results'),
                url     : uri,
                contentBlock : true
            });
            $section.show();
		});

	});

});


</script>
<?php
Template::inc('footer.tpl', 'tao');
?>
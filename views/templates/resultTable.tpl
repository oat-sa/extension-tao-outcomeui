<?if(get_data('message')):?>
	<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
		<span><?=get_data('message')?></span>
	</div>
<?endif?>
    <script type="text/javascript">
require(['require', 'jquery', '/taoResults/views/js/viewResult', 'grid/tao.grid', '/taoResults/views/js/resultTable'], function(req, $) {
    
    $(function(){
	    //models and columns are parameters used and manipulated by the table operations functions. 
	    document.models = [];
	    document.columns = [];
	    //there is no _url helper in JS,
	    // in order to avoid php call within JS and externalize the js 
	    // of the grid in a separate js file, the links to he actions are stored
	    // in the document
	    document.dataUrl = "<?=_url('data')?>";
	    document.getActionSubjectColumnUrl = "<?=_url('getResultOfSubjectColumn')?>";
	    document.getActionGradeColumnUrl = "<?=_url('getGradeColumns')?>";
	    document.getActionResponseColumnUrl = "<?=_url('getResponseColumns')?>";
	    document.getActionCsvFileUrl = "<?=_url('getCsvFile')?>";
	    document.getActionViewResultUrl= "<?=_url('viewResult','InspectResults')?>";
	    document.JsonFilter = <?=tao_helpers_Javascript::buildObject(get_data("filter"))?>;
	    document.JsonFilterSelection = <?=tao_helpers_Javascript::buildObject(array('filter' => get_data("filter")))?>;
	    document.resultOfSubjectConstant = "<?php echo PROPERTY_RESULT_OF_SUBJECT;?>";
	    //initiate the grid
	    initiateGrid();
	    //Bind the remove score button click that removes all variables that are taoCoding_models_classes_table_GradeColumn
	    bindTogglingButtons('#rmScoreButton', '#getScoreButton', document.getActionGradeColumnUrl, 'remove');
	    //Bind the remove score button click that removes all variables that are taoCoding_models_classes_table_ResponseColumn
	    bindTogglingButtons('#rmResponseButton', '#getResponseButton', document.getActionResponseColumnUrl, 'remove');
	    //Bind the get score button click that add all variables that are taoCoding_models_classes_table_ResponseColumn
	    bindTogglingButtons('#getResponseButton', '#rmResponseButton', document.getActionResponseColumnUrl, 'add');
	    //Bind the get score button click that add all variables that are taoCoding_models_classes_table_GradeColumn
	    bindTogglingButtons('#getScoreButton', '#rmScoreButton', document.getActionGradeColumnUrl, 'add');
	    bindTogglingButtons('#viewSubject', '#removeSubject', document.getActionSubjectColumnUrl, 'add');
	    bindTogglingButtons('#removeSubject', '#viewSubject', document.getActionSubjectColumnUrl, 'remove');
	    /**
	    * Trigger the download of a csv file using the data provider used for the table display
	     */
	    $('#getCsvFile').click(function(e) {
		e.preventDefault();
		//jquery File Download is a jqueryplugin that allows to trigger a download within a Xhr request.
		//The file is being flushed in the buffer by _url('getCsvFile') 
		require([root_url  + 'tao/views/js/jquery.fileDownload.js'],
				function(data){
				$.fileDownload(document.getActionCsvFileUrl, {
				    preparingMessageHtml: __("We are preparing your report, please wait..."),
				    failMessageHtml: __("There was a problem generating your report, please try again."),
				    successCallback: function () { },
				    httpMethod: "POST",
				     ////This gives the current selection of filters (facet based query) and the list of columns selected from the client (the list of columns is not kept on the server side class.taoTable.php
				    data: {'filter': document.JsonFilter, 'columns':document.columns}
				});

				}); 
	    });
	    //binds the column chooser button taht launches the feature from jqgrid allowing to make a selection of the columns displayed
	     $('#columnChooser').click(function(e) {
		    e.preventDefault();
		    columnChooser();
	    });
    });
});
</script>
    <div class="main-container">
    <span>
    <div style="padding:10px">
    <span class="ui-state-disabled ui-corner-all" id="viewSubject">
	    <a href="#" >
		    <img src="<?=TAOBASE_WWW?>img/add.png" alt="add" /> <?=__('Add Test Taker')?>
	    </a>
    </span>
    <span class="ui-state-default ui-corner-all" id="getScoreButton">
	    <a href="#" >
		    <img src="<?=TAOBASE_WWW?>img/add.png" alt="add" /> <?=__('Add All grades')?>
	    </a>
   </span>
   <span class="ui-state-default ui-corner-all " id="getResponseButton">
	    <a href="#" >
		    <img src="<?=TAOBASE_WWW?>img/add.png" alt="add" /> <?=__('Add All responses')?>
	    </a>
    </span>
    </div>
    <div style="padding:10px">
    <span class="ui-state-default ui-corner-all" id="removeSubject">
	    <a href="#" >
		    <img src="<?=TAOBASE_WWW?>img/delete.png" alt="remove" /> <?=__('Anonymise')?>
	    </a>
    </span>
    <span class="ui-state-disabled ui-corner-all" id="rmScoreButton">
	    <a href="#" >
		    <img src="<?=TAOBASE_WWW?>img/delete.png" alt="remove" /> <?=__('Remove All grades')?>
	    </a>
    </span>
    <span class="ui-state-disabled ui-corner-all " id="rmResponseButton">
	    <a href="#" >
		    <img src="<?=TAOBASE_WWW?>img/delete.png" alt="remove" /> <?=__('Remove All responses')?>
	    </a>
    </span>
     </div>
    </div>    

    <table id="result-table-grid"></table>
    <div id="pagera1"></div>

    <div style="padding:10px">
    <span class="ui-state-default ui-corner-all" id="columnChooser">
	    <a href="#" >
		    <img src="<?=TAOBASE_WWW?>img/wf_ico.png" alt="settings" /> <?=__('Filter columns')?>
	    </a>
    </span>
     </span>
     <span class="ui-state-default ui-corner-all">
	    <a href="#" id="getCsvFile">
		    <img src="<?=TAOBASE_WWW?>img/download.png" alt="Download" /> <?=__('Download CSV File')?>
	    </a>
    </span><!--
     <span class="ui-state-disabled ui-corner-all">
	    <a href="#" id="getCsvFile">
		    <img src="<?=TAOBASE_WWW?>img/download.png" alt="Export Individual Report" /> <?=__('Download PDF File')?>
	    </a>
    </span>!-->
    </div>   
</div>


<?include(TAO_TPL_PATH.'/footer.tpl');?>
<?if(get_data('message')):?>
	<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
		<span><?=get_data('message')?></span>
	</div>
<?endif?>
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
    </span>
     <span class="ui-state-disabled ui-corner-all">
	    <a href="#" id="getCsvFile">
		    <img src="<?=TAOBASE_WWW?>img/download.png" alt="Export Individual Report" /> <?=__('Download PDF File')?>
	    </a>
    </span>
    </div>   
       
</div>
<script type="text/javascript">
require(['require', 'jquery', 'grid/tao.grid'], function(req, $) {
    /**
     * Initiate or refresh the '#result-table-grid grid with data from _url('data') and using the columns document.columns/document.models current selection of data
     */
    function showGrid() {
    $('#result-table-grid').jqGrid('GridUnload');
    var myGrid = $("#result-table-grid").jqGrid({
	url: "<?=_url('data')?>",
	postData: {'filter': <?=tao_helpers_Javascript::buildObject($filter)?>, 'columns':document.columns},
	mtype: "post",
	datatype: "json",
	colNames: document.columns.values,
	colModel: document.models,
	rowNum:20,
	height:'auto',
	width: (parseInt($("#result-table-grid").width())),
	rowNum:20,
	rowList:[5,10,30],
	pager: '#pagera1',
	sortName: 'id',
	viewrecords: true,
	rownumbers: true,
	sortorder: "asc",
	gridview : true,
	caption: __("Delivery results"),
	onCellSelect: function(rowid,iCol,cellcontent, e) {helpers.openTab(__('Delivery Result'), '<?=_url('viewResult','Results')?>?uri='+escape(rowid));}
    });
    jQuery("#result-table-grid").jqGrid('navGrid','#pagera1', {edit:false,add:false,del:false,search:false,refresh:false});
    jQuery("#result-table-grid").jqGrid('navButtonAdd',"#pagera1", {caption:"Column chooser",title:"Column chooser", buttonicon :'ui-icon-gear',onClickButton:function(){columnChooser();}});
    //jQuery("#result-table-grid").jqGrid('filterToolbar');
    }
    /**
     * Initiate the grid and shows it using a startup document.column and a document.models containing the ResultOfSubject property
     */
    function initiateGrid(){
    $.getJSON("<?=_url('getResultOfSubjectColumn')?>", <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>, function (data) {setColumns(data.columns)});
    }
    /*
    * Triggers the jquery jqGrid functionnality allowing to select columns to be displayed
    */
    function columnChooser(){
     jQuery("#result-table-grid").jqGrid('columnChooser', {"title": "Select column"});
    }
    /**
     * Update document.columns/document.models current selection of data with columns
     */
    function setColumns(columns) {
	for (key in columns) {
		 //used for the inner function set in the formatter callback
		 var currentColumn = columns[key];
		 document.columns.push(columns[key]);
		 document.models.push({
			 'name': columns[key]['label'], 
			 cellattr: function(rowId, tv, rawObject, cm, rdata){return "data-uri=void, data-type=void";},
			 formatter : function(value, options, rData){return layoutData(value,currentColumn);
			 }
		 });

	 }
	 showGrid();
    }
    /**
    * Transforms plain data into html data based on the property or based on the type of data sent out by the server (loose)
    */
    function layoutData(data, column){
    //Simple Properties 
    if (column.type == "tao_models_classes_table_PropertyColumn"){
    switch (column.prop){
	case "<?php echo PROPERTY_RESULT_OF_SUBJECT;?>": {return  "<span class=highlight>"+data+"</span>";}
	default:return data;
	}
    }
    //Grade properties
    else if ((column.type == "taoCoding_models_classes_table_GradeColumn")){
    return  "<span class=numeric>"+data+"</span>";
    }
    //Actual responses properties
    else if ((column.type == "taoCoding_models_classes_table_ResponseColumn")){
	try{
	var jsData = $.parseJSON(data);
	var formattedData = "";
	if (jsData instanceof Array) {
	    //the formatter callback expects a string to be returned, normal DOM modifications seems not to work.
	    formattedData = '<UL class="cellDataList">';
	    for (key in jsData){
		formattedData += '<li class="cellDataListElement">';
		formattedData += jsData[key];
		 formattedData += "</li>";
		}
	     formattedData += "</UL>";
	} else {
	formattedData = data;
	}
	}
	catch(err){formattedData = data;}
	return formattedData;
	}

    }
       /**
     * Update document.columns/document.models current selection of data with columns
     */
    function removeColumns(columns) {
	   //loops on the columns parameter and find out if document.columns contains this column already. 
	   for (key in columns) {
		    for (dockey in document.columns) {
		    //if the property delivery result is the same and the facet (score, response) is the same) 
		    if ((document.columns[dockey].ca == columns[key].ca) & (document.columns[dockey].type == columns[key].type)){
			 document.columns.splice(dockey,1);
			 //document.models.splice(modelkey,1);
			}
		    }

		    for (modelkey in document.models) {
		    if ((document.models[modelkey].name == columns[key].label)){
			    document.models.splice(modelkey,1);
			}
		    }
	   }
	    showGrid();
    }
    //Bind the get score button click that add all variables that are taoCoding_models_classes_table_GradeColumn
    $('#getScoreButton').click(function(e) {
	    e.preventDefault();
	    if ($('#getScoreButton').hasClass("ui-state-default")) {
	    $('#getScoreButton').addClass("ui-state-disabled").removeClass("ui-state-default");
	    $('#rmScoreButton').addClass("ui-state-default").removeClass("ui-state-disabled");
	    $.getJSON( "<?=_url('getGradeColumns')?>"
		    , <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>
		    , function (data) {
			    setColumns(data.columns)
		    }
	    );
	    }
    });
     //Bind the get score button click that add all variables that are taoCoding_models_classes_table_ResponseColumn
    $('#getResponseButton').click(function(e) {
	    e.preventDefault();
	    if ($('#getResponseButton').hasClass("ui-state-default")) {
	    $('#getResponseButton').addClass("ui-state-disabled").removeClass("ui-state-default");
	    $('#rmResponseButton').addClass("ui-state-default").removeClass("ui-state-disabled");
	    $.getJSON( "<?=_url('getResponseColumns')?>"
		    , <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>
		    , function (data) {
			    setColumns(data.columns)
		    }
	    );
	    }
    });
     //Bind the remove score button click that removes all variables that are taoCoding_models_classes_table_ResponseColumn
    $('#rmResponseButton').click(function(e) {
	    e.preventDefault();
	    if ($('#rmResponseButton').hasClass("ui-state-default")) {
	    $('#rmResponseButton').addClass("ui-state-disabled").removeClass("ui-state-default");
	    $('#getResponseButton').addClass("ui-state-default").removeClass("ui-state-disabled");
	    $.getJSON( "<?=_url('getResponseColumns')?>"
		    , <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>
		    , function (data) {
			    removeColumns(data.columns)
		    }
	    );
	    }
    });
     //Bind the remove score button click that removes all variables that are taoCoding_models_classes_table_GradeColumn
    $('#rmScoreButton').click(function(e) {
	    e.preventDefault();
	    if ($('#rmScoreButton').hasClass("ui-state-default")) {
	    $('#rmScoreButton').addClass("ui-state-disabled").removeClass("ui-state-default");
	    $('#getScoreButton').addClass("ui-state-default").removeClass("ui-state-disabled");
	    $.getJSON( "<?=_url('getGradeColumns')?>"
		    , <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>
		    , function (data) {
			   removeColumns(data.columns)
		    }
	    );
	    }
    });
    /**
    * Trigger the download of a csv file using the data provider used for the table display
     */
    $('#getCsvFile').click(function(e) {
	e.preventDefault();
	//jquery File Download is a jqueryplugin that allows to trigger a download within a Xhr request.
	//The file is being flushed in the buffer by _url('getCsvFile') 
	require([root_url  + 'tao/views/js/jquery.fileDownload.js'],
			function(data){
			$.fileDownload("<?=_url('getCsvFile')?>", {
			    preparingMessageHtml: __("We are preparing your report, please wait..."),
			    failMessageHtml: __("There was a problem generating your report, please try again."),
			    successCallback: function () { },
			    httpMethod: "POST",
			     ////This gives the current selection of filters (facet based query) and the list of columns selected from the client (the list of columns is not kept on the server side class.taoTable.php
			    data: {'filter': <?=tao_helpers_Javascript::buildObject($filter)?>, 'columns':document.columns}
			});

			}); 
    });
    $('#viewSubject').click(function(e) {
	    e.preventDefault();
	    if ($('#viewSubject').hasClass("ui-state-default")) {
	    $('#viewSubject').addClass("ui-state-disabled").removeClass("ui-state-default");
	    $('#removeSubject').addClass("ui-state-default").removeClass("ui-state-disabled");
	    $.getJSON( "<?=_url('getResultOfSubjectColumn')?>"
		    , <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>
		    , function (data) {
			    setColumns(data.columns)
		    }
	    );
	    }
    });
    $('#removeSubject').click(function(e) {
	    e.preventDefault();
	    if ($('#removeSubject').hasClass("ui-state-default")) {
	    $('#removeSubject').addClass("ui-state-disabled").removeClass("ui-state-default");
	    $('#viewSubject').addClass("ui-state-default").removeClass("ui-state-disabled");
	    $.getJSON( "<?=_url('getResultOfSubjectColumn')?>"
		    , <?=tao_helpers_Javascript::buildObject(array('filter' => $filter))?>
		    , function (data) {
			    removeColumns(data.columns)
		    }
	    );
	    }
    });
    //binds the column chooser button taht launches the feature from jqgrid allowing to make a selection of the columns displayed
     $('#columnChooser').click(function(e) {
	    e.preventDefault();
	    columnChooser();

    });

    $(function(){
	    //models and columns are parameters used and manipulated by the table operations functions. 
	    document.models = [];
	    document.columns = [];
	    initiateGrid();

    });
});
</script>
<?include(TAO_TPL_PATH.'/footer.tpl');?>
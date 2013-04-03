    /**
     * This functions transforms the data  from a response variable and add basic formatting tags.
     * It should be refactored using some explicit information about the data types found in such variables
     * @param {string} data
     * @returns {string}
     */
    
    function layoutResponse(data){
	var formattedData = "";
	//the data may be not valid json, in this case there is a silent fail and the data is returned. 
	try{
	var jsData = $.parseJSON(data);
	
	if (jsData instanceof Array) {
	    formattedData = '<UL >';
	    for (key in jsData){
		formattedData += '<li >';
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

 //Multiple Entries with epoch
 function layoutResponseArray(data){
	var formattedData = "";
	
	if (data.length> 0){
	    for (key in data) {
		    if (data[key].length==2){
		    observedData = data[key][0];
		    observedDataEpoch = data[key][1];
		    $timeAffix = "";
		    if (observedDataEpoch!= '') {$timeAffix ="<span class=epoch>("+observedDataEpoch+")</span>";}
		    formattedData +="<div>"+layoutResponse(observedData)+ " "+$timeAffix+"</div>";
		    }
	    }
	}
	return formattedData;
	}
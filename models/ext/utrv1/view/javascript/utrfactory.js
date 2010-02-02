/* 
 * Younes Djaghloul, CRP Henri Tudor Luxembourg
 * TAO transfer Project
 * UTR Task ( Ultimate Table for Result module)
 * 
 */
var speed = 333;
var initialInstancesUri = new Array();// array of instances URI
var rootClasses= new Array();//The classes of the initial Instances
var actualClass; //The class choosed

//the actual class information, note that the actualPropertySourceUri is the most important
//it is the bridge between classes and the path is build as sequence of theses URI

var actualClassUri;
var actualClassLabel = "Root Classes";
var actualPropertySourceUri='';

var classesContext = new Array(); // Context classes are in this version the Rang e of the actual class,
var propertiesContext= new Array();//the properties of the actual class

//The path taht contains a sequence of properties 

var pathProperties = new Array();

//the actual utr
var actualUTR = new Array();

//the context for undo redo
var historyAction = new Array();


//Visual intro
function utrIntro(){
   
    historyAction = new Array();//Reset history of actions
    pathProperties = new Array();
    $("#divInitialInstances").html("");
    $("#contextClasses").html("");
    $("#contextProperties").html("");
    $("#propertyBinding").hide();
    $("#divPathWizard").hide();
    $("#menuPathBuilder").hide();
    $("#pieStat").hide();

    $("#utrTemplateManager").hide();
    
    

//remove session
    
//$("#tablePreview").hide();
}

//save context as history to undo redo
function saveContext(){
    
    var action =[];
    action.path = pathProperties;
    action.actualClassUri = actualClassUri;
    action.actualClassLabel = actualClassLabel;
    //alert (action.actualClassUri);
    
    historyAction.push(action);
}
function backContext(){

    var action = new Array();

    if ( historyAction.length>0){


        action = historyAction.pop();
        //action = historyAction.pop();
        //restore path and actualClass
        pathProperties = action.path;
        pathProperties.pop();
        actualClassUri = action.actualClassUri;
        //Refrensh the interface
        //
        //alert(actualClassUri);
        if (actualClassUri == 'rootClasses'){
            actualClassLabel = "Root Classes";
            getRootClassesOfInstances();
            
        
        }else
        {
            actualClassLabel=action.actualClassLabel
            getRangeClasses(actualClassUri);
        
        
        }
    
        getProperties(actualClassUri);
    }else{
        alert("There is no parent");
    }


}

//get the initial instances that will be used to begin the process of path building
function getInitialInstances(){
    //The list of instances are extracted direcly from the server, this list is already prepared by other mechanism

    $.ajax({
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",//"b1.php",//
        data: {
            op:"listInstances"
        },
        dataType :"json",
        success: function(msg){
            //If success, we have a list of instances with this structure tab[Uri] {label}
            initialInstancesUri = msg;

            //prerview the list of Instances , Only Lable
            trli = msg;
            for (i in trli){
                p = trli[i];
                $("#divInitialInstances").append("<br>"+p.label);
            }
        }//succes

    });
    return initialInstancesUri;
}

function manageMC(){
    //alert ("ff");
    //    for (i in uriInstances){
    //        p = uriInstances[i];
    //        $("#liListInstances").append("<br>"+p.label);
    //    }
    var t = new Array();
    t=getRootClassesOfInstances();
    alert("fin");
    for (i in t){
        alert(t[i].label);
    }

}
//view the list of classes
function previewListClasses(listClasses){
    //remove old content
    var titleClass;
    //put the title in the header of the box
    titleClass = 'List of context classes: '+actualClassLabel;
    $("#contextClassHeader h1").text(titleClass);
    
    //get the actual class info
    $("#contextClasses").text('');
    for (i in listClasses){
        cl = listClasses[i];
        // we have a button with all information to acces to class info
        //content = '<input id="'+cl.uriClass+'" type="button" value="'+cl.label+'" name ="classInfos_'+cl.uriClass +'" /></input>';
        content = '<input id="'+cl.uriClass+'" class= "classInfos" type="button" value="'+cl.label+'" name ="'+cl.propertySourceUri +'" /></input>';
        //content = '<a id="younes11" href="#" onclick="getClassInfos()">'+cl.label +'</a>';//onclick="getClassInfos()
        $("#contextClasses").append(content);
    }
}
//preview the list of properties of the actual class
function previewListProperties(listProperties){
    var pl = new Array();
    var titleContextProperties;
    titleContextProperties = "List of properties :"+actualClassLabel;
    $("#contextPropertiesHeader h1").text(titleContextProperties);
    
    $("#contextProperties").text('');
    for (uriP in listProperties){
        pl = listProperties[uriP];
        //we have a button with all informations about properties
        content = '<input id="'+uriP+'" type="button" value="'+pl.label+'" name ="propertyInfos_'+uriP +'" class="property" /></input>';
        $("#contextProperties").append(content);
    }
}

//Get the list of classes according to the intial list of instances,
//the list of instances is savedd on the server side

function getRootClassesOfInstances(){
    //According to the list of instances we get the list of classes
    listClasses = new Array();
    $.ajax({
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"getClassesOfInstances"
        },
        dataType :"json",
        async : false,
        success: function(msg){
            var vide=[];
            listClasses = msg;
            rootClasses = msg;
            // preview the list of classes
            previewListClasses(rootClasses);
            previewListProperties(vide);
            //save the context
            actualClassUri = 'rootClasses';
            pathProperties = [];

        //return listClasses;
        }//succes

    });
    return listClasses;
}
//get the properties of  the class, according to the URI as parameter
function getProperties(uriC){
    
    //alert(uriC);
    $.ajax({
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"getProperties",
            uriClass:uriC
        },
        dataType :"json",
        success: function(msg){
            propertiesContext= msg;
            previewListProperties(propertiesContext);
        }//succes
    });
    return propertiesContext;
}

//get the range of a class, a range is a set iof classesthat are range of properties of the actual class
function getRangeClasses(uriC){

    //alert(uriC);
    $.ajax({
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"getRangeClasses",
            uriClass:uriC
        },
        dataType :"json",
        success: function(msg){
            classesContext= msg;
            previewListClasses(classesContext);

        }//succes
    });

    return classesContext;
}
//add a new bridge to path

function addToPath(propertyUri){

    if (propertyUri != 'undefined'){
        pathProperties.push(propertyUri);
    }
    
    // just for illustration 
    pathString =pathProperties.join("__");
    //$("#pathProp").val(pathString);

    return pathString;
}

//this method gets the properties and the range of a particular class,
//it uses getRangeClasses and get getProperties.
function getClassInfos (){
    saveContext();
    //get the URI of the class
    var uriC = $(this).attr("id");
    //get the label of the class
    var labelClass = $(this).attr("value");
    //get the propertySourceUri
    var propertySource = $(this).attr("name");// le

    //save the uri and label of the class as actual context
    actualClassUri = uriC;
    actualClassLabel = labelClass;
    actualPropertySourceUri= propertySource;

    //add to path
    addToPath(actualPropertySourceUri);

    //Save the context
    

    //alert ("l'uri est "+uri);
    //get the properties and the range of the class, this is thje next step of the process
    getRangeClasses(uriC);
    getProperties(uriC);
}
//show the div to preview the values of the property
//in order to add the column
function getPropertyBinding(){
    var uriP = $(this).attr("id");
    var labelP = $(this).attr("value");

    //add to path
    var pathString =addToPath(uriP);

    //Put the default values
    $("#columnName").val(labelP);
    $("#finalPath").val(pathString);
    $("#propertyBinding").fadeIn(speed);
}
//delete the colomn from the server side and re-preview the table

function deleteColumn(colId){
    //$colId = $(this).attr(id);
    alert ("Confirmation " + colId);
    // add the column on the server side, and preview the table after succes
    $.ajax({
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"deleteColumn",
            'columnId':colId
            
        },
        dataType :"json",
        success: function(msg){
            //alert ()
            actualUTR = msg;
            previewTable(msg);
            //close the window
            //$("#propertyBinding").hide();
            utrIntro();

        }//succes
    });
}
//Verification of the existance of the column name
function verifyColumnLabel(colLabel){
    var exist = false;//if the label exists
    var actualModel = [];
    actualModel = actualUTR['utrModel'];//get only the utrModel of the whole utrTable

    var listNames = [];


    if ( actualModel != undefined ){
        if (actualModel[colLabel]!=undefined){
            exist = true;
        }

    }else
    {
        exist = false;
    }
    return exist;
        
}

//add a column to the virtual table on server by using Ajax
function addColumn(){
    //get parameter from interface
    var cn = $("#columnName").val();
    if (cn==''){
        cn ='noName'
        };
    var te = $("#typeExtraction").val();
    var pf = $("#finalPath").val();
    //Verification of the existance of the column name
    if (verifyColumnLabel(cn)==true ){
        alert ('Name exists, you should change it...')

    }else{

        // add the column on the server side, and preview the table after succes
        $.ajax({
            type: "POST",
            url: "../classes/class.TReg_VirtualTable.php",
            data: {
                op:"addColumn",
                columnName:cn,
                typeExtraction:te,
                finalPath:pf
            },
            dataType :"json",
            success: function(msg){
                actualUTR = msg;
                previewTable(msg);
                //close the window
                //$("#propertyBinding").hide();
                utrIntro();

            }//succes
        });

    }//else
    

}
// this method prewiews the table generated from the server side, according to the the interface technique. ( jgrid, slick, simple table)
//
//in the actual version we generate an HTML table, I hope to use in the furtur more sophistacted grid...jGrid, slick...
function previewTable(table){
    //save the table in a global var
    actualUTR = table;


    finalTable = new Array();
    //get the 2 tables (Model + rowsHTML + rowsInfo)
    finalUtrModel = table.utrModel;//

    //use the rowsHTML to generate the body part of the table
    finalRowsHTML = table.rowsHTML;//the content that will be used to generate the html Table

    //get the rows infor
    finalRowsInfo = table.rowsInfo;

    //generate the header
    var strTableHead = '';
    var strHeadNameColomn ='';
    //add the first column of rowStat
    strTH ='<th> Columns </th>';
    for ( i in finalUtrModel){
        var columnDescription = finalUtrModel[i];
        //get columnDescription
        var columnName = columnDescription['columnName'];
        var totalRows = columnDescription["totalRows"];
        var totalRowsNotNull=columnDescription["totalRowsNotNull"] ;

        columnLabel = columnName ;
        //calculate the pourcentage and add a new header
        pourcentageCol = totalRowsNotNull + '/'+totalRows;
        //
        //button delete
        var btnDelete = '<input id='+i+' title="Delete column" type="button" value="" class = "deleteColumnClass"/></input>';
        var btnInfo = '<input id='+i+' title="Info column" type="button" value="Info" class = "infoColumnClass"/></input>';

        strTH = strTH+ '<th>'+btnDelete +" "+btnInfo+'<br>'+columnLabel+'</th>';
    }
    strHeadNameColomn = strTH;
    //the sat of column
     
    strTH ='<th> % </th>';
    var strStatColumn = '';
    for ( i in finalUtrModel){
        columnDescription = finalUtrModel[i];
        //get columnDescription
        //columnName = columnDescription["columnName"];
        totalRows = columnDescription["totalRows"];
        totalRowsNotNull=columnDescription["totalRowsNotNull"] ;

        
        //calculate the pourcentage and add a new header
        pourcentageCol = (parseFloat(totalRowsNotNull/totalRows)*100).toFixed(2);
        columnLabel =  pourcentageCol;
        strTH = strTH+ '<th>'+columnLabel+'</th>';
    }
    strStatColumn = strTH;

    //final head
    strTableHead = '<tr>'+strHeadNameColomn+'</tr>' + '<tr>'+strStatColumn+'</tr>';

    //put in the table head
    $("#utrHead").html(strTableHead);

    //the body of the table
    strTableBody = '';//the html code of the table body

    for (uri in finalRowsHTML){
        //get the value of the row
        rowHTML = finalRowsHTML[uri];
        //get statistic info of rows
        var rowInfo = finalRowsInfo[uri];
        var totalColumns = rowInfo["totalColumns"];
        var totalColumnsNotNull=rowInfo['totalColumnsNotNull']
        
        var pourcentageRow = parseFloat(totalColumnsNotNull/totalColumns)*100;
        pourcentageRow = pourcentageRow.toFixed(2);

        //generate the html tag for table
        strTR = '';//initialize the row
        strTD = '<td class="statRow"> <input class = "statCheck" type="checkbox" name="rowCheck" value="'+uri+'" >'+pourcentageRow+'</input> </td>';//initialize the cell
        //build the data of the row, a set of cells
        for ( i in rowHTML){
            var cellValue = '<pre>'+rowHTML[i]+'</pre>';//
            strTD = strTD+'<td>'+cellValue+'</td>';
        }
        //create the row
        strTR = '<tr id = "'+ uri+'">'+strTD+'</tr>';
        strTableBody = strTableBody + strTR;
    }
    //alert (strTableBody);
    $("#utrBody").html(strTableBody);


}



function removeSession(){
    $.ajax({
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"removeSession"
            
        },
        //dataType :"json",
        success: function(msg){
            //alert(msg);
            //close the window
            //$("#propertyBinding").hide();
            utrIntro();

        }//succes
    });
}
function showColumnInfo(colId){
    
    //get column description
    var raphael;
    var columnDescription = new Array();
    var utrm = actualUTR.utrModel;
    columnDescription = utrm[colId];
    
    var totalRows = columnDescription['totalRows'];
    var totalRowsNotNull=columnDescription["totalRowsNotNull"] ;
    //alert (totalRows+ " -- "+totalRowsNotNull);
   

    $("#pieStat").slideDown();
    //put the two arrays of value and labels
    var pieValues = [],
    pieLabels = [];
    var pourcentageRowNotNull = parseFloat(totalRowsNotNull/totalRows)*100;
    var pourcentageRowNull =100-pourcentageRowNotNull;
    pieValues.push(pourcentageRowNotNull);
    pieValues.push(pourcentageRowNull);
    pieLabels.push('Not Null');
    pieLabels.push('Null');
    
    

    (function (raphael) {
        $(function () {
            //alert ("gfghfdgh"+totalRows+ " -- "+totalRowsNotNull);
            raphael("pieStat", 540, 370).pieChart(270, 200, 120, pieValues, pieLabels, "#fff");
            
            
        });
    })(Raphael.ninja());

}

//delete the list of rows chosed bu the user
//
function deleteListRows(){
    //get the list of selected rows, from the vlaue attribut of rowStat class
    var listRows =[];
    var listRowsString = '';
    //get only the selected row 
    $(".statCheck:checked").each(function(){
        //if ($(this).attr("checked")=)
        codeRow = $(this).attr("value");

        listRows.push(codeRow);
        
    });
    listRowsString = listRows.join('|');

    //using ajax, send thelist to delete

    options={
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"deleteListRows",
            listRowsToDelete: listRowsString

        },
        dataType:"json",
        success: function(msg){
            //get the new UTR table
            actualUTR = msg;
            
            previewTable(msg);
            //close the window
            //$("#propertyBinding").hide();
            utrIntro();

        }


    };
    $.ajax(options);

}
//request to save the actual utrModel
function saveUtr(){
    var modelName = $("#txtUtrName").val();
    options={
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"saveUtr",
            idModel: modelName

        },
        //dataType:"json",
        success: function(msg){
            alert (msg);
            utrIntro();
        
        }


    };
    $.ajax(options);
    


}
function getUtrTemplate(){
    var modelName = $(this).attr('id');
    loadUtr(modelName)

}
function loadUtr(modelName){
    //modelName = $("#txtUtrName").val();
    options={
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"loadUtr",
            idModel: modelName

        },
        dataType:"json",
        success: function(msg){
            //get the new UTR table
            actualUTR = msg;
            previewTable(msg);
            //close the window
            //$("#propertyBinding").hide();
            utrIntro();

        }

    };
    $.ajax(options);
}

function getUtrModels(){
    $("#utrTemplateManager").show(speed);
    $("#txtUtrName").focus();
    options={
        type: "POST",
        url: "../classes/class.TReg_VirtualTable.php",
        data: {
            op:"getUtrModels"
        },
        dataType:"json",
        success: function(msg){
            //get the new UTR table
            listUtr = msg;
            //alert (msg);

            //preview the list in the div
            $("#utrTemplateModelList").html("");
            for (i in listUtr){

                cl = listUtr[i];
                content = '<input id="'+i+'" class= "utrTemplate" type="button" value="'+i+'" name ="cl.propertySourceUri " /></input>';
                $("#utrTemplateModelList").append(content);
            }

            
            //close the window
            //$("#propertyBinding").hide();
            //utrIntro();
        }

    };
    $.ajax(options);

}

//manage the event of the index page
function manageEvents(){
    //alert ("manage");
    $("#closePathBuilder").click(function (){
        $("#divPathWizard").slideUp(speed);
    });
    $("#getInitialInstances").click(getInitialInstances);
    $("#getRootClasses").click(getRootClassesOfInstances);
    //get class infos and create path
    $("input[class *='classInfos']").live('click',getClassInfos);
    $("input[name *='propertyInfos']").live('click', getPropertyBinding);
    $("#remove").click(removeSession);
    //hide the statistic info
    $("input[class = 'utrTemplate']").live('click',getUtrTemplate);

    

    //add column event
    $("#addColumn").click(addColumn);
    $("#exitAddColumn").click(function(){
        $("#propertyBinding").fadeOut(speed*2);
        //delete the last property in the path
        pathProperties.pop();
    });
    //delete column
    $(".deleteColumnClass").live('click',function(){
        //get parameter
        
        var colId = $(this).attr("id");
        //alert ("delete "+ colId);
        deleteColumn(colId);
    });
    $("#columnBuilder").click(function(){
        //reset patth and history of actions
        historyAction = new Array();//Reset history of actions
        pathProperties = new Array();


        //show the path bulder div
        $("#divPathWizard").show(speed*2);

        getRootClassesOfInstances();

    });
    $("#backClass").click(backContext);
    //
    
    $("#hidePieStat").click(function (){
        $("#pieStat").slideUp(speed);
    });

    //show column detail
    $(".infoColumnClass").live('click', function(){
        //get class id
        var colId = $(this).attr("id");
        showColumnInfo(colId);


    });

    //Manage delete row
    $("#deleteListRows").click(deleteListRows);

    $("#saveUtrBtn").click(saveUtr);
    $("#loadUtrBtn").click(loadUtr);

    $("#manageUtr").click(function(){
        /*$("#saveUtrBtn").toggle();
        $("#loadUtrBtn").toggle();
        $("#txtUtrName").toggle();*/

        getUtrModels();


    });
    //close utrManager
    $("#cancelUtrManager").click(function(){
        $("#utrTemplateManager").hide(speed);


    });

    



}
function utrConstructor(){
    $(function(){
        //alert("o jQuery");
        //   $("#coco").hide();
        //   $("#coco").toggle(1000);
        //remove session, neww table

        removeSession();
        utrIntro();
        manageEvents();
    });

}

this.utrConstructor();




var iBInfo = [];
var revNumber = 'rev1';
var currentPassedItem='';
var currentRicUri='';

function revIntro(){
    $("#reviewersReport").hide();
    $("#reviewContainer").hide();

    

    
}
function revConstructor(){
    $(function(){
        //$("#container").accordion({active:1,animated: 'bounceslide',event:'click',icons: { 'header': 'ui-icon-print', 'headerSelected': 'ui-icon-minus' }});
        revIntro();

        manageEvents();
        //getItemBehaviorInformation();
        
        getListOfTestees();
     
        //var t =[];
        t = getCurrentRevTestItem();

        //get ric Info
        
        var ricInfo = getRicInformation(t['idRev'],t['idTest'],t['idItem']);
        var capacity = ricInfo['capacity'];
        var comment = ricInfo['comment'];
        currentRicUri = ricInfo['uriRic'];
        if ( capacity =='yes'){
            $("#chCapacity").attr("checked", "checked");
        }else{
            $("#chCapacity").removeAttr("checked");
        }
        $("#currentCapacity").val(capacity);
        $('#currentComment').val(comment);

        getRicAllReviewers();
            

       
    });
}

//Manage events
function manageEvents(){
    $("#okRev").click(function(){
        setRevInformation(revNumber);

    });
   
    $("#okRevFinal").click(function(){
        setRevInformation('revf');

    });
    $("#confirmCapacity").click(function(){
        var capacity = $("#currentCapacity").val();
        var comment = $('#currentComment').val();
        //alert($("#chCapacity").attr("checked"));
        var capacity='';
        if ($("#chCapacity").attr("checked")){
            capacity = 'yes';
        }
        else{
            capacity = 'no';
        }
        setRicInfo(currentRicUri,capacity,comment);
    });

}

//set reviewer info
function setRevInformation(revNumber){
    
    var item = iBInfo[0];
    //revNumber = item['revNumber'];

    // get the instance of the pased item
    var currentItemreviewed = item['uriPassedItem'];
    //get reviewer info
    var currentRevId =$("#revId").val();
    var currentRevComment = $("#revComment").val();
    var currentRevEndorsement =$("#revEndorsement").val();

    if ( revNumber == 'revf'){

        var currentItemreviewed = item['uriPassedItem'];
        //get reviwer info
        var currentRevId ='younes';
        var currentRevComment = $("#revComment_Final").val();

        var currentRevEndorsement =$("#revEndorsement_Final").val();
    }

    options={
        type: "POST",
        url: "../classes/class.ReviewResult.php",
        data: {
            revOp:"setReviewInformation",
            revNum:revNumber,
            uriItemReviewed:currentItemreviewed,
            revId:currentRevId,
            revComment:currentRevComment,
            revEndorsement:currentRevEndorsement
        },
        success: function(msg){
        //alert(msg);
        alert ("Review Submited!")

        }

    };
    $.ajax(options);
}


//get all item information

function getItemBehaviorInformation(subjectId){
    options={
        type: "POST",
        url: "../classes/class.ReviewResult.php",
        data: {
            revOp:"getItermBehaviorInformation",
            revSubjectId : subjectId
        },
        dataType:"json",
        success: function(msg){
            iBInfo= msg;

            previewReviewItemInformation();
            
        }

    };
    $.ajax(options);
   
}

//get list of testees
function getListOfTestees(){
    options={
        type: "POST",
        url: "../classes/class.ReviewResult.php",
        data: {
            revOp:"getListOfTestees"
        },
        dataType:"json",
        success: function(msg){
            list= msg;
            perviewListTestees(list)

        }

    };
    $.ajax(options);

}

//preview the list of testees
function perviewListTestees(list){
    content = '';
    var testee =[];
    for (i in list){
        testee=list[i];
        
        revSubjectLabel=testee['idTesteeLabel'];
        revSubjectId = "'"+testee['idSubject']+"'";
        link = 'revService.php?'+revSubjectId;
        //content =  content = '<input id="'+cl.uriClass+'" class= "classInfos" type="button" value="'+cl.label+'" name ="'+cl.propertySourceUri +'" /></input>';
    

        content = content + '<a href ="#" OnClick ="getItemBehaviorInformation('+revSubjectId+');">Test taker: '+revSubjectLabel+'</a>'+'<br>';

    }
    $("#listTestees").text('');
    $("#listTestees").append(content);
 
}


function previewReviewItemInformation(){

    var testedItem = iBInfo[0];
    revNumber = testedItem['revNumber'];

    responceOfTestee = decodeURI(testedItem['endorsement']);
    //alert(testedItem['iDTest'])
    var testId = testedItem['iDTestLabel'];
    var subjectId = testedItem['subjectIdLabel'];
    var itemId = testedItem ['itemIdLabel'];

    $("#subjectId").text(subjectId);
    $("#testId").text(testId);
    $("#itemId").text(itemId);

    
    if (revNumber =='rev1'){

        var revId= testedItem['revId_1Label'];
        var revComment= testedItem['revComment_1'];
        var revEndorsement= testedItem['revEndorsement_1'];
        $("#reviewContainer").show();

    }

    if (revNumber =='rev2'){

        var revId= testedItem['revId_2Label'];
        var revComment= testedItem['revComment_2'];
        var revEndorsement= testedItem['revEndorsement_2'];
        $("#reviewContainer").show();

    }

    if (revNumber =='rev3'){

        var revId= testedItem['revId_3Label'];
        var revComment= testedItem['revComment_3'];
        var revEndorsement= testedItem['revEndorsement_3'];
        $("#reviewContainer").show();

    }

    if (revNumber =='rev4'){

        var revId= testedItem['revId_4Label'];
        var revComment= testedItem['revComment_4'];
        var revEndorsement= testedItem['revEndorsement_4'];
        $("#reviewContainer").show();
    }
    
    //  feed the input box
    $("#responceOfTestee").val(responceOfTestee)
    $("#revId").val(revId);
    $("#revEndorsement").val(revEndorsement);
    $("#revComment").val(revComment);


    if (revNumber =='revf'){
        var revComment_Final= testedItem['revComment_Final'];
        var revEndorsement_Final= testedItem['revEndorsement_Final'];

        $("#revComment_Final").val(revComment_Final);
        $("#revEndorsement_Final").val(revEndorsement_Final);
        //show reviewer div
        $("#revZone").hide();
        $("#reviewersReport").show();

        var revId_1 = testedItem['revId_1Label'];
        var revComment_1= testedItem['revComment_1'];
        var revEndorsement_1= testedItem['revEndorsement_1'];

        var revId_2= testedItem['revId_2Label'];
        var revComment_2= testedItem['revComment_2'];
        var revEndorsement_2= testedItem['revEndorsement_2'];

        var revId_3= testedItem['revId_3Label'];
        var revComment_3= testedItem['revComment_3'];
        var revEndorsement_3= testedItem['revEndorsement_3'];

        var revId_4= testedItem['revId_4Label'];
        var revComment_4= testedItem['revComment_4'];
        var revEndorsement_4= testedItem['revEndorsement_4'];


        var revComment_Final= testedItem['revComment_Final'];
        var revEndorsement_Final= testedItem['revEndorsement_Final'];

        //html
        $("#responceOfTestee").val(responceOfTestee)
        $("#revId_1").val(revId_1);
        $("#revEndorsement_1").val(revEndorsement_1);
        $("#revComment_1").val(revComment_1);

        $("#revId_2").val(revId_2);
        $("#revEndorsement_2").val(revEndorsement_2);
        $("#revComment_2").val(revComment_2);

        $("#revId_3").val(revId_3);
        $("#revEndorsement_3").val(revEndorsement_3);
        $("#revComment_3").val(revComment_3);

        $("#revId_4").val(revId_4);
        $("#revEndorsement_4").val(revEndorsement_4);
        $("#revComment_4").val(revComment_4);


        $("#revComment_Final").val(revComment_Final);
        $("#revEndorsement_Final").val(revEndorsement_Final);


    // get all ric of reviewers
        
        

    }

/*var revId_1 = testedItem['revId_1'];
    var revComment_1= testedItem['revComment_1'];
    var revEndorsement_1= testedItem['revEndorsement_1'];

    var revId_2= testedItem['revId_2'];
    var revComment_2= testedItem['revComment_2'];
    var revEndorsement_2= testedItem['revEndorsement_2'];


    var revComment_Final= testedItem['revComment_Final'];
    var revEndorsement_Final= testedItem['revEndorsement_Final'];

    //html
    $("#responceOfTestee").val(responceOfTestee)
    $("#revId_1").val(revId_1);
    $("#revEndorsement_1").val(revEndorsement_1);
    $("#revComment_1").val(revComment_1);

    $("#revId_2").val(revId_2);
    $("#revEndorsement_2").val(revEndorsement_2);
    $("#revComment_2").val(revComment_2);

    $("#revComment_Final").val(revComment_Final);
    $("#revEndorsement_Final").val(revEndorsement_Final);*/
$("#reviewContainer").show();

}
//get RIC information
function getRicInformation(idRev, idTest,idItem){
    

    var ricInfo=[];
    options={
        type: "POST",
        url: "../classes/class.revItemCapacity.php",
        data: {
            revOp:"getRicInformation",
            ricRev:idRev,
            ricTest:idTest,
            ricItem:idItem

        },
        dataType:"json",
        async:false,
        success: function(msg){
            ricInfo= msg;
        //perviewListTestees(list)
            
        //previewReviewItemInformation();

        }

    };
    $.ajax(options);
    return ricInfo;

}
//set the ric information
function setRicInfo(ricUri,ricCapacity,ricComment){
    
    options={
        type: "POST",
        url: "../classes/class.revItemCapacity.php",
        data: {
            revOp:"setRicInformation",
            ricUriS:ricUri,
            ricCapacityS:ricCapacity,
            ricCommentS:ricComment

        },
        
        
        success: function(msg){
            alert(msg)
        //perviewListTestees(list)

        //previewReviewItemInformation();

        }

    };
    $.ajax(options);
   

}


//get current rev item, test
function getCurrentRevTestItem(){
    var t=[];
    options={
        type: "POST",
        url: "../classes/class.ReviewResult.php",
        data: {
            revOp:"getCurrentRevTestItem"

        },
        dataType:"json",
        async:false,
        success: function(msg){
            
            t= msg;
        //perviewListTestees(list)
  
        //previewReviewItemInformation();

        }

    };
    $.ajax(options);
    return t;
}
// get all ric of this review
function getRicAllReviewers(){
    var t=[];
    options={
        type: "POST",
        url: "../classes/class.revItemCapacity.php",
        data: {
            revOp:"getRicAllReviewers"
  
        },
        dataType:"json",
 
        success: function(msg){
            var allRic= msg;
            // preview all ric od reviewers
            var ric =[];
            var contentRic = '';

            for ( i in allRic){
                ric = allRic[i];
                //<input type="checkbox" name="" value="ON" checked="checked" />
                var checkCapacity = ' <input type="checkbox" name="" value="ON" />';
                var divClass = "ui-widget-content";
                if (ric['capacity']=='yes'){
                    checkCapacity = ' <input type="checkbox" name="" value="ON" checked="checked" />';
                    divClass = "ui-state-error";

                };
                
                contentRic = contentRic +'<div class="'+divClass+'" style=" margin-top: 1px">'+
                '<div class="ui-widget-header ui-corner-top ui-state-default"> <h1>'+ric['labelRev']+' item capacity </h1> </div>'+
                '<div class="ui-priority-primary " ></div>'+
                ' <table border="0">'+

                '<tbody>'+
                ' <tr>'+
                '    <td>Problem ?</td>'+
                '   <td>'+checkCapacity+'</td>'+
                '</tr>'+
                '<tr>'+
                '<td>Comment</td>'+
                '<td><textarea name="" rows="4" cols="18">'+ric['comment']+' </textarea></td>'+
                '</tr>'+

                '  </tbody>'+
                '   </table>'+

                '</div>';

    

            }
           
            //put the new content in the div
            
                        
            $("#ricAllReviewers").append(contentRic);



        }

    };
    $.ajax(options);
   
}

//pre

revConstructor();





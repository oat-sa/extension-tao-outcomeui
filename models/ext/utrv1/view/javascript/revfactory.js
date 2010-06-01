var iBInfo = [];
var revNumber = 'rev1';
var currentPassedItem='';

function revIntro(){
    $("#reviewersReport").hide();
    $("#revZone").hide();
    
}
function revConstructor(){
    $(function(){
        //$("#container").accordion({active:1,animated: 'bounceslide',event:'click',icons: { 'header': 'ui-icon-print', 'headerSelected': 'ui-icon-minus' }});
        revIntro();

        manageEvents();
        getItemBehaviorInformation();
       
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

}
//set reviewer info
function setRevInformationold(revNumber){
    alert (revNumber);
    var item = iBInfo[0];
    //revNumber = item['revNumber'];

    // get the instance of the pased item
   

    var currentItemreviewed = item['uriPassedItem'];
    //get reviwer info
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
     
        }

    };
    $.ajax(options);
}

//set reviewer info
function setRevInformation(revNumber){
    alert (revNumber);
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

        }

    };
    $.ajax(options);
}


//get all item information

function getItemBehaviorInformation(){
    options={
        type: "POST",
        url: "../classes/class.ReviewResult.php",
        data: {
            revOp:"getItermBehaviorInformation"
        },
        dataType:"json",
        success: function(msg){
            iBInfo= msg;

            previewReviewtemInformation();
            
        }

    };
    $.ajax(options);
    
}

//to create html presentation of the item behavior information

function previewAllItemInformation(){
    var testedItem = iBInfo[0];
    revNumber = testedItem['revNumber'];
    
    responceOfTestee = decodeURI(testedItem['endorsement']);
    //alert(testedItem['iDTest'])
    var testId = testedItem['iDTest'];
    var subjectId = testedItem['subjectId'];
    var itemId = testedItem ['itemId'];

    if (revNumber =='rev1'){
 
        var revId= testedItem['revId_1'];
        var revComment= testedItem['revComment_1'];
        var revEndorsement= testedItem['revEndorsement_1'];

    }

    if (revNumber =='rev2'){

        var revId= testedItem['revId_2'];
        var revComment= testedItem['revComment_2'];
        var revEndorsement= testedItem['revEndorsement_2'];
  
    }

    if (revNumber =='revFinal'){
        var revComment_Final= testedItem['revComment_Final'];
        var revEndorsement_Final= testedItem['revEndorsement_Final'];

        $("#revComment_Final").val(revComment_Final);
        $("#revEndorsement_Final").val(revEndorsement_Final);


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

}


function previewReviewtemInformation(){
    var testedItem = iBInfo[0];
    revNumber = testedItem['revNumber'];


    responceOfTestee = decodeURI(testedItem['endorsement']);
    //alert(testedItem['iDTest'])
    var testId = testedItem['iDTest'];
    var subjectId = testedItem['subjectId'];
    var itemId = testedItem ['itemId'];

    $("#subjectId").text(subjectId);
    $("#testId").text(testId);
    $("#itemId").text(itemId);

    if (revNumber)
        if (revNumber =='rev1'){

            var revId= testedItem['revId_1'];
            var revComment= testedItem['revComment_1'];
            var revEndorsement= testedItem['revEndorsement_1'];
            $("#revZone").show();

        }

    if (revNumber =='rev2'){

        var revId= testedItem['revId_2'];
        var revComment= testedItem['revComment_2'];
        var revEndorsement= testedItem['revEndorsement_2'];
        $("#revZone").show();

    }
    

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

        var revId_1 = testedItem['revId_1'];
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
        $("#revEndorsement_Final").val(revEndorsement_Final);





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




}

revConstructor();





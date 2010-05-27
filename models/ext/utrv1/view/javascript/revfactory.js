var iBInfo = [];
var currentPassedItem='';
function revConstructor(){
    $(function(){
        manageEvents();
        getItemBehaviorInformation();
       

    });
}

//Manage events
function manageEvents(){
    $("#okRev1").click(function(){
        setRevInformation('rev1');

    });
    $("#okRev2").click(function(){
        setRevInformation('rev2');
    });

    $("#okRevFinal").click(function(){
        setRevInformation('revf');

    });

}
//set reviewer info
function setRevInformation(revNumber){
alert (revNumber);
var item = iBInfo[0];
// get the instance of the pased item

var currentItemreviewed = item['uriPassedItem'];
//get reviwer info
var currentRevId =$("#revId_1").val();
var currentRevComment = $("#revComment_1").val();

var currentRevEndorsement =$("#revEndorsement_1").val();

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
        dataType:"json",
        success: function(msg){
            alert('msg');
            

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

            previewAllItemInformation();
            
        }

    };
    $.ajax(options);
    
}

//to create html presentation of the item behavior information

function previewAllItemInformation(){
    var testedItem =iBInfo[0];
    
    responceOfTestee = decodeURI(testedItem['endorsement']);
    //alert(testedItem['iDTest'])
    var testId = testedItem['iDTest'];
    var subjectId = testedItem['subjectId'];
    var itemId = testedItem ['itemId'];

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






revConstructor();




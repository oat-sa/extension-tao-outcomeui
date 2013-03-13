/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
var iBInfo = [];
var revNumber = 'rev1';
//var currentPassedItem='';

function revIntro(){
    
    
}
function revConstructor(){
    $(function(){
        //$("#container").accordion({active:1,animated: 'bounceslide',event:'click',icons: { 'header': 'ui-icon-print', 'headerSelected': 'ui-icon-minus' }});
        

        manageEvents();
        getReviewReport();
       
    });
}

//Manage events
function manageEvents(){
    
}


//get all item information
function  getReviewReport(){
    options={
        type: "POST",
        url: "../classes/class.ReviewResult.php",
        data: {
            revOp:"getItermBehaviorInformation"
        },
        dataType:"json",
        success: function(msg){
            iBInfo= msg;

            previewReviewItemInformation();
            
        }

    };
    $.ajax(options);
    
}

//preview all the information about the reviewed item
function previewReviewItemInformation(){
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


    var revComment_Final= testedItem['revComment_Final'];
    var revEndorsement_Final= testedItem['revEndorsement_Final'];

    $("#revComment_Final").val(revComment_Final);
    $("#revEndorsement_Final").val(revEndorsement_Final);
    //show reviewer div

    $("#revZone").hide();
    $("#reviewersReport").show();
    $("#ricAllReviewers").show();


    var revId_1 = testedItem['revId_1'];
    var revComment_1= testedItem['revComment_1'];
    var revEndorsement_1= testedItem['revEndorsement_1'];

    var revId_2= testedItem['revId_2'];
    var revComment_2= testedItem['revComment_2'];
    var revEndorsement_2= testedItem['revEndorsement_2'];


    var revId_3= testedItem['revId_3'];
    var revComment_3= testedItem['revComment_3'];
    var revEndorsement_3= testedItem['revEndorsement_3'];

    var revId_4= testedItem['revId_4'];

    var revComment_4= testedItem['revComment_4'];
    var revEndorsement_4= testedItem['revEndorsement_4'];


    var revComment_Final= testedItem['revComment_Final'];
    var revEndorsement_Final= testedItem['revEndorsement_Final'];

    //html
    $("#responceOfTestee").text(responceOfTestee)
    $("#revId_1").text(revId_1);
    $("#revEndorsement_1").text(revEndorsement_1);
    $("#revComment_1").text(revComment_1);

    $("#revId_2").text(revId_2);
    $("#revEndorsement_2").text(revEndorsement_2);
    $("#revComment_2").text(revComment_2);

    $("#revId_3").text(revId_3);
    $("#revEndorsement_3").text(revEndorsement_3);
    $("#revComment_3").text(revComment_3);

    $("#revId_4").text(revId_4);
    $("#revEndorsement_4").text(revEndorsement_4);
    $("#revComment_4").text(revComment_4);

    $("#revComment_Final").text(revComment_Final);
    $("#revEndorsement_Final").text(revEndorsement_Final);




}

revConstructor();





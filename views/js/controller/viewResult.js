define([
    'module', 
    'jquery', 
    'i18n', 
    'helpers', 
    'layout/section',
    'jquery.fileDownload'
], function (module, $,  __,  helpers, section) {
        
       return {
        start : function(){
           var conf = module.config();
           var $container = $('#view-result');              
           var $filterField = $('.result-filter', $container);              

            $filterField.select2({
                minimumResultsForSearch : -1
            }).select2('val', conf.filter || 'all');

            $('.result-filter-btn', $container).click(function(e) {
                section.loadContentBlock(
                    helpers._url('viewResult', 'Results', 'taoResults'), {
                    uri: conf.uri,
                    classUri:  conf.classUri,
                    filter: $filterField.select2('val')
                });
            });

            $('.download', $container).on("click", function (e) {
                var variableUri = $(this).val();
                $.fileDownload(helpers._url('getFile', 'Results', 'taoResults'), {
                    preparingMessageHtml: __("We are preparing your report, please wait..."),
                    failMessageHtml: __("There was a problem generating your report, please try again."),
                    httpMethod: "POST",
                    //This gives the current selection of filters (facet based query) and the list of columns selected from the client (the list of columns is not kept on the server side class.taoTable.php
                    data: {'variableUri': variableUri}
                });
            });
        }
    };
});

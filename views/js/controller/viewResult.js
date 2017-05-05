/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'jquery',
    'i18n',
    'util/url',
    'layout/section',
    'taoItems/preview/preview',
    'jquery.fileDownload'
], function (module, $,  __,  urlHelper, section, preview) {
    'use strict';

    /**
     * @exports taoOutcomeUi/controller/viewResult
     */
    var viewResultController =  {

        /**
         * Controller entry point
         */
        start : function(){
           var conf = module.config();
           var $container = $('#view-result');
           var $resultFilterField = $('.result-filter', $container);
           var $classFilterField = $('[name="class-filter"]', $container);
           var classFilter = JSON.parse(conf.filterTypes) || [];
            //set up filter field
            $resultFilterField.select2({
                minimumResultsForSearch : -1
            }).select2('val', conf.filterSubmission || 'all');

            for(var i in classFilter){
                $('[value="'+classFilter[i]+'"]').prop('checked', 'checked');
            }

            $('.result-filter-btn', $container).click(function(e) {
                classFilter = [''];
                $classFilterField.each(function(){
                    if($(this).prop('checked')){
                        classFilter.push($(this).val());
                    }
                });
                section.loadContentBlock(
                    urlHelper.route('viewResult', 'Results', 'taoOutcomeUi'), {
                    id: conf.id,
                    classUri:  conf.classUri,
                    filterSubmission: $resultFilterField.select2('val'),
                    filterTypes: classFilter
                });
            });


            //bind the download buttons
            $('.download', $container).on("click", function (e) {
                var variableUri = $(this).val();
                $.fileDownload(urlHelper.route('getFile', 'Results', 'taoOutcomeUi'), {
                    preparingMessageHtml: __("We are preparing your report, please wait..."),
                    failMessageHtml: __("There was a problem generating your report, please try again."),
                    httpMethod: "POST",
                    //This gives the current selection of filters (facet based query) and the list of columns selected from the client (the list of columns is not kept on the server side class.taoTable.php
                    data: {'variableUri': variableUri, 'deliveryUri':conf.classUri}
                });
            });

            $('.preview', $container).on("click", function (e) {
                var $this = $(this);
                var uri = $this.data('uri');
                var state = $this.data('state');
                e.preventDefault();
                window.scrollTo(0,0);
                preview.init(urlHelper.route('forwardMe', 'ItemPreview', 'taoItems', {uri : uri}), state);
                preview.show();
            });

        }
    };

    return viewResultController;
});

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'i18n', 
    'module',
    'helpers', 
    'layout/actions/binder',
    'uri',
    'ui/feedback',
    'util/encode',
    'ui/datatable',
    'jquery.fileDownload'
], function($, __, module, helpers, binder, uri, feedback, encode) {
    'use strict';

    /**
     * Load results into the datatable
     * @param {jQueryElement} $container - the  container 
     * @param {Object} model - the data model for the table
     * @param {String} classUri - The delivery result uri
     */
    function loadResults($container, model, classUri){
        var params = {
            classUri : classUri
        };
        $('.inspect-results-grid', $container)
            .empty()
            .data('ui.datatable', null)
            .datatable({ 
                url  : helpers._url('getResults', 'Results', 'taoOutcomeUi', params),
                model : model,
                actions : {
                    'view' : function openResource(id){
                        var action = {binding : "load", url: helpers._url('viewResult', 'Results', 'taoOutcomeUi')};
                        binder.exec(action, {id: id, classUri : uri.decode(classUri)} || this._resourceContext);
                    },
                    'delete' : function deleteResource(id){
                        // prompt a confirmation lightbox and then delete the result
                        var confirmBox = $('.preview-modal-feedback'),
                            cancel = confirmBox.find('.cancel'),
                            save = confirmBox.find('.save'),
                            close = confirmBox.find('.modal-close');

                        confirmBox.modal({ width: 500 });

                        save.off('click')
                            .on('click', function () {
                            $.ajax({
                                url: helpers._url('delete', 'Results', 'taoOutcomeUi'),
                                type: "POST",
                                data: {
                                    uri: uri.encode(id)
                                },
                                dataType: 'json',
                                success: function(response){
                                    if(response.deleted){
                                        feedback().success(__('Result has been deleted'));
                                        loadResults($container,model,classUri);
                                    }
                                    else{
                                        feedback().error(__('Something went wrong ...')+'<br>'+encode.html(response.error), {encodeHtml: false});
                                    }
                                }
                            });
                            confirmBox.modal('close');
                        });

                        cancel.off('click')
                              .on('click', function () {
                            confirmBox.modal('close');
                        });
                    },
                    'download' : function downloadResource(id){
                        $.fileDownload(helpers._url('downloadXML', 'Results', 'taoOutcomeUi'), {
                            preparingMessageHtml: __("We are preparing your report, please wait..."),
                            failMessageHtml: __("There was a problem generating your report, please try again."),
                            httpMethod: 'GET',
                            data: { 'deliveryExecution': id }
                        });
                    }
                }
            });
    }

    /**
     * @exports taoOutcomeUi/controller/resultTable
     */
    var inspectResultController =  {

        /**
         * Controller entry point
         */
        start : function(){
            var $container = $('#inspect-result');
            //load results also at the beginning unfiltered
            loadResults($container, $container.data('model'), $container.data('uri'));

            binder.register('download_csv', function (item) {
                $.fileDownload(this.url, {
                    httpMethod: 'GET',
                    data: {uri : item.uri}
                });
            });
        }
    };

    return inspectResultController;
});

<div class="data-container-wrapper col-12">
    <div class="grid-row">
        <a href="#" id="export-delivery-results" class="btn-info">
            <span class="icon-export"></span>
            <?=__('Results export for delivery "%s"', get_data('label'));?>
        </a>
    </div>
</div>
<div class="data-container-wrapper col-12">
    <div id="task-list"></div>
</div>

<script>
    require([
            'jquery',
            'i18n',
            'helpers',
            'ui/feedback',
            'ui/taskQueue/table'
        ],
        function($, __, helpers, feedback, taskQueueTableFactory) {

            var $queueArea = $('#task-list');
            var taskQueueTable = taskQueueTableFactory({
                rows : 10,
                context : '<?= get_data("context"); ?>',
                dataUrl : helpers._url('getTasks', 'TaskQueueData', 'tao'),
                statusUrl : helpers._url('getStatus', 'TaskQueueData', 'tao'),
                removeUrl : helpers._url('archiveTask', 'TaskQueueData', 'tao'),
                downloadUrl : helpers._url('downloadTask', 'TaskQueueData', 'tao')
            })
            .init()
            .render($queueArea);

            $('#export-delivery-results').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var toSend = {
                    'uri': '<?= get_data("uri"); ?>'
                };
                $.ajax({
                    url: '<?= get_data("create-task-callback-url"); ?>',
                    type: "POST",
                    data: toSend,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            feedback().success(response.message);
                        } else {
                            feedback().error(__('Something went wrong during task creation.'));
                        }
                    }
                });
            });
        });
</script>